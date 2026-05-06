<?php
/**
 * @version    4.1.0
 * @package    com_gatracklog
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatracklog\Administrator\Helper;
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

/**
 * Gatracklog helper.
 *
 * @since  1.6
 */
class GatracklogHelper
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
	public static function getHTTPQuery($existQ = null, $viewTask = 'view', $contModel = 'tracklog', $ref = 'id', $linkId = 0)
	{
		if (!$existQ) {
			$query_string = array();
			$query_string['option'] = 'com_gatracklog';
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
     * Gets todays date object based on global timezone settings
     */
    public static function getTodaysDate()
	{
		$tz = Factory::getConfig()->get('offset');
		$date = Factory::getDate('now', $tz);
		//$today = date_format($date,'Y-m-d H:i:s');

		return $date;
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
    public static function canUserEdit($user, $item)
    {
        $permission = false;

        if ($user->authorise('core.edit', 'com_gatracklog')) {
            $permission = true;
        } else {
            if (isset($item->created_by) && $item->created_by) {
                if ($user->authorise('core.edit.own', 'com_gatracklog') && $item->created_by == $user->id) {
                    $permission = true;
                }
            } else {
                if ($user->authorise('core.create', 'com_gatracklog')) {
                    $permission = true;
                }
            }
        }

        return $permission;
    }

	public static function getListOptions($table = '#__gatracklog_tracklogs', $label = 'Tracklog', $field = 'tran_desc' )
	{
		// get the records
        $db = Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "0" as "value", " - Select '.$label.' - " as "text" UNION SELECT id as value, '.$field.' as text ');
		$query->from( $table );
		$query->where(' state = 1' );
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

	public static function getCategoryOptions($ext = 'com_gatracklog', $label = 'Category Type', $field = 'title' )
	{
		// get the categories
        $db = Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "0" as "value", " - Select '.$label.' - " as "text" UNION SELECT id as value, '.$field.' as text ');
		$query->from( ' #__categories ' );
		$query->where(' published = 1' );
		$query->where(' extension = '.$db->quote($ext) );
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

	public static function getCategory($id = 0 )
	{
		// get the categories
        $db = Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' * ');
		$query->from( ' #__categories ' );
		$query->where(' published = 1' );
		$query->where(' id = ' . (int) $id );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
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
		$query->select(' b.title As fieldName, a.value AS fieldValue ');
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

	/**
	 * Send out an email
	 * @param   array   $recipients  mandatory
	 * @param   string  $body  - text of the email body
	 * @param   string  $subject  - text for the subject line
	 * @param   string  $attachfile - file location address
	 * @return  boolean	false on fail
	 */
    public static function sendEmail($recipients, $body, $subject, $attachfile)
	{
        //$params = ComponentHelper::getParams('com_gausers');
	    $app		= Factory::getApplication();
        $mailfrom	= $app->get('mailfrom');       // system email address
        $fromname	= $app->get('fromname');       // Site name or system name
        // Build the email and send
        $mail = Factory::getMailer();
        $mail->isHTML(true);
		$mail->addRecipient($recipients);
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($subject);
		$mail->setBody($body);
		if (is_file($attachfile)) {
            $mail->addAttachment($attachfile);
        }
        $sent = $mail->Send();

        return true;
	}

	/**
	 * Gets the files attached to an item
	 * @param   int     $pk     The item's id
	 * @param   string  $table  The table's name
	 * @param   string  $field  The field's name
	 * @return  array  The files
	 */
	public static function getTracklogComments($id)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query->select('a.*, b.name AS user_name');
		$query->from('#__gatracklog_trackcomments AS a');
		$query->join('LEFT','#__users AS b ON a.user_id = b.id');
		$query->where('a.track_id = ' . (int) $id);
		$query->where('a.state = 1');
		$query->order('a.created_date DESC');
		$db->setQuery($query);
	    try {
	        return $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }
	}

	/**
	 * Update record to trashed status
	 * @param   int     $pk     The item's id
	 * @return  boolean
	 */
	public static function deleteTracklogComments($id)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query->update('#__gatracklog_trackcomments');
		$query->set('state = -2');
		$query->where('id = ' . (int) $id);
		$db->setQuery($query);
	    try {
	        return $db->execute();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }
	}

	/**
	 * Gets the details of requested article
	 * @param   int     $id
	 * @return  an article object
	 */
	public static function getArticleDetails($id = 0)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query->select('a.*, b.name AS created_by_name');
		$query->from('#__content AS a');
		$query->join('LEFT','#__users AS b ON a.created_by = b.id');
		$query->where('a.id = ' . (int) $id);
		$db->setQuery($query);
	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }
	}


}

