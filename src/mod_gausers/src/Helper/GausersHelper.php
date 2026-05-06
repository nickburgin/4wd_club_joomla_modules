<?php
/*
# ------------------------------------------------------------------------
# @version     5.1.6
# @copyright   Copyright (C) 2014. All rights reserved.
# @license     GNU General Public License version 2 or later; see LICENSE.txt
# Author:      Glenn Arkell
# Websites:    http://www.glennarkell.com.au
# ------------------------------------------------------------------------
*/

namespace GlennArkell\Module\Gausers\Site\Helper;

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

use \Joomla\CMS\Application\CMSApplication;
use \Joomla\CMS\Factory;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\Registry\Registry;

class GausersHelper
{
	var $members;

    /**
     * Retrieves the records
     * @param array $params An object containing the module parameters
     * @access public
     */
    public static function getMembers( $params )
    {
		$display_group = $params->get('display_group');
		$prof_suffix = $params->get('prof_suffix');
		$prof_field = $params->get('prof_field', 'mdod');

   		$db = Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.id as user_id, a.name, a.email, a.registerDate, substr(a.registerDate,1,4) AS join_year ');
		$query->select(' substr(p.profile_value,1,4) AS death_year, k.profile_value AS mbr_image ');
		$query->from(' #__user_usergroup_map as m');
		$query->join('LEFT','#__users AS a ON m.user_id = a.id ');
		$query->join('LEFT','#__user_profiles AS p ON p.user_id = a.id AND p.profile_key = "profile'.$prof_suffix.'.'.$prof_field.'"');
		$query->join('LEFT','#__user_profiles AS k ON k.user_id = a.id AND k.profile_key = "profile'.$prof_suffix.'.m_img"');
		$query->where(' m.group_id = '. (int) $display_group);
		$query->order(' a.registerDate ASC');
		$db->setQuery((string)$query);

	    try {
	        $members = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }

    	return $members;
    }


}
?>
