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
use Joomla\CMS\MVC\Model\FormModel;
use \GlennArkell\Component\Gatracklog\Administrator\Helper\GatracklogHelper;

/**
 * Form model.
 * @since  1.6
 */
class TracklogformModel extends FormModel
{
    private $item = null;

    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     * @return void
     * @since  1.6
     * @throws Exception
     */
    protected function populateState()
    {
        $app = Factory::getApplication('com_gatracklog');

        // Load state from the request userState on edit or from the passed variable on default
        if ($app->input->get('layout') == 'edit') {
                $id = $app->getUserState('com_gatracklog.edit.tracklog.id');
        } elseif ($app->input->get('layout') == 'modal') {
                $id = $app->getUserState('com_gatracklog.edit.tracklog.id');
        } else {
                $id = $app->input->get('id');
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
     * Method to get an ojbect.
     * @param   integer $id The id of the object to get.
     * @return Object|boolean Object on success, false on failure.
     * @throws Exception
     */
    public function getItem($id = null)
    {
        if ($this->item === null) {
            $this->item = false;

            if (empty($id)) {
                    $id = $this->getState('tracklog.id');
            }

            // Get a level row instance.
            $table = $this->getTable();

            if ($table !== false && $table->load($id) && !empty($table->id)) {
                $user = Factory::getApplication()->getIdentity();
                $id   = $table->id;

                $canEdit = $user->authorise('core.edit', 'com_gatracklog') || $user->authorise('core.create', 'com_gatracklog');

                if (!$canEdit && $user->authorise('core.edit.own', 'com_gatracklog')) {
                    $canEdit = $user->id == $table->created_by;
                }

                if (!$canEdit) {
                    throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
                }

                // Check published state.
                if ($published = $this->getState('filter.published')) {
                    if (isset($table->state) && $table->state != $published) {
                        return $this->item;
                    }
                }

                // Convert the Table to a clean Object.
                $properties = $table->getProperties(1);
                $this->item = ArrayHelper::toObject($properties, 'stdClass');

            }
        }

        return $this->item;
    }

    /**
     * Method to get the table
     * @param   string $type   Name of the Table class
     * @param   string $prefix Optional prefix for the table class name
     * @param   array  $config Optional configuration array for Table object
     * @return  Table|boolean Table if found, boolean false on failure
     */
    public function getTable($type = 'Tracklog', $prefix = 'Administrator', $config = array())
    {
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Get an item by alias
     * @param   string $alias Alias string
     * @return int Element id
     */
    public function getItemIdByAlias($alias)
    {
        $table      = $this->getTable();
        $properties = $table->getProperties();

        if (!in_array('alias', $properties)) {
                return null;
        }

        $table->load(array('alias' => $alias));
        $id = $table->id;

        return $id;
        
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
     * Method to get the form.
     * The base form is loaded from XML
     * @param   array   $data     An optional array of data for the form to interogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     * @return  Form    A Form object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_gatracklog.tracklog', 'tracklogform', array(
                    'control'   => 'jform',
                    'load_data' => $loadData ) );

        if (empty($form)) {
                return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     * @return    array  The default data is an empty array.
     * @since    1.6
     */
    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_gatracklog.edit.tracklog.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        if ($data) {

            return $data;
        }

        return array();
    }

    /**
     * Method to save the form data.
     * @param   array $data The form data
     * @return bool
     * @throws Exception
     * @since 1.6
     */
    public function save($data)
    {
        $id    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('tracklog.id');
        $state = (!empty($data['state'])) ? 1 : 0;
        $user  = Factory::getApplication()->getIdentity();

        if ($id) {
            // Check the user can edit this item
            $authorised = $user->authorise('core.edit', 'com_gatracklog') || $authorised = $user->authorise('core.edit.own', 'com_gatracklog');
        } else {
            // Check the user can create new items in this section
            $authorised = $user->authorise('core.create', 'com_gatracklog');
        }

        if ($authorised !== true) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $table = $this->getTable();

        if ($table->save($data) === true) {
            return $table->id;
        } else {
            return false;
        }
        
    }

    /**
     * Method to delete data
     * @param   int $pk Item primary key
     * @return  int  The id of the deleted item
     * @throws Exception
     * @since 1.6
     */
    public function delete($pk)
    {
        $user = Factory::getApplication()->getIdentity();

        if (empty($pk)) {
            $pk = (int) $this->getState('tracklog.id');
        }

        if ($pk == 0 || $this->getItem($pk) == null) {
            throw new \Exception(Text::_('COM_GAGATRACKLOG_ITEM_DOESNT_EXIST'), 404);
        }

        if ($user->authorise('core.delete', 'com_gatracklog') !== true) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $table = $this->getTable();

        if ($table->delete($pk) !== true) {
            throw new \Exception(Text::_('COM_GAGATRACKLOG_ITEM_DELETED_FAILED'), 501);
        }

        return $pk;
        
    }

    /**
     * Check if data can be saved
     * @return bool
     */
    public function getCanSave()
    {
        $table = $this->getTable();

        return $table !== false;
    }
    
	/**
	 * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
	 * @param   string  $date  Date to be checked
	 * @return bool
	 */
	public function isValidDate($date)
	{
		$date = \str_replace('/', '-', $date);
		return (\date_create($date)) ? Factory::getDate($date)->format("Y-m-d") : null;
	}

	/**
	 * Method to save the form data.
	 * @param   array  $data  The form data
	 * @return bool
	 * @throws Exception
	 * @since 1.6
	 */
	public function saveComment($data)
	{
		$id    = (!empty($data['track_id'])) ? $data['track_id'] : 0;
		$user = Factory::getApplication()->getIdentity();
		// get the current date-time based on timezone
		$today = Factory::getDate()->toSql();

		if ($id) {
			$data['state'] = 1;
	        $db		= Factory::getContainer()->get('DatabaseDriver');
			$query = $db->getQuery(true);
			$query->insert('`#__gatracklog_trackcomments`');
			$query->set( ' state = '.(int)$data['state'] );
			$query->set( ' user_id = '.(int)$data['user_id'] );
			$query->set( ' track_id = '.(int)$id );
			$query->set( ' comment = '.$db->Quote($data['tcomment']) );
			$query->set( ' created_by = '.$db->Quote($data['created_by']) );
			$query->set( ' created_date = '.$db->Quote($today) );
	        $db->setQuery($query);

		    try {
		        $db->execute();
		    } catch (RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage($e->getMessage());
		        return false;
		    }
		} else {
			throw new \Exception(Text::_('No Track Identified'), 403);
		}

		return true;

	}
}
