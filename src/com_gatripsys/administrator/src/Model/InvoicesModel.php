<?php
/**
 * @version     5.3.0
 * @package     com_gatripsys
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

namespace GlennArkell\Component\Gatripsys\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\Database\ParameterType;
use Joomla\CMS\Date\Date;

/**
 * Methods supporting a list of records.
 */
class InvoicesModel extends ListModel
{

    /**
     * Constructor.
     * @param    array    An optional associative array of configuration settings.
     * @see        Controller
     * @since    1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'ordering', 'a.ordering',
                'state', 'a.state',
                'checked_out', 'a.checked_out',
                'created_date', 'a.created_date',
                'created_by', 'a.created_by',
                'user_id', 'a.user_id',
                'att_id', 'a.att_id',
                'invoice_amt', 'a.invoice_amt',
                'paid_date', 'a.paid_date',
                'comment', 'a.comment',

            );
        }

        parent::__construct($config);
    }


	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 */
	protected function populateState($ordering = null, $direction = null)
	{
        // List state information.
        parent::populateState("a.id", "DESC");

        $context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $context);
		// Filtering state
		$published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_published');
		$this->setState('filter.state', $published);
		//Filtering work_paid
		$work_paid = $this->getUserStateFromRequest($this->context . '.filter.work_paid', 'filter_work_paid');
		$this->setState('filter.work_paid', $work_paid);

	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 * @return	string		A store id.
	 * @since	1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id.= ':' . $this->getState('filter.search');
		$id.= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->clear();

		// Select the required fields from the table.
		$query->select( $this->getState( 'list.select', 'a.*' ) );
		$query->from('#__gatripsys_invoices AS a');

        // Join over the users for the client name.
        $query->select('member.name AS member_name');
        $query->join('LEFT', '#__users AS member ON member.id=a.user_id');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the created by field 'created_by'
		$query->select('created_by.name AS created_by');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		// Join over the trip field 'att_id'
		$query->select('trip.title AS trip_title ');
		$query->join('LEFT', '#__gatripsys_attendees AS att ON att.id = a.att_id');
		$query->join('LEFT', '#__gatripsys_trips AS trip ON trip.id = att.trip_id');

        // Filter by published state
        $published = $this->getState('filter.state');
        if (is_numeric($published)) {
            $query->where('a.state = '.(int) $published);
        } elseif ($published === '') {
            $query->where('(a.state IN (0, 1))');
        }

		//Filtering work_paid
		$filter_work_paid = $this->state->get("filter.work_paid");
		if ($filter_work_paid == 2) {
			$query->where('a.paid_date = "0000-00-00"' );
		}
        elseif ($filter_work_paid == 1) {
			$query->where('a.paid_date > "0000-00-00"' );
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('( a.user_id LIKE '.$search.' OR  member.name LIKE '.$search.' OR  trip.title LIKE '.$search.' )');
			}
		}
        
		// Add the list ordering clause.
		$query->order($db->escape(' trip.title ASC, id ASC') );

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
