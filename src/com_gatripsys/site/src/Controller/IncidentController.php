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
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

/**
 * Controller class.
 * @since  1.6
 */
class IncidentController extends BaseController
{
	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 * @return void
	 * @since    1.6
	 */
	public function edit()
	{
		$app = Factory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_gatripsys.edit.incident.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gatripsys.edit.incident.id', $editId);

		// Get the model.
		$model = $this->getModel('Incident', 'Site');

		// Check out the item
		if ($editId) {
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId && $previousId !== $editId) {
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=incidentform&layout=edit', false));
	}

	/**
	 * Method to save a user's profile data.
	 * @return    void
	 * @throws Exception
	 * @since    1.6
	 */
	public function publish()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = GatripsysHelper::getSpecificUser();

		if ($user->authorise('core.edit', 'com_gatripsys') || $user->authorise('core.edit.state', 'com_gatripsys')) {
			$model = $this->getModel('Incident', 'Site');

			// Get the user data.
			$id    = $app->input->getInt('id');
			$state = $app->input->getInt('state');

			// Attempt to save the data.
			$return = $model->publish($id, $state);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf('Save failed: %s', $model->getError()), 'warning');
			}

			// Clear the profile id from the session.
			$app->setUserState('com_gatripsys.edit.incident.id', null);

			// Flush the data from the session.
			$app->setUserState('com_gatripsys.edit.incident.data', null);

			// Redirect to the list screen.
			$this->setMessage(Text::_('COM_GATRIPSYS_ITEM_SAVED_SUCCESSFULLY'));
			$menu = Factory::getApplication()->getMenu();
			$item = $menu->getActive();

			if (!$item) {
				// If there isn't any menu item active, redirect to list view
				$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=incidents', false));
			} else {
				$this->setRedirect(Route::_($item->link . $menuitemid, false));
			}
		} else {
			throw new \Exception(500);
		}
	}

	/**
	 * Remove data
	 * @return void
	 * @throws Exception
	 */
	public function remove()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Get the user data.
		$id = $app->input->getInt('id', 0);
		$trip_id = $app->input->getInt('trip_id', 0);
        $trip = GatripsysHelper::getTrip($trip_id);

		// Checking if the user can remove object
		$user = GatripsysHelper::getSpecificUser();
		$canEdit = GatripsysHelper::canUserEdit($user, $trip);

		if ($canEdit) {
			$model = $this->getModel('Incident', 'Site');

			// Attempt to save the data.
			$return = $model->delete($id);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf('Delete failed', $model->getError()), 'warning');
			} else {
				// Check in the profile.
				if ($return) {
					$model->checkin($return);
				}

				// Clear the profile id from the session.
				$app->setUserState('com_gatripsys.edit.incident.id', null);

				// Flush the data from the session.
				$app->setUserState('com_gatripsys.edit.incident.data', null);

				$this->setMessage(Text::_('COM_GATRIPSYS_FILE_DELETED_SUCCESSFULLY'));
			}
		} else {
			$this->setMessage(Text::_('COM_GATRIPSYS_NOT_AUTHORISED'), 'warning');
		}
		$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=trip&id='.(int) $trip_id, false));
	}
}
