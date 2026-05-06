<?php
/**
 * @version    3.0.0
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gacalevents\Site\Controller;

\defined('_JEXEC') or die;

use \Joomla\CMS\Application\SiteApplication;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Multilanguage;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Controller\BaseController;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Uri\Uri;
use \Joomla\Utilities\ArrayHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;

/**
 * Singular class.
 * @since  1.6.0
 */
class EventController extends BaseController
{
	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 * @return void
	 * @since    1.6
     * @throws Exception
	 */
	public function edit()
	{
		$app = Factory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_gacalevents.edit.event.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gacalevents.edit.event.id', $editId);

		// Get the model.
		$model = $this->getModel('Event', 'Site');

		// Check out the item
		if ($editId) {
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId && $previousId !== $editId) {
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gacalevents&view=eventform&layout=edit', false));
	}

	/**
	 * Method to save data
	 * @return    void
	 * @throws Exception
	 * @since    1.6
	 */
	public function publish()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = GacaleventsHelper::getSpecificUser();

		if ($user->authorise('core.edit', 'com_gacalevents') || $user->authorise('core.edit.state', 'com_gacalevents')) {
			$model = $this->getModel('Event', 'Site');

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
			$app->setUserState('com_gacalevents.edit.event.id', null);

			// Flush the data from the session.
			$app->setUserState('com_gacalevents.edit.event.data', null);

			// Redirect to the list screen.
			$this->setMessage(Text::_('COM_GACALEVENTS_ITEM_SAVED_SUCCESSFULLY'));
			$menu = $app->getMenu();
			$item = $menu->getActive();

			if (!$item) {
				// If there isn't any menu item active, redirect to list view
				$this->setRedirect(Route::_('index.php?option=com_gacalevents&view=events', false));
			} else {
                $this->setRedirect(Route::_('index.php?Itemid='. $item->id, false));
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

		// Checking if the user can remove object
		$user = GacaleventsHelper::getSpecificUser();

		if ($user->authorise('core.delete', 'com_gacalevents')) {
			$model = $this->getModel('Event', 'Site');

			// Get the user data.
			$id = $app->input->getInt('id', 0);

			// Attempt to save the data.
			$return = $model->delete($id);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf('Delete failed', $model->getError()), 'warning');
			} else {
				// Check in the profile.
				if ($return)
				{
					$model->checkin($return);
				}

                $app->setUserState('com_gacalevents.edit.event.id', null);
                $app->setUserState('com_gacalevents.edit.event.data', null);

                $app->enqueueMessage(Text::_('COM_GACALEVENTS_ITEM_DELETED_SUCCESSFULLY'), 'success');
                $app->redirect(Route::_('index.php?option=com_gacalevents&view=events', false));
			}

			// Redirect to the list screen.
			$item = $app->getMenu()->getActive();
			$this->setRedirect(Route::_($item->link, false));
		} else {
			throw new \Exception(500);
		}
	}

	public function removeAtt()
	{
		// Initialise variables.
		$app	= Factory::getApplication();
		$model	= $this->getModel('Event', 'Site');

		// Get the data from the form POST
		$id = $app->input->getInt('id', 0);
		//$attendee = $app->input->getInt('attendee', 0);

		$model->deleteAtt($id);
        $app->enqueueMessage(Text::_('COM_GACALEVENTS_ITEM_DELETED_SUCCESSFULLY'), 'success');
        $this->setRedirect(Route::_('index.php?option=com_gacalevents&view=events', false));

		return true;
	}

	public function eventrego()
	{
		// Initialise variables.
		$app	= Factory::getApplication();
		$model	= $this->getModel('Event', 'Site');

		// Get the data from the form POST
		$event_id = $app->input->getInt('event_id', 0);
		$attendee = $app->input->getInt('attendee', 0);
		$eventaction = $app->input->getInt('eventaction', 0);

		$model->registerAttendee($event_id, $attendee, $eventaction);

        $this->setRedirect(Route::_('index.php?option=com_gacalevents&view=events', false));

		return true;
	}

	public function reminder()
	{
		// Initialise variables.
		$app	= Factory::getApplication();
		$model	= $this->getModel('Event', 'Site');

		// Get the data from the form POST
		$event_id = $app->input->getInt('event_id', 0);
		
		if ($event_id) {
			$return	= $model->sendReminder($event_id);
		}

		// Redirect to the list screen.
		$item = $app->getMenu()->getActive();
		if (!$item) {
			$this->setRedirect(Route::_('index.php?option=com_gacalevents&view=events', false));
		} else {
			$this->setRedirect(Route::_($item->link, false));
		}

		return true;
	}

	public function extractAttendees()
	{
		// Initialise variables.
		$app	= Factory::getApplication();
		$model	= $this->getModel('Event', 'Site');

		// Get the passed recordId
		$event_id = $app->input->getInt('event_id');
		
		$return	= $model->extractAttendees($event_id);

		// Redirect to the list screen.
		$item = $app->getMenu()->getActive();
		if (!$item) {
			$this->setRedirect(Route::_('index.php?option=com_gacalevents&view=events', false));
		} else {
			$this->setRedirect(Route::_($item->link, false));
		}

		return true;
	}
}
