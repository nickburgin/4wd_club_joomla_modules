<?php
/**
 * @version    3.0.0
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gacalevents\Administrator\Model;

// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;

/**
 * Methods supporting a list of records.
 * @since  1.6
 */
class AttendeesModel extends ListModel
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
				'attendee', 'a.attendee',
				'event', 'a.event', 'event_title',
				'att_cat', 'a.att_cat', 'att_cat_name',
				'pub_title', 'a.pub_title',
				'pub_name', 'a.pub_name',
				'pub_fname', 'a.pub_fname',
				'pub_sname', 'a.pub_sname',
				'pub_partner', 'a.pub_partner',
				'position', 'a.position',
				'pub_address1', 'a.pub_address1',
				'pub_address2', 'a.pub_address2',
				'pub_address3', 'a.pub_address3',
				'pub_suburb', 'a.pub_suburb',
				'pub_state', 'a.pub_state',
				'pub_pcode', 'a.pub_pcode',
				'pub_email', 'a.pub_email',
				'pub_phone', 'a.pub_phone',
				'pub_from', 'a.pub_from',
				'paid', 'a.paid',
				'paid_amt', 'a.paid_amt',
				'qty_att', 'a.qty_att',
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
        parent::populateState("e.depart_date", "DESC");

        $context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $context);

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
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'DISTINCT a.*'
			)
		);
		// tweek old records where pub_name not populated
		$query->select('IF(a.pub_name = "", mbr.name, a.pub_name) AS pub_name');

		$query->from('#__gacalevents_attendees AS a');
		$query->join("LEFT", "#__users AS mbr ON mbr.id = a.attendee");

		// Join over the users for the checked out user
		$query->select("uc.name AS uEditor");
		$query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

		// Join over the user field 'created_by'
		$query->select('created_by.name AS created_by');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		// Join over the user field 'modified_by'
		$query->select('modified_by.name AS modified_by');
		$query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');

		// Join over the user field 'attendee'
		$query->select('att.name AS attendee_name');
		$query->join('LEFT', '#__users AS att ON att.id = a.attendee');

		// Join over the user field 'user_id'
		$query->select('c.title AS att_cat_name');
		$query->join('LEFT', '#__categories AS c ON c.id = a.att_cat');

		// Join over the events field 'event'
		$query->select("e.title AS event_name, DATE_FORMAT(e.depart_date,'%D %M %Y') AS depart_date");
		$query->join('LEFT', '#__gacalevents_events AS e ON e.id = a.event');
		// Filter by published state
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif (empty($published))
		{
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( att.name LIKE ' . $search . ' OR  a.pub_name LIKE ' . $search . ' OR  e.title LIKE '.$search.' )');
			}
		}
                
		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', "e.depart_date");
		$orderDirn = $this->state->get('list.direction', "DESC");

		if ($orderCol && $orderDirn)
		{
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
		$items = parent::getItems();
                
		return $items;
	}
}
