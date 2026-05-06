<?php

/**
 * @version     5.3
 * @package     pkg_gacalevents
 * @subpackage  mod_gacalevents
 * @author      Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright   2021 Glenn Arkell
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Module\Gacalevents\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Filesystem\Path;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\SiteApplication;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Registry\Registry;

/**
 * Helper for mod_gacalevents
 * @package     com_gacalevents
 * @subpackage  mod_gacalevents
 * @since       1.6
 */
class GacaleventsHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Retrieves the records to display
     * @param array $params An object containing the module parameters
     * @access public
     */
    public static function getEvents( Registry $params, SiteApplication $app )
    {
		$details_len = $params->get('details_len', 200);

		$date = Factory::getDate();
		//$date = new Date();
        $today = \date_format($date,'Y-m-d');

		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query
			->select(" *, SUBSTRING(event_details, 1, ".(int)$details_len.") AS edetails ")
			->from('#__gacalevents_events')
			->where('state = 1')
			->where('SUBSTRING(depart_date,1,10) >= '.$db->quote($today))
			->order('depart_date ASC');

		$db->setQuery($query);
		$items = $db->loadObjectList();

		return $items;
	}

}
