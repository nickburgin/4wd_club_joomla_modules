<?php
/**
 * @version    4.1.2
 * @package    com_gaforsale
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gaforsale\Administrator\Helper;

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
use \Joomla\CMS\Mail\MailTemplate;
use \Joomla\CMS\Mail\MailerFactoryInterface;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Access\Access;
use \Joomla\CMS\Installer\Installer;
use \Joomla\Session\SessionInterface;
use \Joomla\CMS\Application\SiteApplication;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gaforsale\Administrator\Helper\GaforsaleHelper;

/**
 * Gadecisions helper.
 * @since  1.6
 */
class GaemailHelper
{
    protected $autoloadLanguage = true;

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
        $params = ComponentHelper::getParams('com_gaforsale');
	    $app		= Factory::getApplication();
        $mailfrom	= $app->get('mailfrom');       // system email address
        $fromname	= $app->get('fromname');       // Site name or system name
        // Build the email and send
        $mail = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
        $mail->isHTML(true);
		$mail->addRecipient($recipients);
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($subject);
		$mail->setBody($body);
		if (is_file($attachfile)) {
            $mail->addAttachment($attachfile);
        }

        if (!$params->get('set_test', 0)) {
            $sent = $mail->Send();
        }
        Factory::getApplication()->enqueueMessage(Text::_('COM_GAFORSALE_MAIL_SEND_SUCCESSFUL'), 'success');

        return true;
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
        $lang       = Factory::getLanguage()->load('com_gaforsale', JPATH_ADMINISTRATOR);

        $params = ComponentHelper::getParams('com_gaforsale');

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
            if ($params->get('set_test', 1)) {
                $return = true;
            } else {
                $return = $mailer->send();
            }
            Factory::getApplication()->enqueueMessage(Text::sprintf('COM_GAFORSALE_MAIL_SEND_SUCCESSFUL', $data['name']), 'success');
        } catch (\Exception $exception) {
            Factory::getApplication()->enqueueMessage(Text::_($exception->getMessage()), 'warning');
            $return = false;
        }

        return $return;
	}

	/**
	 * Send out an email using the template layout
	 * @param   string  $tmpl  mandatory
	 * @param   object  $item  mandatory
	 * @param   array   $params  component parameters
	 * @return  array	data array
	 */
    public static function setupData($tmpl, $item, $params)
	{
        $lang       = Factory::getLanguage()->load('com_gaforsale', JPATH_ADMINISTRATOR);

        $data['name'] = $item->user_id ? $item->user_name : $item->seller_contact;
		$data['email'] = $item->user_id ? $item->user_email : 'noemail-seller@dummy.com';
        $data['recips'] = array(array('email'=>$data['email'], 'name'=>$data['name']));
        //$data['bcc_recips'] = array(array('email'=>$mailfrom, 'name'=>$fromname));

		// setup the data to include in email
		$data['created_date'] = HtmlHelper::date($item->created_date, Text::_('COM_GAFORSALE_DISPLAY_DATE'));
		$data['item_desc'] = $item->item_desc;
		$data['item_price'] = $item->item_price;
		if ($params->get('incl_details', 0)) {
    		$data['item_details'] = $item->item_details;
		} else {
    		$data['item_details'] = '';
		}
		$data['seller_contact'] = $item->seller_contact;
		$data['seller_phone'] = $item->seller_phone;
		
		return $data;
	}

}

