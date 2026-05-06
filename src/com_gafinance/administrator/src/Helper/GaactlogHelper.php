<?php
/**
 * @version    5.2.3
 * @package    Com_Gafinance
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2020 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gafinance\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GafinanceHelper;

/**
 * Gafinance helper for user action logs.
 */
class GaactlogHelper
{
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
	 * Record transaction details in log record
	 * @param   object  $user    Saves getting the current user again.
	 * @param   int     $tran_id  The transaction id just created or updated
	 * @param   int     $id  Passed id reference from the form to identify if new record
	 * @return  boolean	True
	 */
    public static function recordActionLog($user = null, $tran_id = 0, $id = 0)
	{
		// get the component details such as the id
		//$extension =  self::getExtensionDetails('com_gafinance');
		// get the transaction details for use in the log for easy reference
        $today = GafinanceHelper::getTodaysDate();
        $tran = GafinanceHelper::getTransaction($tran_id);
        //$con_type = "transaction";
        if (!$id) { $type = 'New '; } else { $type = 'Update '; }

		$message = array();
		$message['action'] = $con_type;
		$message['type'] = $type . $tran->tran_type . ' - '.$tran->tran_desc . ' $' . $tran->tran_amount;
		$message['id'] = $tran->id;
		$message['title'] = 'com_gafinance';
		$message['extension_name'] = strtolower('com_gafinance' ?? '');
		$message['itemlink'] = "index.php?option=com_gafinance&task=transaction.edit&id=".$tran->id;
		$message['userid'] = $user->id;
		$message['username'] = $user->username;
		$message['accountlink'] = "index.php?option=com_users&task=user.edit&id=".$user->id;
		
		$messages = array($message);
		
		//$context = strtolower($extension->name).'.'.$con_type;


		// Create and populate an object.
		$actlog = new \stdClass();
		$actlog->id = 0;
		$actlog->user_id = $user->id;
		$actlog->item_id = $tran->id;
		$actlog->message_language_key = 'COM_GAFINANCE_GAFINANCE_TRANSACTION';
		$actlog->log_date = $today;
		$actlog->extension = 'com_gafinance.transaction';
		$actlog->message = json_encode($message);

		// Insert the object into the user profile table.
		$db = Factory::getContainer()->get('DatabaseDriver');
		$result = $db->insertObject('#__action_logs', $actlog);
		$lastID = $db->insertid();

		return $lastID;
	}

}
