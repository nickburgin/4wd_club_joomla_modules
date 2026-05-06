<?php
/**
 * @version    5.1.0
 * @package    Com_Gafinance
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gafinance\Administrator\Model;
// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;

/**
 * Multiples model.
 * @since  1.6
 */
class TransactionsModel extends ListModel
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
				'modified_date', 'a.modified_date',
				'user_id', 'a.user_id',
				'tran_type', 'a.tran_type',
				'tran_date', 'a.tran_date',
				'tran_amount', 'a.tran_amount',
				'tran_desc', 'a.tran_desc',
				'tran_file', 'a.tran_file',
				'accnt_id', 'a.accnt_id',
				'gst_amt', 'a.gst_amt',
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
        parent::populateState("a.tran_date", "DESC");

        $context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $context);
        $state = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state');
        $this->setState('filter.state', $state);
        $accnt_id = $this->getUserStateFromRequest($this->context.'.filter.accnt_id', 'filter_accnt_id');
        $this->setState('filter.accnt_id', $accnt_id);
        $tran_type = $this->getUserStateFromRequest($this->context.'.filter.tran_type', 'filter_tran_type');
        $this->setState('filter.tran_type', $tran_type);
        $cat_id = $this->getUserStateFromRequest($this->context.'.filter.cat_id', 'filter_cat_id');
        $this->setState('filter.cat_id', $cat_id);

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
		$query->from('#__gafinance_transactions AS a');
                
		// Join over the users for the checked out user
		$query->select("uc.name AS uEditor");
		$query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

		// Join over the user field 'created_by'
		$query->select('created_by.name AS created_by');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		// Join over the user field 'modified_by'
		$query->select('modified_by.name AS modified_by');
		$query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');

		// Join over the user field 'user_id'
		$query->select('user_id.name AS user_id_name');
		$query->join('LEFT', '#__users AS user_id ON user_id.id = a.user_id');
                
		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
			$query->where('a.state = ' . (int) $published);
		} elseif (empty($published)) {
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = ' . (int) substr($search, 3));
			} else {
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( user_id.name LIKE ' . $search . ' OR a.tran_desc LIKE ' . $search . ' )');
			}
		}
                
		// Filter by accnt_id
		$accnt_id = $this->getState('filter.accnt_id');
		if (!empty($accnt_id)) {
			$query->where('a.accnt_id = '.(int) $accnt_id);
		}

		// Filter by cat_id
		$tran_type = $this->getState('filter.tran_type');
		if (!empty($tran_type)) {
			$query->where('a.tran_type = '.$db->quote($tran_type));
		}

		// Filter by cat_id
		$cat_id = $this->getState('filter.cat_id');
		if (!empty($cat_id)) {
			$query->where('a.cat_id = '.(int) $cat_id);
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', "a.tran_date");
		$orderDirn = $this->state->get('list.direction', "DESC");

		if ($orderCol && $orderDirn) {
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Get an array of data items
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getItems()
	{
		return parent::getItems();
	}
}
