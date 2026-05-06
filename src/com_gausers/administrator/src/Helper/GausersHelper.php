<?php
/**

 * @version     6.0.0                                                     
 * @package     com_gausers
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gausers\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\Data\DataObject;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Filesystem\Path;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\User\User;
use Joomla\CMS\Helper\UserGroupsHelper;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Access\Access;
use Joomla\Database\ParameterType;   //INTEGER, STRING, BOOLEAN, NULL, LARGE_OBJECT
use GlennArkell\Component\Gausers\Administrator\Helper\GaregistrationHelper;
use GlennArkell\Component\Gausers\Administrator\Helper\GainvoiceHelper;
use GlennArkell\Component\Gausers\Administrator\Helper\MdpdfHelper;
use GlennArkell\Component\Gausers\Administrator\Helper\GanamesHelper;
use GlennArkell\Component\Gausers\Administrator\Helper\GaemailHelper;

/**
 * Gausers helper.
 */
class GausersHelper
{
	/**
	 * Build the HTTP query array
	 * @param array of the http query if the http query already prepared and we want to add more
	 * @param string view or a task
	 * @param string control method to be used
	 * @param string reference indicator
	 * @param string/int reference string or id
	 * @return array
	 * @ hint - this is used with http_build_query($query_string, '', '&amp;') to build a clean url
	 */
	public static function getHTTPQuery($existQ = null, $viewTask = 'view', $contModel = 'invoice', $ref = 'id', $linkId = 0)
	{
		if (!$existQ) {
			$query_string = array();
			$query_string['option'] = 'com_gausers';
			$query_string[$viewTask] = $contModel;
			if ($ref) {
				$query_string[$ref] = $linkId;
			}
		} else {
			$query_string = $existQ;
			$query_string[$ref] = $linkId;
		}

		return $query_string;
	}

	/**
	 * Build the HTTP query array for the Scheduled Task
	 */
	public static function getHTTPQueryAjax($plg = null, $format = 'json', $group = 'system', $taskID = 0)
	{
		$query_string = array();
		$query_string['option'] = 'com_ajax';
		$query_string['format'] = $format;
		$query_string['plugin'] = $plg;
		$query_string['group'] = $group;
		$query_string['id'] = $taskID;
		return $query_string;
	}

    /**
     * Gets todays date object based on global timezone settings
     */
    public static function getTodaysDate()
	{
		//$tz = Factory::getConfig()->get('offset');
		//$date = Factory::getDate('now', $tz);
		//$today = date_format($date,'Y-m-d H:i:s');
		// TODO need to swap over to this for todays date
		//$date = Factory::getDate()->toSql();
		$date = Factory::getDate();

		return $date;
	}

    /**
     * Prints out a variable value in human readable format
     */
    public static function gaPrint($val){
        echo '<pre>Test<br />';
        \print_r($val);
        echo  '</pre>';
    }

    /**
     * Load template over-rides when using modal view
     * This is important for Cloud White template scheme
     */
    public static function loadTmplStyleModal($wa)
	{
        $tmpl = Factory::getApplication()->getTemplate(true);
        if ($tmpl->params->get('colorName') == 'colors_white') {
        	$wa->addInlineStyle('.btn-primary {background-color:'.$tmpl->params->get('btnPbgColor').' !important;}');
        	$wa->addInlineStyle('.nav-link {color:var(--template-contrast) !important;}');
        	$wa->addInlineStyle('.form-check-input:checked, .form-select[multiple] option:checked, [multiple].custom-select option:checked {background-color:'.$tmpl->params->get('btnPbgColor').' !important;}');
        	$wa->addInlineStyle('.page-item.active .page-link {color: var(--rkic41site-color-link); background-color: #c1cee1 !important; border-color: #dfe3e7 !important;}');
        	$wa->addInlineStyle('.form-select[multiple] option:checked, [multiple].custom-select option:checked {background-color: var(--rkic41site-color-primary-border) !important;}');
        }
		return true;
	}

    /**
     * Gets the user record for the specific id reference
     *  $user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($id);
     */
    public static function getSpecificUser($id)
	{
// 		if ($id) {
// 			//$container = Factory::getContainer();
// 			//$userFactory = $container->get(UserFactoryInterface::class);
// 			//$user = $userFactory->loadUserById($id);
// 			$user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($id);
// 		} else {
// 			$user = Factory::getApplication()->getIdentity();
// 		}
		$user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($id);
		unset($user->password);
		unset($user->password_clear);
		unset($user->activation);
		unset($user->params);
		unset($user->lastResetTime);
		unset($user->resetCount);
		unset($user->otpKey);
		unset($user->otep);
		unset($user->requireReset);

		return $user;
	}

	/**
	 * Gets the files attached to an item
	 * @param   int     $pk     The item's id
	 * @param   string  $table  The table's name
	 * @param   string  $field  The field's name
	 * @return  array  The files
	 */
	public static function getFiles($pk, $table, $field)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query
			->select($field)
			->from($table)
			->where('id = ' . (int) $pk);

		$db->setQuery($query);

