<?php
/**
 * @version    5.3.0
 * @package    com_gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatripsys\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Filesystem\Path;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\User\User;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Installer\Installer;
use Joomla\Session\SessionInterface;
use Joomla\CMS\Application\SiteApplication;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GainvoiceHelper;

/**
 * Gatripsys helper.
 * @since  1.6
 */
class GaemailHelper
{

	/**
	 * Send out an email
	 * @param   array   $recipients  mandatory
	 * @param   string  $body  - text of the email body
	 * @param   string  $subject  - text for the subject line
	 * @param   string  $attachfile - file location address
	 * @param   boolean  $bcc - flag to indicate recipients as bcc
	 * @param   boolean  $reply_sender - flag to indicate a reply to senders email address
	 * @return  boolean	false on fail
	 */
    public static function sendEmail($recipients, $body, $subject, $attachfile, $bcc = 0, $reply_sender = 0)
	{
        $params = ComponentHelper::getParams('com_gatripsys');
	    $app		= Factory::getApplication();
        $app->setUserState('com_gatripsys.trip.recipients', $recipients);
        $mailfrom	= $app->get('mailfrom');       // system email address
        $fromname	= $app->get('fromname');       // Site name or system name
        $user = GatripsysHelper::getSpecificUser();

        // Build the email and send
        $mail = Factory::getMailer();
        $mail->isHTML(true);
		if ($bcc) {
			foreach ($recipients as $email) {
                $mail->addBcc($email);
            }
			$mail->addRecipient($mailfrom);
		} else {
			foreach ($recipients as $email) {
                $mail->addRecipient($email);
            }
		}
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($subject);
		$mail->setBody($body);
		if ($reply_sender) {
            $mail->addReplyTo($user->email, $user->name);
        }
		if (is_file($attachfile)) {
            $mail->addAttachment($attachfile);
        }

	    try {
	        $sentOK = true;
            if (!$params->get('test_switch', 0)) {
                $sentOK = $mail->Send();
            }
	        return $sentOK;
        } catch (\Exception $e) {
            $app->enqueueMessage(Text::_($e->getMessage()), 'warning');
	        return false;
	    }
	}

	/**
	 * Setup the content or body of the email
	 * @param   object  $sale  mandatory (full record of the sale)
	 * @return  boolean	false on fail
	 */
    public static function setupEmailContent($trip)
	{
        $params = ComponentHelper::getParams('com_gatripsys');
	    $app		= Factory::getApplication();

    	$body = '<p>'.Text::sprintf('COM_GATRIPSYS_NOTIFY_MEMBERS_BODY',$trip->leader_name, $trip->title, $trip->dept_date_disp);
    	$body .= '</p><p> </p>';
    	$body .= '<p>'.$sitename.'</p>';

        return $body;
	}

	/**
	 * Send out an email using the template layout
	 * @param   string  $template_id  mandatory (full template_id ie com_gatripsys.tripnew)
	 * @param   object  $item  mandatory - containing all relevant info
	 * @param   string  $link  optional
	 * @param   string  $filename  optional - name of the file if required without full path
	 * @param   string  $attachment  optional - including full path
	 * @return  boolean	false on fail
	 */
    public static function sendEmailTmpl($template_id, $item, $link = null, $filename = null, $attachfile = null)
	{
        $params = ComponentHelper::getParams('com_gatripsys');
	    $app		= Factory::getApplication();
	    $mailfrom	= $app->get('mailfrom');
        $tmplId = explode('.', $template_id)[1];

        // load all the data elements as set in the template setup
        $data = array();
        $data['sitename'] = $app->get('sitename');
        if ($link) {
            $mode = $app->get('force_ssl', 0) == 2 ? 1 : (-1);
            $data['link_text'] = Route::_($link, false, $mode);
        }
        $data['coord_name'] = (isset($item->coord_name) && $item->coord_name > '') ? $item->coord_name : '';
        $data['trip_title'] = $item->trip_details->title;
        $data['dept_date'] = $item->dept_date_disp;
        $data['leader_name'] = $item->leader_name;
        $data['booking_status'] = (isset($item->booking_status) && $item->booking_status > '') ? $item->booking_status : '';

        foreach ($item->members as $recip) {
            if (isset($item->att_name) && $item->att_name > '') {
                $data['member_name'] = $item->att_name; 
                $name = isset($recip->full_name) ? $recip->full_name : $recip->name;
            } else {
                $name = isset($recip->full_name) ? $recip->full_name : $recip->name;
                $data['member_name'] = $name;
            }

            // Build the email and send
            $mailer = new MailTemplate($template_id, $app->getLanguage()->getTag());
            $mailer->addTemplateData($data);
            $mailer->setReplyTo($mailfrom, $data['sitename']);
            $mailer->addRecipient($recip->email, $name);

            if ($attachfile) {
                $mailer->addAttachment($filename, $attachfile);
            }
    
            // Try to send the email.
            try {
                if (!$params->get('test_switch', 0)) {
                    $return = $mailer->send();
                } else {
                    $return = true;
                }
            } catch (\Exception $exception) {
                $app->enqueueMessage(Text::_($exception->getMessage()), 'warning');
                $return = false;
            }
        }

        if ($return) {
            $app->enqueueMessage(Text::_('COM_GATRIPSYS_MAIL_SEND_SUCCESSFUL'), 'success');
        }

        return $return;
	}

