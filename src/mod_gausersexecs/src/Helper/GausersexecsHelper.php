<?php
/*
# ------------------------------------------------------------------------
# @version     5.3
# @copyright   Copyright (C) 2014. All rights reserved.
# @license     GNU General Public License version 2 or later; see LICENSE.txt
# Author:      Glenn Arkell
# Websites:    http://www.glennarkell.com.au
# ------------------------------------------------------------------------
*/

namespace GlennArkell\Module\Gausersexecs\Site\Helper;

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

class GausersexecsHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Retrieves the designated slides to show
     * @param array $params An object containing the module parameters
     * @access public
     */
    public static function getList( Registry $params, SiteApplication $app )
    {
		$show_past = $params->get('show_past', 0);
    	// Query the adverts table to get the ads to display
   		$db = Factory::getContainer()->get('DatabaseDriver');
   		$query = $db->getQuery(true);
   		$query->select(' * ');
   		$query->from($db->quoteName('#__gausers_club_execs'));
   		if ($show_past) {
		    $query->where($db->quoteName('state') . ' IN (0,1) ');
		} else {
			$query->where($db->quoteName('state') . ' = ' . (int) 1);
		}	
   		$query->order($db->quoteName('end_term') . ' DESC ');
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
