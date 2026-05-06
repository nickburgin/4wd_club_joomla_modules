<?php
/**
 * @version 	5.3
 * @copyright	Copyright (C) 2011 - 2019 Glenn Arkell. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Plugin\Task\Gasubscriptions\Extension;

use \Joomla\CMS\Extension\ExtensionHelper;
use \Joomla\CMS\Mail\Exception\MailDisabledException;
use \Joomla\CMS\Mail\MailTemplate;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Plugin\CMSPlugin;
use \Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use \Joomla\Component\Scheduler\Administrator\Task\Status;
use \Joomla\Component\Scheduler\Administrator\Task\Task;
use \Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use \Joomla\Component\Scheduler\Administrator\Rule\ExecutionRulesRule;
use \Joomla\Database\DatabaseAwareTrait;
use \Joomla\Database\ParameterType;
use \Joomla\Event\SubscriberInterface;
use \Joomla\CMS\Component\ComponentHelper;
use \PHPMailer\PHPMailer\Exception as phpMailerException;
use \GlennArkell\Component\Gausers\Administrator\Helper\GainvoiceHelper;
use \JLoader;

\defined('_JEXEC') or die;

/**
 * Additional information for the profile plugin.
 */
final class Gasubscriptions extends CMSPlugin implements SubscriberInterface
{
	// use methods from the TaskPluginTrait -- very important!
    use DatabaseAwareTrait;
    use TaskPluginTrait;

    private const RULE_TYPE_FIELD = "execution_rules.rule-type";

	// Map supported tasks to some useful data
	private const TASKS_MAP =  [
        	'renewal_task_id' => [
            	'langConstPrefix' => 'PLG_TASK_GASUBSCRIPTIONS_FREQUENCY',
            	'form' => 'renewalForm',
            	'method' => 'renewalTask',
        	],
        	/* more tasks can be added if required */
    	];

	/**
	 * Load the language file on instantiation.
	 * @var    boolean
	 */
    protected $autoloadLanguage = true;

	/**
	 * Triggered from the component controller
	 * @return	boolean
	 */
	public static function getSubscribedEvents(): array
	{
		return [
		'onTaskOptionsList' => 'advertiseRoutines',
		'onExecuteTask' => 'standardRoutineHandler',
		'onContentPreparForm' => 'enhanceTaskItemForm',
		];
	}
    
    /**
     * this is the method called when task is triggered
     * Note this is the method key of the renewalTask in the TASKS_MAP!
     */
    private function renewalTask(ExecuteTaskEvent $event) : int
    {
        $lang = $this->getApplication()->getLanguage();
        $lang->load('com_scheduler', JPATH_ADMINISTRATOR, 'en-GB', true, true);
        $lang->load('plg_task_gasubscriptions', JPATH_ADMINISTRATOR, 'en-GB', true, true);
        $lang->load('plg_task_gasubscriptions', JPATH_ADMINISTRATOR, null, true, false);
        // get the component model
        $compFactory  = Factory::getApplication()->bootComponent('com_gausers')->getMVCFactory();
        $fmodel  = $compFactory->createModel('Currentuserform', 'Site', array('ignore_request' => true));

        // Your task goes here
        // plugin config parameters in manifest can be retrieved by
        //$specialParam = $this->params->get('user_group', 1);
        // task entry params can be retrieved by
        //$uGroup  = $event->getArguments('params')->user_group;

        $taskType  = $event->getArgument('routineId');
        // get the rules
        $rules = $event->getArgument('subject')->get('execution_rules');


        $ruleType = $rules->{'rule-type'};
        $ruleInter = $rules->{$ruleType};
        $intType = STRTOUPPER(substr($ruleType,strpos($ruleType, '-')+1));

        $date = Factory::getDate();
        //$modDate = $date->modify('-'.$runFreq.' '.$freqType);
        $modDate = $date->modify('-'.$ruleInter.' '.$intType);
        $endDate = date_format($modDate,'Y-m-d');

        $baseURL  = Uri::base();
        $baseURL  = rtrim($baseURL, '/');
        $baseURL .= (substr($baseURL, -13) !== 'administrator') ? '/administrator/' : '/';
        $uri      = new Uri($baseURL);

        //$this->getApplication()->triggerEvent('onBuildAdministratorLoginURL', [&$uri]);

        //$this->logTask($lang->_('PLG_TASK_GASUBSCRIPTIONS_ERROR_NO_PARAMS'), 'notice');

        $mbrRenewals = $this->getInvoiceMbrs($endDate);
        //$this->getApplication()->setUserState('com_gausers.test.data', $mbrRenewals);

        if (empty($mbrRenewals)) {
            $this->logTask($lang->_('PLG_TASK_GASUBSCRIPTIONS_NO_RENEWALS_REQ'), 'notice');
            return Status::OK;
        }

        foreach ($mbrRenewals as $mbr) {
            $processOK = $this->checkInvoicedAlready($mbr);
            if ($processOK) {
                $message = Text::sprintf('PLG_TASK_GASUBSCRIPTIONS_ALREADY_INVOICED',$mbr->name);
                $this->logTask($lang->_($message), 'notice');
                continue;
            }

            $params = ComponentHelper::getParams('com_gausers');
            $exclude_member  = $params->get('exclude_member');
            $mship_exempt  = $params->get('mship_exempt');
            $mship_extend  = $params->get('mship_extend');
            $profile_suffix  = $params->get('profile_suffix');
            $local_profile = 'profile'.$profile_suffix;

            //$this->getApplication()->setUserState('com_gausers.test.data', $mbr->name);
            if (in_array($mbr->user_id, $exclude_member)) {
                $message = Text::sprintf('PLG_TASK_GASUBSCRIPTIONS_FREQUENCY_EXCLUDE',$mbr->name);
                $this->logTask($lang->_($message), 'notice');
                continue;
            }

            // check if renewal required
            if (in_array($mbr->mship_id, $mship_exempt)) {
                $message = Text::sprintf('PLG_TASK_GASUBSCRIPTIONS_TYPE_EXCLUDE',$mbr->name);
                $this->logTask($lang->_($message), 'notice');
                continue;
            }

            if (in_array($mbr->mship_id, $mship_extend)) {
                $nextDate = $date->modify('+'.$mbr->mship_term.' '.$mbr->term_type);
                $this->updateExpiryDate($mbr->user_id, $nextDate);
                $message = Text::sprintf('PLG_TASK_GASUBSCRIPTIONS_ENDDATE_EXTENDED',$mbr->name);
                $this->logTask($lang->_($message), 'notice');
                continue;
            }

            // update user to be deactivated
            $this->updateUser($mbr->user_id, 'block', '1');
            $message1 = Text::sprintf('PLG_TASK_GASUBSCRIPTIONS_MEMBER_DEACTIVATED',$mbr->name);
            $this->logTask($lang->_($message1), 'notice');

            // create a renewal invoice record & send
            $inv = $fmodel->generateInvoice($mbr->user_id, $mbr->user_id);
            //$inv = GainvoiceHelper::mainInvoiceCreation($mbr->user_id, $mbr->user_id, $params);
            $message2 = Text::sprintf('PLG_TASK_GASUBSCRIPTIONS_MEMBER_RENEWAL',$mbr->name);
            $this->logTask($lang->_($message2), 'notice');
        }

        return Status::OK;
    }

