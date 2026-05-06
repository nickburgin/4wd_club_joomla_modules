<?php
/**
 * @version    3.3.1
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gacalevents\Site\Model;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Component\ComponentHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;

/**
 * Methods supporting a list of records.
 * @since  1.6
 */
class EventsModel extends ListModel
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
                'cat_id', 'a.cat_id', 'cat_id_name',
                'title', 'a.title',
                'brief_desc', 'a.brief_desc',
                'event_details', 'a.event_details',
                'inc_mod', 'a.inc_mod',
                'leader', 'a.leader',
                'depart_point', 'a.depart_point',
                'depart_date', 'a.depart_date',
                'return_date', 'a.return_date',
                'forumid', 'a.forumid',
                'start_time', 'a.start_time',
                'end_time', 'a.end_time',
                'max_attend', 'a.max_attend',
                'mbr_cost', 'a.mbr_cost',
                'pub_cost', 'a.pub_cost',
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
				$ordering = "a.depart_date";
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
		//$app->input->set('list', null);   // if you use this, list always defaults to Global

        // List state information.
        parent::populateState($ordering, $direction);

        $context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $context);
        $datefr = $this->getUserStateFromRequest($this->context.'.filter.date_fr', 'filter_date_fr');
        $this->setState('filter.date_fr', $datefr);
        $dateto = $this->getUserStateFromRequest($this->context.'.filter.date_to', 'filter_date_to');
        $this->setState('filter.date_to', $dateto);

	}

	/**
	 * Build an SQL query to load the list data.
	 * @return   JDatabaseQuery
	 * @since    1.6
	 */
	protected function getListQuery()
	{
        $user	= Factory::getApplication()->getIdentity();
        $showUnpub = false;
		$params = ComponentHelper::getParams('com_gacalevents');
		$siteFilter = $params->get('site_data_filter', 1);
		$adminUsers = $params->get('authorised_id', 0);
		if (is_array($adminUsers)) {
			$showUnpub = in_array($user->id, $adminUsers) ? true : false;
		}

        // Create a new query object.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select( $this->getState( 'list.select', 'DISTINCT a.*' ) );
		$query->select(" DATE_FORMAT(a.depart_date,'%W %d-%b-%Y') as disp_depart_date, DATE_FORMAT(a.return_date,'%W %d-%b-%Y') as disp_return_date ");
		$query->select(" DATE_FORMAT(a.depart_date,'%M') as depart_month ");

        $query->from('#__gacalevents_events AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS uEditor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the created by field 'created_by'
		$query->select('created_by.name AS created_by_name');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		// Join over the created by field 'modified_by'
		$query->select('modified_by.name AS modified_by_name');
		$query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');

		// Join over the user field 'user_id'
		$query->select('c.title AS cat_id_name, d.title AS depart_point_name');
		$query->join('LEFT', '#__categories AS c ON c.id = a.cat_id');
		$query->join('LEFT', '#__categories AS d ON d.id = a.depart_point');


		if ($showUnpub) {
			$query->where(' a.state IN (0,1) ');
		} else {
			$query->where('a.state = 1');
		}

        // Filter by datefr
        $datefr = $this->getState('filter.date_fr');
        $dateto = $this->getState('filter.date_to');
        if (!empty($datefr) && !empty($dateto)) {
    		$query->where(' a.depart_date >= '.$db->Quote($datefr) );
    		$query->where(' a.depart_date <= '.$db->Quote($dateto) );
		} else {
    		$query->where(' (a.depart_date >= DATE_SUB(now(), INTERVAL '.(int)$siteFilter.' DAY) OR a.return_date >= DATE_SUB(now(), INTERVAL '.(int)$siteFilter.' DAY))');
    		$query->where(' a.depart_date <= DATE_ADD(now(), INTERVAL 18 MONTH)');
		}

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.leader LIKE ' . $search . ' OR a.title LIKE ' . $search . ' )');
            }
        }
            
        // Add the list ordering clause.
		$query->order($db->escape(' a.depart_date ASC, a.ordering ASC, a.title ASC '));

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
			$app->enqueueMessage(Text::_("COM_GACALEVENTS_SEARCH_FILTER_DATE_FORMAT"), "warning");
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
