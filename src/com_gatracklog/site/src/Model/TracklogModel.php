<?php
/**
 * @version    4.2.0
 * @package    pkg_mypackage
 * @subpackage com_gatracklog
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatracklog\Site\Model;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Helper\TagsHelper;
use \GlennArkell\Component\Gatracklog\Administrator\Helper\GatracklogHelper;

/**
 * Item model.
 * @since  1.6
 */
class TracklogModel extends ItemModel
{
    public $_item;

	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 * @return void
	 * @since    1.6
     * @throws Exception
	 */
	protected function populateState()
	{
		$app  = Factory::getApplication('com_gatracklog');
		$user = $app->getIdentity();

		// Check published state
		if ((!$user->authorise('core.edit.state', 'com_gatracklog')) && (!$user->authorise('core.edit', 'com_gatracklog')))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}

		// Load state from the request userState on edit or from the passed variable on default
		if (Factory::getApplication()->input->get('layout') == 'edit')
		{
			$id = $app->getUserState('com_gatracklog.edit.tracklog.id');
		}
		else
		{
			$id = Factory::getApplication()->input->get('id');
			$app->setUserState('com_gatracklog.edit.tracklog.id', $id);
		}

		$this->setState('tracklog.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id'])) {
			$this->setState('tracklog.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get an object.
	 * @param   integer $id The id of the object to get.
	 * @return  mixed    Object on success, false on failure.
     * @throws Exception
	 */
	public function getItem($id = null)
	{
        if ($this->_item === null) {
            $this->_item = false;

            if (empty($id)) {
                $id = $this->getState('tracklog.id');
            }

            // Get a level row instance.
            $table = $this->getTable();

            // Attempt to load the row.
            if ($table->load($id)) {

                // Check published state.
                if ($published = $this->getState('filter.published')) {
                    if (isset($table->state) && $table->state != $published) {
                        throw new \Exception(Text::_('COM_GAGATRACKLOG_ITEM_NOT_LOADED'), 403);
                    }
                }

                $properties  = $table->getProperties(1);
                $this->_item = ArrayHelper::toObject($properties, 'stdClass');

            }

            if (empty($this->_item)) {
				throw new \Exception(Text::_('COM_GAGATRACKLOG_ITEM_NOT_LOADED'), 404);
			}
        }

		if (isset($this->_item->created_by)) {
			$this->_item->created_by_name = GatracklogHelper::getSpecificUser($this->_item->created_by)->name;
		}

		if (isset($this->_item->modified_by)) {
			$this->_item->modified_by_name = GatracklogHelper::getSpecificUser($this->_item->modified_by)->name;
		}

		if (isset($this->_item->rating)) {
			$this->_item->cat = GatracklogHelper::getCategory($this->_item->rating);
			$this->_item->rating_name = $this->_item->cat->title;
		}
		if (isset($this->_item->track_zone)) {
			$this->_item->track_zone_name = GatracklogHelper::getCategory($this->_item->track_zone)->title;
		}
		if (isset($this->_item->season_close)) {
			$this->_item->season_close_name = GatracklogHelper::getCategory($this->_item->season_close)->title;
		}
		
		$this->_item->tracklog_comments = GatracklogHelper::getTracklogComments($this->_item->id);

        return $this->_item;
    }

	/**
	 * Get an instance of Table class
	 * @param   string $type   Name of the Table class to get an instance of.
	 * @param   string $prefix Prefix for the table class name. Optional.
	 * @param   array  $config Array of configuration values for the Table object. Optional.
	 * @return  Table|bool Table if success, false on failure.
	 */
	public function getTable($type = 'Tracklog', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Get the id of an item by alias
	 * @param   string $alias Item alias
	 * @return  mixed
	 */
	public function getItemIdByAlias($alias)
	{
        $table      = $this->getTable();
        $properties = $table->getProperties();
        $result     = null;
        $aliasKey   = null;
        if (method_exists($this, 'getAliasFieldNameByView')) {
            $aliasKey   = $this->getAliasFieldNameByView('tracklog');
        }

        if (key_exists('alias', $properties)) {
            $table->load(array('alias' => $alias));
            $result = $table->id;
        } elseif (isset($aliasKey) && key_exists($aliasKey, $properties)) {
            $table->load(array($aliasKey => $alias));
            $result = $table->id;
        }

        return $result;

	}

	/**
	 * Method to check in an item.
	 * @param   integer $id The id of the row to check out.
	 * @return  boolean True on success, false on failure.
	 * @since    1.6
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int) $this->getState('tracklog.id');
                
		if ($id) {
			// Initialise the table
			$table = $this->getTable();

			// Attempt to check the row in.
			if (method_exists($table, 'checkin')) {
				if (!$table->checkin($id)) {
					return false;
				}
			}
		}

		return true;
                
	}

	/**
	 * Method to check out an item for editing.
	 * @param   integer $id The id of the row to check out.
	 * @return  boolean True on success, false on failure.
	 * @since    1.6
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('tracklog.id');

                
		if ($id) {
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = Factory::getApplication()->getIdentity();

			// Attempt to check the row out.
			if (method_exists($table, 'checkout')) {
				if (!$table->checkout($user->id, $id)) {
					return false;
				}
			}
		}

		return true;
                
	}

	/**
	 * Publish the element
	 * @param   int $id    Item id
	 * @param   int $state Publish state
	 * @return  boolean
	 */
	public function publish($id, $state)
	{
		$table = $this->getTable();
                
		$table->load($id);
		$table->state = $state;

		return $table->store(true);
                
	}

	/**
	 * Method to delete an item
	 * @param   int $id Element id
	 * @return  bool
	 */
	public function delete($id)
	{
		$updateNulls = true;
		$table = $this->getTable();
		$table->load($id);
		$table->state = -2;

		return $table->store($updateNulls);
	}
	
	/**
	 * Method to delete an item
	 * @param   int  $id  Element id
	 * @return  bool
	 */
	public function deleteComment($id)
	{
		$return = GatracklogHelper::deleteTracklogComments($id);

		return $return;
	}

	public function sendtrklog($data)
	{
		$id = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('tracklog.id');
		$user = Factory::getApplication()->getIdentity();
        $mailfrom	= Factory::getApplication()->get('mailfrom');       // system email address
        $fromname	= Factory::getApplication()->get('fromname');       // Site name or system name

		$table = $this->getTable();
		$table->load($data['id']);

        $tracklog  = "TL".str_pad($data['id'], 8, '0', STR_PAD_LEFT)."_".substr($table->modified_date,0,10);
        $folder = 'images/tracklogs';
        $path = Path::clean( JPATH_SITE . '/'. $folder .'/'. $tracklog .'.'.$table->file_format );
        if (!is_file($path)) {
			File::write($path, $table->trklog);
		}
		$subject = 'Tracklog from '.$fromname;
		$body = 'Attached file for your use with no guarantees as to the accuracy or currency.';

        GatracklogHelper::sendEmail(array($user->email), $body, $subject, $path);

		return true;
	}

}
