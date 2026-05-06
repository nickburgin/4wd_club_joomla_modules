<?php
/**
 * @version     4.3.3
 * @package     com_gabroadcast
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gabroadcast\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Database\ParameterType;   //INTEGER, STRING, BOOLEAN, NULL, LARGE_OBJECT

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
        $params  = ComponentHelper::getParams('com_gabroadcast');
		$prof_pref  = $params->get('profile_suffix', 'b4wdc');

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select(
            [
                $db->quoteName('a.id'),
                $db->quoteName('a.name'),
                $db->quoteName('a.email'),
                $db->quoteName('a.block'),
                $db->quoteName('a.registerDate'),
                $db->quoteName('a.lastvisitDate'),
                $db->quoteName('d.profile_value', 'address1'),
                $db->quoteName('f2.profile_value', 'address2'),
                $db->quoteName('f.profile_value', 'suburb'),
                $db->quoteName('fr.profile_value', 'region'),
                $db->quoteName('g.profile_value', 'pcode'),
                $db->quoteName('k.profile_value', 'altphone'),
                $db->quoteName('l.profile_value', 'fwdvic_no'),
                $db->quoteName('b.profile_value', 'phone'),
                $db->quoteName('h.profile_value', 'partner'),
                $db->quoteName('e.profile_value', 'inc_altemail'),
                $db->quoteName('p.profile_value', 'altemail'),
                $db->quoteName('p0.profile_value', 'use_post'),
                $db->quoteName('p1.profile_value', 'postal_address1'),
                $db->quoteName('p2.profile_value', 'postal_address2'),
                $db->quoteName('p3.profile_value', 'postal_suburb'),
                $db->quoteName('p4.profile_value', 'postal_region'),
                $db->quoteName('p5.profile_value', 'postal_pcode'),
            ]
            )
            ->select('DATE_FORMAT(a.registerDate, "%Y-%m-%d") as regoDate')
            ->select("SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 1), ' ', -1) AS firstname" )
            ->select("SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 1), ' ', -1) AS first_name" )
            ->select("If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 2), ' ', -1) ,NULL) as middle1_name" )
            ->select("If( If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 3), ' ', -1) ,NULL) = SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1), null, If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 3), ' ', -1) ,NULL)) as middle2_name" )
            ->select("SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1) AS surname" )
            ->select("SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1) AS last_name" )
            ->select("SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 1), ' ', -1) AS firstnamep" )
            ->select("SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 1), ' ', -1) AS first_namep" )
            ->select("If( length(h.profile_value) - length(replace(h.profile_value, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 2), ' ', -1) ,NULL) as middle1_namep" )
            ->select("If( If( length(h.profile_value) - length(replace(h.profile_value, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 3), ' ', -1) ,NULL) = SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 4), ' ', -1), null, If( length(h.profile_value) - length(replace(h.profile_value, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 3), ' ', -1) ,NULL)) as middle2_namep" )
            ->select("SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 4), ' ', -1) AS surnamep" )
            ->select("SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 4), ' ', -1) AS last_namep" )
            ->from($db->quoteName('#__users', 'a'))
            ->join('LEFT', $db->quoteName('#__user_profiles', 'e'), $db->quoteName('e.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('e.profile_key') . ' = '.$db->Quote('profile'.$prof_pref.'.inc_altemail'))
            ->join('LEFT', $db->quoteName('#__user_profiles', 'h'), $db->quoteName('h.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('h.profile_key') . ' = '.$db->Quote('profile'.$prof_pref.'.partner'))
            ->join('LEFT', $db->quoteName('#__user_profiles', 'k'), $db->quoteName('k.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('k.profile_key') . ' = '.$db->Quote('profile'.$prof_pref.'.altphone'))
            ->join('LEFT', $db->quoteName('#__user_profiles', 'l'), $db->quoteName('l.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('l.profile_key') . ' = '.$db->Quote('profile'.$prof_pref.'.fwdvic_no'))
            ->join('LEFT', $db->quoteName('#__user_profiles', 'b'), $db->quoteName('b.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('b.profile_key') . ' = '.$db->Quote('profile.phone'))
            ->join('LEFT', $db->quoteName('#__user_profiles', 'd'), $db->quoteName('d.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('d.profile_key') . ' = '.$db->Quote('profile.address1'))
            ->join('LEFT', $db->quoteName('#__user_profiles', 'f2'), $db->quoteName('f2.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('f2.profile_key') . ' = '.$db->Quote('profile.address2'))
            ->join('LEFT', $db->quoteName('#__user_profiles', 'f'), $db->quoteName('f.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('f.profile_key') . ' = '.$db->Quote('profile.city'))
            ->join('LEFT', $db->quoteName('#__user_profiles', 'fr'), $db->quoteName('fr.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('fr.profile_key') . ' = '.$db->Quote('profile.region'))
            ->join('LEFT', $db->quoteName('#__user_profiles', 'g'), $db->quoteName('g.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('g.profile_key') . ' = '.$db->Quote('profile.postal_code'))
            ->join('LEFT', $db->quoteName('#__user_profiles', 'p'), $db->quoteName('p.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('p.profile_key') . ' = '.$db->Quote('profile'.$prof_pref.'.altemail'))
            ->join('LEFT', $db->quoteName('#__user_profiles', 'p0'), $db->quoteName('p0.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('p0.profile_key') . ' = '.$db->Quote('profile'.$prof_pref.'.use_post'))
            ->join('LEFT', $db->quoteName('#__user_profiles', 'p1'), $db->quoteName('p1.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('p1.profile_key') . ' = '.$db->Quote('profile'.$prof_pref.'.postal_address1'))
            ->join('LEFT', $db->quoteName('#__user_profiles', 'p2'), $db->quoteName('p2.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('p2.profile_key') . ' = '.$db->Quote('profile'.$prof_pref.'.postal_address2'))
            ->join('LEFT', $db->quoteName('#__user_profiles', 'p3'), $db->quoteName('p3.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('p3.profile_key') . ' = '.$db->Quote('profile'.$prof_pref.'.postal_suburb'))
            ->join('LEFT', $db->quoteName('#__user_profiles', 'p4'), $db->quoteName('p4.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('p4.profile_key') . ' = '.$db->Quote('profile'.$prof_pref.'.postal_region'))
            ->join('LEFT', $db->quoteName('#__user_profiles', 'p5'), $db->quoteName('p5.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('p5.profile_key') . ' = '.$db->Quote('profile'.$prof_pref.'.postal_pcode'))
            ->where($db->quoteName('a.id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
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

