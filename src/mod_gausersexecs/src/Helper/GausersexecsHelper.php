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

namespace GlennArkell\Module\Gausersexecs\Site\Helper;

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

use \Joomla\CMS\Application\CMSApplication;
use \Joomla\CMS\Factory;
use \Joomla\Filesystem\Path;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\Registry\Registry;

class GausersexecsHelper
{
	var $execs;

    /**
     * Retrieves the designated slides to show
     * @param array $params An object containing the module parameters
     * @access public
     */
    public static function getExecMembers(Registry $params)
    {
		$show_past = $params->get('show_past', 0);
    	// Query the adverts table to get the ads to display
   		$db = Factory::getContainer()->get('DatabaseDriver');
   		$query = $db->getQuery(true);
   		$query->select(' a.*, DATE_FORMAT(end_term, "%D %b %Y") AS disp_edate');
   		$query->from('#__gausers_club_execs AS a');
   		if ($show_past) {
		    $query->where('a.state IN (0,1) ');
		} else {
			$query->where('a.state = 1 ');
		}	
   		$query->order('a.end_term DESC ');
   		$db->setQuery((string)$query);

	    try {
	        $execs = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }

    	return $execs;
    }
}

?>
