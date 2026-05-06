<?php
/**
 * @version    3.1.0
 * @package    pkg_gacalevents
 * @subpackage com_gacalevents
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

/**
 * Main helper.
 * @since  1.6
 */
class GavcardHelper
{
	/**
	 * Get the calendar event data and create a VCalendar file
	 * @param   id  $user id.
	 * @param   event  $event_id
	 * @return  file
	 */
    public static function createVcalendar($id, $event)
	{
        $file = 'BEGIN:VCALENDAR'."\r\n";
        $file .= 'VERSION:2.0'."\r\n";
        $file .= 'BEGIN:VEVENT'."\r\n";
        $file .= 'SUMMARY:'.$item->title."\r\n";


DTSTART:20250317T230000Z
DTEND:20250318T000000Z
DTSTAMP:20250315T231157Z
UID:1742080317624-TestEvent
DESCRIPTION:Lorem ipsum dolor sit amet. A laudantium rerum ad aliquam excepturi aut nisi magnam.
LOCATION:Somewhere
ORGANIZER:Guesswho
STATUS:CONFIRMED
PRIORITY:0
END:VEVENT
END:VCALENDAR

		return $vcard;

	}

}

