<?php
/**
 * @version     4.2.0
 * @package     pkg_mypackage
 * @subpackage  com_gatracklog
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatracklog\Administrator\Model;
// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of records.
 * @since  1.6
 */
class TracklogsModel extends ListModel
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
				'name', 'a.name',
				'track_zone', 'a.track_zone', 'track_zone_name', 't.title',
				'season_close', 'a.season_close', 'season_close_name', 's.title',
				'rating', 'a.rating', 'rating_name', 'r.title',
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
        $ordering = !empty($ordering) ? $ordering : $this->getUserStateFromRequest($this->context . '.filter.ordering', 'filter_ordering', '');
        $direction = !empty($direction) ? $direction : $this->getUserStateFromRequest($this->context . '.filter.direction', 'filter_direction', '');

        $context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $context);

        $state = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state');
        $this->setState('filter.state', $state);

        $rating = $this->getUserStateFromRequest($this->context.'.filter.rating', 'filter_rating');
        $this->setState('filter.rating', $rating);

        $track_zone = $this->getUserStateFromRequest($this->context.'.filter.track_zone', 'filter_track_zone');
        $this->setState('filter.track_zone', $track_zone);

        $season_close = $this->getUserStateFromRequest($this->context.'.filter.season_close', 'filter_season_close');
        $this->setState('filter.season_close', $season_close);

        // Populate state information.
        parent::populateState($ordering, $direction);
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
        // Process select filters.
        $status    = $this->getState('filter.state', '');
        $search = $this->getState('filter.search', '');

		// Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('a.*')
            ->select(
                [
                    $db->quoteName('uChecked.name', 'uEditor'),
                    $db->quoteName('uCreated.name', 'uCreator'),
                    $db->quoteName('uModified.name', 'uModifier'),
                    $db->quoteName('r.title', 'rating_name'),
                    $db->quoteName('t.title', 'track_zone_name'),
                    $db->quoteName('s.title', 'season_close_name'),
                ]
            )
            ->from($db->quoteName('#__gatracklog_tracklogs', 'a'))
            ->join('LEFT', $db->quoteName('#__users', 'uChecked'), $db->quoteName('uChecked.id') . ' = ' . $db->quoteName('a.checked_out'))
            ->join('LEFT', $db->quoteName('#__users', 'uCreated'), $db->quoteName('uCreated.id') . ' = ' . $db->quoteName('a.created_by'))
            ->join('LEFT', $db->quoteName('#__users', 'uModified'), $db->quoteName('uModified.id') . ' = ' . $db->quoteName('a.modified_by'))
            ->join('LEFT', $db->quoteName('#__categories', 'r'), $db->quoteName('r.id') . ' = ' . $db->quoteName('a.rating'))
            ->join('LEFT', $db->quoteName('#__categories', 't'), $db->quoteName('t.id') . ' = ' . $db->quoteName('a.track_zone'))
            ->join('LEFT', $db->quoteName('#__categories', 's'), $db->quoteName('s.id') . ' = ' . $db->quoteName('a.season_close'));

		// Filter by category
        $rating = $this->getState('filter.rating');
		if ($rating) {
            $rating = (int) $rating;
			$query->where('a.rating = :rating')
                ->bind(':rating', $rating, ParameterType::INTEGER);
        }
        $track_zone = $this->getState('filter.track_zone');
		if ($track_zone) {
            $track_zone = (int) $track_zone;
			$query->where('a.track_zone = :track_zone')
                ->bind(':track_zone', $track_zone, ParameterType::INTEGER);
        }
        $season_close = $this->getState('filter.season_close');
		if ($season_close) {
            $season_close = (int) $season_close;
			$query->where('a.season_close = :season_close')
                ->bind(':season_close', $season_close, ParameterType::INTEGER);
        }

		// Filter by state
		if (is_numeric($status)) {
            $status = (int) $status;
            $query->where($db->quoteName('a.state') . ' = :status')
                ->bind(':status', $status, ParameterType::INTEGER);
		} elseif ($status === '*') {
			// show all and don't filter on status
		} else {
			$query->where($db->quoteName('a.state') . ' IN (0, 1) ');
        }

		// Filter by search in field
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = '%' . str_replace(' ', '%', trim($search)) . '%';
                $query->where($db->quoteName('t.title') . ' LIKE :search')
                    ->bind(':search', $search);
            }
		}
                
		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', "t.title");
		$orderDirn = $this->state->get('list.direction', "ASC");

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
		$items = parent::getItems();
                
		return $items;
	}
}
