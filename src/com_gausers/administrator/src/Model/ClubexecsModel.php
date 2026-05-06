<?php

/**
 * @version     6.0.0
 * @package     com_gausers
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gausers\Administrator\Model;

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
 * Methods supporting a list of Gausers records.
 */
class ClubexecsModel extends ListModel {

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
                'club_id', 'a.club_id',
                'club_name', 'a.club_name',
                'club_address', 'a.club_address',
                'club_suburb', 'a.club_suburb',
                'club_pcode', 'a.club_pcode',
                'club_phone', 'a.club_phone',
                'club_email', 'a.club_email',
                'use_clubemail', 'a.use_clubemail',
                'club_website', 'a.club_website',
                'club_logo', 'a.club_logo',
                'club_region', 'a.club_region',
                'meeting_day', 'a.meeting_day',
                'meeting_time', 'a.meeting_time',
                'meeting_info', 'a.meeting_info',
                'meeting_address', 'a.meeting_address',
                'integ_mbrs', 'a.integ_mbrs',
                'pres_id', 'a.pres_id',
                'pres_idp', 'a.pres_idp',
                'vpres_id', 'a.vpres_id',
                'vpres_idp', 'a.vpres_idp',
                'secr_id', 'a.secr_id',
                'secr_idp', 'a.secr_idp',
                'tres_id', 'a.tres_id',
                'tres_idp', 'a.tres_idp',
                'edit_id', 'a.edit_id',
                'edit_idp', 'a.edit_idp',
                'delg_id', 'a.delg_id',
                'delg_idp', 'a.delg_idp',
                'regr_id', 'a.regr_id',
                'regr_idp', 'a.regr_idp',
                'istc_id', 'a.istc_id',
                'istc_idp', 'a.istc_idp',
                'end_term', 'a.end_term',
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
        parent::populateState("a.end_term", "DESC");

        $context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $context);

        $published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published');
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
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select( $this->getState( 'list.select', 'a.*' ) );
        $query->from('`#__gausers_club_execs` AS a');

		// Join over the users for the checked out user
		$query->select("uc.name AS editor");
		$query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

		// Join over the user field 'created_by'
		$query->select('created_by.name AS created_by');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		// Join over the committee
		$query->select('pres.name AS pres_id');
		$query->join('LEFT', '#__users AS pres ON pres.id = a.pres_id');
		$query->select('vpres.name AS vpres_id');
		$query->join('LEFT', '#__users AS vpres ON vpres.id = a.vpres_id');
		$query->select('secr.name AS secr_id');
		$query->join('LEFT', '#__users AS secr ON secr.id = a.secr_id');
		$query->select('tres.name AS tres_id');
		$query->join('LEFT', '#__users AS tres ON tres.id = a.tres_id');
		$query->select('e.name AS edit_id');
		$query->join('LEFT', '#__users AS e ON e.id = a.edit_id');
		$query->select('regr.name AS regr_id');
		$query->join('LEFT', '#__users AS regr ON regr.id = a.regr_id');
		$query->select('delg.name AS delg_id');
		$query->join('LEFT', '#__users AS delg ON delg.id = a.delg_id');
		$query->select('istc.name AS istc_id');
		$query->join('LEFT', '#__users AS istc ON istc.id = a.istc_id');



		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
			$query->where('a.state = ' . (int) $published);
		} elseif ($published === '*') {
			// show all and don't filter on status
		} else {
			$query->where('(a.state IN (0, 1))');
		}

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('( pres_name LIKE '.$search.'  OR vpres_name LIKE '.$search.'  OR  secr_name LIKE '.$search.' OR  tres_name LIKE '.$search.' OR  edit_name LIKE '.$search.' OR  regr_name LIKE '.$search.' OR  delg_name LIKE '.$search.' OR  istc_name LIKE '.$search.' OR clubs.club_name LIKE '.$search.' )');
            }
        }

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
