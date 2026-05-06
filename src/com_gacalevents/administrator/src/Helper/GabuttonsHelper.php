<?php
/**
 * @version    3.0.0
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gacalevents\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\CMS\MVC\Model\ItemModel;
use \Joomla\Data\DataObject;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Access\Access;
use \Joomla\CMS\Installer\Installer;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\User\UserFactoryInterface;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GanamesHelper;

/**
 * Main helper.
 * @since  1.6
 */
class GabuttonsHelper
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
	public static function getHTTPQuery($existQ = null, $viewTask = 'view', $contModel = 'event', $ref = 'id', $linkId = 0)
	{
		if (!$existQ) {
			$query_string = array();
			$query_string['option'] = 'com_gacalevents';
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
	 * Builds the html to display buttons
	 * @param   Object  $event being the event ready to display
	 * @param   array  $attendee record list
	 * @param   Object  $user currently logged in
	 * @param   boolean  if the user is an approver true else false
	 * @param   Object  $params component parameters
	 * @return  string  html of all relevant buttons to display
	 */
	public static function buildButtons($event = 0, $attendees = 0, $user = 0, $apprvuser = false, $params = 0)
	{
		// Initialize variables.
		$app  = Factory::getApplication();
		$htmlBtn = array();
		$htmlAtt = array('<div class="attendances">');
		$htmlApl = array();
		$attRecordExists = false;
		$AttRecID = 0;
        $numberAtts = 0;
        $numberApols = 0;
        $show_reminder = $params->get('show_reminder', 0);
		$dload_attend = $params->get('dload_attend', 0);
		$notif_attend = $params->get('notif_attend', 0);
		$show_reminder = $params->get('show_reminder', 0);
		$charge_event = $params->get('charge_event', 0);
		$allow_pub = $params->get('allow_pub', 0);
		$jointMship = $params->get('partner_mship', 0);
		// check vaccination highlight
		$highlight_attendee = $params->get('highlight_attendee', 0);
		$vaxed_fld = $params->get('vaxed_fld', 0);
		$exempt_fld = $params->get('exempt_fld', 0);
		$profsuf = $params->get('profile_suffix', 0);
		$profpart = $params->get('profile_partner', 'partner');
		$profpartner = 'profile'.$profsuf.'.'.$profpart;

		$attRecord = GacaleventsHelper::checkAttendee($event->id, $user->id);
        if (isset($attRecord) && $attRecord->id > 0) { $attRecordExists = true; $AttRecID = $attRecord->id; }

		// Build all the possible buttons
        //$attURL = Route::_('index.php?option=com_gacalevents&task=event.eventrego&eventaction=1&event_id='.$event->id.'&attendee='.$user->id, false, 0);
        $attURL = Route::_('index.php?option=com_gacalevents&task=attendeeform.edit&id=0&event_id='.$event->id.'&attendee='.$user->id.'&formal='.$event->formal_event, false, 0);
        $attBtn = '<a class="btn btn-secondary" title="'.Text::_('COM_GACALEVENTS_ATTEND_EVENT').'" ';
        $attBtn .= 'href="'.$attURL.'" ';
        $attBtn .= ' "="" data-original-title="'.Text::_('COM_GACALEVENTS_ATTEND_EVENT').'"><i class="icon-publish" style="color: var(--success);"></i></a> ';

        $aplURL = Route::_('index.php?option=com_gacalevents&task=event.eventrego&eventaction=0&event_id='.$event->id.'&attendee='.$user->id, false, 0);
        $aplBtn = '<a class="btn btn-secondary" title="'.Text::_('COM_GACALEVENTS_SUBMIT_APOLOGY').'" ';
        $aplBtn .= 'href="'.$aplURL.'" ';
        $aplBtn .= ' "="" data-original-title="'.Text::_('COM_GACALEVENTS_SUBMIT_APOLOGY').'"><i class="icon-minus"></i></a> ';

        $updURL = Route::_('index.php?option=com_gacalevents&task=eventform.edit&id='.$event->id, false, 0);
        $updBtn = '<a class="btn btn-warning" title="'.Text::_('COM_GACALEVENTS_UPDATE_EVENT').'" ';
        $updBtn .= 'href="'.$updURL.'" ';
        $updBtn .= ' "="" data-original-title="'.Text::_('COM_GACALEVENTS_UPDATE_EVENT').'"><i class="icon-edit"></i></a> ';

        $delURL = Route::_('index.php?option=com_gacalevents&task=event.remove&id='.$event->id, false, 0);
        $delBtn = '<a class="btn btn-danger" title="'.Text::_('COM_GACALEVENTS_DELETE_EVENT').'" ';
        $delBtn .= 'href="'.$delURL.'" ';
        $delBtn .= ' "="" data-original-title="'.Text::_('COM_GACALEVENTS_DELETE_EVENT').'"><i class="icon-trash"></i></a> ';

        $bokURL = Route::_('index.php?option=com_gacalevents&task=attendeeform.edit&id=0&event_id='.$event->id.'&formal='.$event->formal_event, false, 0);
        $bokBtn = '<a class="btn btn-success" title="'.Text::_('COM_GACALEVENTS_BOOKON_EVENT').'" ';
        $bokBtn .= 'href="'.$bokURL.'" ';
        $bokBtn .= ' "="" data-original-title="'.Text::_('COM_GACALEVENTS_BOOKON_EVENT').'"><i class="icon-publish inverse"></i></a> ';

        $remURL = Route::_('index.php?option=com_gacalevents&task=event.reminder&event_id='.$event->id, false, 0);
        $remBtn = '<a class="btn btn-secondary" title="'.Text::_('COM_GACALEVENTS_REMIND_EVENT').'" ';
        $remBtn .= 'href="'.$remURL.'" ';
        $remBtn .= ' "="" data-original-title="'.Text::_('COM_GACALEVENTS_REMIND_EVENT').'"><i class="icon-flash" style="padding:5px;"></i></a> ';

        $dnlURL = Route::_('index.php?option=com_gacalevents&task=event.extractAttendees&event_id='.$event->id, false, 0);
        $dnlBtn = '<a class="btn btn-secondary pull-right " title="'.Text::_('COM_GACALEVENTS_DLOAD_ATTEND_DESC').'" ';
        $dnlBtn .= 'href="'.$dnlURL.'" ';
        $dnlBtn .= ' "="" data-original-title="'.Text::_('COM_GACALEVENTS_DLOAD_ATTEND_DESC').'"><i class="icon-download"></i></a> ';

        $revURL = Route::_('index.php?option=com_gacalevents&task=event.removeAtt&id='.$AttRecID, false, 0);
        $revBtn = '<a class="btn btn-danger" title="'.Text::_('COM_GACALEVENTS_DELETE_ATTEND').'" ';
        $revBtn .= 'href="'.$revURL.'" ';
		$revBtn .= ' "="" data-original-title="'.Text::_('COM_GACALEVENTS_DELETE_ATTEND').'"><i class="icon-trash"></i></a> ';

        $resURL = Route::_('index.php?option=com_gacalevents&task=attendee.publish&id='.$AttRecID.'&state=1', false, 0);
        $resBtn = '<a class="btn btn-secondary" title="'.Text::_('COM_GACALEVENTS_ATTEND_EVENT').'" ';
        $resBtn .= 'href="'.$resURL.'" ';
		$resBtn .= ' "="" data-original-title="'.Text::_('COM_GACALEVENTS_ATTEND_EVENT').'"><i class="icon-publish"></i></a> ';

        $attLink = self::getHTTPQuery(null, 'view', 'attendeeform', 'id', 0);
        $attLink = self::getHTTPQuery($attLink, null, null, 'event_id', $event->id);
        $attLink = self::getHTTPQuery($attLink, null, null, 'attendee', $user->id);
        $attLink = self::getHTTPQuery($attLink, null, null, 'tmpl', 'component');
        $modparams = array( 'url'        => 'index.php?'.http_build_query($attLink, '', '&amp;'),
        	        'title'      => Text::_("COM_GACALEVENTS_ATTEND_EVENT"), 'closeButton'=> true,
        	        'modalWidth' => 60, 'bodyHeight' => 35, 'backdrop'   => 'static' );
        $modname = 'modal-myLeftModal'.$event->id;
        $htmlModal = '<a class="btn btn-secondary" href="#'.$modname.'" data-bs-toggle="modal">';
        $htmlModal .= '<i class="fas fa-sign-out-alt" title="'.Text::_('COM_GACALEVENTS_ATTEND_EVENT').'"></i></a>';
        $htmlModal .= HTMLHelper::_('bootstrap.renderModal', $modname, $modparams);

        $notifLink = self::getHTTPQuery(null, 'view', 'eventform', 'event_id', $event->id);
        $notifLink = self::getHTTPQuery($notifLink, null, null, 'layout', 'modalemail');
        $notifLink = self::getHTTPQuery($notifLink, null, null, 'tmpl', 'component');
        $notifmodparams = array( 'url'        => 'index.php?'.http_build_query($notifLink, '', '&amp;'),
        	        'title'      => Text::_("COM_GACALEVENTS_NOTIF_EVENT"), 'closeButton'=> true,
        	        'modalWidth' => 60, 'bodyHeight' => 35, 'backdrop'   => 'static' );
        $notifmodname = 'modal-myNotifModal'.$event->id;
        $htmlModalnotif = '<a class="btn btn-info" href="#'.$notifmodname.'" data-bs-toggle="modal">';
        $htmlModalnotif .= '<i class="fas fa-mail-bulk" title="'.Text::_('COM_GACALEVENTS_NOTIF_EVENT').'"></i></a>';
        $htmlModalnotif .= HTMLHelper::_('bootstrap.renderModal', $notifmodname, $notifmodparams);

        $notifURL = Route::_('index.php?option=com_gacalevents&task=event.notify&event_id='.$event->id, false, 0);
        $notifBtn = '<a class="btn btn-secondary" title="'.Text::_('COM_GACALEVENTS_NOTIF_EVENT').'" ';
        $notifBtn .= 'href="'.$notifURL.'" ';
        $notifBtn .= ' "="" data-original-title="'.Text::_('COM_GACALEVENTS_NOTIF_EVENT').'"><i class="fas fa-mail-bulk" style="padding:5px;"></i></a> ';

		$unpubURL = Route::_('index.php?option=com_gacalevents&task=event.publish&id=' . $event->id . '&state=' . (($event->state + 1) % 2), false, 2);
		$unpubBtn = '<a class="btn btn-micro" href="'.$unpubURL.'"><i class="icon-info"></i></a>';

		// Logic to work out what buttons to display
		if ($event)
		{
			if ($event->state == 0  && $apprvuser) {
				$htmlBtn[] = '<div style="width:98%;padding:5px;background-color:red;color:white;">'.Text::_("COM_GACALEVENTS_UNPUBLISHED_EVENT").'<br />'.$unpubBtn.'</div><br /><br />';
			}

            // setup the date to display
			if ($event->depart_date == $event->return_date || $event->return_date == '0000-00-00 00:00:00' || \is_null($event->return_date) ) {
                $depart_date = HTMLHelper::_('date', $event->depart_date, Text::_('COM_GACALEVENTS_DISPLAY_DATE'));
                $htmlBtn[] = '<div class="center">' . $depart_date . '</div>';
			} else {
                $depart_date = HTMLHelper::_('date', $event->depart_date, Text::_('COM_GACALEVENTS_DISPLAY_DATE'));
                $return_date = HTMLHelper::_('date', $event->return_date, Text::_('COM_GACALEVENTS_DISPLAY_DATE'));
				$htmlBtn[] = '<div class="center">'.$depart_date.'<br /><br />'.$return_date.'</div>';
			}
		}

		//$allow_pub = true;
		if ($user->id == 0 && $allow_pub) {
			if ($event->max_attend && $numberAtts >= $event->max_attend) {
				$htmlBtn[] = '<div class="center" style="width:98%;padding:10px;">'.$attBtn.'<br />'.Text::_('COM_GACALEVENTS_ATTEND_EVENT').'</div>';
			} else {
				$htmlBtn[] = '<div class="center" style="width:98%;padding:10px;">'.Text::_('COM_GACALEVENTS_EVENT_FULL').'</div>';
			}
		}

		if ($user && $user->id > 0)
		{
			if (is_array($attendees)) {
				$numberTotal = count($attendees);
				$htmlAtt[] = '<div class="span6 attending"><span style="text-decoration:underline wavy;"> Attending </span><br />';
				$htmlApl[] = '<div class="span6 apologies"><span style="text-decoration:underline wavy;"> Apologies </span><br />';
				// cycle through to check user is listed
				foreach ($attendees AS $att) {
					// setup list and count details
					if ($highlight_attendee) {
						$vaxed = GacaleventsHelper::isUserVaccinated($att->attendee, $vaxed_fld, $exempt_fld);
					 	if ($vaxed) { $hl_style = ''; } else { $hl_style = 'color:#ff0000;'; }
					} else {
					 	$hl_style = '';
					}
					// identify if a partner included
					if ($jointMship) {
                        $mbr = GanamesHelper::breakdownNamesFromUserID($att->attendee, $profpartner);
						$fullname = GanamesHelper::combineNames($mbr);
					} else {
                        $fullname = $att->pub_name;
                    }
                    // trim last & if it exists
                    $fullname = (substr($fullname,-1) != '&') ? $fullname : substr($fullname,0,-1);

                    if (strpos($fullname ?? '','&')) { $qtyAtt = 2; } else { $qtyAtt = 1; }

					// identify if a guest included
                    if ($att->qty_att > $qtyAtt) {
                        $plusGuests = ' (+ '.($att->qty_att - $qtyAtt).')';
                    } elseif ($att->qty_att < $qtyAtt) {
                        $plusGuests = ' (1 only)';
                    } else {
                        $plusGuests = '';
                    }

					if ($att->state == 1) {
						$numberAtts = ($numberAtts + $att->qty_att);
		                $htmlAtt[] = '<span style="'.$hl_style.'">'.$fullname . $plusGuests.'</span><br />';
					} elseif ($att->state == 0) {
                        $numberApols = ($numberApols + $att->qty_att);
		                $htmlApl[] = '<span style="'.$hl_style.'">'.$fullname . $plusGuests.'</span><br />';
					}
				}

				$htmlAtt[] = '</div>';
				$htmlApl[] = '</div></div>';
				$attendances = implode($htmlAtt);
				$app->setUserState('com_gacalevents.attendancenames.data', $attendances);
				$apologies = implode($htmlApl);
				$app->setUserState('com_gacalevents.apologynames.data', $apologies);
			}
            if ($event->max_attend) {
				$htmlBtn[] = '<div class="center" style="width:98%;padding:10px;">'.Text::sprintf('COM_GACALEVENTS_MAX_ATTEND',$event->max_attend).'</div>';
			}
			$htmlBtn[] = '<div class="center" style="width:98%;padding:10px;">Attending: '.$numberAtts.'</div>';

			// buttons for attending or apology or both
			if ($attRecordExists) {
				if ($attRecord->state == 1) {
					// attending so show apology button
					$htmlBtn[] = '<div class="center" style="width:98%;padding:10px;">'.$aplBtn.' &nbsp; &nbsp; &nbsp; '.$revBtn.'</div>';
				} elseif ($attRecord->state == 0) {
					if ($event->max_attend && $numberAtts >= $event->max_attend) {
						// don't show attend button
						$htmlBtn[] = '<div class="center" style="width:98%;padding:10px;">'.Text::_('COM_GACALEVENTS_EVENT_FULL').'</div>';
						$htmlBtn[] = '<div class="center" style="width:98%;padding:10px;">'.$revBtn.'</div>';
					} else {
						// apology so show attending button
						$htmlBtn[] = '<div class="center" style="width:98%;padding:10px;">'.$resBtn.' &nbsp; &nbsp; &nbsp; '.$revBtn.'</div>';
					}
				} else {
					if ($event->max_attend && $numberAtts >= $event->max_attend) {
						// don't show attend button
						$htmlBtn[] = '<div class="center" style="width:98%;padding:10px;">'.Text::_('COM_GACALEVENTS_EVENT_FULL').'</div>';
						$htmlBtn[] = '<div class="center" style="width:98%;padding:10px;">'.$aplBtn.'</div>';
					} else {
						// TODO get modal working and use $htmlModal
						// show both button
						$htmlBtn[] = '<div class="center" style="width:98%;padding:10px;">'.$attBtn.' &nbsp; &nbsp; &nbsp; '.$aplBtn.'</div>';
					}
				}
			} else {
				if ($event->max_attend && $numberAtts >= $event->max_attend) {
					// don't show attend button
					$htmlBtn[] = '<div class="center" style="width:98%;padding:10px;">'.Text::_('COM_GACALEVENTS_EVENT_FULL').'</div>';
					$htmlBtn[] = '<div class="center" style="width:98%;padding:10px;">'.$aplBtn.'</div>';
				} else {
					// show both button
					$htmlBtn[] = '<div class="center" style="width:98%;padding:10px;">'.$attBtn.' &nbsp; &nbsp; &nbsp; '.$aplBtn.'</div>';
				}
			}

			if ($event->pub_cost > 0.00 || $event->mbr_cost > 0.00) {
				$htmlBtn[] = '<div class="center" style="width:98%;padding:10px;">';
				$htmlBtn[] = '<p> </p><p><strong>Charges for this Event:</strong><br />';
				if ($event->pub_cost > 0.00) {
					$htmlBtn[] = 'Non-Member: $'.number_format($event->pub_cost,2).'</p></div>';
				}
				if ($event->mbr_cost > 0.00) {
					$htmlBtn[] = 'Member: $'.number_format($event->mbr_cost,2).'<br />';
				}
				$htmlBtn[] = '</div>';
			}

			/* Now set up other buttons where necessary */
			if ($apprvuser) {
				$htmlBtn[] = '<div class="center" style="width:98%;padding:10px;">';
				$htmlBtn[] = $updBtn . ' &nbsp; &nbsp; ' . $delBtn . '</div>';
				$htmlBtn[] = '<div class="center" style="width:98%;padding:10px;">';
				if ($show_reminder) {
					$htmlBtn[] = $remBtn . ' &nbsp; &nbsp; ';
				}
				$htmlBtn[] = $bokBtn . '</div>';
	            if ($dload_attend) {
					$htmlBtn[] = '<div class="center" style="width:98%;padding:10px;">' . $dnlBtn . '</div>';
				}
	            if ($notif_attend) {
					$htmlBtn[] = '<div class="center" style="width:98%;padding:10px;">' . $htmlModalnotif . '</div>';
				}
			}
		}

		return implode($htmlBtn);
	}

}

