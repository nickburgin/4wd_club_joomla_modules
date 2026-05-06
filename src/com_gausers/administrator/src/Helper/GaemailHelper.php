<?php
/**
 * @version     6.0.0
 * @package     com_gausers
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @author      Glenn Arkell <glenn@glennarkell.com.au>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gausers\Administrator\Helper;

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
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GanamesHelper;

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
    public static function sendEmail($recipients, $body, $subject, $attachfile, $bcc = false, $reply_sender = false)
	{
        $user = Factory::getApplication()->getIdentity();
        $params = ComponentHelper::getParams('com_gausers');
	    $app		= Factory::getApplication();
        $mailfrom	= $app->get('mailfrom');       // system email address
        $fromname	= $app->get('fromname');       // Site name or system name

        // Build the email and send
        $mail = Factory::getMailer();
        $mail->isHTML(true);
		if (is_array($recipients)) {
            foreach ($recipients as $email) {
                if ($bcc) {
                    $mail->addBcc($email);
                    $mail->addRecipient($mailfrom);
                } else {
                    $mail->addRecipient($email);
                }
            }
        }

		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($subject);
		$mail->setBody($body);
		if ($reply_sender) {
            $mail->addReplyTo(array($user->email));
        }
		if (is_file($attachfile)) {
            $mail->addAttachment($attachfile);
        }

	    try {
	        if (!$params->get('test_block', 0)) {
                $mail->Send();
            }
	        return true;
        } catch (\Exception $e) {
            $app->enqueueMessage(Text::_($e->getMessage()), 'warning');
	        return false;
	    }
	}

	/**
	 * Setup the content or body of the email
	 * @param   object  $item  mandatory (full record of the item)
	 * @return  boolean	false on fail
	 */
    public static function setupEmailContent($item)
	{
    	$body = Text::sprintf('COM_GAUSERS_NOTIFY_MEMBERS_BODY',$item->name);
    	$body .= '<p> </p>';
    	$body .= '<p>'.$item->sitename.'</p>';

        return $body;
	}

	/**
	 * Send out an email using the template layout
	 * @param   string  $template_id  mandatory (full template_id ie com_gausers.mbrnew)
	 * @param   object  $item  mandatory - containing all relevant info
	 * @param   string  $link  optional
	 * @param   string  $filename  optional - name of the file if required without full path
	 * @param   string  $attachment  optional - including full path
	 * @return  boolean	false on fail
	 */
    public static function sendEmailTmpl($template_id, $item, $link = null, $filename = null, $attachfile = null)
	{
        $lang = Factory::getApplication()->getLanguage();
        $lang->load('com_gausers', JPATH_ADMINISTRATOR);
        $params = ComponentHelper::getParams('com_gausers');
	    $app		= Factory::getApplication();
	    $mailfrom	= $app->get('mailfrom');

        // load all the data elements as set in the template setup
        $data = array();
        $data['sitename'] = $app->get('sitename');
        if ($link) {
            $mode = $app->get('force_ssl', 0) == 2 ? 1 : (-1);
            $data['link_text'] = Route::_(Uri::base(), false, $mode);
        }
        $data['name'] = $item->name;
        $data['email'] = $item->email;

        // Build the email and send
        $mailer = new MailTemplate($template_id, $app->getLanguage()->getTag());
        $mailer->addTemplateData($data);
        $mailer->setReplyTo($mailfrom, $data['sitename']);
        $mailer->addRecipient($item->email, $item->name);

        if ($attachfile) {
            $mailer->addAttachment($filename, $attachfile);
        }

        // Try to send the email.
        try {
            if (!$params->get('test_block', 0)) {
                $return = $mailer->send();
                $app->enqueueMessage(Text::_('COM_GAUSERS_MAIL_SENT_SUCCESSFUL'), 'success');
            } else {
                $return = true;
            }
        } catch (\Exception $exception) {
            $app->enqueueMessage(Text::_($exception->getMessage()), 'warning');
            $return = false;
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
            $member = GanamesHelper::breakdownNamesFromUserID($m);
            if ($params->get('mship_single',0))
            {
                $name = GanamesHelper::combineNames($member);
    		} else {
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
    public static function sendEmailTemplate($template_id, $data, $filename = null, $link = null, $attachfile = null)
	{
	    $app		= Factory::getApplication();
        $mailfrom	= $app->get('mailfrom');
        $fromname	= $app->get('fromname');
        $lang = Factory::getApplication()->getLanguage();
        $lang->load('com_gausers', JPATH_ADMINISTRATOR);
        $lang->load('com_gausers', JPATH_SITE);

        $params = ComponentHelper::getParams('com_gausers');
        
        if ($link) {
            $mode = $app->get('force_ssl', 0) == 2 ? 1 : (-1);
            $data['link_text'] = Route::_($link, false, $mode);
        } else {
            $data['link_text'] = '';
        }    

        // Build the email and send
        $mailer = new MailTemplate($template_id, $app->getLanguage()->getTag());
        $mailer->addTemplateData($data);

        // set the recipients with third param options ('to', 'cc', 'bcc')
        if (!empty($data['recips'])) {
            foreach ($data['recips'] as $recip) {
                $mailer->addRecipient($recip['email'], $recip['name'], 'to');
            }
        }

        if (!empty($data['cc_recips'])) {
            foreach ($data['cc_recips'] as $ccs) {
                $mailer->addRecipient($ccs['email'], $ccs['name'], 'cc');
            }
        }

        if (!empty($data['bcc_recips'])) {
            if (empty($data['recips'])) {
                $mailer->addRecipient($mailfrom, $fromname, 'to');
            }
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
            if (!$params->get('test_block', 0)) {
                $return = $mailer->send();
            } else {
                $return = true;
            }
            Factory::getApplication()->enqueueMessage(Text::_('COM_GAUSERS_MAIL_SENT_SUCCESSFUL'), 'success');
        } catch (\Exception $exception) {
            Factory::getApplication()->enqueueMessage(Text::_($exception->getMessage()), 'warning');
            $return = false;
        }

        return $return;
	}


}

