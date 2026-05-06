<?php

/**
 * @version    4.0.7
 * @package    Com_Gamerchandise
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gamerchandise\Site\Model;

// No direct access.
\defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Layout\FileLayout;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;
use \GlennArkell\Component\Gamerchandise\Administrator\Helper\GamerchandiseHelper;

/**
 * List Model.
 * @since  1.6
 */
class SalesModel extends ListModel
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
				'id', 'a.id',
				'ordering', 'a.ordering',
				'state', 'a.state',
				'created_by', 'a.created_by',
				'modified_by', 'a.modified_by',
				'created_date', 'a.created_date',
				'update_date', 'a.update_date',
				'user_id', 'a.user_id',
				'prod_id', 'a.prod_id', 'p.prod_name',
				'cat_colour_id', 'a.cat_colour_id',
				'cat_size_id', 'a.cat_size_id',
				'order_qty', 'a.order_qty',
				'date_required', 'a.date_required',
				'date_delivered', 'a.date_delivered',
				'part_qty', 'a.part_qty',
				'part_delivered', 'a.part_delivered',
				'ord_amt', 'a.ord_amt',
				'ord_paid', 'a.ord_paid',
				'paid_date', 'a.paid_date',
				'comment', 'a.comment',
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
				$ordering = "cust.name";
			}
			$this->setState('list.ordering', $ordering);
		}
		if(empty($direction)) {
			$direction = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', $app->get('filter_order_Dir'));
			if (!in_array(strtoupper($direction ?? ''), array('ASC', 'DESC', ''))) {
				$direction = "ASC";
			}
			$this->setState('list.direction', $direction);
		}

		$list['limit']     = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $app->get('list_limit'), 'uint');
		$list['start']     = $app->input->getInt('start', 0);
		$list['ordering']  = $ordering;
		$list['direction'] = $direction;
		
		$app->setUserState($this->context . '.list', $list);

        // List state information.
        parent::populateState($ordering, $direction);

        $context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $context);
        $status = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state');
        $this->setState('filter.state', $status);

	}

	/**
	 * Build an SQL query to load the list data.
	 * @return   JDatabaseQuery
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		$user = GamerchandiseHelper::getSpecificUser();
		$canManage = $user->authorise('core.manage', 'com_gamerchandise');

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			[
				$db->quoteName('a.id'),
				$db->quoteName('a.state'),
				$db->quoteName('a.checked_out'),
				$db->quoteName('a.user_id'),
				$db->quoteName('a.order_ref'),
				$db->quoteName('a.order_qty'),
				$db->quoteName('a.ord_amt'),
				$db->quoteName('a.prod_id'),
				$db->quoteName('s.title', 'cat_size_id_name'),
				$db->quoteName('col.title', 'cat_colour_id_name'),
				$db->quoteName('p.prod_name', 'product'),
				$db->quoteName('p.gender', 'gender'),
				$db->quoteName('m.name', 'modified_by_name'),
				$db->quoteName('c.name', 'created_by_name'),
				$db->quoteName('uc.name', 'editor'),
				$db->quoteName('cust.name', 'user_id_name')
			]
		)
		->from($db->quoteName('#__gamerchandise_sales', 'a'))
		->join('LEFT', $db->quoteName('#__users', 'cust'), $db->quoteName('cust.id') . ' = ' . $db->quoteName('a.user_id'))
		->join('LEFT', $db->quoteName('#__users', 'uc'), $db->quoteName('uc.id') . ' = ' . $db->quoteName('a.checked_out'))
		->join('LEFT', $db->quoteName('#__users', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('a.created_by'))
		->join('LEFT', $db->quoteName('#__users', 'm'), $db->quoteName('m.id') . ' = ' . $db->quoteName('a.modified_by'))
		->join('LEFT', $db->quoteName('#__categories', 's'), $db->quoteName('s.id') . ' = ' . $db->quoteName('a.cat_size_id'))
		->join('LEFT', $db->quoteName('#__categories', 'col'), $db->quoteName('col.id') . ' = ' . $db->quoteName('a.cat_colour_id'))
		->join('LEFT', $db->quoteName('#__gamerchandise_products', 'p'), $db->quoteName('p.id') . ' = ' . $db->quoteName('a.prod_id'));

		if ($canManage) {
			// 0 = prepared, 5 = committed
			$query->where($db->quoteName('a.state') . ' IN (0, 1, 3, 4, 5)');
		} else {
			$query->where($db->quoteName('a.user_id') . ' = '.(int) $user->id );
			$query->where($db->quoteName('a.state') . ' IN (0,1,5)');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where($db->quoteName('a.id') . ' = ' . (int) substr($search, 3));
			} else {
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->extendWhere('( ' . $db->quoteName('p.prod_name') . ' LIKE ' . $search . ' OR ' . $db->quoteName('cust.name') . ' LIKE ' . $search . ' )');
			}
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 's.title');
		$orderDirn = $this->state->get('list.direction', 'asc');

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
			$app->enqueueMessage(Text::_("COM_GAMERCHANDISE_SEARCH_FILTER_DATE_FORMAT"), "warning");
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
