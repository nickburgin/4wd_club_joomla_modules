<?php
/**
 * @version    5.3.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatripsys\Site\Model;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GauploadimgHelper;

/**
 * Form model.
 * @since  1.6
 */
class IncidentformModel extends FormModel
{
	private $item = null;

	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 * @return void
	 * @since  1.6
	 */
	protected function populateState()
	{
		$app = Factory::getApplication('com_gatripsys');

		// Load state from the request userState on edit or from the passed variable on default
		if (Factory::getApplication()->input->get('layout') == 'edit')
		{
			$id = Factory::getApplication()->getUserState('com_gatripsys.edit.incident.id');
			$trip_id = Factory::getApplication()->getUserState('com_gatripsys.edit.trip.id', 0);
		}
		else
		{
			$id = Factory::getApplication()->input->get('id');
			Factory::getApplication()->setUserState('com_gatripsys.edit.incident.id', $id);
			$trip_id = Factory::getApplication()->input->get('trip_id');
			Factory::getApplication()->setUserState('com_gatripsys.edit.trip.id', $trip_id);
		}

		$this->setState('incident.id', $id);
		$this->setState('trip.id', $trip_id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id']))
		{
			$this->setState('incident.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get an ojbect.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return Object|boolean Object on success, false on failure.
	 *
	 * @throws Exception
	 */
	public function getData($id = null)
	{
		$user = GatripsysHelper::getSpecificUser();
        if ($this->item === null)
		{
			$this->item = false;

			if (empty($id))
			{
				$id = $this->getState('incident.id');
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table !== false && $table->load($id))
			{
				$id   = $table->id;
				$canEdit = $user->authorise('core.edit', 'com_gatripsys') || $user->authorise('core.create', 'com_gatripsys');

				if (!$canEdit && $user->authorise('core.edit.own', 'com_gatripsys'))
				{
					$canEdit = $user->id == $table->created_by;
				}

				if (!$canEdit)
				{
					throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 500);
				}

				// Check published state.
				if ($published = $this->getState('filter.published'))
				{
					if ($table->state != $published)
					{
						return $this->item;
					}
				}

				// Convert the Table to a clean Object.
				$properties  = $table->getProperties(1);
				$this->item = ArrayHelper::toObject($properties, 'stdClass');
			}

			if (isset($this->item->inc_img) && $this->item->inc_img > '') {
				$this->item->inc_img_disp = $this->item->inc_img;
			}
		}

		if (!isset($this->item->trip_id)) {
			$this->item->user_id = $user->id;
			$this->item->trip_id = $this->getState('trip.id');
            $this->item->trip_details = GatripsysHelper::getTripInformation($this->item->trip_id);
		}

		return $this->item;
	}

	/**
	 * Method to get the table
	 *
	 * @param   string  $type    Name of the Table class
	 * @param   string  $prefix  Optional prefix for the table class name
	 * @param   array   $config  Optional configuration array for Table object
	 *
	 * @return  Table|boolean Table if found, boolean false on failure
	 */
	public function getTable($type = 'Incident', $prefix = 'Administrator', $config = array())
	{
        return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Get an item by alias
	 *
	 * @param   string  $alias  Alias string
	 *
	 * @return int Element id
	 */
	public function getItemIdByAlias($alias)
	{
		$table = $this->getTable();

		$table->load(array('alias' => $alias));

		return $table->id;
	}

	/**
	 * Method to check in an item.
	 *
	 * @param   integer  $id  The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int) $this->getState('incident.id');

		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Attempt to check the row in.
			if (method_exists($table, 'checkin'))
			{
				if (!$table->checkin($id))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to check out an item for editing.
	 *
	 * @param   integer  $id  The id of the row to check out.
	 *
	 * @return  boolean True on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('incident.id');

		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = GatripsysHelper::getSpecificUser();

			// Attempt to check the row out.
			if (method_exists($table, 'checkout'))
			{
				if (!$table->checkout($user->get('id'), $id))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to get the profile form.
	 *
	 * The base form is loaded from XML
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return    JForm    A JForm object on success, false on failure
	 *
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_gatripsys.incident', 'incidentform', array(
			'control'   => 'jform',
			'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 *
	 * @since    1.6
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_gatripsys.edit.incident.data', array());

		if (empty($data))
		{
			$data = $this->getData();
		}

		

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data
	 *
	 * @return bool
	 *
	 * @throws Exception
	 * @since 1.6
	 */
	public function save($data)
	{
		$id    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('incident.id');
		//$state = (!empty($data['state'])) ? $data['state'] : 0;
		$user  = GatripsysHelper::getSpecificUser();

		if ($id) {
			// Check the user can edit this item
			$authorised = $user->authorise('core.edit', 'com_gatripsys') || $authorised = $user->authorise('core.edit.own', 'com_gatripsys');
		} else {
			// Check the user can create new items in this section
			$authorised = $user->authorise('core.create', 'com_gatripsys');
			// get the current date-time based on timezone
			$jdate = new Date();
			$today = date_format($jdate,'Y-m-d H:i:s');
			$data['state'] = 1;
			$data['created_date'] = $today;
			$data['modified_date'] = $today;
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
	 *
	 * @param   array  $data  Data to be deleted
	 *
	 * @return bool|int If success returns the id of the deleted item, if not false
	 *
	 * @throws Exception
	 */
	public function delete($data)
	{
		$id = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('incident.id');

		if (GatripsysHelper::getSpecificUser()->authorise('core.delete', 'com_gatripsys') !== true)
		{
			throw new \Exception(403, Text::_('JERROR_ALERTNOAUTHOR'));
		}

		$table = $this->getTable();
		$table->load($data['id']);
		$table->state = -2;
		Factory::getApplication()->setUserState('com_gatripsys.tripid.data',$table->trip_id);

		if ($table->store() === true) {
			return $id;
		} else {
			return false;
		}
	}

	/**
	 * Check if data can be saved
	 *
	 * @return bool
	 */
	public function getCanSave()
	{
		$table = $this->getTable();

		return $table !== false;
	}

    /**
     * Method to upload an attachment using the helper
     */
    public function uploadAttachment($tran_file = null, $params = null, $file_type = 'trip')
    {
        $safeFileOptions  = $params->get( 'safe_files' );
        $tripincdir  = $params->get( 'incimg_dir', 'images/trips/trip_incidents' );
		$file_ext = substr($tran_file['name'],-3);

		if (!in_array($file_ext, $safeFileOptions)) {
			Factory::getApplication()->enqueueMessage(Text::_('File format ('.$file_ext.') not allowed'), 'danger');
			return false;
		}

        return GauploadimgHelper::uploadAttachment($tripincdir, $tran_file);

		//return $file;
    }

}
