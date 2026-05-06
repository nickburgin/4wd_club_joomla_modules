<?php
/*
# ------------------------------------------------------------------------
# @version     3.3.11
# @copyright   Copyright (C) 2014. All rights reserved.
# @license     GNU General Public License version 2 or later; see LICENSE.txt
# Author:      Glenn Arkell
# Websites:    http://www.glennarkell.com.au
# ------------------------------------------------------------------------
*/
// no direct access
defined('_JEXEC') or die( 'Restricted access' );

use \Joomla\CMS\Factory;

class modGatripsysHelper
{
	var $trips;

    /**
     * Retrieves the designated slides to show
     *
     * @param array $params An object containing the module parameters
     * @access public
     */
    public static function getTrips( $params )
    {
        JHtml::_('stylesheet','mod_gatripsys/default.css', false, true);
		$trips_history = $params->get('trips_history', 0);
		$history_age = $params->get('history_age', 0);
		$max_age = $params->get('max_age', 600);
		$only_pub = $params->get('only_pub', 1);
		$only_past = $params->get('only_past', 1);
		$trips_order = $params->get('trips_order', 1);
		// get the current date-time based on timezone
		$date = new DateTime();
		$config = Factory::getConfig();
		$date->setTimezone(new DateTimeZone($config->get('offset')));
		$date->modify('-'.$history_age.' days');
		$today = date_format($date,'Y-m-d H:i:s');
		$date->modify('-'.$max_age.' days');
		$maxage = date_format($date,'Y-m-d H:i:s');

		// Query the trips table to get the records
   		$db = Factory::getDbo();
   		$query = $db->getQuery(true);
   		$query->select(' a.title, a.details, a.leader, a.dept_date, a.ret_date ');
   		$query->from($db->quoteName('#__gatripsys_trips').' AS a');

    	if ($trips_history) {
	   		if ($only_pub) {
			    $query->where($db->quoteName('a.public_view') . ' = 1 ');
			}
	   		$query->where($db->quoteName('a.dept_date') . ' >= '.$db->quote($maxage));
	   		if ($only_past) {
			    $query->where($db->quoteName('a.ret_date') . ' <= '.$db->quote($today));
			}
		} else {
	   		if ($only_pub) {
		   		$query->where($db->quoteName('a.public_view') . ' = 1 ');
			}
	   		if ($only_past) {
			    $query->where($db->quoteName('a.ret_date') . ' <= '.$db->quote($today));
			}
		}

		// 1 = booking, 2 = unpublished, 3 = proposed, 4 = cancelled, 5 = finalised, -2 = trashed
   		$query->where($db->quoteName('a.state') . ' IN (1,2,5)');
   		if ($trips_order) {
		   $query->order($db->quoteName('a.dept_date').' asc');
		} else {
		   $query->order($db->quoteName('a.dept_date').' desc');
		}
   		$db->setQuery((string)$query);
        try {
            $trips = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }

    	return $trips;
    }
}
?>
