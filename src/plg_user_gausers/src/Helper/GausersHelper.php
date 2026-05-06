<?php
/**

 * @version     5.1.5                                                     
 * @package     com_gausers
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Plugin\User\Gausers\Helper;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;

/**
 * Gausers helper.
 */
class GausersHelper
{
    /**
     * Gets the user record for the specific id reference
     */
    public static function getSpecificUser($id = 0)
	{
		if ($id) {
			$container = Factory::getContainer();
			$userFactory = $container->get(UserFactoryInterface::class);
			$user = $userFactory->loadUserById($id);
		} else {
			$user = Factory::getApplication()->getIdentity();
		}
		unset($user->password);
		unset($user->activation);
		unset($user->params);
		unset($user->lastResetTime);
		unset($user->resetCount);
		unset($user->otpKey);
		unset($user->otep);
		unset($user->requireReset);

		return $user;
	}

	/**
	 * Get member from database
	 * @param   array  $mbr - user details
	 * @param   object  $params.
	 * @return  object  ObjectList of members data
	 */
    public static function checkNewUser($mbr = 0)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select(' a.id, a.name, a.email, a.block, a.registerDate, a.lastvisitDate ' );
        $query->select(' if(d.profile_value IS NULL, "", d.profile_value) AS address1 ');
        $query->select(' if(e.profile_value IS NULL, "", e.profile_value) AS address2 ');
        $query->select(' if(f.profile_value IS NULL, "", f.profile_value) AS city ');
        $query->select(' if(g.profile_value IS NULL, "", g.profile_value) AS postal_code ');
        $query->select(' if(k.profile_value IS NULL, "", k.profile_value) AS phone ');
        $query->from('#__users AS a');
		$query->join('LEFT','#__user_profiles AS d ON d.user_id = a.id AND d.profile_key = "profile.address1" ');
		$query->join('LEFT','#__user_profiles AS e ON e.user_id = a.id AND e.profile_key = "profile.address2" ');
		$query->join('LEFT','#__user_profiles AS f ON f.user_id = a.id AND f.profile_key = "profile.city" ');
		$query->join('LEFT','#__user_profiles AS g ON g.user_id = a.id AND g.profile_key = "profile.postal_code" ');
		$query->join('LEFT','#__user_profiles AS k ON k.user_id = a.id AND k.profile_key = "profile.phone" ');
		$query->where(' a.name = ' . $db->Quote($mbr['name']) . ' OR a.email = ' . $db->Quote($mbr['email']) );

        $db->setQuery($query);
		try {
			return $db->loadObject();
		} catch (RuntimeException $e) {
			$this->app->enqueueMessage($e->getMessage(), 'danger');
			return false;
		}
	}

	/**
	 * Update member record as disabled
	 * @param   array  $idList - csv of enabled users
	 * @return  boolean
	 */
    public static function disabledUsers($idList = 0)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->update(' #__users ');
        $query->set(' block = 1 ' );
		$query->where(' id NOT IN (' . $idList . ')' );

        $db->setQuery($query);
		try {
            $db->execute();
			return $db->getAffectedRows();
		} catch (RuntimeException $e) {
			$this->app->enqueueMessage($e->getMessage(), 'danger');
			return false;
		}
	}
}
