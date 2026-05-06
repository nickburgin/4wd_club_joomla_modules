<?php
/**
 * @version    5.1.0
 * @package    Com_Gafinance
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gafinance\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\User\UserFactoryInterface;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\Filesystem\Path;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\CMS\User\User;
use \Joomla\CMS\Date\Date;
use \Joomla\CMS\Access\Access;
use \Joomla\CMS\Installer\Installer;
use \Joomla\Session\SessionInterface;
use \Joomla\CMS\Application\SiteApplication;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GapdfHelper;

/**
 * Gafinance helper.
 *
 * @since  1.6
 */
class GafinanceHelper
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
	public static function getHTTPQuery($existQ = null, $viewTask = 'view', $contModel = 'transaction', $ref = 'id', $linkId = 0)
	{
		if (!$existQ) {
			$query_string = array();
			$query_string['option'] = 'com_gafinance';
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
     * Gets todays date based on global timezone settings
     */
    public static function getTodaysDate()
	{
		//$tz = Factory::getConfig()->get('offset');
		//$date = Factory::getDate('now', $tz);
		//$today = date_format($date,'Y-m-d H:i:s');
        $today  = Factory::getDate()->toSql();

		return $today;
	}

    /**
     * Gets the user record for the specific id reference
     */
    public static function getSpecificUser($id = 0)
	{
		if ($id) {
			$container = Factory::getContainer();
			$userFactory = $container->get(UserFactoryInterface::class);
			$user = $userFactory->loadUserById($id);
		} else {
			$user = Factory::getApplication()->getIdentity();
		}

		return $user;
	}

	/**
	 * Set up a web asset object for use
	 */
	public static function setupGAWebApp()
	{
        // Boot the DI container.
        $container = Factory::getContainer();
        // Alias the session service key to the web session service.
        $container->alias(SessionInterface::class, 'session.web.site');
        // Get the application.
        $app      = $container->get(SiteApplication::class);
        //$template = $app->getTemplate(true);
        //$params   = $template->params;
        //$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        
        return $app;
	}

    /**
     * Load template over-rides when using modal view
     * This is important for Cloud White template scheme
     * because modals have the primary css over-ride any
     * css loaded in the template index.php file
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
	 * Get the version of the component
	 * @return version element of manifest
	 */
	public static function getComponentVersion()
	{
		$componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/com_gafinance/gafinance.xml'));

		return $componentXML['version'];
	}

	/**
	 * Gets the value of a field
	 * @param   int     $pk     The item's id
	 * @param   string  $table  The table's name
	 * @param   string  $field  The field's name
	 * @return  string  value of the field
	 */
	public static function getRecordValue($pk, $table, $field)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query
			->select($field)
			->from($table)
			->where('id = ' . (int) $pk);

		$db->setQuery($query);

		return $db->loadResult();
	}

    /**
     * Gets the edit permission for an user
     * @param   mixed  $item  The item
     * @return  bool
     */
    public static function canUserEdit($item)
    {
        $permission = false;
        $user       = self::getSpecificUser();

        if ($user->authorise('core.edit', 'com_gafinance')) {
            $permission = true;
        } else {
            if (isset($item->created_by)) {
                if ($user->authorise('core.edit.own', 'com_gafinance') && $item->created_by == $user->id) {
                    $permission = true;
                }
            } else {
                $permission = true;
            }
        }

        return $permission;
    }

	public static function getListOptions($table = '#__gafinance_accounts', $label = 'Account', $field = 'accnt_name' )
	{

		// get the user records in the supplier group
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "0" as "value", " - Select '.$label.' - " as "text" UNION SELECT a.id as value, a.'.$field.' as text ');
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

	public static function getCategoryOptions($ext = 'com_gafinance', $label = 'Invoice Type', $field = 'title' )
	{

		// get the user records in the supplier group
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "0" as "value", " - Select '.$label.' - " as "text" UNION SELECT a.id as value, a.'.$field.' as text ');
		$query->from( ' #__categories AS a ' );
		$query->where(' a.published = 1' );
		$query->where(' a.extension = '.$db->quote($ext) );
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

	public static function getAccount($id = 0)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' * ');
		$query->from( '#__gafinance_accounts' );
		$query->where(' id = ' . (int) $id );
		$db->setQuery((string)$query);

	    try {
	        $accntObj = $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

		return $accntObj;
	}

	/**
	 * Method to get all data for a given transaction
	 * @params  int     $id key to the record
	 * @return  object
	 */
	public static function getTransaction($id = 0)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select( 'a.*' );
        $query->from('#__gafinance_transactions AS a');
        $query->select('u.name AS user_name');
        $query->join('LEFT', '#__users AS u ON u.id=a.user_id');
		$query->select('cat_id.title AS cat_name');
		$query->join('LEFT', '#__categories AS cat_id ON cat_id.id = a.cat_id');
		$query->select('accnt_id.accnt_name AS accnt_name');
		$query->join('LEFT', '#__gafinance_accounts AS accnt_id ON accnt_id.id = a.accnt_id');
		$query->where(' a.id = '.(int) $id );
		$db->setQuery((string)$query);
	    try {
	        $transaction = $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }
	    
	    return $transaction;
    }

	/**
	 * Get all the name fields as an object
	 * @param   id  $user id.
	 * @return  object	membership names object
	 */
    public static function breakdownNamesFromUserID($id = null)
	{
		$params = ComponentHelper::getParams('com_gafinance');
		$profsuf  = $params->get( 'profile_suffix' );

        $db		= Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select(' a.id, a.name, a.email, a.block, a.registerDate, a.lastvisitDate ' );
        $query->select(' substr(name, 1, LOCATE(" ",name)) AS firstname ');
        $query->select(' if(substr(name, (LOCATE(" ",name)+1), 1)="&",substr(name, LOCATE(" ",name,(LOCATE(" ",name)+3))+1),substr(name, LOCATE(" ",name)+1)) AS surname ');
        $query->select(' if(h.profile_value IS NULL, "", h.profile_value) AS partner ');
        $query->select(' substr(h.profile_value, 1, LOCATE(" ",h.profile_value)) AS firstnamep ');
        $query->select(' substr(h.profile_value, LOCATE(" ",h.profile_value)+1) AS surnamep ');
        $query->from('#__users AS a');
        $query->join('LEFT', ' #__user_profiles AS h ON a.id = h.user_id AND h.profile_key = "profile'.$profsuf.'.partner" ');
        $query->where('a.id = ' . (int) $id );
        $db->setQuery($query);
		try {
			$member =  $db->loadObject();
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
			$member = false;
		}

		return $member;

	}

	/**
	 * Break up the name fields and form a display name version
	 * @param   object  $member  data including name, partner and the breakup firstnames and surnames.
	 * @return  string	combined name for the membership display
	 */
    public static function combineNames($member = null)
	{
		if ($member) {
			if (empty($member->partner) || $member->partner == '' || $member->partner == ' ') {
				$result = $member->name;
			} else {
				if (trim($member->surname) == trim($member->surnamep)) {
					$result = trim($member->firstname). ' & ' .trim($member->firstnamep) . ' ' . trim($member->surname);
				} else {
					$result = trim($member->name). ' & ' .trim($member->partner);
				}
			}
		} else {
			$result = 'Not Linked to Member';
		}

		return $result;

	}

	/**
	 * Get the transactions between given dates
	 */
	public static function getAllTransactions($startdate = null, $enddate = null, $rpt_accnt = 0)
	{
        $params = ComponentHelper::getParams('com_gafinance');
        $combine_accnts = $params->get('combine_accnts', 0);
        $select_accnts = $params->get('select_accnts');
        $combine_rpt = $params->get('combine_rpt', 0);
        if (is_array($select_accnts)) {
			$select_accnts = implode(',',$select_accnts);
		}

		// get the current date-time based on timezone
		$tz = Factory::getConfig()->get('offset');
		$date = Factory::getDate('now', $tz);
		$today = date_format($date,'Y-m-d H:i:s');
		$todayM = date_format($date,'m');
		$todayY = date_format($date,'Y');
		$default_sdate = $todayY.'-'.$todayM.'-01 00:00:00';
		$default_edate = substr($today,0,10).' 23:59:59';

        if (empty($startdate)) {
			$startdate = $default_sdate; 
		}
        if (empty($enddate)) { 
			$enddate = $default_edate;
		}

        // Create a new query object to get all the transactions between to the requested dates.
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->clear();
        $query->select( ' a.accnt_id, a.tran_type, c.title, a.tran_desc, SUM(a.tran_amount) as tranAmt, a.state ' );
		if ($rpt_accnt) {
	        $query->select( ' b.accnt_name ' );
		} else {
	        $query->select( ' "Combined Accounts" AS accnt_name ' );
		}       
        $query->from('#__gafinance_transactions AS a ');
        $query->join('LEFT', '#__categories AS c ON c.id = a.cat_id ');
        $query->join('LEFT', '#__gafinance_accounts AS b ON b.id = a.accnt_id ');
        $query->where(' a.state IN (0,1) ');
		if ($rpt_accnt) {
			if ($combine_accnts) {
				if ($combine_rpt) {
					$query->where(' a.accnt_id IN ('.$select_accnts.')');
					$query->where(' a.tran_type <> "D" ');
				} else {
					$query->where(' a.accnt_id = '.(int) $rpt_accnt);
				}
			} else {
				$query->where(' a.accnt_id = '.(int) $rpt_accnt);
			}
		}

        $query->where(' a.tran_type <> "Z" ');

        $query->where(' a.tran_date >= '.$db->Quote($startdate) );
        $query->where(' a.tran_date <= '.$db->Quote($enddate) );
		if ($combine_rpt) {
	        $query->group(' a.tran_type, c.title, a.tran_desc, a.state ' );
	        $query->order(' a.tran_type DESC, c.title ASC, a.tran_desc ASC ' );
		} else {
	        $query->group(' a.accnt_id, a.tran_type, c.title, a.tran_desc, a.state ' );
	        $query->order(' a.accnt_id ASC, a.tran_type DESC, c.title ASC, a.tran_desc ASC ' );
		}
        $db->setQuery((string)$query);

	    try {
	        $pldata = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }
	    
        return $pldata;
    }

	/**
	 * Get the opening balance for a given date
	 */
	public static function getOpeningBalance($startdate = null, $enddate = null, $rpt_accnt = 1)
	{
        $params = ComponentHelper::getParams('com_gafinance');
        $combine_accnts = $params->get('combine_accnts', 0);
        $combine_rpt = $params->get('combine_rpt', 0);

		// Create a new query object to get the opening balance figure & date.
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->clear();
        $query->select( " a.tran_amount, date_format(a.tran_date,'%Y-%m-%d') AS tran_date2, a.tran_date " );
        $query->from(' #__gafinance_transactions AS a ');
        $query->where(' a.state = 1 ');
        $query->where(' a.accnt_id = '.(int) $rpt_accnt);
        $query->where(' a.tran_type = "Z" ');
        $query->where(' a.tran_date < '.$db->Quote($enddate) );
        $query->where(' a.tran_date >= (select max(tran_date) from #__gafinance_transactions where tran_type = "Z" and state = 1 and tran_date < '.$db->Quote($enddate).' and accnt_id = '.(int) $rpt_accnt.' ) ' );

        $db->setQuery((string)$query);
        if (!$db->execute()) {
           throw new \Exception(500, $db->getErrorMsg());
        }
        $reconbal = $db->loadObject();
        
        if ($reconbal->tran_date < $startdate) {
	        $tdate = Factory::getDate($reconbal->tran_date);
	        $tran_date = $tdate->toSql();
			// Create a new query object to get transaction from reconciliation date to start date.
	        $db = Factory::getContainer()->get('DatabaseDriver');
	        $query = $db->getQuery(true);
	        $query->clear();
	        $query->select( ' sum(a.tran_amount) AS cum_tranamt ' );
	        $query->from(' #__gafinance_transactions AS a ');
	        $query->where(' a.state = 1 ');
	        $query->where(' a.accnt_id = '.(int) $rpt_accnt);
	        $query->where(' a.tran_type <> "Z" ');
			if ($combine_accnts) {
				if ($combine_rpt) {
					$query->where(' a.tran_type <> "D" ');
				}
			}
	        $query->where(' a.tran_date < '.$db->Quote($startdate) );
	        $query->where(' a.tran_date >= '.$db->Quote($tran_date) );
	
	        $db->setQuery((string)$query);
	        if (!$db->execute()) {
	           throw new \Exception(500, $db->getErrorMsg());
	        }
	        $cumulativebal = $db->loadObject();
	        
	        $openbal = $reconbal->tran_amount + $cumulativebal->cum_tranamt;

		} else {
	        // Create a new query object to get all the transactions before reconciliation balance back to start date.
	        $db = Factory::getContainer()->get('DatabaseDriver');
	        $query = $db->getQuery(true);
	        $query->clear();
	        $query->select( ' sum(a.tran_amount) AS cum_tranamt ' );
	        $query->from(' #__gafinance_transactions AS a ');
	        $query->where(' a.state = 1 ');
	        $query->where(' a.accnt_id = '.(int) $rpt_accnt);
	        $query->where(' a.tran_type <> "Z" ');
			if ($combine_accnts) {
				if ($combine_rpt) {
					$query->where(' a.tran_type <> "D" ');
				}
			}
	        $query->where(' a.tran_date >= '.$db->Quote($startdate) );
	        $query->where(' a.tran_date < '.$db->Quote($reconbal->tran_date) );
	
	        $db->setQuery((string)$query);
	        if (!$db->execute()) {
	           throw new \Exception(500, $db->getErrorMsg());
	        }
	        $cumulativebal = $db->loadObject();
	        
	        $openbal = $reconbal->tran_amount - $cumulativebal->cum_tranamt;
		}

        return $openbal;
    }

	/**
	 * Get the closing balance for a given date
	 */
	public static function getClosingBalance($enddate = null, $rpt_accnt = 1)
	{
        $params = ComponentHelper::getParams('com_gafinance');
        $combine_accnts = $params->get('combine_accnts', 0);
        $combine_rpt = $params->get('combine_rpt', 0);

		$tz = Factory::getConfig()->get('offset');
		$date = Factory::getDate('now', $tz);
		$today = date_format($date,'Y-m-d H:i:s');
		$todayM = date_format($date,'m');
		$todayY = date_format($date,'Y');
		$default_sdate = $todayY.'-'.$todayM.'-01 00:00:00';
		$default_edate = substr($today,0,10).' 23:59:59';

        if (empty($enddate)) { $enddate = $default_edate; }

		// Create a new query object to get the closing balance figure calculated from all transactions since the last reconciliation record.
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->clear();
        $query->select( ' sum(a.tran_amount) ' );
        $query->from('#__gafinance_transactions AS a ');
        $query->where(' a.state = 1 ');
		if ($combine_accnts) {
			if ($combine_rpt) {
				$query->where(' a.tran_type <> "D" ');
			}
		}
		$query->where(' a.accnt_id = '.(int) $rpt_accnt);
        $query->where(' a.tran_date >= (select max(tran_date) from #__gafinance_transactions AS b where b.tran_type = "Z" and b.state = 1 and b.accnt_id = '.(int) $rpt_accnt.' ) ' );
        $query->where(' a.tran_date <= '.$db->Quote($enddate) );

        $db->setQuery((string)$query);
        if (!$db->execute()) {
           throw new \Exception(500, $db->getErrorMsg());
        }
        $closebal = $db->loadResult();
        return $closebal;
    }

	/**
	 * Method to get all assets
	 * @return  object
	 */
	public static function getAllAssets()
	{
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->clear();
        $query->select( ' a.dep_term, a.asset_name, a.asset_date, a.asset_value, a.asset_desc');
        $query->from('#__gafinance_busassets AS a ');
        $query->where(' a.state = 1 ');
        $query->order(' a.asset_date ');
		$db->setQuery((string)$query);

	    try {
	        $assets = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }
	    
	    return $assets;
    }

	/**
	 * Method to get all records since last balance reset
	 * @return  object
	 */
	public static function getAllTransSinceReset($accnt_id = 0)
	{
		//get all records for a given Account
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select( 'a.id, a.state, a.tran_type, a.tran_desc, a.tran_file, a.tran_ref, a.tran_date, FORMAT(a.tran_amount,2) as tran_amount, a.gst_amt, a.comment' );
        $query->from('#__gafinance_transactions AS a');
        $query->select('u.name AS user_name');
        $query->join('LEFT', '#__users AS u ON u.id=a.user_id');
		$query->select('cat_id.title AS cat_name');
		$query->join('LEFT', '#__categories AS cat_id ON cat_id.id = a.cat_id');
		$query->select('accnt_id.accnt_name AS accnt_name');
		$query->join('LEFT', '#__gafinance_accounts AS accnt_id ON accnt_id.id = a.accnt_id');
		$query->where(' a.tran_date >= (select max(tran_date) from #__gafinance_transactions where tran_type = "Z" and state = 1 ) ' );
		if ($accnt_id) {
			$query->where(' a.accnt_id = '.(int) $accnt_id);
		}
		$query->order(' a.accnt_id ASC, a.tran_date DESC, a.id DESC ');
		$db->setQuery((string)$query);
	    try {
	        $transactions = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }
	    
	    return $transactions;
    }

    public static function processEmail($attachfile = null, $subject = null, $recipients = array(), $body = null, $bcc = array())
	{
        $app = Factory::getApplication();
        $mailfrom	= $app->get('mailfrom');       // system email address
        $fromname	= $app->get('fromname');       // Site name or system name
        $bcc[] = $mailfrom;
	    $params = ComponentHelper::getParams('com_gafinance');
	    $testMode = $params->get('test_mode', 0);

        // Build the email and send
        $mail = Factory::getMailer();
		if (is_array($recipients) && !empty($recipients)) {
            $mail->addRecipient($recipients);
    		$mail->addBCC($bcc);
        } else {
            $mail->addRecipient(array($mailfrom));
    		$mail->addBCC($bcc);
        }
		//$mail->addReplyTo(array($user_email, $user_name));
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($subject);
		$mail->setBody($body);
		if ($attachfile) {
            $mail->addAttachment($attachfile);
        }

		if (!$testMode) {
            $sent = $mail->Send();
        }

        return $sent;
	}

}

