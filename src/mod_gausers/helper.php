<?php
/*
# ------------------------------------------------------------------------
# @version     4.2.0
# @copyright   Copyright (C) 2014. All rights reserved.
# @license     GNU General Public License version 2 or later; see LICENSE.txt
# Author:      Glenn Arkell
# Websites:    http://www.glennarkell.com.au
# ------------------------------------------------------------------------
*/
// no direct access
defined('_JEXEC') or die( 'Restricted access' );

use \Joomla\CMS\Factory;

class modGausersHelper
{
	var $members;

    /**
     * Retrieves the designated slides to show
     *
     * @param array $params An object containing the module parameters
     * @access public
     */
    public static function getMembers( $params )
    {
        //JLoader::register('GausersHelper', JPATH_ADMINISTRATOR . '/components/com_gausers/helpers/gausers.php');
		JHtml::_('stylesheet','mod_gausers/default.css', false, true);
		$display_group = $params->get('display_group');
		$prof_suffix = $params->get('prof_suffix');
		$prof_field = $params->get('prof_field', 'mdod');

		//$news = GausersHelper::getNews();
		$db		= Factory::getDbo();
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
	        // If it fails, it will throw a RuntimeException
	        $members = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }

    	return $members;
    }


}
?>