	/**
	 * gather name and emails from members data
	 * @param   array  $members  mandatory - array of user ids
	 * @param   object  $params  saves getting them again
	 * @return  array	$names and $emails as an assoc array
	 */
    public static function getRecipients($members, $params)
	{
		$recipients = array();
        $exEmail = $params->get('exclude_email', 'noemail');
		$exLen = strlen($exclude_email);

        foreach ($members AS $m) 
        {
            if ($params->get('mship_single',0)) 
            {
    			$member = GainvoiceHelper::breakdownNamesFromUserID($m);
                $name = GainvoiceHelper::combineNames($member);
    		} else {
    			$member = GainvoiceHelper::breakdownNamesFromUserID($m);
                $name = $member->name;
    		}
			if (substr($member->email,0,$exLen) != $exEmail)
            {
                $recipients[$name] = $member->email;

				if ($member->inc_altemail &&
                    isset($member->altemail) &&
                    $member->altemail > '' &&
                    substr($member->altemail,0,$exLen) != $exEmail &&
                    isset($member->partner) &&
                    $member->partner > '') 
                {
					$recipients[$member->partner] = $member->altemail;
				}
			}
		}
		
		return $recipients;

	}

	/**
	 * Send out an email using the template layout
	 * @param   string  $template_id  mandatory (full template_id ie com_gasales.mail)
	 * @param   array   $data  mandatory - containing all relevant info (mandatory elements recipients)
	 * @param   string  $filename  optional - name of the file if required without full path
	 * @param   string  $link  optional
	 * @param   string  $attachment  optional - including full path
	 * @return  boolean	false on fail
	 */
    public static function sendEmailTemplate($template_id, $data, $link = null, $filename = null, $attachfile = null)
	{
	    $app		= Factory::getApplication();
        $mailfrom	= $app->get('mailfrom');
        $fromname	= $app->get('fromname');
        $data['sitename'] = $fromname;

        $params = ComponentHelper::getParams('com_gatripsys');

        if ($link) {
            $mode = $app->get('force_ssl', 0) == 2 ? 1 : (-1);
            $data['link_text'] = Route::_($link, false, $mode);
        }

        // Build the email and send
        $mailer = new MailTemplate($template_id, $app->getLanguage()->getTag());
        $mailer->addTemplateData($data);

        // set the recipients with third param options ('to', 'cc', 'bcc')
        if (!empty($data['recips'])) {
            foreach ($data['recips'] as $recip) {
                $mailer->addRecipient($recip['email'], $recip['name'], 'to');
            }
        } else {
            $mailer->addRecipient($mailfrom, $fromname, 'to');
        }

        if (!empty($data['cc_recips'])) {
            foreach ($data['cc_recips'] as $ccs) {
                $mailer->addRecipient($ccs['email'], $ccs['name'], 'cc');
            }
        }

        if (!empty($data['bcc_recips'])) {
            foreach ($data['bcc_recips'] as $bccs) {
                $mailer->addRecipient($bccs['email'], $bccs['name'], 'bcc');
            }
        }

        $mailer->setReplyTo($mailfrom, $fromname);

        if ($attachfile) {
            $mailer->addAttachment($filename, $attachfile);
        }

        // Try to send the email.
        try {
            if (!$params->get('test_switch', 0)) {
                $return = $mailer->send();
            } else {
                $return = true;
            }
            Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_MAIL_SEND_SUCCESSFUL'), 'success');
        } catch (\Exception $exception) {
            Factory::getApplication()->enqueueMessage(Text::_($exception->getMessage()), 'warning');
            $return = false;
        }

        return $return;
	}


}