		return explode(',', $db->loadResult());
	}

    /**
     * Gets the edit permission for an user
     * @param   mixed  $item  The item
     * @return  bool
     */
    public static function canUserEdit($item)
    {
        $permission = false;
        $user       = Factory::getApplication()->getIdentity();

        if ($user->authorise('core.edit', 'com_gausers')) {
            $permission = true;
        } else {
            if (isset($item->created_by)) {
                if ($user->authorise('core.edit.own', 'com_gausers') && $item->created_by == $user->id) {
                    $permission = true;
                }
            } else {
                $permission = true;
            }
        }

        return $permission;
    }

	/**
	 * Returns valid contexts
	 * @return  array
	 * @since   3.7.0
	 */
	public static function getContexts()
	{
		Factory::getLanguage()->load('com_gausers', JPATH_ADMINISTRATOR);

		$contexts = array(
			'com_gausers.currentuser' => Text::_('COM_GAUSERS_TITLE_CURRENTUSERS'),
			'com_gausers.clubexec' => Text::_('COM_GAUSERS_TITLE_CLUBEXECS'),
		);

		return $contexts;
	}

	public static function validateSection($section, $item)
	{
		if (Factory::getApplication()->isClient('site') && $section == 'form') {
			return 'gausers';
		}
		if ($section != 'gausers' && $section != 'form') {
			return null;
		}

		return $section;
	}

	/**
	 * Get the version of the component
	 * @return version element of manifest
	 */
	public static function getComponentVersion()
	{
		$componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/com_gausers/gausers.xml'));

		return $componentXML['version'];
	}

    /**
    *   Method to get the required record
    *   @param string $table table name
    *   @param int $id id reference
    *   @return object record data
    */
	public static function getRecord($table, $field, $id)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' * ');
		$query->from($db->quoteName($table));
		if (is_numeric($id)) {
            $query->where($db->quoteName($field).' = '.(int) $id );
        } else {
            $query->where($db->quoteName($field).' = '.$db->quote($id) );
        }
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Record');
	        return false;
	    }

	}

    /**
    *   Method to get the required record
    *   @param string $table table name
    *   @param string $field field name to match on the id reference
    *   @param int $id id reference
    *   @return object record data
    */
	public static function getRecordList($table, $field, $id, $order = 'a.created_date', $dir = 'DESC')
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.* ');
		$query->from($db->quoteName($table, 'a'));
		$query->where($db->quoteName($field).' = '.(int) $id );
		if ($order) {
            $query->order($order.' '.$dir);
        }
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' RecordList');
	        return false;
	    }

	}

	/**
	 * Get the generic list options
	 */
	public static function getListOptions($table = '#__fields', $label = 'Custom Field', $field = 'a.title' )
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
// 		if ($table == '#__gausers_mshiptypes') {
//             $query->select(' "0" as "value", " - Select '.$label.' - " as "text" UNION SELECT a.id as value, '.$field.' as text ');
// 		} else {
//             $query->select(' "0" as "value", " - Select '.$label.' - " as "text" UNION SELECT a.id as value, '.$field.' as text ');
// 		}
		$query->select(' "0" as "value", " - Select '.$label.' - " as "text" UNION SELECT a.id as value, '.$field.' as text ');
		$query->from( $table . ' AS a ' );
		if ($table == '#__users') {
			$query->where(' a.block = 0' );
		} else {
			$query->where(' a.state = 1' );
		}
		$query->order(' text ASC ' );
		$db->setQuery((string)$query);

	    try {
	        $options = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

		return $options;
	}

    /**
    *   Method to get a list of records
    *   @return array of object record data
    */
	public static function getAllMshiptypes($availOpts = 0)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
        if ($availOpts) {
    		$params = ComponentHelper::getParams('com_gausers');
    		$ignoreMship  = implode(",", $params->get( 'ignoreMship', array()));
            $query->select(' "0" as "value", " - Select Membership Type - " as "text" UNION SELECT id as value, title as text ');
            $query->where(' id NOT IN ('.$ignoreMship.')' );
        } else {
            $query->select(' *, id as mship_id ');
        }
		$query->from(' #__gausers_mshiptypes ');
		$query->where(' state = '.(int) 1 );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Membership Type');
	        return false;
	    }

	}

    /**
    *   Method to get the required record
    *   @param int $id id reference
    *   @return object record data
    */
	public static function getMshiptypeID($id = 0)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' *, id as mship_id ');
		$query->from(' #__gausers_mshiptypes ');
		$query->where(' id = '.(int) $id );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Membership Type');
	        return false;
	    }

	}

    /**
    *   Method to get the required record
    *   @param string $title
    *   @return object record data
    */
	public static function getMshiptypeTitle($title = '')
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' * ');
		$query->from(' #__gausers_mshiptypes ');
		$query->where(' title = '.$db->quote($title) );
		$query->where(' state = 1 ' );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Membership Type');
	        return false;
	    }

	}

    /**
    *   Method to get all the local profile fields available
    *   @param int $id field_id reference
    *   @param int $userId user id reference
    *   @return object field name and value
    */
	public static function getLocalProfileOptions()
	{
		$params = ComponentHelper::getParams('com_gausers');
		$profile_suffix  = $params->get( 'profile_suffix', 'b4wdc' );
		$locProf = 'profile'.$profile_suffix;
		$profLen = strlen($locProf);
        $flds = GaauditHelper::getProfileFields($locProf);
        \asort($flds);
        return $flds;
//         $db		= Factory::getContainer()->get('DatabaseDriver');
// 		$query	= $db->getQuery(true);
//         $query->clear();
// 		$query->select(' "" as "value", " - Select Profile Fields - " as "text" UNION SELECT DISTINCT(substr(a.profile_key,'.(int)($profLen+1).')) as "value", substr(a.profile_key,'.(int)($profLen+1).') as "text"  ');
// 		$query->from(' #__user_profiles AS a ');
// 		$query->where(' substr(a.profile_key,1,'.(int)$profLen.') = '.$db->quote($locProf));
// 		$query->order(' text ASC ');
// 		$db->setQuery((string)$query);
// 
// 	    try {
// 	        return $db->loadObjectList();
// 	    } catch (RuntimeException $e) {
// 	        Factory::getApplication()->enqueueMessage($e->getMessage().' Local Profile Values');
// 	        return false;
// 	    }

	}

    /**
    *   Method to get all the local profile fields available
    *   @param int $id field_id reference
    *   @param int $userId user id reference
    *   @return object field name and value
    */
	public static function getProfileGroupOptions()
	{
		$params = ComponentHelper::getParams('com_gausers');
		$profile_suffix  = $params->get( 'profile_suffix', 'b4wdc' );
		$profile_gp  = $params->get( 'profile_group', 'locgrp' );
		$locProf = 'profile'.$profile_suffix.'.'.$profile_gp;

        // remove quotes so no duplicates
        $dbU		= Factory::getContainer()->get('DatabaseDriver');
		$queryU	= $dbU->getQuery(true);
        $queryU->clear();
		$dbU->setQuery('UPDATE #__user_profiles SET profile_value = REPLACE(profile_value,\'"\', \'\') WHERE profile_key = '.$dbU->quote($locProf));
		if (!$dbU->execute()) {
            Factory::getApplication()->enqueueMessage('Remove Local Profile Values Quotes Failed');
        }

        // now get the disting options for this profile field
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "" as "value", " - Select Profile Group - " as "text" UNION SELECT DISTINCT(a.profile_value) as "value", a.profile_value as "text"  ');
		$query->from(' #__user_profiles AS a ');
		$query->join('LEFT', ' #__users AS b ON b.id = a.user_id AND b.block = 0');
		$query->where(' a.profile_key = '.$db->quote($locProf));
		$query->where(' b.name IS NOT NULL ');
		$query->group(' value ');
		$query->order(' text ASC ');
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Local Profile Values');
	        return false;
	    }

	}

    /**
    *   Method to get values for a member based on field id
    *   @param int $id field_id reference
    *   @param int $userId user id reference
    *   @return object field name and value
    */
	public static function getCustomFieldValue($id = 0, $userId = 0)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.field_id AS id, b.title AS fieldName, a.value AS fieldValue ');
		$query->from(' #__fields_values AS a ');
		$query->join('LEFT', ' #__fields AS b ON b.id = a.field_id');
		$query->where(' a.field_id = '.(int) $id );
		$query->where(' a.item_id = '.(int) $userId );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Field Values');
	        return false;
	    }

	}

	public static function getUserGroupName($id = 0)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.title ');
		$query->from(' #__usergroups AS a ');
		$query->where(' a.id = '.(int) $id );
		$db->setQuery((string)$query);

	    try {
	        // If it fails, it will throw a RuntimeException
	        return $db->loadResult();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Group Name');
	        return false;
	    }

	}

    public static function updateExtensionParams($compname = 'com_gausers')
	{
        // update the configuration parameter
        $comptype = 'component';

        // get the payment date value in the configuration data
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
        $query->select(' a.* ');
		$query->from(' #__extensions as a ');
        $query->where(' a.name = '.$db->Quote($compname) );
        $query->where(' a.type = '.$db->Quote($comptype) );
        $query->where(' a.element = '.$db->Quote($compname) );
		$db->setQuery((string)$query);

	    try {
	        $configdata = $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Getting Parameters for Extension');
	        return false;
	    }

		///////////////////////////////////////////////////////////

        $configparams = json_decode($configdata->params);
        $new_invDate = new Date($configparams->invoice_date);
        $new_invDate = $new_invDate->modify('+1 YEAR');
        $new_invDate = $new_invDate->format('Y-m-d');
        $configparams->invoice_date = $new_invDate;

        if ($configparams->use_cutoff) {
            $new_offDate = new Date($configparams->cutoff_date);
            $new_offDate = $new_offDate->modify('+1 YEAR');
            $new_offDate = $new_offDate->format('Y-m-d');
            $configparams->cutoff_date = $new_offDate;
        }
        // load back into object
        $configparams = json_encode($configparams);
        $configdata->params = $configparams;

        Factory::getContainer()->get('DatabaseDriver')->updateObject('#__extensions', $configdata, 'extension_id');

		///////////////////////////////////////////////////////////
        return true;
	}

    public static function getMemberInvoices($id = 0)
	{
		$params = ComponentHelper::getParams('com_gausers');
		$mship_period  = $params->get( 'mship_period', 1 );    // 1=Financial, 2=Calendar
        $lastInvDate  = $params->get( 'invoice_date');   // this is the last invoice generated date

        if ($id) {
			$db		= Factory::getContainer()->get('DatabaseDriver');
			$query	= $db->getQuery(true);
	        $query->clear();
			$query->select(' a.*, c.title AS pay_type_name ');
			$query->select(' date_format(a.created_date,"%Y") AS created_year ');
			$query->select(' date_format(a.created_date,"%m") AS created_month ');
			$query->select(' date_format(a.end_date,"%Y") AS end_date_year ');
			$query->select(' date_format(a.end_date,"%b") AS end_date_month ');
			$query->select(' date_format('.$db->quote($lastInvDate).',"%Y") AS renewal_year ');
			$query->select(' date_format('.$db->quote($lastInvDate).',"%m") AS renewal_month ');
            if ($mship_period == 1) {
				$query->select(' date_format(a.created_date,"%Y") AS start_fin_term ');
				$query->select(' date_format(a.created_date,"%Y") AS start_fin_year ');
				$query->select(' date_format(date_add(a.created_date,INTERVAL 1 YEAR),"%Y") AS end_fin_year ');
				$query->select(' date_format(a.end_date,"%Y") AS end_fin_term ');
			} else {
				$query->select(' CONCAT("Jan-",(date_format(a.created_date,"%Y")+1)) AS start_fin_term ');
				$query->select(' CONCAT("Jan-",(date_format(a.created_date,"%Y")+1)) AS start_fin_year ');
				$query->select(' CONCAT("Dec-",(date_format(a.created_date,"%Y")+1)) AS end_fin_year ');
				$query->select(' CONCAT("Dec-",(date_format(a.end_date,"%Y"))) AS end_fin_term ');
			}
			$query->select(' if(a.state=1,"Waiting Payment",if(a.state=2,"Paid",if(a.state=-2,"Cancelled","unknown"))) AS status ');
			$query->select(' CONCAT("Invoice", LPAD(a.id, 6, 0)) AS inv_no, m.mship_term, m.term_type, m.title ');
			$query->from(' #__gausers_invoices AS a ');
			$query->join('LEFT', '#__gausers_mshiptypes AS m ON m.id = a.mship_id');
			$query->join('LEFT', '#__categories AS c ON c.id = a.pay_type');
			$query->where(' a.user_id = '.(int) $id );
			$query->order(' a.created_date DESC ');
			$db->setQuery((string)$query);

		    try {
		        return $db->loadObjectList();
		    } catch (RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage($e->getMessage().' Member Invoices');
		        return 0;
		    }
	    } else {
			return 0;
	    }

	}

    public static function getUnpaidInvoiceUsers()
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.* ');
		$query->from(' #__gausers_invoices AS a ');
		$query->where(' ( a.paid_date IS NULL OR a.paid_date = '.$db->Quote('0000-00-00 00:00:00').' )' );
		$query->where(' a.state = '.(int) 1 );
		$db->setQuery((string)$query);

	    try {
	        // If it fails, it will throw a RuntimeException
	        return $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Unpaid Invoices');
	        return false;
	    }

	}

    /**
    *   Method to create a finance record
    *   @param $data array includes $data['pay_type'] being category (extension=com_gausers.payment)
    */
    public static function createFinanceTrans($data)
	{
        $fin_accnt = GausersHelper::getRecord("#__categories", "id", $data['pay_type'])->note;
        $params = ComponentHelper::getParams('com_gausers');
        $finance_cat  = $params->get('finance_cat');
        $finance_accnt  = !empty($fin_accnt) ? $fin_accnt : $params->get('finance_accnt', 1);
        //$pp_cat  = $params->get('pp_cat');
        //$cc_cat  = $params->get('cc_cat');
        $membership_desc  = $params->get('membership_desc');
        $temp_mship  = $params->get('temp_mship', 0);
        $membership_desc  = $data['mship_id'] == $temp_mship ? 'Temporary Membership' : $membership_desc;

		$db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->insert(' #__gafinance_transactions ');
		$query->set(' created_date = '.$db->Quote($data['paid_date']) );
		$query->set(' tran_date = '.$db->Quote($data['paid_date']) );
		$query->set(' user_id = '. (int) $data['user_id'] );
		$query->set(' tran_amount = '.$db->Quote($data['invoice_amt']) );
		$query->set(' tran_ref = '.$db->Quote('Invoice '.$data['id']) );
		$query->set(' tran_type = "I" ' );
		$query->set(' cat_id = '. (int) $finance_cat );
		$query->set(' accnt_id = '. (int) $finance_accnt );
		$query->set(' tran_desc = '.$db->Quote($membership_desc) );
		$query->set(' comment = "Auto Loaded from Members Invoicing" ' );
		$db->setQuery((string)$query);

        if (!$db->execute()) {
            throw new \Exception(500, $db->getErrorMsg());
            return false;
        }
        return true;

	}

    /**
    *   Method to get all member data
    */
    public static function getMembersDetails($params, $onlyfinancial = 1)
	{
        $settotest  = $params->get('set_test');
        $testid  = $params->get('user_id');     /* user ID */
        $sendto_group  = $params->get('sendto_group');
        $xclude = $params->get('exclude_member');     /* user ID */
        $adminuser  = $params->get('admin_id');
        $finmembers   = $params->get('include_userfilter');    /* filter on user status */
        $discount_allowed  = $params->get('discount_allowed');
        $discount_switch  = $params->get('discount_switch');
        $profile_suffix  = $params->get('profile_suffix');
        $local_profile = 'profile'.$profile_suffix;
        if (isset($xclude)) {
			$xclude = implode(",",$xclude);
		} else {
			$xclude = 0;
		}

		$db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(
                [
                    $db->quoteName('a.id'),
                    $db->quoteName('a.id', 'user_id'),
                    $db->quoteName('a.name'),
                    $db->quoteName('a.email'),
                    $db->quoteName('b.profile_value', 'altemail'),
                    $db->quoteName('c.profile_value', 'inc_altemail'),
                    "SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 1), ' ', -1) AS firstname",
                    "SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 1), ' ', -1) AS first_name",
                    "If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 2), ' ', -1) ,NULL) as middle1_name",
                    "If( If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 3), ' ', -1), NULL) = SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1), NULL, If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 3), ' ', -1), NULL)) as middle2_name",
                    "If( If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1), NULL) = SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 5), ' ', -1), NULL, If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1), NULL)) as middle3_name",
                    "SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 5), ' ', -1) AS surname",
                    "SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 5), ' ', -1) AS last_name",
                ]
            );
		$query->select(' d.profile_value as address1, e.profile_value as address2, f.profile_value as suburb, fr.profile_value as region ');
		$query->select(' g.profile_value as pcode, h.profile_value as memtype, j.profile_value as partner, k.profile_value as altphone ');
		$query->select(' l.profile_value as fwdvic_no, p.profile_value as phone, a.registerDate, DATE_FORMAT(a.registerDate, "%Y-%m-%d") as regoDate ');
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(j.profile_value, ' ', 1), ' ', -1) AS firstnamep " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(j.profile_value, ' ', 1), ' ', -1) AS first_namep " );
        $query->select(" If( length(j.profile_value) - length(replace(j.profile_value, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(j.profile_value, ' ', 2), ' ', -1) ,NULL) as middle1_namep " );
        $query->select(" If( If( length(j.profile_value) - length(replace(j.profile_value, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(j.profile_value, ' ', 3), ' ', -1) ,NULL) = SUBSTRING_INDEX(SUBSTRING_INDEX(j.profile_value, ' ', 4), ' ', -1), null, If( length(j.profile_value) - length(replace(j.profile_value, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(j.profile_value, ' ', 3), ' ', -1) ,NULL)) as middle2_namep " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(j.profile_value, ' ', 5), ' ', -1) AS surnamep " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(j.profile_value, ' ', 5), ' ', -1) AS last_namep " );
		$query->select(' s.profile_value as use_post, n.profile_value as postal_address1, o.profile_value as postal_address2 ');
		$query->select(' q.profile_value as postal_suburb, qr.profile_value as postal_region, r.profile_value as postal_pcode ');
		$query->select(' cty.profile_value as country, pcty.profile_value as postal_country ');
		if ($discount_allowed) {
			$query->select(' i.profile_value as "'.$discount_switch.'"');
		}
		$query->from(' #__users as a')
    		->join('RIGHT',$db->quoteName('#__user_usergroup_map', 'm') . ' ON m.group_id = :gpid AND m.user_id = a.id');
		$query->join('LEFT','#__user_profiles AS b ON b.user_id = a.id AND b.profile_key = "'.$local_profile.'.altemail" ');
		$query->join('LEFT','#__user_profiles AS c ON c.user_id = a.id AND c.profile_key = "'.$local_profile.'.inc_altemail" ');
		$query->join('LEFT','#__user_profiles AS d ON d.user_id = a.id AND d.profile_key = "profile.address1" ');
		$query->join('LEFT','#__user_profiles AS e ON e.user_id = a.id AND e.profile_key = "profile.address2" ');
		$query->join('LEFT','#__user_profiles AS f ON f.user_id = a.id AND f.profile_key = "profile.city" ');
		$query->join('LEFT','#__user_profiles AS fr ON fr.user_id = a.id AND fr.profile_key = "profile.region" ');
		$query->join('LEFT','#__user_profiles AS g ON g.user_id = a.id AND g.profile_key = "profile.postal_code" ');
		$query->join('LEFT','#__user_profiles AS h ON h.user_id = a.id AND h.profile_key = "'.$local_profile.'.memtype" ');
		$query->join('LEFT','#__user_profiles AS j ON j.user_id = a.id AND j.profile_key = "'.$local_profile.'.partner" ');
		$query->join('LEFT','#__user_profiles AS k ON k.user_id = a.id AND k.profile_key = "'.$local_profile.'.altphone" ');
		$query->join('LEFT','#__user_profiles AS l ON l.user_id = a.id AND l.profile_key = "'.$local_profile.'.fwdvic_no" ');
		$query->join('LEFT','#__user_profiles AS s ON s.user_id = a.id AND s.profile_key = "'.$local_profile.'.use_post" ');
		$query->join('LEFT','#__user_profiles AS n ON n.user_id = a.id AND n.profile_key = "'.$local_profile.'.postal_address1" ');
		$query->join('LEFT','#__user_profiles AS o ON o.user_id = a.id AND o.profile_key = "'.$local_profile.'.postal_address2" ');
		$query->join('LEFT','#__user_profiles AS p ON p.user_id = a.id AND p.profile_key = "profile.phone" ');
		$query->join('LEFT','#__user_profiles AS q ON q.user_id = a.id AND q.profile_key = "'.$local_profile.'.postal_city" ');
		$query->join('LEFT','#__user_profiles AS qr ON qr.user_id = a.id AND qr.profile_key = "'.$local_profile.'.postal_region" ');
		$query->join('LEFT','#__user_profiles AS r ON r.user_id = a.id AND r.profile_key = "'.$local_profile.'.postal_post_code" ');
		$query->join('LEFT','#__user_profiles AS cty ON cty.user_id = a.id AND cty.profile_key = "profile.country" ');
		$query->join('LEFT','#__user_profiles AS pcty ON pcty.user_id = a.id AND pcty.profile_key = "'.$local_profile.'.postal_country" ');
		if ($discount_allowed) {
			$query->join('LEFT','#__user_profiles AS i ON i.user_id = a.id AND i.profile_key = "'.$local_profile.'.'.$discount_switch.'" ');
		}
		if ($onlyfinancial) {
    		$query->where('a.block = 0');
        }
		if ($settotest) {
            $query->where('a.id = :testid')
                ->bind(':testid', $testid, ParameterType::INTEGER);
        } else {
    		if ($xclude) {
				$query->where('a.id NOT IN (:xclude)')
				->bind(':xclude', $xclude, ParameterType::STRING);
   			}
    		$query->where('a.id != :adminid')
                ->bind(':adminid', $adminuser, ParameterType::INTEGER);
        }
		$query->order('surname ASC')
    		->bind(':gpid', $sendto_group, ParameterType::INTEGER);
        $db->setQuery($query);

	    try {
	        $itemList = $db->loadObjectList();
	        $members = array();

	        foreach ($itemList as $item) {
                // remove quote marks from the profile data
                $item->privacy = isset($item->privacy) ? str_replace('"','',$item->privacy) : 0;
                $item->use_post = isset($item->use_post) ? str_replace('"','',$item->use_post) : 0;
                $item->phone = isset($item->phone) ? str_replace('"','',$item->phone) : '';
                $item->partner = isset($item->partner) ? str_replace('"','',$item->partner) : '';
                $item->fwdvic_no = isset($item->fwdvic_no) ? str_replace('"','',$item->fwdvic_no) : 0;
                
                $members[] = $item;
            }
            
            return $members;

	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Get Members Details');
	        return false;
	    }

	}

	/* Get current executive members and club details
	 * params   $params = parameters from module
	 * result   obj list of executives and club details
	 */
	public static function getCurrentExecMembers($params)
	{
		$prof_suffix = $params->get('prof_suffix', 'b4wdc');
		$prof_field = $params->get('prof_field', 'partner');
		$show_past = $params->get('show_past', 0);

		//$news = GausersHelper::getNews();
		$db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.* ');
		$query->select(' b.name AS pres_member, c.profile_value AS pres_partner, d.profile_value AS pres_mbr_image, e.profile_value AS pres_prt_image ');
		$query->select(' f.name AS secr_member, g.profile_value AS secr_partner, h.profile_value AS secr_mbr_image, i.profile_value AS secr_prt_image ');
		$query->select(' j.name AS vpres_member, k.profile_value AS vpres_partner, l.profile_value AS vpres_mbr_image, m.profile_value AS vpres_prt_image ');
		$query->select(' n.name AS tres_member, o.profile_value AS tres_partner, p.profile_value AS tres_mbr_image, q.profile_value AS tres_prt_image ');
		$query->from(' #__gausers_club_execs as a');
		$query->join('LEFT','#__users AS b ON a.pres_id = b.id ');
		$query->join('LEFT','#__user_profiles AS c ON c.user_id = b.id AND c.profile_key = "profile'.$prof_suffix.'.'.$prof_field.'"');
		$query->join('LEFT','#__user_profiles AS d ON d.user_id = b.id AND d.profile_key = "profile'.$prof_suffix.'.m_img"');
		$query->join('LEFT','#__user_profiles AS e ON e.user_id = b.id AND e.profile_key = "profile'.$prof_suffix.'.p_img"');
		$query->join('LEFT','#__users AS f ON a.secr_id = f.id ');
		$query->join('LEFT','#__user_profiles AS g ON g.user_id = f.id AND g.profile_key = "profile'.$prof_suffix.'.'.$prof_field.'"');
		$query->join('LEFT','#__user_profiles AS h ON h.user_id = f.id AND h.profile_key = "profile'.$prof_suffix.'.m_img"');
		$query->join('LEFT','#__user_profiles AS i ON i.user_id = f.id AND i.profile_key = "profile'.$prof_suffix.'.p_img"');
		$query->join('LEFT','#__users AS j ON a.vpres_id = j.id ');
		$query->join('LEFT','#__user_profiles AS k ON k.user_id = j.id AND k.profile_key = "profile'.$prof_suffix.'.'.$prof_field.'"');
		$query->join('LEFT','#__user_profiles AS l ON l.user_id = j.id AND l.profile_key = "profile'.$prof_suffix.'.m_img"');
		$query->join('LEFT','#__user_profiles AS m ON m.user_id = j.id AND m.profile_key = "profile'.$prof_suffix.'.p_img"');
		$query->join('LEFT','#__users AS n ON a.tres_id = n.id ');
		$query->join('LEFT','#__user_profiles AS o ON o.user_id = n.id AND o.profile_key = "profile'.$prof_suffix.'.'.$prof_field.'"');
		$query->join('LEFT','#__user_profiles AS p ON p.user_id = n.id AND p.profile_key = "profile'.$prof_suffix.'.m_img"');
		$query->join('LEFT','#__user_profiles AS q ON q.user_id = n.id AND q.profile_key = "profile'.$prof_suffix.'.p_img"');
		if (!$show_past) {
			$query->where(' a.state = 1 ');
		}
		$query->order(' a.end_term desc ');
		$db->setQuery((string)$query);

	    try {
	        if (!$show_past) {
				$execs = $db->loadObject();
			} else {
				$execs = $db->loadObjectList();
			}
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }

    	return $execs;
	}

	/* Create CSV file of members
	 * params   $today = todays date (YmdHis)
	 * params   $path = path to file location
	 * params   $club = object
	 * params   $members = objectList
	 * result   csv file name
	 */
	public static function createMemberExtract()
	{
		$date = self::getTodaysDate();
		$new_ext_date = date_format($date,'Y-m-d H:i:s');
		$today = date_format($date,'YmdHis');
		$cntr = 0;

	    $app		= Factory::getApplication();
        $fromname	= $app->get('fromname');       // Site name or system name

		$params = ComponentHelper::getParams('com_gausers');
		$extract_members = $params->get('extract_members',0);
		$extract_email = $params->get('extract_email',0);
        $extract_number  = $params->get('extract_number', 0);
        $extract_date  = $params->get('extract_date');
		$club_id = $params->get('club_id',0);
		$extract_excludes = $params->get('extract_excludes');
		$mship_single = $params->get('mship_single');
        $locProf = $params->get('profile_suffix', 'b4wdc');
        $prof = 'profile'.$locProf;

        // get all selected extra elements in an array
        $profile_extract = $params->get('profile_extract');
        $fin_members = $params->get('fin_members', 1);

		$members = self::getMembersDetails($params, $fin_members);

		$extract_dir = $params->get('extract_dir', 'images/members/extracts');
		$path = Path::clean( JPATH_SITE . '/' . $extract_dir . '/' );

		$filename = $path.'Members-'.$club_id.'-'.$today.'.csv';

        if ($locProf == 'docs') {
            $head_data = 'id,status,name,email,address1,address2,suburb,region,pcode,phone,partner_name,partner_phone,partner_email,club_mship_id';
        } elseif ($locProf == 'brb') {
            $head_data = 'id,status,joindate,name,email,address1,address2,suburb,region,pcode,phone,partner_name,partner_phone,partner_email,club_mship_id';
        } else {
            $head_data = 'id,club_name,fwdv_no,name,email,address1,address2,suburb,region,pcode,phone,partner_name,partner_phone,partner_email,club_mship_id';
        }
		
        foreach ($profile_extract AS $proffield) {
            //  add any extra fields to the header
            $head_data .= ','.$proffield;
        }

        $head_data .= "\r\n";
        $mship_data = '';

		foreach ($members AS $m) {

			if ($m->name > '' && !in_array($m->user_id, $extract_excludes)) 
            {
				$profile = UserHelper::getProfile($m->user_id);
				$mship = GainvoiceHelper::getLastInvoiceMship($m->user_id);
				
				$status = $m->block ? 'disabled' : 'enabled';

                if ($locProf == 'docs') {
                    $mship_data .= $m->user_id.','.$status.',';
                } elseif ($locProf == 'brb') {
                    $mship_data .= $m->user_id.','.$status.','.$m->registerDate.',';
                } else {
                    $mship_data .= $club_id.','.$fromname.',';
    				$mship_data .= $m->fwdvic_no.',';
				}

                // setup name field
				if ($mship_single) {
					$mship_data .= GanamesHelper::combineNames($m);
					$mship_data .= ',';
				} else {
					$mship_data .= $m->name.',';
				}

				$mship_data .= $m->email.',';

                // setup address field
				if ($m->use_post) {
					$mship_data .= $m->postal_address1.','.$m->postal_address2.','.$m->postal_suburb.','.$m->postal_region.','.$m->postal_pcode.',';
				} else {
					$mship_data .= $m->address1.','.$m->address2.','.$m->suburb.','.$m->region.','.$m->pcode.',';
				}

				if (!empty($profile_extract) && in_array('phone',$profile_extract)) { $mship_data .= ' ,'; } else { $mship_data .= $m->phone.','; }

                // setup partner fields
				$mship_data .= $m->partner.',';
				if (!empty($profile_extract) && in_array('altphone',$profile_extract)) { $mship_data .= ' ,'; } else { $mship_data .= $m->altphone.','; }
				if (!empty($profile_extract) && in_array('altemail',$profile_extract)) { $mship_data .= ' ,'; } else { $mship_data .= $m->altemail.','; }
				$mship_data .= $m->user_id.',';

				foreach ($profile_extract AS $proffield) {
                    $extVal = str_replace('"', '', $profile->$prof[$proffield]);
                    $extVal = str_replace(',', ' - ', $extVal);
                    //  add any extra fields to the data
                    $mship_data .= $extVal.',';
                }

                $mship_data .= "\r\n";
				$cntr++;
			}
		}

		$output = $head_data;
	    $output .= $mship_data;

	    File::write($filename, $output);

	    $sentOK = self::despatchExtract($fromname, $filename, $extract_email, $extract_number, $extract_date, $cntr, $new_ext_date);

		return $sentOK;
	}

    public static function despatchExtract($fromname, $attachfile, $extract_email, $extract_number, $extract_date, $member_count, $new_ext_date)
	{
  		$subject = 'Club Member Extract - '.$fromname.' ('.$member_count.')';
   		$body = 'Find attached extract of members. The last extract sent ('.substr($extract_date,0,10).') included '.$extract_number.' members and this extract has '.($member_count - $extract_number).' extra.';
            
		if (is_array($extract_email)) {
			$recipients = $extract_email;
		} else {
			$recipients = array();
			$recipients[] = $extract_email;
		}
            $sentOK = GaemailHelper::sendEmail($recipients, $body, $subject, $attachfile);

		// update parameters for this extract
		$extparams = ComponentHelper::getParams('com_gausers');
		// Set new value of param(s)
		$extparams->set('extract_number', $member_count);
		$extparams->set('extract_date', $new_ext_date);

		// Save the parameters
		$componentid = ComponentHelper::getComponent('com_gausers')->id;
		$exttable = Table::getInstance('extension');
		$exttable->load($componentid);
		$exttable->bind(array('params' => $extparams->toString()));
		
		// check for error
		if (!$exttable->check()) {
		    echo $exttable->getError();
		    return false;
		}
		// Save to database
		if (!$exttable->store()) {
		    echo $exttable->getError();
		    return false;
		}

		return true;

	}

    public static function generateExtract()
	{
        $params = ComponentHelper::getParams('com_gausers');
        $extract_members  = $params->get('extract_members');
        $extract_number  = $params->get('extract_number', 0);
        $extract_date  = $params->get('extract_date');

        $profiles = true;
		$date = self::getTodaysDate();
		$today = date_format($date,'YmdHis');

        if($extract_members) {
			$extract_email  = $params->get('extract_email');
	        $profile_extract  = $params->get('profile_extract');
	        $profsuf  = $params->get( 'profile_suffix' );
	        $xclude  = $params->get( 'exclude_member' );
	        $html_email  = $params->get('html_email');
	        $extract_type  = $params->get('extract_type');
	        $extract_excludes  = $params->get('extract_excludes');
	        $xtraselect = "";
	        $xtrajoin = "";
	        $xtrawhere = "";
	        $i = 0;

	        if (isset($xclude)) {
				$csv = '0,';
				foreach ($xclude AS $x) {
					$csv .= $x.',';
				}
				$xclude = substr($csv,0,-1);
			} else {
				$xclude = 0;
			}

	        if (isset($extract_excludes)) {
				$csv = '0,';
				foreach ($extract_excludes AS $x) {
					$csv .= $x.',';
				}
				if ($xclude) {
					$xclude .= substr($csv,0,-1);
				} else {
					$xclude = substr($csv,0,-1);
				}
			}

            if (count($profile_extract) == 0) { $xtraselect = ""; $xtrajoin = ""; }
            else {
                // cycle through items to add code to the query
                foreach ($profile_extract as $xtradata) {
                    if ($xtradata == "") {
                    } else {
                        $i++;
                        if ($i == 1) { $j = 'b'; }
                        elseif ($i == 2) { $j = 'c'; }
                        elseif ($i == 3) { $j = 'd'; }
                        elseif ($i == 4) { $j = 'e'; }
                        elseif ($i == 5) { $j = 'f'; }
                        elseif ($i == 6) { $j = 'g'; }
                        elseif ($i == 7) { $j = 'h'; }
                        elseif ($i == 8) { $j = 'i'; }
                        elseif ($i == 9) { $j = 'j'; }
                        elseif ($i == 10) { $j = 'k'; }
                        else { $j = 'l'; }

						if ($xtradata == 'address1' || $xtradata == 'address2' || $xtradata == 'city' || $xtradata == 'region' || $xtradata == 'postal_code' || $xtradata == 'phone' || $xtradata == 'country' || $xtradata == 'dob' || $xtradata == 'website') {
							$suffx = '';
						} else {
							$suffx = $profsuf;
						}
                        $xtraselect .= ", ".$j.".profile_value AS ".$xtradata;
                        $xtrajoin .= ' LEFT JOIN #__user_profiles AS '.$j.' ON a.id = '.$j.'.user_id AND '.$j.'.profile_key = "profile'.$suffx.'.'.$xtradata.'" ';

                    }
                }
            }

	        // Create a new query object.
	        $db = Factory::getContainer()->get('DatabaseDriver');
	        $query = $db->getQuery(true);
	        $query->select( ' if(x.profile_value is null, 0, x.profile_value) AS fwdvic_no, a.id AS club_mship_no, a.name, a.email '.$xtraselect );
	        $query->from('#__users AS a '.$xtrajoin);
	        $query->join('LEFT','#__user_profiles AS x ON a.id = x.user_id AND x.profile_key = "profile'.$profsuf.'.fwdvic_no"');
            $query->where( ' a.block = 0 ' );
			$query->where( ' a.id NOT IN ('.$xclude.') '.$xtrawhere );
   			$query->order( ' a.name ' );
            $db->setQuery((string)$query);
		    try {
		        $profiles = $db->loadObjectList();
		    } catch (RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage($e->getMessage().' Get Members Profile Data');
		        return false;
		    }
         	
         	// cycle through the records and load into a text file
         	$mship_data = '';
         	if ($extract_type) { // 1 = CSV, 0 = SQL
	         	$head_data = '';
	         	$i = 0;
	         	foreach ($profiles as $member) {
					$i++;
					$mship_record = '';
					foreach ($member as $prof_item=>$v) {
						$v = str_replace(',', '-', $v);
						if ($i == 1) {
							$head_data .= '"'.$prof_item.'",';
						}
						if (substr($v,0,1) == '"') {
							$mship_record .= $v.',';
						} else {
							$mship_record .= '"'.$v.'",';
						}
					}
					$mship_record = substr($mship_record,0,-1);
	                $mship_record .= "\r\n";
	                $mship_data .= $mship_record;
			    }
			    $member_count = $i;

			} else {
				$head_data = 'INSERT into #__temp_members (';
	         	$i = 0;
	         	foreach ($profiles as $member) {
					$i++;
					$mship_record = '(';
					foreach ($member as $prof_item=>$v) {
						if ($i == 1) {
							$head_data .= '"'.$prof_item.'",';
						}
						if (substr($v,0,1) == '"') {
							$mship_record .= $v.',';
						} else {
							$mship_record .= '"'.$v.'",';
						}
					}
					$mship_record = substr($mship_record,0,-1);
	                $mship_record .= '),'." \r\n";
	                $mship_data .= $mship_record;
			    }
			    $member_count = $i;

			}

         	if ($extract_type) { // 1 = CSV, 0 = SQL
				$head_data = substr($head_data,0,-1)."\r\n";
				$file_ext = 'csv';
			} else {
				$head_data = substr($head_data,0,-1).') VALUES '."\r\n";
				$file_ext = 'sql';
			}

		    $output = $head_data;
		    $output .= $mship_data;

		    $path = Path::clean( JPATH_SITE . '/images/bcastnews' );
		    File::write($path . '/mshipdata_'.$today.'.'.$file_ext, $output);
		    $attachfile = $path . '/mshipdata_'.$today.'.'.$file_ext;

  			$subject = 'Club Member Extract - '.$fromname.' ('.$member_count.')';
   			$body = 'Find attached extract of members. The last extract sent ('.substr($extract_date,0,10).') included '.$extract_number.' members and this extract has '.($member_count - $extract_number).' extra.';
            
			if (is_array($extract_email)) {
				$recipients = $extract_email;
			} else {
				$recipients = array();
				$recipients[] = $extract_email;
			}
            $sentOK = GaemailHelper::sendEmail($recipients, $body, $subject, $attachfile);

			// update parameters for this extract
			$extparams = ComponentHelper::getParams('com_gausers');
			// Set new value of param(s)
			$extparams->set('extract_number', $member_count);
			$extparams->set('extract_date', $new_ext_date);

			// Save the parameters
			$componentid = ComponentHelper::getComponent('com_gausers')->id;
			$exttable = Table::getInstance('extension');
			$exttable->load($componentid);
			$exttable->bind(array('params' => $extparams->toString()));
			
			// check for error
			if (!$exttable->check()) {
			    echo $exttable->getError();
			    return false;
			}
			// Save to database
			if (!$exttable->store()) {
			    echo $exttable->getError();
			    return false;
			}
        }

        return true;

	}

	public static function createMembersDirectory()
	{
		$lang = Factory::getApplication()->getLanguage();
		$lang->load('com_gausers', JPATH_ADMINISTRATOR);
		$params = ComponentHelper::getParams('com_gausers');
        $incl_partner  = $params->get( 'incl_partner' );
        $mship_single  = $params->get( 'mship_single' );
        $xclude  = $params->get( 'exclude_member' );
        $allow_mbrdir  = $params->get( 'allow_mbrdir' );
        $send_mbrdir  = $params->get( 'send_mbrdir', 0);
        $membership_privacy  = $params->get( 'membership_privacy' );
        $profsuf  = $params->get( 'profile_suffix', 'b4wdc' );
        $profsuf  = 'profile'.$profsuf;
        $attachfile = false;
        if ($send_mbrdir) { $mdOption = "F"; } else { $mdOption = "F"; } // tried the I option but always failed

            $members = self::getCurrentMembers(0);
            if ($members) {
				// create pdf listing
                $pdf=new MdpdfHelper("P","mm","A4");
                $lh = 4;
                $w = 65;
                $pdf->SetMargins(10,10,10);
                $pdf->AddPage();
		        $pdf->SetTextColor(0,100,148); // r,g,b
		        $pdf->SetFillColor(255,191,123); // r,g,b
		        $pdf->MultiCell(0, 4, $membership_privacy, 1, "C", true);
		        $pdf->Cell(0, $lh, "", "T", 1, "C");
                $pdf->SetFont('Arial','',10);
		        $pdf->SetTextColor(64,64,64); // r,g,b
				$cntr = 0;
				$perpage = 0;
				$pagecntr = 1;

				foreach ($members as $mbr) {

					if (in_array($mbr->id, $xclude)) {
						// do nothing
					} else {
                        $mbr = GamemberprofileHelper::getMemberProfile($mbr, $params);
						$cntr++;

						if ($cntr == 3) {
							$cntr = 0;

							$member3 = $mbr->fullname. " \n";
							$member3 .= $mbr->address." \n";
							$member3 .= $mbr->suburb.' '.$mbr->region.' '.$mbr->postcode." \n";
							$member3 .= Text::_('COM_GAUSERS_FORM_LBL_GAUSER_PHONE').': '.$mbr->phone." \n";
							$member3 .= Text::_('COM_GAUSERS_FORM_LBL_GAUSER_ALTPHONE').': '.$mbr->altphone." \n";
							$member3 .= 'Sat Phone: '.$mbr->sat_phone." \n";
							$member3 .= $mbr->email." \n";
// 							$member3 .= 'Veh: '.$mbr->vehicle_make;
// 							$member3 .= ' - '.$mbr->vehicle_model." \n";
// 							$member3 .= 'Rego: '.$mbr->vehicle_rego." \n";
// 							$member3 .= 'Fuel: '.$mbr->vehicle_fuel." \n";
// 							$member3 .= 'Van: '.$mbr->camp_caravan." \n";
// 							$member3 .= 'Camper: '.$mbr->camp_other." \n";

							$x = $pdf->GetX();
							$y = $pdf->GetY();
			                $pdf->MultiCell($w, $lh, $member1, 0, "L");
			                $pdf->Cell(10, $lh, " ", 0, 0, "L"); // Sets an spacer
	                        $pdf->SetXY($x + $w, $y);
			                $pdf->MultiCell($w, $lh, $member2, 0, "L");
			                $pdf->Cell(10, $lh, " ", 0, 0, "L"); // Sets an spacer
	                        $pdf->SetXY($x + $w + $w, $y);
			                $pdf->MultiCell($w, $lh, $member3, 0, "L");
			                $pdf->Ln(3);
			                $pdf->Cell(0, $lh, "", "T", 1, "C");

	                        $member1 = '';
	                        $member2 = '';
	                        $member3 = '';
	                        $perpage++;
					    } elseif ($cntr == 1) {
							$member1 = $mbr->fullname. " \n";
							$member1 .= $mbr->address." \n";
							$member1 .= $mbr->suburb.' '.$mbr->region.' '.$mbr->postcode." \n";
							$member1 .= Text::_('COM_GAUSERS_FORM_LBL_GAUSER_PHONE').': '.$mbr->phone." \n";
							$member1 .= Text::_('COM_GAUSERS_FORM_LBL_GAUSER_ALTPHONE').': '.$mbr->altphone." \n";
							$member1 .= 'Sat Phone: '.$mbr->sat_phone." \n";
							$member1 .= $mbr->email." \n";
// 							$member1 .= 'Veh: '.$mbr->vehicle_make;
// 							$member1 .= ' - '.$mbr->vehicle_model." \n";
// 							$member1 .= 'Rego: '.$mbr->vehicle_rego." \n";
// 							$member1 .= 'Fuel: '.$mbr->vehicle_fuel." \n";
// 							$member1 .= 'Van: '.$mbr->camp_caravan." \n";
// 							$member1 .= 'Camper: '.$mbr->camp_other." \n";
					    } elseif ($cntr == 2) {
							$member2 = $mbr->fullname. " \n";
                            $member2 .= $mbr->address." \n";
							$member2 .= $mbr->suburb.' '.$mbr->region.' '.$mbr->postcode." \n";
							$member2 .= Text::_('COM_GAUSERS_FORM_LBL_GAUSER_PHONE').': '.$mbr->phone." \n";
							$member2 .= Text::_('COM_GAUSERS_FORM_LBL_GAUSER_ALTPHONE').': '.$mbr->altphone." \n";
							$member2 .= 'Sat Phone: '.$mbr->sat_phone." \n";
							$member2 .= $mbr->email." \n";
// 							$member2 .= 'Veh: '.$mbr->vehicle_make;
// 							$member2 .= ' - '.$mbr->vehicle_model." \n";
// 							$member2 .= 'Rego: '.$mbr->vehicle_rego." \n";
// 							$member2 .= 'Fuel: '.$mbr->vehicle_fuel." \n";
// 							$member2 .= 'Van: '.$mbr->camp_caravan." \n";
// 							$member2 .= 'Camper: '.$mbr->camp_other." \n";
						}
						if ($perpage == 5 && $pagecntr == 1) { $pdf->AddPage(); $pdf->SetXY(10,45); $perpage = 0; $pagecntr++; }
						if ($perpage == 6) { $pdf->AddPage(); $pdf->SetXY(10,45); $perpage = 0; $pagecntr++; }
					}
				}

				// clean up hangover records
				if ($cntr) {
					$x = $pdf->GetX();
					$y = $pdf->GetY();
	                $pdf->MultiCell($w, $lh, $member1, 0, "L");
	                $pdf->Cell(10, $lh, " ", 0, 0, "L"); // Sets an spacer
                    $pdf->SetXY($x + $w, $y);
	                $pdf->MultiCell($w, $lh, $member2, 0, "L");
	                $pdf->Cell(10, $lh, " ", 0, 0, "L"); // Sets an spacer
                    $pdf->SetXY($x + $w + $w, $y);
	                $pdf->MultiCell($w, $lh, $member3, 0, "L");
				}

                $path = Path::clean( JPATH_SITE . '/images/members' );
                $pdf->Output($mdOption, $path . "/MembersDirectory.pdf");
                $attachfile = $path.'/MembersDirectory.pdf';

				if ($send_mbrdir) {
					$user = Factory::getApplication()->getIdentity();
			        $sentOK = GaemailHelper::sendEmail(array($user->email), 'Find attached membership directory you requested.', 'Club Membership Directory - '.$fromname, $attachfile);
				}
			}

            return $attachfile;
        //}
	}

	/**
	* Get extension details using extension name
	* @param string extension name
	* @return extension object
	*/
	public static function getExtensionDetails($ext_name) {
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$db->setQuery('SELECT * FROM #__extensions WHERE name = ' . $db->Quote($ext_name));
		return $db->loadObject();
	}

	/**
	 * Get the Model from another component for use
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional
	 * @param   array   $config  Configuration array for model. Optional
	 * @return object	The model
	 */
	public static function getForeignModel($comp = 'com_actionlogs', $name = 'Actionlog', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		$fmodel  = Factory::getApplication()->bootComponent($comp)->getMVCFactory()->createModel($name, $prefix, $config);

		return $fmodel;
	}

	/**
	 * Record transaction details in log record
	 * @param   object  $user    Saves getting the current user again.
	 * @param   int     $id  id reference for the member being updated
	 * @param   string  $messType  string to identify type of action
	 * @param   int     $tran_id  The transaction id just created or updated
	 * @return  boolean	True
	 */
    public static function recordActionLog($user = null, $id = 0, $messType = 'member', $tran_id = 0)
	{
		$today = Factory::getDate()->toSql();
		// get the component details such as the id
		$extension =  self::getExtensionDetails('com_gausers');
		// get the transaction details for use in the log for easy reference
		$row = self::getSpecificUser($id);
        $profile = UserHelper::getProfile($row->id);
		//$row->memtype = $profile->profileb4wdc['memtype'];
		$row->memtype = GainvoiceHelper::getLastInvoiceMship($id)->title;
		$row->fwdvic_no = $profile->profileb4wdc['fwdvic_no'];

        $con_type = "member";
		$message = array();
		$message['action'] = $con_type;

        if ($messType == 'memberdied' || $messType == 'memberleft' || $messType == 'memberreturned' || $messType == 'convertmember') {
			// set up link to audit record
			$message['type'] = $row->name . ' - Action Taken: '.$today;
			$message['itemlink'] = "index.php?option=com_gausers&task=audit.edit&id=".$tran_id;
		} else {
			$message['type'] = $row->name . ' ('.$row->memtype.') '.$row->fwdvic_no.' - Action Taken: '.$today;
			$message['itemlink'] = "index.php?option=com_users&task=user.edit&id=".$row->id;
		}
		$message['id'] = $row->id;
		$message['title'] = $extension->name;
		$message['extension_name'] = STRTOLOWER($extension->name ?? '');
		$message['userid'] = $user->id;
		$message['username'] = $user->name;
		$message['accountlink'] = "index.php?option=com_users&task=user.edit&id=".$user->id;
		
		$messages = array($message);

		if ($messType == 'invoice') {
			$messageLanguageKey = Text::_('COM_GAUSERS_MEMBER_INVOICE');
		} elseif ($messType == 'memberleft') {
			$messageLanguageKey = Text::_('COM_GAUSERS_MEMBER_LEFT');
		} elseif ($messType == 'memberreturned') {
			$messageLanguageKey = Text::_('COM_GAUSERS_MEMBER_RETURNED');
		} elseif ($messType == 'memberdied') {
			$messageLanguageKey = Text::_('COM_GAUSERS_MEMBER_DIED');
		} elseif ($messType == 'convertmember') {
			$messageLanguageKey = Text::_('COM_GAUSERS_MEMBER_CONVERT');
		} else {
			$messageLanguageKey = Text::_('COM_GAUSERS_MEMBER');
		}
		$context = STRTOLOWER($extension->name ?? '').'.'.$con_type;

		$fmodel = self::getForeignModel('com_actionlogs', 'Actionlog', 'Administrator');

		$fmodel->addLog($messages, $messageLanguageKey, $context, $user->id);

		return true;
	}

	public static function unblockUser($user_id = 0, $block = 0) {

		$db = Factory::getContainer()->get('DatabaseDriver');
		$db->setQuery('UPDATE #__users SET block = '.(int)$block . ' WHERE id = '.(int)$user_id );
		try {
			$db->execute();
			return true;
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
			return false;
		}
	}

	public static function updateUser($user_id = 0, $fieldname = '', $value = '') {

		$db = Factory::getContainer()->get('DatabaseDriver');
		$db->setQuery('UPDATE #__users SET '.$fieldname.' = '.$db->Quote($value) . ' WHERE id = '.(int)$user_id );
		try {
			$db->execute();
			return true;
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
			return false;
		}
	}

	public static function updateUserProfile($user_id = 0, $key = '', $value = '') {

		$db = Factory::getContainer()->get('DatabaseDriver');
		$db->setQuery('SELECT count(user_id) FROM #__user_profiles WHERE user_id = '.(int)$user_id . ' AND profile_key = '.$db->Quote($key).' GROUP BY user_id ' );
		$entryExists = $db->loadResult();

		if ($entryExists > 0) {
			$db = Factory::getContainer()->get('DatabaseDriver');
			$db->setQuery('UPDATE #__user_profiles SET profile_value = '.$db->Quote($value).' WHERE user_id = '.(int)$user_id . ' AND profile_key = '.$db->Quote($key));
		} else {
			$db = Factory::getContainer()->get('DatabaseDriver');
			$db->setQuery('INSERT INTO #__user_profiles (user_id, profile_key, profile_value) VALUES ('.(int)$user_id.', '.$db->Quote($key).', '.$db->Quote($value).' )');
		}

		try {
			$result = $db->execute();
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
		}

        return true;
	}

	public static function resetMemberUserGroup($user = 0, $group_id = 0)
	{
		// this is for updating members who have left the group or died so need to remove all other groups
        // remove from all groups
		$db = Factory::getContainer()->get('DatabaseDriver');
		$db->setQuery('DELETE from #__user_usergroup_map WHERE user_id = '.(int)$user->id );
		try {
			$result = $db->execute();
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage().' Remove From User Groups');
		}

		// insert into resigned or deceased group
		$db = Factory::getContainer()->get('DatabaseDriver');
		$db->setQuery('INSERT into #__user_usergroup_map (user_id, group_id) VALUES ('.(int)$user->id.','.(int)$group_id.')');
		try {
			$result = $db->execute();
			Factory::getApplication()->enqueueMessage('Reset User Groups Success - '.$user->name, 'notice');
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage().' Reset User Groups Failed - user='.$user->name.' group='.$group_id);
		}

        return true;
	}

	public static function getNextUserID() {

		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->clear()
			->select(' (max(id)+1) ')
			->from($db->quoteName('#__users'));
		$db->setQuery($query);

		try {
			return $db->loadResult();
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
			return 0;
		}
	}

	/**
	 * Duplicate user record when member marked as passed away
	 * @param   id  $user_id  id reference of the existing user record.
	 * @return  boolean	True
	 */
    public static function duplicateUserRecord($u = 0, $deceased_group = 0)
	{
		// alter the table
		$db1 = Factory::getContainer()->get('DatabaseDriver');
		$db1->setQuery('SHOW COLUMNS FROM #__users WHERE FIELD = '.$db1->Quote('params'));
        $cols =  $db1->loadObject();

        if ($cols->Null != 'YES') {
    		$db2 = Factory::getContainer()->get('DatabaseDriver');
    		$db2->setQuery('ALTER TABLE #__users CHANGE params params TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL');
    		try {
    			$db2->execute();
    		} catch (RuntimeException $e2) {
    			Factory::getApplication()->enqueueMessage($e2->getMessage().' Alter User Table Failed - params field to accept null');
    		}
		}
        if($u) {
			// Create and populate an object.
			$new_user = new \stdClass();
			$new_user->id = 0;
			$new_user->block=1;
			$new_user->email= 'noemail_'.$u->email;
			$new_user->name=$u->dec_name;
			$new_user->username=$u->username.$u->id;
			$new_user->password=$u->password;
			$new_user->sendEmail=0;
			$new_user->registerDate=$u->registerDate;
			$new_user->lastvisitDate=$u->lastvisitDate;
			$new_user->activation=$u->activation;
			$new_user->params=$u->params;
			$new_user->lastResetTime=$u->lastResetTime;
			$new_user->resetCount=$u->resetCount;
			$new_user->otpKey=$u->otpKey;
			$new_user->otep=$u->otep;
			$new_user->requireReset=$u->requireReset;

			// Insert the object into the user profile table.
		    try {
		        Factory::getContainer()->get('DatabaseDriver')->insertObject('#__users', $new_user, 'id');
                $result = $new_user->id;
				// insert userGroupMap record;
                UserHelper::setUserGroups($new_user->id, array($deceased_group));

                if ($cols->Null != 'YES') {
                    // reset the database back again
            		$db3 = Factory::getContainer()->get('DatabaseDriver');
            		$db3->setQuery('ALTER TABLE #__users CHANGE params params TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL');
            		try {
            			$result = $db3->execute();
            		} catch (RuntimeException $e3) {
            			Factory::getApplication()->enqueueMessage($e3->getMessage().' Alter User Table Failed - params field back to no default');
            		}
        		}
		    } catch (RuntimeException $e1) {
		        Factory::getApplication()->enqueueMessage($e->getMessage().' Duplicate User Record', 'danger');
		        $result = 0;
		    }
        } else {
			Factory::getApplication()->enqueueMessage('Empty User Object Submitted', 'danger');
			$result = 0;
		}

		return $result;
	}

	/**
	 * Genereate barcode image for member
	 * @param   object  $m is a member object.
	 * @return  file	barcode image
	 */
    public static function createMemberBarcode($m = null)
	{
		$params = ComponentHelper::getParams('com_gausers');
		$bc_field  = $params->get( 'bc_field', 'id' );
		$bc_size  = $params->get( 'bc_size', 20 );
		// build the text to use for the barcode
		if ($bc_field == 'both') {
			$username = str_replace("-","",$m->username);
			$bc_text = $username.'-'.$m->id;
		} else {
			$bc_text = $m->$bc_field;
		}
		$r_date = new Date($m->registerDate);
		$r_date = $r_date->format('Ymd');
		$file_text = $m->name.' ('.$r_date.') '.$m->id;

        //$bc_text  =  strtoupper($bc_text);
        $dispText = "* Member - ".$m->name." (".$r_date.") ".$m->id." *";
        $image_name = $file_text.'.png';

		$filepath = Path::clean( JPATH_SITE . '/images/members/barcodes/'.$image_name);
		$filelink = htmlspecialchars(Uri::root().'/images/members/barcodes/'.$image_name, ENT_QUOTES);
		GabarcodeHelper::barcode( $filepath, $bc_text, $bc_size );

        Factory::getApplication()->setUserState('com_gausers.barcode.data', $filelink);

		return true;
	}

	/**
	 * Duplicate profile data for member
	 * @param   int  $user_id is a user id reference of existing user record.
	 * @param   int  $user_id is a user id reference of existing user record.
	 * @return  bool void
	 */
	public static function duplicateUserProfile($user_id = 0, $new_id = 0) {

		$db = Factory::getContainer()->get('DatabaseDriver');
		$db->setQuery('SELECT * FROM #__user_profiles WHERE user_id = '.(int)$user_id);
		$orig = $db->loadObjectList();

		foreach ($orig as $oldRec) {
			$db = Factory::getContainer()->get('DatabaseDriver');
			$db->setQuery('INSERT INTO #__user_profiles (user_id, profile_key, profile_value) VALUES ('.(int)$new_id.', '.$db->Quote($oldRec->profile_key).', '.$db->Quote($oldRec->profile_value).' )');
			try {
				$result = $db->execute();
			} catch (RuntimeException $e) {
				Factory::getApplication()->enqueueMessage(Text::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()), 'warning');
			}
		}

        return true;
	}

	/**
	* Update value of Custom Field
	* @param int field_id value
	* @param string item_id value
	* @param string value submitted
	* @return boolean
	*/
	public static function updateCustomFieldValue($field_id = 0, $item_id = 0, $value = 0) {
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$db->setQuery('UPDATE #__fields_values SET value = '.$db->Quote($value).' WHERE field_id = '.(int) $field_id.' AND item_id = '.$db->Quote($item_id) );
		try {
			$db->execute();
			return true;
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage(Text::_('COM_GAUSERS_CUSTFLD_UPD_FAILED'), 'warning');
			return false;
		}
	}

	/**
	* Create value of Custom Field
	* @param int field_id value
	* @param string item_id value
	* @param string value submitted
	* @return boolean
	*/
	public static function createCustomFieldValue($field_id = 0, $item_id = 0, $value = 0) {
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$db->setQuery('INSERT into #__fields_values (field_id, item_id, value) VALUES ('.(int) $field_id.','.$db->Quote($item_id).','.$db->Quote($value).')' );
		try {
			$db->execute();
			return true;
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage(Text::_('COM_GAUSERS_CUSTFLD_INS_FAILED'), 'warning');
			return false;
		}
	}

	/**
	* Check if user passed vaccination
	* @param int user_id value
	* @return boolean
	*/
	public static function isUserVaccinated($user_id = 0, $vax_field = 0, $exempt_field = 0) {

		$db1 = Factory::getContainer()->get('DatabaseDriver');
		$query1 = $db1->getQuery(true);
		$db1->setQuery('SELECT value FROM #__fields_values WHERE field_id = '.(int) $vax_field.' AND item_id = '.$db1->Quote($user_id) );
		try {
			$vaxed = $db1->loadResult();
		} catch (RuntimeException $e) {
			$vaxed = 0;
		}

		$db2 = Factory::getContainer()->get('DatabaseDriver');
		$query2 = $db2->getQuery(true);
		$db2->setQuery('SELECT value FROM #__fields_values WHERE field_id = '.(int) $exempt_field.' AND item_id = '.$db2->Quote($user_id) );
		try {
			$exempt = $db2->loadResult();
		} catch (RuntimeException $e) {
			$exempt = 0;
		}
		
		if ($vaxed || $exempt) { return true; } else { return false; }
	}

	/**
	* Get current user records
	* @param int user_id value
	* @return boolean
	*/
	public static function getCurrentMembers($id = 0)
	{
		$params  = ComponentHelper::getParams('com_gausers');
		$prof_pref  = $params->get('profile_suffix');
		$p_key = 'profile'.$prof_pref.'.altemail';
		$e_key = 'profile'.$prof_pref.'.inc_altemail';
		$part_key = 'profile'.$prof_pref.'.partner';

        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.id, a.name, a.email, a.registerDate ');
        $query->select(' p.profile_value AS altemail, e.profile_value AS inc_altemail ');
        $query->select(' if(g.profile_value IS NULL, "", g.profile_value) AS partner ');
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 1), ' ', -1) AS firstname " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 1), ' ', -1) AS first_name " );
        $query->select(" If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 2), ' ', -1) ,NULL) as middle1_name " );
        $query->select(" If( If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 3), ' ', -1) ,NULL) = SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1), null, If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 3), ' ', -1) ,NULL)) as middle2_name " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1) AS surname " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1) AS last_name " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(g.profile_value, ' ', 1), ' ', -1) AS firstnamep " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(g.profile_value, ' ', 1), ' ', -1) AS first_namep " );
        $query->select(" If( length(g.profile_value) - length(replace(g.profile_value, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(g.profile_value, ' ', 2), ' ', -1) ,NULL) as middle1_namep " );
        $query->select(" If( If( length(g.profile_value) - length(replace(g.profile_value, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(g.profile_value, ' ', 3), ' ', -1) ,NULL) = SUBSTRING_INDEX(SUBSTRING_INDEX(g.profile_value, ' ', 4), ' ', -1), null, If( length(g.profile_value) - length(replace(g.profile_value, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(g.profile_value, ' ', 3), ' ', -1) ,NULL)) as middle2_namep " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(g.profile_value, ' ', 4), ' ', -1) AS surnamep " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(g.profile_value, ' ', 4), ' ', -1) AS last_namep " );
		$query->from(' #__users AS a ');
		$query->join('LEFT', '#__user_profiles AS p ON a.id = p.user_id AND p.profile_key = '.$db->Quote($p_key));
		$query->join('LEFT', '#__user_profiles AS e ON a.id = e.user_id AND e.profile_key = '.$db->Quote($e_key));
		$query->join('LEFT', '#__user_profiles AS g ON a.id = g.user_id AND g.profile_key = '.$db->Quote($part_key));
		if ($id) {
            $query->where(' a.id = ' . (int) $id );
        } else {
            $query->where(' a.block = 0 ' );
        }
		$query->order(' substr(a.name, (LENGTH(a.name) - LOCATE(" ", REVERSE(a.name))+1)) ' );
		$db->setQuery((string)$query);

	    try {
	        $options = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

		$newdata = array();
        foreach ($options AS $ta) {
			$ta->inc_altemail = (isset($ta->inc_altemail) && !empty($ta->inc_altemail)) ? str_replace('"', '', $ta->inc_altemail) : '';
			$ta->altemail = (isset($ta->altemail) && !empty($ta->altemail)) ? str_replace('"', '', $ta->altemail) : '';
			$ta->fullname = GanamesHelper::combineNames($ta);
			$newdata[] = $ta;
		}

		return $newdata;
	}

	/**
	* Get current ip address on new member form
	* @return string IP Address
	*/
	public static function get_user_ip()
	{
        $ip = '';
        // Check for Cloudflare-specific header if applicable
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Handle multiple IPs in the header (e.g., from multiple proxies)
            $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($ip_list as $single_ip) {
                $single_ip = trim($single_ip);
                // Optional: validate the IP address to filter out private ranges or invalid IPs
                if (filter_var($single_ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    $ip = $single_ip;
                    break;
                }
            }
            // Fallback if no valid public IP found in the list, or if the header only had private IPs
            if (empty($ip)) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;

	}
}
