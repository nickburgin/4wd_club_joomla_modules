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

/**
 * Main helper.
 * @since  1.6
 */
class GanamesHelper
{
	/**
	 * Get all the name fields as an object
	 * @param   id  $user id.
	 * @param   profsuf  $profsuf used in this component
	 * @return  object	membership names object
	 */
    public static function breakdownNamesFromUserID($id, $profpartner)
	{
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select(' a.id, a.name, a.email, a.block, a.registerDate, a.lastvisitDate ' );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 1), ' ', -1) AS first_name " );
        $query->select(" If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 2), ' ', -1) ,NULL) as middle1_name " );
        $query->select(" If( If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 3), ' ', -1) ,NULL) = SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1), null, If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 3), ' ', -1) ,NULL)) as middle2_name " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1) AS last_name " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 1), ' ', -1) AS first_namep " );
        $query->select(" If( length(h.profile_value) - length(replace(h.profile_value, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 2), ' ', -1) ,NULL) as middle1_namep " );
        $query->select(" If( If( length(h.profile_value) - length(replace(h.profile_value, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 3), ' ', -1) ,NULL) = SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 4), ' ', -1), null, If( length(h.profile_value) - length(replace(h.profile_value, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 3), ' ', -1) ,NULL)) as middle2_namep " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 4), ' ', -1) AS last_namep " );
        $query->select(" h.profile_value AS partner " );
        $query->from('#__users AS a');
        $query->join('LEFT', ' #__user_profiles AS h ON h.user_id = a.id AND h.profile_key = '.$db->quote($profpartner));
        $query->where('a.id = ' . (int) $id );
        $db->setQuery($query);
		try {
			$member =  $db->loadObject();
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
			$member = false;
		}

		return $member;

	}

	/**
	 * Break up the name fields and form a display name version
	 * @param   object  $member  data including name, partner and the breakup firstnames and surnames.
	 * @return  string	combined name for the membership display
	 */
    public static function combineNames($member = null)
	{
        if (isset($member->partner)) {
            $member->partner = str_replace('"','',$member->partner);
            $member->first_namep = trim(str_replace('"','',$member->first_namep));
            $member->last_namep = trim(str_replace('"','',$member->last_namep));
        }

        if (isset($member->partner) && !empty($member->partner) && $member->partner != ' ') {
			if (trim($member->last_name) == trim($member->last_namep)) {
				$result = trim($member->first_name). ' & ' .trim($member->first_namep) . ' ' . trim($member->last_name);
			} else {
				$result = trim($member->name). ' & ' .trim($member->partner);
			}
		} else {
            $result = $member->name;
        }

		return $result;

	}

}

