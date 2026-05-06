<?php
/**
 * @version    5.1.6
 * @package    Com_Gausers
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gausers\Administrator\Helper;

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
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;

/**
 * Main helper.
 * @since  1.6
 */
class GafamilyHelper
{
	/**
	 * Get all the family members based on profile settings
	 * @param   id  $user id.
	 * @param   profsuf  $profsuf used in this component
	 * @return  object	membership names object
	 */
    public static function getFamilyMembers($id, $prof = 'docs.mship_no')
	{
		//Factory::getApplication()->setUserState('com_gausers.test.data', $id);
		$p_key = 'profile'.$prof;

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select(' a.user_id AS id, a.profile_value AS mship_number ');
        $query->from('#__user_profiles AS a');
        $query->where('a.user_id = ' . (int) $id );
        $query->where('a.profile_key = ' . $db->Quote($p_key) );
        $query->where('a.profile_value > 0 ' );
        $db->setQuery($query);
		try {
			$family =  $db->loadObject();

            if (empty($family)) {
                Factory::getApplication()->enqueueMessage('Member with id ref: '. $id . ' has no '.$prof.' reference', 'warning');
                return 0;
            } else {
                // if child/partner of family record, ignore
                $mNo = $family->mship_number;
                if (str_contains($mNo,".") === false) {
                    $famMbrs = $mNo.'.1,'.$mNo.'.2,'.$mNo.'.3,'.$mNo.'.4,'.$mNo.'.5,'.$mNo.'.6,'.$mNo.'.7';
                    $db1 = Factory::getContainer()->get('DatabaseDriver');
                    $query1 = $db1->getQuery(true);
                    $query1->select(' a.user_id AS id, a.profile_value AS mship_number ');
                    $query1->from('#__user_profiles AS a');
                    $query1->where('a.profile_key = ' . $db1->Quote($p_key) );
                    $query1->where('a.profile_value IN (' . $famMbrs . ')' );
                    $db1->setQuery($query1);
            		try {
            			return $db1->loadObjectList();
            		} catch (RuntimeException $e) {
            			Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
            			return 0;
            		}
        		} else {
                    return false;
        		}
    		}
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
			return 0;
		}
		
	}

}

