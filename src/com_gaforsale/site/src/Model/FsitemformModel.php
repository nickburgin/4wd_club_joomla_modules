<?php
/**
 * @version    4.2.2
 * @package    Com_Gaforsale
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  Copyright (C) 2013. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gaforsale\Site\Model;

// No direct access.
\defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Date\Date;
use \Joomla\CMS\MVC\Model\FormModel;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Component\ComponentHelper;
use \GlennArkell\Component\Gaforsale\Administrator\Helper\GaforsaleHelper;

/**
 * Form model.
 * @since  1.6
 */
class FsitemformModel extends FormModel
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
		$app = Factory::getApplication('com_gaforsale');

		// Load state from the request userState on edit or from the passed variable on default
		if (Factory::getApplication()->input->get('layout') == 'edit') {
			$id = Factory::getApplication()->getUserState('com_gaforsale.edit.fsitem.id');
		} else {
			$id = Factory::getApplication()->input->get('id');
			Factory::getApplication()->setUserState('com_gaforsale.edit.fsitem.id', $id);
		}

		$this->setState('fsitem.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id'])) {
			$this->setState('fsitem.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get an ojbect.
	 * @param   integer  $id  The id of the object to get.
	 * @return Object|boolean Object on success, false on failure.
	 * @throws Exception
	 */
	public function getItem($id = null)
	{
		$user = GaforsaleHelper::getSpecificUser();
		if ($this->item === null) {
			$this->item = false;

			if (empty($id)) {
				$id = $this->getState('fsitem.id');
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table !== false && $table->load($id)) {
				$id   = $table->id;
				$canEdit = $user->authorise('core.edit', 'com_gaforsale') || $user->authorise('core.create', 'com_gaforsale');

				if (!$canEdit && $user->authorise('core.edit.own', 'com_gaforsale')) {
					$canEdit = $user->id == $table->created_by;
				}

				if (!$canEdit) {
					throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 500);
				}

				// Check published state.
				if ($published = $this->getState('filter.published')) {
					if ($table->state != $published) {
						return $this->item;
					}
				}

				// Convert the Table to a clean JObject.
				$properties  = $table->getProperties(1);
				$this->item = ArrayHelper::toObject($properties, 'JObject');
			}
		}
		
		if (isset($this->item->item_image) && $this->item->item_image != '') {
			$this->item->item_image_txt = $this->item->item_image;
			//$this->item->item_image = '';
		}
		if (!isset($this->item->user_id) || $this->item->user_id == '') {
			$this->item->user_id = $user->id;
			$this->item->user_id_name = $user->name;
		} else {
			$this->item->user_id_name = $user->name;
		}
		if (!isset($this->item->seller_contact) || $this->item->seller_contact == '') {
			$this->item->seller_contact = $user->name;
		}

		return $this->item;
	}

	/**
	 * Method to get the table
	 * @param   string  $type    Name of the Table class
	 * @param   string  $prefix  Optional prefix for the table class name
	 * @param   array   $config  Optional configuration array for Table object
	 * @return  Table|boolean Table if found, boolean false on failure
	 */
	public function getTable($type = 'Fsitem', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Get an item by alias
	 * @param   string  $alias  Alias string
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
	 * @param   integer  $id  The id of the row to check out.
	 * @return  boolean True on success, false on failure.
	 * @since    1.6
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int) $this->getState('fsitem.id');

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
	 * @param   integer  $id  The id of the row to check out.
	 * @return  boolean True on success, false on failure.
	 * @since    1.6
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('fsitem.id');

		if ($id) {
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = GaforsaleHelper::getSpecificUser();

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
	 * Method to get the profile form.
	 * The base form is loaded from XML
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 * @return    JForm    A JForm object on success, false on failure
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_gaforsale.fsitem', 'fsitemform', array(
			'control'   => 'jform',
			'load_data' => $loadData
			)
		);

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 * @return    mixed    The data for the form.
	 * @since    1.6
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_gaforsale.edit.fsitem.data', array());

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
	 * @param   array  $data  The form data
	 * @return bool
	 * @throws Exception
	 * @since 1.6
	 */
	public function save($data)
	{
		$id    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('fsitem.id');
		$data['state'] = (!empty($data['state'])) ? $data['state'] : (int) 0;
		$user = GaforsaleHelper::getSpecificUser();

		if ($id) {
			// Check the user can edit this item
			$authorised = $user->authorise('core.edit', 'com_gaforsale') || $authorised = $user->authorise('core.edit.own', 'com_gaforsale');
		    if ($data['state'] == 1) { $data['state'] = 0; }
		} else {
			// Check the user can create new items in this section
			$authorised = $user->authorise('core.create', 'com_gaforsale');
		}

		if ($authorised !== true) {
			throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		if (!empty($data['item_image'])) {
			$data['item_image'] = $this->uploadAttachment($data['item_image']);
		} else {
			if (isset($data['item_image_txt'])) {
				$data['item_image'] = $data['item_image_txt'];
			} else {
				$data['item_image'] = '';
			}
		}
		//Factory::getApplication()->setUserState('com_gaforsale.test.data', $data);

        $tmpl = !$id ? 'fsitems' : '';

		$table = $this->getTable();

		if ($table->save($data) === true) {
            if (!$id) {
				// notify of new item loaded
				GaforsaleHelper::notifyForsale($table->id, $tmpl);
			}
			return $table->id;
		} else {
			return false;
		}
	}

	/**
	 * Method to delete data
	 * @param   array  $data  Data to be deleted
	 * @return bool|int If success returns the id of the deleted item, if not false
	 * @throws Exception
	 */
	public function delete($data)
	{
		$id = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('fsitem.id');

		if (GaforsaleHelper::getSpecificUser()->authorise('core.delete', 'com_gaforsale') !== true) {
			throw new \Exception(403, Text::_('JERROR_ALERTNOAUTHOR'));
		}

		$table = $this->getTable();
		$table->load($data['id']);
		$table->state = -2;


		if ($table->store() === true) {
			return $id;
		} else {
			return false;
		}
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
     * Method to upload an attachment
     */
    public function uploadAttachment($tran_file = null)
    {
		$app = Factory::getApplication();
		$params = ComponentHelper::getParams('com_gaforsale');
        $safeFileOptions  = $params->get( 'safe_files', array('jpg') );
        $max_size  = $params->get( 'max_size', 250 ) * 10000;
		$file_ext = strtolower(substr($tran_file['name'],-3) ?? '');


		if (!in_array($file_ext, $safeFileOptions)) {
			$app->enqueueMessage(Text::sprintf('COM_GAFORSALE_FILE_FORMAT_NO', $file_ext), 'danger');
			return false;
		}

        if (file_exists('file://'.$tran_file['tmp_name'])) {
			$tooBig = $tran_file['size'] > $max_size ? true : false;
			if ($tooBig) {
				$app->enqueueMessage(Text::_('COM_GAFORSALE_FILE_TOO_BIG'), 'danger');
				return null;
			}
			$fileName = File::makeSafe($tran_file['name']);
			$fileName = str_replace(' ', '_', $fileName ?? '');
			$src = $tran_file['tmp_name'];
			$fileName = 'images/forsale/'.$fileName;

			$path = Path::clean( JPATH_SITE . '/' );
			$destfile = $path.'/'.$fileName;

			if ( File::upload($src, $destfile, false, false, $safeFileOptions) ) {
				$app->enqueueMessage(Text::sprintf('COM_GAFORSALE_FILE_UPLOAD_SUCC', $fileName), 'success');
				return $fileName;
			} else {
				$app->enqueueMessage(Text::sprintf('COM_GAFORSALE_FILE_UPLOAD_FAIL', $fileName), 'danger');
				return null;
			}
  		} else {
			$app->enqueueMessage(Text::sprintf('COM_GAFORSALE_FILE_NOEXIST', $fileName), 'danger');
			return null;
		}
    }

}
