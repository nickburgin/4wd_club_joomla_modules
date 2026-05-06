<?php
/**
 * @version    3.3.1
 * @package    pkg_gacalevents
 * @subpackage com_gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gacalevents\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\Data\DataObject;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Installer\Installer;
use Joomla\Filesystem\Path;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GaemailHelper;

/**
 * Communications helper.
 * @since  1.6
 */
class GacommunicationsHelper
{

	/**
	 * Build a confirmation email based on submitted event details
	 * @param   array  $data  details of the submitted event
	 * @return  boolean
	 */
    public static function sendConfirmation($data)
	{
        $recipients = array();
	    $app		= Factory::getApplication();
        // get the name of the user loading this record
        $user	= Factory::getApplication()->getIdentity();
        $user_id	= $user->get('id');
        $user_name	= $user->get('name');
        $user_email	= $user->get('email');

        // get the parameters
        $params = ComponentHelper::getParams('com_gacalevents');
        $authid     = $params->get( 'authorised_id' );
		if (is_array($authid)) {
			if (in_array($user->id, $authid)) {$apprvuser = true; } else { $apprvuser = false; }
			foreach ($authid AS $a_id) {
				$recipients[] = GacaleventsHelper::getSpecificUser($a_id)->email;
			}
		} else {
			if ($user->id == $authid) {$apprvuser = true; } else { $apprvuser = false; }
			$recipients[] = GacaleventsHelper::getSpecificUser($authid)->email;
		}

        // set the variables from the passed data
        $title = $data['title'];
        $brief_desc = $data['brief_desc'];
        $depart_date = $data['depart_date'];
        $departdate = new DateTime($depart_date);

		// ----------------------------------------------------------------------

		$urllink = Uri::base(). "administrator/index.php?option=com_gacalevents&view=events";
  		$mailfrom	= $app->get('mailfrom');       // system email address
    	$fromname	= $app->get('fromname');       // Site name or system name
    	$sitename	= $app->get('sitename');

		$subject	= Text::sprintf('New Calendar Event Loaded by ');
		$subject	.= $user_name;

		// Prepare email body
		$body	= "Hello Event Administrator, \r\n\r\n";
		$prefix = Text::sprintf('The following event has been submitted and needs to be published (approved) if appropriate. ', Uri::base());
		$body	.= $prefix." \r\n\r\n";
		$body	.= "     Title = ".$data['title']." \r\n";
		$body	.= "     Departure = ".$departdate->format('jS, F Y')." \r\n";
		$body	.= "     Brief Desc = " . $data['brief_desc'] . " \r\n\r\n";
		if ($apprvuser) {
            $body .= "     This event is now published. \r\n\r\n";
		} else {
			$body	.= "     " . $urllink . " \r\n\r\n";
		}
  		$body	.= $sitename." \r\n\r\n";

		GaemailHelper::sendEmail($recipients, $body, $subject, null, false, false);
		//GacommunicationsHelper::processEmail(null, $subject, $recipients, $body, $user_email);

        return true;

	}

	/**
	 * Build a reminder email
	 * @param   int  $id  reference to the event
	 * @return  boolean
	 */
    public static function sendReminder($id)
	{
	    $app		= Factory::getApplication();
  		$mailfrom	= $app->get('mailfrom');       // system email address
    	$fromname	= $app->get('fromname');       // Site name or system name
    	$sitename	= $app->get('sitename');
		$lang = Factory::getApplication()->getLanguage();
		$lang->load('com_gacalevents', JPATH_ADMINISTRATOR);

        $params = ComponentHelper::getParams('com_gacalevents');
        $reminder_gp = $params->get('reminder_gp', 2);
        $reminder_ignore = $params->get('reminder_ignore', 'noemail');
        $ignore_users = $params->get('ignore_users', 0);
        $reminder_len = strlen($reminder_ignore);
        $userEmail = array();
        $bcc = array();

        $event = GacaleventsHelper::getEvent($id);

        // get the name of the user loading this record
        $user	= Factory::getApplication()->getIdentity();
        $userEmail[] = $user->email;
        $bcc[] = $user->email;
		$allUsers = Access::getUsersByGroup($reminder_gp);

		if (is_array($allUsers)) {
			foreach ($allUsers AS $a_id) {
                if (is_array($ignore_users) && in_array($a_id, $ignore_users)) { 
					//skip to next
					continue;
				}
				$member = GacaleventsHelper::getSpecificUser($a_id);
				if (substr($member->email,0,$reminder_len) == $reminder_ignore || $member->block) {
					// do nothing because this record is inactive or noemail is set
				} else {
					$bcc[] = $member->email;
				}
			}
		}

		// ----------------------------------------------------------------------

		$subject	= Text::sprintf('COM_GACALEVENTS_EVENT_REMINDER', $event->title);
        $urllink = Uri::base();

		// Prepare email body
		$body	= "<p>Dear Members, </p>";
		$body	.= "<p>A reminder of the coming calendar event with some details below.</p><ul>";
		$body	.= "<li>Title = ".$event->title."</li>";
		$body	.= "<li>Date = ".$event->ddate."</li>";
		$body	.= "<li>Contact = ".$event->leader."</li>";
		$body	.= "<li>Where = ".$event->depart_point_name."</li>";
		$body	.= "<li>Brief Desc = " . $event->brief_desc . "</li><p> </p>";
		$body	.= "<p> </p>";
		$body	.= "<p> </p>";
		$body	.= "<p>".Text::_('COM_GACALEVENTS_EXTRA_REMINDER_TEXT')."</p>";
  		$body	.= '<p><a href="'.$urllink.'" alt="" title="Link to Website" target="_blank">'.$sitename.'</a></p>';
		// Build the email and send
		self::processEmail(null, $subject, $userEmail, $body, $bcc);

		$cntr = count($bcc);
		Factory::getApplication()->enqueueMessage(Text::_('Reminder Sent to '.$cntr.' Members '), 'notice');

        return true;

	}

	/**
	 * Send the email out
	 * @param   string  $attachfile  if a file needs to be attached
	 * @param   string  $subject  The subject line of the email
	 * @param   array  $recipients  Recipient emails as an array
	 * @param   string  $body  The body of the email
	 * @param   array  $bcc Any emails as BCC
	 * @return  boolean
	 */
    public static function processEmail($attachfile = null, $subject = null, $recipients = array(), $body = null, $bcc = array())
	{
        $app = Factory::getApplication();
        $mailfrom	= $app->get('mailfrom');       // system email address
        $fromname	= $app->get('fromname');       // Site name or system name
        $bcc[] = $mailfrom;
        $params = ComponentHelper::getParams('com_gacalevents');
        $test = $params->get('set_test', 0);

        // Build the email and send
        $mail = Factory::getMailer();
        $mail->isHTML(true);
		if (is_array($recipients) && !empty($recipients)) {
            $mail->addRecipient($recipients);
    		if (is_array($bcc) && !empty($bcc)) {
				$mail->addBcc($bcc);
			}
        } else {
            $mail->addRecipient(array($mailfrom));
    		if (is_array($bcc) && !empty($bcc)) {
				$mail->addBcc($bcc);
			}
        }
		//$mail->addReplyTo(array($user_email, $user_name));
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($subject);
		$mail->setBody($body);
		if ($attachfile) {
            $mail->addAttachment($attachfile);
        }

		if (!$test) {
            $sent = $mail->Send();
            return $sent;
        }

        return true;
	}

}

