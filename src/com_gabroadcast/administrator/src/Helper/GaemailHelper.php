<?php
/**
 * @version     4.3.3
 * @package     com_gabroadcast
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @author      Glenn Arkell <glenn@glennarkell.com.au>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gabroadcast\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\User\UserHelper;

/**
 * Gatripsys helper.
 * @since  1.6
 */
class GaemailHelper
{

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
        $app->getLanguage()->load('com_gabroadcast', JPATH_ADMINISTRATOR);
        $app->getLanguage()->load('com_gabroadcast', JPATH_SITE);

        $params = ComponentHelper::getParams('com_gabroadcast');
        
        if ($link) {
            $mode = $app->get('force_ssl', 0) == 2 ? 1 : (-1);
            $data['link_text'] = '<p style="text-align:center;"><a href="'.Route::_($link, false, $mode).'">'.$fromname.'</a></p>';
        } else {
            $data['link_text'] = '<p style="text-align:center;">'.$fromname.'</p>';;
        }    

        // Build the email and send
        $mailer = new MailTemplate($template_id, $app->getLanguage()->getTag());
        $mailer->addTemplateData($data);
        $sentTo = '';
        // set the recipients with third param options ('to', 'cc', 'bcc')
        if (!empty($data['recips'])) {
            foreach ($data['recips'] as $recip) {
                $mailer->addRecipient($recip['email'], $recip['name'], 'to');
                $sentTo .=  $recip['name'] . ' - ' . $recip['email'];
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

        if (!empty($data['replyto']) && \is_array($data['replyto'])) {
            $mailer->setReplyTo(trim($data['replyto'][0]), trim($data['replyto'][1]));
        } else {
            $mailer->setReplyTo($mailfrom, $fromname);
        }

        if ($attachfile && \is_file($attachfile)) {
            $mailer->addAttachment($filename, $attachfile);
        }

        // Try to send the email.
        try {
	        if ($params->get('actually_send', 0)) {
                $app->enqueueMessage(Text::_('Actually sent - '). $sentTo, 'notice');
                return $mailer->Send();
            } else {
                return true;
	        }
        } catch (\Exception $exception) {
            $app->enqueueMessage(Text::_($exception->getMessage()), 'warning');
            return false;
        }

	}

	/**
	 * gather name and emails from members data
	 * @param   array  $data  mandatory - array of submitted information
	 * @param   object  $params  saves getting them again
	 * @return  array	$names and $emails as an assoc array
	 */
    public static function setupBody($data, $user, $params)
	{
	    $app		= Factory::getApplication();
        $mailfrom	= $app->get('mailfrom');
        $fromname	= $app->get('fromname');
        $app->getLanguage()->load('com_gabroadcast', JPATH_ADMINISTRATOR);
        $app->getLanguage()->load('com_gabroadcast', JPATH_SITE);
        $attach = $params->get('attach_link', 1);   // 1 = attach, 0 = link
        $edata = array();
        $edata['site_link'] = $params->get('link_site', 1) ? Uri::base() : null;

        if ($params->get('incl_unsub', 1)) {
            $unsub = '<a href="'.Uri::base().'/index.php/'.Text::_('COM_GABROADCAST_UNSUBSCRIBE').'">'.Text::_('COM_GABROADCAST_UNSUBSCRIBE').'</a>';
            $unsubscribe = Text::sprintf('COM_GABROADCAST_UNSUB_MESSAGE', $unsub);
            $edata['unsubscribe'] = $unsubscribe;
        }

        // setup the email content
        if (!empty($data['attach_file'])) {
            $edata['filename'] = $data['filename'];
            $edata['link_text'] = '<a href="'.Uri::base().$data['attach_file'].'">'.Text::_('COM_GABROADCAST_LINK_TEXT').'</a>';
			if ($attach) {
                $edata['attach_file'] = $data['attach_file'];
            } else {
                $edata['attach_file'] = null;
                $data['news_detail'] = $data['news_detail'] . '<p> </p><p style="text-align:center;">' . $edata['link_text'] . '</p>';
            }
        } else {
			$edata['link_text'] = '<a href="'.Uri::base().'">'.Text::_('COM_GABROADCAST_LINK_TEXT').'</a>';
			$edata['attach_file'] = null;
			$edata['filename'] = null;
		}

		$edata['sitename'] = $fromname;
        $edata['body'] = $data['news_detail'];
		$edata['subject'] = $data['news_subject'];
		$edata['recips'] = array();
		$edata['sender'] = $user->name;
		$edata['replyto'] = '';

        return $edata;
	}

	/**
	 * gather name and emails from members data
	 * @param   array  $members  mandatory - array of user information objects
	 * @param   object  $params  saves getting them again
	 * @return  array	$names and $emails as an assoc array
	 */
    public static function getRecipients($members, $data, $params)
	{
		$recipients = array();
		$snailmail = array();
        $exEmail = $params->get('exclude_email_pref', 'noemail');
		$exLen = strlen($exEmail);
		$sendto_group  = $params->get('sendto_group', 2);
		$filterUsers = $params->get('filter_users',0);
		$filterType = $params->get('filter_type','p');

		// override the send to group if group being used in filter
        if (isset($data['usergroup_only']) && $data['usergroup_only']) {
            $sendto_group = $data['usergroup_only'];
		}

        foreach ($members AS $m)
        {
            /* ------------   Filter out based on user filter settings  ---------------   */
            if ($filterUsers) {
                if ($filterType == 'p') {
                    if ($data['user_proffld'] == "All" || str_contains($m->profFld_value, $data['user_proffld'])) {
                    } else {
                        continue;
                    }
                } else {
                    if ($data['user_custfld'] == "All" || $data['user_custfld'] == $m->custFld_value) {
                    } else {
                        continue;
                    }
                }
            }
            
            /* ------------   Filter out based on user group settings  ---------------   */
            $inGroup = false;
			$ug = UserHelper::getUserGroups($m->user_id);

            $inGroup = (!$inGroup && in_array($sendto_group, $ug)) ? true : $inGroup;

			$m->inc_altemail = trim(str_replace('"','',$m->inc_altemail ?? ''));
			$m->altemail = trim(str_replace('"','',$m->altemail ?? ''));
			$m->partner = trim(str_replace('"','',$m->partner ?? ''));
            if ($inGroup) {
                if (substr($m->email,0,$exLen) != $exEmail) {
                    $recipients[] = array('name'=>$m->name, 'email'=>$m->email);
                } else {
                    $snailmail[] = $m->name;
                }
                if ($m->inc_altemail &&
                    isset($m->altemail) &&
                    $m->altemail > '' &&
                    substr($m->altemail,0,$exLen) != $exEmail &&
                    isset($m->partner) &&
                    $m->partner > '')
                {
    				$recipients[] = array('name'=>$m->partner, 'email'=>$m->altemail);
    			}
			}
		}
		
		Factory::getApplication()->setUserState('com_gabroadcast.snailmail.list', $snailmail);

		return $recipients;

	}


}

