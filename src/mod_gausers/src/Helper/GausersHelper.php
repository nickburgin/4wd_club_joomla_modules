<?php
/*
# ------------------------------------------------------------------------
# @version     5.4
# @copyright   Copyright (C) 2014. All rights reserved.
# @license     GNU General Public License version 2 or later; see LICENSE.txt
# Author:      Glenn Arkell
# Websites:    http://www.glennarkell.com.au
# ------------------------------------------------------------------------
*/

namespace GlennArkell\Module\Gausers\Site\Helper;

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

use \Joomla\CMS\Factory;
use \Joomla\Filesystem\Path;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Application\SiteApplication;
use \Joomla\Database\DatabaseAwareInterface;
use \Joomla\Database\DatabaseAwareTrait;
use \Joomla\Registry\Registry;

class GausersHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Retrieves the records
     * @param array $params An object containing the module parameters
     * @access public
     */
    public static function getList( Registry $params, SiteApplication $app )
    {
		$display_group = $params->get('display_group');
		$prof_suffix = $params->get('prof_suffix');
		$prof_mship = $params->get('prof_mship', 0);
		$prof_field = $params->get('prof_field', 'mdod');

   		$db = Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.id as user_id, a.name, a.email, a.registerDate, substr(a.registerDate,1,4) AS join_year ');
		$query->select(' k.profile_value AS mbr_image ');
		$query->from(' #__user_usergroup_map as m');
		$query->join('LEFT','#__users AS a ON m.user_id = a.id ');
		if (!$prof_mship) {
            $query->select(' substr(p.profile_value,1,4) AS death_year ');
    		$query->join('LEFT','#__user_profiles AS p ON p.user_id = a.id AND p.profile_key = "profile'.$prof_suffix.'.'.$prof_field.'"');
		} else {
            $query->select(' 0 AS death_year ');
        }
        $query->join('LEFT','#__user_profiles AS k ON k.user_id = a.id AND k.profile_key = "profile'.$prof_suffix.'.m_img"');
		$query->where(' m.group_id = '. (int) $display_group);
		$query->order(' a.registerDate ASC');
		$db->setQuery((string)$query);

	    try {
	        $items = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }

    	return $items;
    }


}
?>
