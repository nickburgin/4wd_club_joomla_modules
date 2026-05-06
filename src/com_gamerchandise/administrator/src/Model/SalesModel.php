<?php

/**
 * @version    4.0.7
 * @package    Com_Gamerchandise
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gamerchandise\Administrator\Model;

// No direct access.
\defined('_JEXEC') or die;

use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;
use \GlennArkell\Component\Gamerchandise\Administrator\Helper\GamerchandiseHelper;

/**
 * Methods supporting a list of records.
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
				'prod_id', 'a.prod_id',
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
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState('a.created_date', 'DESC');

		// Load the filter state.
		$context = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $context);

		$published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

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
	 * @return   JDatabaseQuery
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select( $this->getState( 'list.select', 'DISTINCT a.*' ) );
		$query->from('#__gamerchandise_sales AS a');

		// Join over the users name for the customer
		$query->select("cust.name AS user_id");
		$query->join("LEFT", "#__users AS cust ON cust.id=a.user_id");

		// Join over the users for the checked out user
		$query->select("uc.name AS editor");
		$query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

		// Join over the user field 'created_by'
		$query->select('created_by.name AS created_by');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		// Join over the user field 'modified_by'
		$query->select('modified_by.name AS modified_by');
		$query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');

		// Join over the user field 'colour'
		$query->select('cat_colour.title AS cat_colour_id');
		$query->join('LEFT', '#__categories AS cat_colour ON cat_colour.id = a.cat_colour_id');

		// Join over the user field 'size'
		$query->select('cat_size.title AS cat_size_id');
		$query->join('LEFT', '#__categories AS cat_size ON cat_size.id = a.cat_size_id');

		// Join over the user field 'product'
		$query->select('prod.prod_name AS product');
		$query->join('LEFT', '#__gamerchandise_products AS prod ON prod.id = a.prod_id');

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
			$query->where('a.state = ' . (int) $published);
		} elseif ($published === '') {
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = ' . (int) substr($search, 3));
			} else {
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( prod.prod_name LIKE ' . $search . ' OR cust.name LIKE ' . $search . ' )');
			}
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		if ($orderCol && $orderDirn) {
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Get an array of data items
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();

		return $items;
	}
}
