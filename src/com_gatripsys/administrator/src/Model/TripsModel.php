<?php
/**
 * @version    5.3.0
 * @package    com_gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatripsys\Administrator\Model;
// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Methods supporting a list of records.
 * @since  1.6
 */
class TripsModel extends ListModel
{
    
        
/**
	* Constructor.
	* @param   array  $config  An optional associative array of configuration settings.
	* @see        JController
	* @since      1.6
	*/
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.`id`',
				'ordering', 'a.`ordering`',
				'state', 'a.`state`',
				'created_by', 'a.`created_by`',
				'modified_by', 'a.`modified_by`',
				'created_date', 'a.`created_date`',
				'modified_date', 'a.modified_date',
				'title', 'a.title',
				'rating', 'a.rating',
				'leader', 'a.leader',
				'trip_tec', 'a.trip_tec',
				'suited_for', 'a.suited_for',
				'trip_type', 'a.trip_type',
				'regby_date', 'a.regby_date',
				'max_no', 'a.max_no',
				'min_no', 'a.min_no',
				'dept_date', 'a.dept_date',
				'ret_date', 'a.ret_date',
				'dept_loc', 'a.dept_loc',
				'ret_loc', 'a.ret_loc',
				'equip', 'a.equip',
				'where_go', 'a.where_go',
				'what_do', 'a.what_do',
				'details', 'a.details',
				'trip_img', 'a.trip_img',
				'insurance_cat', 'a.insurance_cat',
				'public_view', 'a.public_view',
				'max_people', 'a.max_people',
				'trip_plan', 'a.trip_plan',
				'trip_cost', 'a.trip_cost',
				'comment', 'a.`comment`',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 * @return void
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
        // List state information.
        parent::populateState("a.dept_date", "DESC");

        $context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $context);

		// Filtering state
		$published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_published');
		$this->setState('filter.state', $published);
		// Filtering rating
		$rating = $this->getUserStateFromRequest($this->context.'.filter.rating', 'filter_rating');
		$this->setState('filter.rating', $rating);

	}

	/**
	 * Method to get a store id based on model configuration state.
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 * @param   string  $id  A prefix for the store id.
	 * @return   string A store id.
	 * @since    1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

        return parent::getStoreId($id);
                
	}

	/**
	 * Build an SQL query to load the list data.
	 * @return   DatabaseQuery
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		$disp_daysago  = ComponentHelper::getParams('com_gatripsys')->get('disp_daysago', 5);
		$tz = Factory::getConfig()->get('offset');
		$date = Factory::getDate('now', $tz);
		$today = date_format($date,'Y-m-d H:i:s');
		if ($disp_daysago) {
			$today5 = $date->modify( ' -' . $disp_daysago . ' day');
		} else {
			$today5 = $date->modify( ' -22 year');
		}

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select( $this->getState( 'list.select', 'DISTINCT a.*' ) );

		$query->from('`#__gatripsys_trips` AS a');
                
		// Join over the users for the checked out user
		$query->select("uc.name AS uEditor");
		$query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

		// Join over the user field 'created_by'
		$query->select('`created_by`.name AS `created_by`');
		$query->join('LEFT', '#__users AS `created_by` ON `created_by`.id = a.`created_by`');

		// Join over the user field 'modified_by'
		$query->select('`modified_by`.name AS `modified_by`');
		$query->join('LEFT', '#__users AS `modified_by` ON `modified_by`.id = a.`modified_by`');

		// Join over the user field 'leader'
		$query->select('tl.name AS leader_name');
		$query->join('LEFT', '#__users AS tl ON tl.id = a.leader');
		$query->select('tec.name AS tec_name');
		$query->join('LEFT', '#__users AS tec ON tec.id = a.trip_tec');

		// Join over the category 'rating'
		$query->select('rating.title AS rating_name');
		$query->join('LEFT', '#__categories AS rating ON rating.id = a.rating');

		// Join over the category 'trip_type'
		$query->select('tript.title AS trip_type_name');
		$query->join('LEFT', '#__categories AS tript ON tript.id = a.trip_type');

		// Join over the category 'suited_for'
		$query->select('suited.title AS suited_for_name');
		$query->join('LEFT', '#__categories AS suited ON suited.id = a.suited_for');

		// Join over the category 'insurance_cat'
		$query->select('ins.title AS insurance_cat_name');
		$query->join('LEFT', '#__categories AS ins ON ins.id = a.insurance_cat');

		// Filter by published state
        $status = $this->getState('filter.state');
		if (is_numeric($status)) {
			$query->where('a.state = ' . (int) $status);
		} elseif ($status === '*') {
			// show all and don't filter on status
		} else {
			$query->where('(a.state IN (0, 1, 2, 3, 4, 5, 6))');
        }

        //$query->where(' ( a.dept_date >= '.$db->Quote($today5).' OR a.ret_date >= '.$db->Quote($today).' )');

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = ' . (int) substr($search, 3));
			} else {
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.title LIKE ' . $search . '  OR  tl.name LIKE ' . $search . ' )');
			}
		}
                
		//Filtering rating
		$filter_rating = $this->state->get("filter.rating");
		if ($filter_rating) {
			$query->where("a.rating = '".$db->escape($filter_rating)."'");
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol && $orderDirn) {
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		} else {
			$query->order($db->escape(' a.dept_date DESC '));
		}

		return $query;
	}

	/**
	 * Get an array of data items
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();
                
		return $items;
	}
}
