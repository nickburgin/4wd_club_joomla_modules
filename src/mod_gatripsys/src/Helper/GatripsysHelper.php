<?php
/**
 * @package     com_gatripsys
 * @subpackage  mod_gatripsys
 * @version     5.0.4
 * @author      Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright   2021 Glenn Arkell
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Module\Gatripsys\Site\Helper;

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

use \Joomla\CMS\Factory;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Date\Date;
use \Joomla\CMS\Uri\Uri;

class GatripsysHelper
{
    /**
     * Retrieves the records
	 * @param   Joomla\Registry\Registry &$params module parameters
	 * @return array Array with all the elements
     */
    public static function getList( &$params )
    {
        HTMLHelper::stylesheet(Uri::base().'media/mod_gatripsys/css/default.css');
		$trips_history = $params->get('trips_history', 0);
		$history_age = $params->get('history_age', 0);
		$max_age = $params->get('max_age', 600);
		$only_pub = $params->get('only_pub', 1);
		$past = $params->get('only_past', 1);
		$trips_order = $params->get('trips_order', 1);
		// get the current date-time based on timezone
		$today = new Date();
		if ($history_age) {
			$today5 = new Date( 'now -' . $history_age . ' day');
		} else {
			$today5 = new Date( 'now -' . $max_age . ' day');
		}

		// Query the trips table to get the records
   		$db = Factory::getContainer()->get('DatabaseDriver');
   		$query = $db->getQuery(true);
   		$query->select(' a.* ');
   		$query->from($db->quoteName('#__gatripsys_trips').' AS a');

    	if ($trips_history) {
	   		if ($only_pub) {
			    $query->where($db->quoteName('a.public_view') . ' = 1 ');
			}
	   		$query->where($db->quoteName('a.dept_date') . ' >= '.$db->quote($today5));
	   		if ($past) {
			    $query->where($db->quoteName('a.ret_date') . ' <= '.$db->quote($today));
			}
		} else {
	   		if ($only_pub) {
		   		$query->where($db->quoteName('a.public_view') . ' = 1 ');
			}
	   		if ($past) {
			    $query->where($db->quoteName('a.ret_date') . ' <= '.$db->quote($today));
			} else {
		   		$query->where($db->quoteName('a.ret_date') . ' >= '.$db->quote($today));
			}
		}

		// Join over the category 'rating'
		$query->select('rating.title AS rating_name, rating.id AS rating_id, rating.params AS cat_params');
		$query->join('LEFT', '#__categories AS rating ON rating.id = a.rating');

		// Join over the category 'trip_type'
		$query->select('tript.title AS trip_type_name');
		$query->join('LEFT', '#__categories AS tript ON tript.id = a.trip_type');

		// Join over the category 'suited_for'
		$query->select('suited.title AS suited_for_name');
		$query->join('LEFT', '#__categories AS suited ON suited.id = a.suited_for');

		// Trip Status -
		// 1=Proposed and needs to be approved by trip coord,
		// 2=Bookings - Approved and taking Bookings - waiting for trip leader to close trip,
		// 4=Closed and waiting on trip-coord to finalise,
		// 5=Cancelled,
		// 6=Finalised or completed,
   		$query->where($db->quoteName('a.state') . ' IN (2,4,6)');
   		if ($trips_order) {
		   $query->order($db->quoteName('a.dept_date').' asc');
		} else {
		   $query->order($db->quoteName('a.dept_date').' desc');
		}
   		$db->setQuery((string)$query);
        try {
            $rows = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }

    	return $rows;
    }
}
?>
