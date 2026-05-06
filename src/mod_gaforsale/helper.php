<?php
/*
# ------------------------------------------------------------------------
# @version     3.0.09
# @copyright   Copyright (C) 2014. All rights reserved.
# @license     GNU General Public License version 2 or later; see LICENSE.txt
# Author:      Glenn Arkell
# Websites:    http://www.glennarkell.com.au
# ------------------------------------------------------------------------
*/
// no direct access
defined('_JEXEC') or die( 'Restricted access' );

use Joomla\CMS\Factory;

class modGaforsaleHelper
{
	var $fsitems;

    /**
     * Retrieves the designated slides to show
     *
     * @param array $params An object containing the module parameters
     * @access public
     */
    public static function getFsitemDetails( $params )
    {
        JHtml::_('stylesheet','mod_gaforsale/default.css', false, true);

    	// Query the articles table to get the articles in the selected category
   		$db = Factory::getDbo();
   		$query = $db->getQuery(true);
   		$query->select(' count(*) ');
   		$query->from($db->quoteName('#__gaforsale_fsitems'));
   		$query->where($db->quoteName('state') . ' = 1 ');
   		$db->setQuery((string)$query);
     	if (!$db->execute()) {
            throw new Exception(500, $db->getErrorMsg());
		} else {
			$fsitems = $db->loadResult();
		}

    	return $fsitems;
    }
}
?>
