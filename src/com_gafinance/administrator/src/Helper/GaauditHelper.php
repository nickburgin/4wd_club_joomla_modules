<?php
/**
 * @version    5.1.0
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
use Joomla\CMS\Table\Table;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\Filesystem\Path;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GafinanceHelper;

/**
 * Gafinance helper for user action logs.
 */
class GaauditHelper
{

	/**
	 * Record transaction details in audit record
	 * @param   object  $user    Saves getting the current user again.
	 * @param   int     $tran_id  The transaction id being updated
	 * @param   array   $data  Passed data from form
	 * @return  boolean	True
	 */
    public static function recordAuditTrail($user = null, $id = 0, $data = 0)
	{
		// get the current date-time based on timezone
		$today = GafinanceHelper::getTodaysDate();

        if ($id) {
			$transaction = GafinanceHelper::getTransaction($id);
		} else {
            $transaction = 'New Record';
		}
		
		$data['created_by'] = $user->id;
		$data['created_date'] = $today;
		
		// Create and populate an object.
		$audit = new \stdClass();
		$audit->state = 1;
		$audit->checked_out = 0;
		$audit->created_by = $user->id;
		$audit->created_date = $today;
		$audit->tran_id = $id;
		$audit->pre_record = json_encode($transaction);
		$audit->post_record = json_encode($data);
	
		// Insert the object into the user profile table.
		$db = Factory::getContainer()->get('DatabaseDriver');
		$result = $db->insertObject('#__gafinance_audit_trail', $audit);
		$lastID = $db->insertid();

		return $lastID;
	}

    public static function addRefAuditTrail($audit_id = 0, $id = 0 )
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->clear();
        $query->update('#__gafinance_audit_trail ');
        $query->set( ' tran_id = '.(int) $id );
        $query->where(' id = '.(int) $audit_id);

	    try {
	        $db->execute();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }

		return true;
	}

    public static function createExtractFile($transactions = null)
	{
		$tz = Factory::getConfig()->get('offset');
		$date = Factory::getDate('now', $tz);
		$today = date_format($date,'Ymd-His');

        $parent = 'images';
        $folder = 'finances';
        $path = Path::clean( JPATH_SITE . '/' . $parent . '/' . $folder . '/audit-'.$today.'.xls' );

		if (!is_dir($path) && !is_file($path)) {
            // create new xml file
            $data = '<?xml version="1.0" encoding="UTF-8" ?>'. "\n";
            $data .= '<transactions>'. "\n";
			foreach ($transactions as $item) {
				$data .= "\t".'<tran>'. "\n";
				$data .= "\t\t".'<id>'.$item->id.'</id>'. "\n";
				if ($item->state == -2) {$state = 'trashed'; } elseif ($item->state == 0) {$state = 'unpresented'; } else {$state = 'complete'; }
				$data .= "\t\t".'<status>'.htmlspecialchars($state).'</status>'. "\n";
				$data .= "\t\t".'<user_name>'.htmlspecialchars($item->user_name).'</user_name>'. "\n";
				$data .= "\t\t".'<tran_desc>'.htmlspecialchars($item->tran_desc).'</tran_desc>'. "\n";
				$data .= "\t\t".'<tran_type>'.htmlspecialchars($item->tran_type).'</tran_type>'. "\n";
				$data .= "\t\t".'<tran_file>'.htmlspecialchars($item->tran_file).'</tran_file>'. "\n";
				$data .= "\t\t".'<tran_date>'.htmlspecialchars($item->tran_date).'</tran_date>'. "\n";
				$data .= "\t\t".'<tran_ref>'.htmlspecialchars($item->tran_ref).'</tran_ref>'. "\n";
				$data .= "\t\t".'<tran_amount>'.htmlspecialchars($item->tran_amount).'</tran_amount>'. "\n";
				$data .= "\t\t".'<cat_name>'.htmlspecialchars($item->cat_name).'</cat_name>'. "\n";
				$data .= "\t\t".'<accnt_name>'.htmlspecialchars($item->accnt_name).'</accnt_name>'. "\n";
				$data .= "\t\t".'<gst_amt>'.htmlspecialchars($item->gst_amt).'</gst_amt>'. "\n";
				$data .= "\t\t".'<comment>'.htmlspecialchars($item->comment).'</comment>'. "\n";
				$data .= "\t".'</tran>'. "\n";
			}
            $data .= '</transactions>'. "\n";

            // write the file
            File::write($path, $data);
		}           

        return $path;
	}

}
