<?php
/**
 * @version     6.0.0
 * @package     com_gausers
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

namespace GlennArkell\Component\Gausers\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\Database\ParameterType;

/**
 * Methods supporting a list of Gausers records.
 */
class ActionsModel extends ListModel
{

    /**
     * Constructor.
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
                'id', 'a.id',
                'ordering', 'a.ordering',
                'created_date', 'a.created_date',
                'user_id', 'a.user_id', 'member.name', 'member_name',
                'act_name', 'a.act_name',
                'category_id', 'a.category_id', 'm.title', 'category_id_name',
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
        parent::populateState('member.name', 'ASC');

        $context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $context);

        $category_id = $this->getUserStateFromRequest($this->context.'.filter.category_id', 'filter_category_id');
        $this->setState('filter.category_id', $category_id);

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

		// Select the required fields from the table.
		$query->select( $this->getState( 'list.select', 'a.*' ) );
		$query->from('`#__gausers_actions` AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the created by field 'created_by'
		$query->select('created_by.name AS created_by');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		// Join over the user field 'modified_by'
		$query->select('modified_by.name AS modified_by');
		$query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');

        // Join over the users for the client name.
        $query->select('member.name AS member_name');
        $query->join('LEFT', '#__users AS member ON member.id=a.user_id');

		$query->select('m.title AS category_id_name');
		$query->join('LEFT', '#__categories AS m ON m.id = a.category_id');

        // Filter by published state
        $published = $this->getState('filter.state');
        if (is_numeric($published)) {
            $query->where('a.state = '.(int) $published);
		} elseif ($published === '*') {
			// show all and don't filter on status
		} else {
            $query->where('(a.state IN (0, 1))');
        }

		// Filter by category_id type
		$category_id = $this->getState('filter.category_id');
		if ($category_id) {
            $query->where( ' a.category_id = '.(int)$category_id );
        }

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('( a.user_id LIKE '.$search.' OR  member.name LIKE '.$search.' )');
			}
		}
        
		// Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 'member.name');
        $orderDirn	= $this->state->get('list.direction', 'ASC');

        if ($orderCol && $orderDirn) {
            $query->order($db->escape( $orderCol.' '.$orderDirn ) );
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
