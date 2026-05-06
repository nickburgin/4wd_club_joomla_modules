<?php

/**
 * @version    5.3.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatripsys\Site\Controller;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Controller class.
 * @since  1.6
 */
class IncidentformController extends FormController
{
	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 * @return void
	 * @since    1.6
	 */
	public function edit($key = NULL, $urlVar = NULL)
	{
		$app = Factory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_gatripsys.edit.incident.id');
		$editId     = $app->input->getInt('id', 0);
		$tripId     = $app->input->getInt('trip_id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gatripsys.edit.incident.id', $editId);
		$app->setUserState('com_gatripsys.edit.trip.id', $tripId);

		// Get the model.
		$model = $this->getModel('Incidentform', 'Site');

		// Check out the item
		if ($editId) {
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId) {
			$model->checkin($previousId);
		}

		// Redirect to the edit screen or the modal layout.
		$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=incidentform&layout=edit', false));
	}

	/**
	 * Method to save a user's profile data.
	 * @return void
	 * @throws Exception
	 * @since  1.6
	 */
	public function save($key = NULL, $urlVar = NULL)
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Incidentform', 'Site');

		// Get the user data.
		$data = $app->input->get('jform', array(), 'array');
		$files = $app->input->files->get('jform', '', 'array');
        $trip_id = $data['trip_id'];

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form) {
			throw new \Exception($model->getError(), 500);
		}

		// Validate the posted data.
		$data = $model->validate($form, $data);

		// Check for errors.
		if ($data === false) {
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			$input = $app->input;
			$jform = $input->get('jform', array(), 'ARRAY');

			// Save the data in the session.
			$app->setUserState('com_gatripsys.edit.incident.data', $jform);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gatripsys.edit.incident.id');
			$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=incidentform&layout=edit&id=' . $id, false));
			return false;
		}

		// Attempt to load images.
		$params = ComponentHelper::getParams('com_gatripsys');
        foreach ($files as $file) { $cntr++;
			if ($file['size']) {
				$data['inc_img'] = $model->uploadAttachment($file, $params, 'trip');
			} else {
				if (empty($data['inc_img_disp'])) {
					$data['inc_img'] = '';
				} else {
					$data['inc_img'] = $data['inc_img_disp'];
				}
			}
		}

		// Attempt to save the data.
		$return = $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_gatripsys.edit.incident.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gatripsys.edit.incident.id');
			$this->setMessage(Text::sprintf('Save failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=incidentform&layout=edit&id=' . $id, false));
			return false;
		}

		// Check in the record.
		if ($return) {
			$model->checkin($return);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_gatripsys.edit.incident.id', null);

		// Redirect to the list screen.
		$this->setMessage(Text::_('COM_GATRIPSYS_ITEM_SAVED_SUCCESSFULLY'));
		//$menu = Factory::getApplication()->getMenu();
		//$item = $menu->getActive();
		//$url  = (empty($item->link) ? 'index.php?option=com_gatripsys&view=trip&id='.$data['trip_id'] : $item->link);
		$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=trip&id='.$trip_id, false));

		// Flush the data from the session.
		$app->setUserState('com_gatripsys.edit.incident.data', null);
	}

	/**
	 * Method to abort current operation
	 * @return void
	 * @throws Exception
	 */
	public function cancel($key = NULL)
	{
		$app = Factory::getApplication();

		// Get the current edit id.
		$editId = (int) $app->getUserState('com_gatripsys.edit.incident.id');
		$tripId = (int) $app->getUserState('com_gatripsys.edit.trip.id');

		// Get the model.
		$model = $this->getModel('Incidentform', 'Site');

		// Check in the item
		if ($editId) {
			$model->checkin($editId);
		}
		
		$this->setMessage(Text::_('COM_GATRIPSYS_CANCEL_SUCCESSFULLY'));
		$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=trip&id='.$tripId, false));

	}

	/**
	 * Method to remove data
	 * @return void
	 * @throws Exception
	 */
	public function remove()
	{
		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Incidentform', 'Site');

		// Get the user data.
		$data       = array();
		$data['id'] = $app->input->getInt('id');

		// Check for errors.
		if (empty($data['id'])) {
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_gatripsys.edit.incident.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gatripsys.edit.incident.id');
			$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=incident&layout=edit&id=' . $id, false));
		}

		// Attempt to save the data.
		$return = $model->delete($data);

		$trip_id = $app->getUserState('com_gatripsys.tripid.data');
        $app->setUserState('com_gatripsys.tripid.data',null);

		// Check for errors.
		if ($return === false) {
			// Save the data in the session.
			$app->setUserState('com_gatripsys.edit.incident.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gatripsys.edit.incident.id');
			$this->setMessage(Text::sprintf('Delete failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=incident&layout=edit&id=' . $id, false));
		}

		// Check in the profile.
		if ($return) {
			$model->checkin($return);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_gatripsys.edit.incident.id', null);

		// Redirect to the list screen.
		$this->setMessage(Text::_('COM_GATRIPSYS_ITEM_DELETED_SUCCESSFULLY'));
		$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=trip&id='.$trip_id, false));

		// Flush the data from the session.
		$app->setUserState('com_gatripsys.edit.incident.data', null);
	}


	/**
	 * Method to save data
	 * @return void
	 * @throws Exception
	 */
	public function modalSave()
	{
		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Incidentform', 'Site');

		// Get the user data.
		$data       = array();
		$data['id'] = $app->input->get('id');
		$data['trip_id'] = $app->input->get('trip_id');
		$app->setUserState('com_gatripsys.edit.trip.id', $data['trip_id']);

		// Check for errors.
		if (empty($data['id']))
		{
			$app->enqueueMessage(Text::_('COM_GATRIPSYS_ITEM_INCIDENT_FAILED'), 'warning');
			$app->setUserState('com_gatripsys.edit.incident.id', null);
	        $app->setUserState('com_gatripsys.edit.trip.id', null);
			$app->setUserState('com_gatripsys.edit.incident.data', null);
			$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=incidents', false));
		} else {

			// Attempt to save the data.
			$return = $model->attaccept($data);
	
			// Check for errors.
			if ($return === false) {
				// Save the data in the session.
				$app->setUserState('com_gatripsys.edit.incident.data', $data);
	
				// Redirect back to the edit screen.
				$id = (int) $app->getUserState('com_gatripsys.edit.incident.id');
				$app->enqueueMessage(Text::_('COM_GATRIPSYS_ITEM_INCIDENT_FAILED'), 'warning');
				$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=incidents', false));
			}
	
			// Check in the profile.
			if ($return) {
				$model->checkin($return);
			}
	
			// Clear the profile id from the session.
			$app->setUserState('com_gatripsys.edit.incident.id', null);
	        $app->setUserState('com_gatripsys.edit.trip.id', null);
			// Redirect to the list screen.
			$app->enqueueMessage(Text::_('COM_GATRIPSYS_ITEM_INCIDENT_SUCCESSFULLY'), 'notice');
			$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=trip&id='.$data['trip_id'], false));
	
			// Flush the data from the session.
			$app->setUserState('com_gatripsys.edit.incident.data', null);
		}
	}
}
