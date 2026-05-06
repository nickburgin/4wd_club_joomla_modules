<?php

/**
 * @version     5.1.0
 * @package     com_gatripsys
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gatripsys\Administrator\Model;

defined('_JEXEC') or die;

use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Date\Date;

/**
 * Methods supporting a list of records.
 */
class AttendeesModel extends ListModel {

    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'ordering', 'a.ordering',
                'state', 'a.state',
                'created_by', 'a.created_by',
                'created_date', 'a.created_date',
                'modified_by', 'a.modified_by',
                'modified_date', 'a.modified_date',
                'trip_id', 'a.trip_id', 'trip_name', 'rating_name',
                'user_id', 'a.user_id',
                'approved_by', 'a.approved_by',
                'in_party', 'a.in_party',
                'comment', 'a.comment',
            );
        }
        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     */
    protected function populateState($ordering = null, $direction = null) 
	{
        // List state information.
        parent::populateState("a.id", "ASC");

        $context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $context);
		// Filtering state
		$published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_published');
		$this->setState('filter.state', $published);

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
    protected function getStoreId($id = '') {
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
    protected function getListQuery() {

		$disp_daysago  = ComponentHelper::getParams('com_gatripsys')->get('disp_daysago');
        $disp_daysago  = $disp_daysago == 0 ? 365 : $disp_daysago;  // default to 12 months ago if set to zero
		// get the current date-time
		$today = new Date();
		$today->modify("-".$disp_daysago." day");
		$today5 = $today->format("Y-m-d H:i:s");

        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select( $this->getState( 'list.select', 'a.*' ) );
        $query->from('#__gatripsys_attendees AS a');

		// Join over the users for the checked out user
		$query->select("uc.name AS editor");
		$query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

		// Join over the user field 'created_by'
		$query->select('created_by.name AS created_by');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		// Join over the created by field 'modified_by'
		$query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');

		// Join over the users for the approver
		$query->select("apprv.name AS approved_by");
		$query->join("LEFT", "#__users AS apprv ON apprv.id=a.approved_by");

		// Join over the trip
		//$query->select("c.title AS rating_name, date_format(t.dept_date,'%D %M, %Y') AS dept_date_disp");
		//$query->select("date_format(t.ret_date,'%D %M, %Y') AS ret_date_disp, t.title AS trip_name");
		$query->select("c.title AS rating_name, t.title AS trip_name");
		$query->select("date_format(t.dept_date,'%D %M, %Y') AS dept_date_disp, date_format(t.ret_date,'%D %M, %Y') AS ret_date_disp");
		$query->join('LEFT', '#__gatripsys_trips AS t ON t.id = a.trip_id');
		$query->join('LEFT', '#__categories AS c ON c.id = t.rating');

		// Join over the user field 'user_id'
		$query->select('user_id.name AS user_id, "User Record Not Found" AS no_user');
		$query->join('LEFT', '#__users AS user_id ON user_id.id = a.user_id');

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
			$query->where('a.state = ' . (int) $published);
		} else if ($published === '') {
			$query->where('(a.state IN (0, 1, 2))');
		}

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('( a.user_id LIKE '.$search.' OR  user_id.name LIKE '.$search.' OR  t.title LIKE '.$search.')');
            }
        }
        
        $query->where(' t.dept_date >= '.$db->Quote($today5));

        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
    }

    public function getItems() {
        $items = parent::getItems();
        
        return $items;
    }

}
