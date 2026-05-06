<?php

/**
 * @version    4.3.3
 * @package    Com_Gabroadcast
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gabroadcast\Site\Model;

\defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\View\GenericDataException;
use \GlennArkell\Component\Gabroadcast\Administrator\Helper\GabroadcastHelper;

/**
 * Methods supporting a list of Gabroadcast records.
 *
 * @since  1.6
 */
class UsernewsModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'ordering', 'a.ordering',
				'state', 'a.state',
				'created_by', 'a.created_by',
				'modified_by', 'a.modified_by',
				'created_date', 'a.created_date',
				'modified_date', 'a.modified_date',
				'cat_id', 'a.cat_id',
				'fin_users_only', 'a.fin_users_only',
				'news_subject', 'a.news_subject',
				'news_detail', 'a.news_detail',
				'comment', 'a.comment',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since    1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
        $app  = Factory::getApplication();
		$list = $app->getUserState($this->context . '.list');

		$ordering  = isset($list['filter_order'])     ? $list['filter_order']     : null;
		$direction = isset($list['filter_order_Dir']) ? $list['filter_order_Dir'] : null;

		if(empty($ordering)) {
			$ordering = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', $app->get('filter_order'));
			if (!in_array($ordering, $this->filter_fields)) {
				$ordering = "a.created_date";
			}
			$this->setState('list.ordering', $ordering);
		}
		if(empty($direction)) {
			$direction = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', $app->get('filter_order_Dir'));
			if (!in_array(strtoupper($direction ?? ''), array('ASC', 'DESC', ''))) {
				$direction = "DESC";
			}
			$this->setState('list.direction', $direction);
		}

		$list['limit']     = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $app->get('list_limit'), 'uint');
		$list['start']     = $app->input->getInt('start', 0);
		$list['ordering']  = $ordering;
		$list['direction'] = $direction;

		$app->setUserState($this->context . '.list', $list);
		//$app->input->set('list', null);   // if you use this, list always defaults to Global

        // List state information.
        parent::populateState($ordering, $direction);

        $context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $context);
        $status = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state');
        $this->setState('filter.state', $status);
        $catid = $this->getUserStateFromRequest($this->context.'.filter.cat_id', 'filter_cat_id');
        $this->setState('filter.cat_id', $catid);

	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
        $menu = Factory::getApplication()->getMenu()->getActive();
        $viewType = $menu->getParams()->get('view_type', 0);

        // Create a new query object.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select($this->getState( 'list.select', 'DISTINCT a.*' ) );
        $query->from('#__gabroadcast_usernews AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS uEditor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the created by field 'created_by'
		$query->select('c.name AS created_by_name');
		$query->join('LEFT', '#__users AS c ON c.id = a.created_by');

		// Join over the created by field 'modified_by'
		$query->select('m.name AS modified_by_name');
		$query->join('LEFT', '#__users AS m ON m.id = a.modified_by');
            
		// Join over the category field 'cat_id'
		$query->select('cat.title AS cat_id_name');
		$query->join('LEFT', '#__categories AS cat ON cat.id = a.cat_id');

		// Only show the broadcasts based on state selected in menu option
		if ($viewType) {
			$query->where('a.state = ' . (int) $viewType);
		} else {
            $filter_state = $this->getState('filter.state');
            if ($filter_state) {
                $query->where('a.state = '.(int) $filter_state);
            } else {
                $query->where('(a.state IN (0, 1))');
            }
        }

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.news_subject LIKE ' . $search . ' )');
            }
        }


		// Filtering cat_id
		$filter_cat_id = $this->state->get("filter.cat_id");
		if ($filter_cat_id) {
			$query->where("a.cat_id = '".$db->escape($filter_cat_id)."'");
		}

        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', "a.created_date");
        $orderDirn = $this->state->get('list.direction', "DESC");

        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
	}

	/**
	 * Method to get an array of data items
	 * @return  mixed An array of data on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();


		return $items;
	}

	/**
	 * Overrides the default function to check Date fields format, identified by
	 * "_dateformat" suffix, and erases the field if it's not correct.
	 * @return void
	 */
	protected function loadFormData()
	{
		$app              = Factory::getApplication();
		$filters          = $app->getUserState($this->context . '.filter', array());
		$error_dateformat = false;

		foreach ($filters as $key => $value) {
			if (strpos($key, '_dateformat') && !empty($value) && $this->isValidDate($value) == null) {
				$filters[$key]    = '';
				$error_dateformat = true;
			}
		}

		if ($error_dateformat) {
			$app->enqueueMessage(Text::_("COM_GABROADCAST_SEARCH_FILTER_DATE_FORMAT"), "warning");
			$app->setUserState($this->context . '.filter', $filters);
		}

		return parent::loadFormData();
	}

	/**
	 * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
	 * @param   string  $date  Date to be checked
	 * @return bool
	 */
	private function isValidDate($date)
	{
		$date = str_replace('/', '-', $date);
		return (date_create($date)) ? Factory::getDate($date)->format("Y-m-d") : null;
	}
}