	/**
	 * Check if member has already been invoiced
	 * @param   object  $mbr - last paid invoice record
	 * @return  boolean
	 */
    private function checkInvoicedAlready($mbr)
	{
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $query->select(' a.* ' );
		$query->from($db->quoteName('#__gausers_invoices', 'a'));
		$query->where($db->quoteName('a.end_date').' > '.$db->quote($mbr->end_date) );
		$query->where($db->quoteName('a.user_id').' = '.(int) $mbr->user_id );
		$query->where($db->quoteName('a.state').' IN (0,1) ' );
        $db->setQuery($query);
		try {
			$inv = $db->loadObject();
		} catch (RuntimeException $e) {
			$this->logTask($e->getMessage());
			return Status::KNOCKOUT;
		}
		
        return !empty($inv) ? true : false;
	}

	/**
	 * Get all the invoices paid with an expiry date of today
	 * @param   date  $endDate - date expiry
	 * @return  boolean
	 */
    private function getInvoiceMbrs($endDate)
	{
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $query->select(' a.*, b.title, b.subscrib_amt, b.term_type, b.mship_term, u.name, u.email ' );
		$query->from($db->quoteName('#__gausers_invoices', 'a'));
		$query->join('LEFT', $db->quoteName('#__gausers_mshiptypes', 'b').' ON '.$db->quoteName('b.id').' = a.mship_id ' );
		$query->join('LEFT', $db->quoteName('#__users', 'u').' ON '.$db->quoteName('u.id').' = a.user_id' );
		$query->where($db->quoteName('a.end_date').' = '.$db->quote($endDate) );
		$query->where($db->quoteName('a.state').' = '.(int) 2 );
        $db->setQuery($query);
		try {
			$invMbrs = $db->loadObjectList();
			$this->logTask('Retrieved Invoice records for members requiring renewals.', 'notice');
			return $invMbrs;
		} catch (RuntimeException $e) {
			$this->logTask($e->getMessage());
			return Status::KNOCKOUT;
		}
	}

	/**
	 * Update member record as disabled
	 * @param   int  $id - user id
	 * @param   date $nextDate - new end date
	 * @return  boolean
	 */
    private function updateExpiryDate($id, $nextDate)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__gausers_invoices'));
        $query->set($db->quoteName('end_date') . ' = ' . $db->quote($nextDate) );
		$query->where($db->quoteName('id') . ' = ' . (int) $id );

        $db->setQuery($query);
		try {
			$execOK = $db->execute();
			$this->logTask('Update Expiry due to Extended Membership Type', 'notice');
			return $execOK;
		} catch (RuntimeException $e) {
			$this->logTask($e->getMessage());
			return Status::KNOCKOUT;
		}
	}

	/**
	 * Update member record as disabled
	 * @param   int  $id - user id
	 * @param   date $nextDate - new end date
	 * @return  boolean
	 */
    private function updateUser($id)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__users'));
        $query->set($db->quoteName('block') . ' = ' . (int) 1 );
		$query->where($db->quoteName('id') . ' = ' . (int) $id );

        $db->setQuery($query);
		try {
			$execOK = $db->execute();
			$this->logTask('Update User to Disable Account until Renewed', 'notice');
			return $execOK;
		} catch (RuntimeException $e) {
			$this->logTask($e->getMessage());
			return Status::KNOCKOUT;
		}
	}

}
