<?php
/**
 * @version    6.0.0
 * @package    Com_Gausers
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gausers\Administrator\Helper;

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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\User\UserFactoryInterface;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;

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
    public static function breakdownNamesFromUserID($id, $profpartner = 'partner')
	{
		//Factory::getApplication()->setUserState('com_gausers.test.data', $id);
        $params  = ComponentHelper::getParams('com_gausers');
		$prof_pref  = $params->get('profile_suffix', 'b4wdc');

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select(' a.id, a.name, a.email, a.block, a.registerDate, DATE_FORMAT(a.registerDate, "%Y-%m-%d") as regoDate, a.lastvisitDate ' );
		$query->select(' d.profile_value as address1, f2.profile_value as address2, f.profile_value as suburb, fr.profile_value as region ');
		$query->select(' g.profile_value as pcode, k.profile_value as altphone ');
		$query->select(' l.profile_value as fwdvic_no, b.profile_value as phone');
        $query->select(' h.profile_value AS partner, p0.profile_value AS use_post ' );
        $query->select(' p.profile_value AS altemail, e.profile_value AS inc_altemail ');
        $query->select(' p1.profile_value AS postal_address1, p2.profile_value AS postal_address2 ');
        $query->select(' p3.profile_value AS postal_suburb, p4.profile_value AS postal_region ');
        $query->select(' p5.profile_value AS postal_pcode ');

        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 1), ' ', -1) AS firstname " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 1), ' ', -1) AS first_name " );
        $query->select(" If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 2), ' ', -1) ,NULL) as middle1_name " );
        $query->select(" If( If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 3), ' ', -1) ,NULL) = SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1), null, If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 3), ' ', -1) ,NULL)) as middle2_name " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1) AS surname " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1) AS last_name " );

        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 1), ' ', -1) AS firstnamep " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 1), ' ', -1) AS first_namep " );
        $query->select(" If( length(h.profile_value) - length(replace(h.profile_value, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 2), ' ', -1) ,NULL) as middle1_namep " );
        $query->select(" If( If( length(h.profile_value) - length(replace(h.profile_value, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 3), ' ', -1) ,NULL) = SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 4), ' ', -1), null, If( length(h.profile_value) - length(replace(h.profile_value, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 3), ' ', -1) ,NULL)) as middle2_namep " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 4), ' ', -1) AS surnamep " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 4), ' ', -1) AS last_namep " );

        $query->from('#__users AS a');
		$query->join('LEFT', '#__user_profiles AS p ON p.user_id = a.id AND p.profile_key = '.$db->Quote('profile'.$prof_pref.'.altemail'));
		$query->join('LEFT', '#__user_profiles AS e ON e.user_id = a.id AND e.profile_key = '.$db->Quote('profile'.$prof_pref.'.inc_altemail'));
		$query->join('LEFT', '#__user_profiles AS h ON h.user_id = a.id AND h.profile_key = '.$db->Quote('profile'.$prof_pref.'.partner'));
		$query->join('LEFT', '#__user_profiles AS k ON k.user_id = a.id AND k.profile_key = '.$db->Quote('profile'.$prof_pref.'.altphone'));
		$query->join('LEFT', '#__user_profiles AS l ON l.user_id = a.id AND l.profile_key = '.$db->Quote('profile'.$prof_pref.'.fwdvic_no'));
		$query->join('LEFT', '#__user_profiles AS b ON b.user_id = a.id AND b.profile_key = '.$db->Quote('profile.phone'));
		$query->join('LEFT', '#__user_profiles AS d ON d.user_id = a.id AND d.profile_key = '.$db->Quote('profile.address1'));
		$query->join('LEFT', '#__user_profiles AS f2 ON f2.user_id = a.id AND f2.profile_key = '.$db->Quote('profile.address2'));
		$query->join('LEFT', '#__user_profiles AS f ON f.user_id = a.id AND f.profile_key = '.$db->Quote('profile.city'));
		$query->join('LEFT', '#__user_profiles AS fr ON fr.user_id = a.id AND fr.profile_key = '.$db->Quote('profile.region'));
		$query->join('LEFT', '#__user_profiles AS g ON g.user_id = a.id AND g.profile_key = '.$db->Quote('profile.postal_code'));

		$query->join('LEFT', '#__user_profiles AS p0 ON p0.user_id = a.id AND p0.profile_key = '.$db->Quote('profile'.$prof_pref.'.use_post'));
		$query->join('LEFT', '#__user_profiles AS p1 ON p1.user_id = a.id AND p1.profile_key = '.$db->Quote('profile'.$prof_pref.'.postal_address1'));
		$query->join('LEFT', '#__user_profiles AS p2 ON p2.user_id = a.id AND p2.profile_key = '.$db->Quote('profile'.$prof_pref.'.postal_address2'));
		$query->join('LEFT', '#__user_profiles AS p3 ON p3.user_id = a.id AND p3.profile_key = '.$db->Quote('profile'.$prof_pref.'.postal_city'));
		$query->join('LEFT', '#__user_profiles AS p4 ON p4.user_id = a.id AND p4.profile_key = '.$db->Quote('profile'.$prof_pref.'.postal_region'));
		$query->join('LEFT', '#__user_profiles AS p5 ON p5.user_id = a.id AND p5.profile_key = '.$db->Quote('profile'.$prof_pref.'.postal_post_code'));

        $query->where('a.id = ' . (int) $id );
        $db->setQuery($query);
		try {
			$member =  $db->loadObject();

            if (empty($member)) {
                $member = false;
                Factory::getApplication()->enqueueMessage('Member with id ref: '. $id . ' has been deleted', 'danger');
            } else {
        		$member->inc_altemail = (isset($member->inc_altemail) && !empty($member->inc_altemail)) ? str_replace('"', '', $member->inc_altemail) : '';
        		$member->altemail = (isset($member->altemail) && !empty($member->altemail)) ? str_replace('"', '', $member->altemail) : '';
        		$member->fullname = self::combineNames($member);
    		}
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

