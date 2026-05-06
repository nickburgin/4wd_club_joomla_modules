<?php
/**
 * @version    5.2.3
 * @package    Com_Gafinance
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gafinance\Administrator\Model;
// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

/**
 * Multiples model.
 * @since  1.6
 */
class InvoicesModel extends ListModel
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
				'client_id', 'a.client_id', 'client_id_name',
				'invoice_type', 'a.invoice_type', 'invoice_type_name',
				'invoice_desc', 'a.invoice_desc',
				'invoice_cost', 'a.invoice_cost',
				'inv_date', 'a.inv_date',
				'paid_date', 'a.paid_date',
				'accnt_id', 'a.accnt_id',
				'email_comment', 'a.email_comment',
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
        parent::populateState("a.inv_date", "DESC");

        $context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $context);
        $state = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state');
        $this->setState('filter.state', $state);

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
		$query->from('#__gafinance_invoices AS a');
                
		// Join over the users for the checked out user
		$query->select("uc.name AS uEditor");
		$query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

		// Join over the user field 'created_by'
		$query->select('created_by.name AS created_by');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		// Join over the user field 'modified_by'
		$query->select('modified_by.name AS modified_by');
		$query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');
                
		// Join over the user field 'modified_by'
		$query->select('cat.invtype_name AS invoice_type_name');
		$query->join('LEFT', '#__gafinance_invtypes AS cat ON cat.id = a.invoice_type');
                
		// Join over the account id reference.
		$query->select('acct.accnt_name AS accnt_id_name');
		$query->join('LEFT', '#__gafinance_accounts AS acct ON acct.id=a.accnt_id');

		// Join over the client id reference.
		$query->select('p.name AS client_id_name');
		$query->join('LEFT', '#__gafinance_patrons AS p ON p.id=a.client_id');

		/* Filter by invoice status
		 * Invoice status explanations
		 * 6 = cancelled
		 * 5 = record created waiting to have pdf generated
		 * 4 = pdf generated waiting to send to client
		 * 3 = sent to client and waiting payment
		 * 7 = paid and stored
		 * -2 = deleted
		*/
		$status = $this->getState('filter.state', 0);
		if ($status) {
			$query->where(' a.state = '.(int) $status);
		} else {
			$query->where('a.state IN (0,1,2,3,4,5) ');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = ' . (int) substr($search, 3));
			} else {
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				
			}
		}
                
		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', "a.inv_date");
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
