<?php
/**
 * @version    3.3.1
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gacalevents\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

/**
 * Event class.
 * @since  1.6.0
 */
class EventformController extends FormController
{
	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 * @return void
	 * @since    1.6
     * @throws Exception
	 */
	public function edit($key = NULL, $urlVar = NULL)
	{
		$app = Factory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_gacalevents.edit.event.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gacalevents.edit.event.id', $editId);

		// Get the model.
		$model = $this->getModel('Eventform', 'Site');

		// Check out the item
		if ($editId) {
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId) {
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gacalevents&view=eventform&layout=edit', false));
	}

	/**
	 * Method to save data.
	 * @return void
	 * @throws Exception
	 * @since  1.6
	 */
	public function save($key = NULL, $urlVar = NULL)
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Eventform', 'Site');

		// Get the user data.
		$data = Factory::getApplication()->input->get('jform', array(), 'array');

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
				if ($errors[$i] instanceof \Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			$input = $app->input;
			$jform = $input->get('jform', array(), 'ARRAY');

			// Save the data in the session.
			$app->setUserState('com_gacalevents.edit.event.data', $jform);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gacalevents.edit.event.id');
			$this->setRedirect(Route::_('index.php?option=com_gacalevents&view=eventform&layout=edit&id=' . $id, false));

			$this->redirect();
		}

		// Attempt to save the data.
		$return = $model->save($data);

		// Check for errors.
		if ($return === false) {
			// Save the data in the session.
			$app->setUserState('com_gacalevents.edit.event.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gacalevents.edit.event.id');
			$this->setMessage(Text::sprintf('Save failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gacalevents&view=eventform&layout=edit&id=' . $id, false));
		}

		// Check in the profile.
		if ($return) {
			$model->checkin($return);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_gacalevents.edit.event.id', null);

		// Redirect to the list screen.
		$this->setMessage(Text::_('COM_GACALEVENTS_ITEM_SAVED_SUCCESSFULLY'));
		$menu = Factory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = (empty($item->link) ? 'index.php?option=com_gacalevents&view=events' : $item->link);
		$this->setRedirect(Route::_($url, false));

		// Flush the data from the session.
		$app->setUserState('com_gacalevents.edit.event.data', null);
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
		$editId = (int) $app->getUserState('com_gacalevents.edit.event.id');

		// Get the model.
		$model = $this->getModel('Eventform', 'Site');

		// Check in the item
		if ($editId)
		{
			$model->checkin($editId);
		}

		$item = Factory::getApplication()->getMenu()->getActive();
		$url  = (empty($item->link) ? 'index.php?option=com_gacalevents&view=events' : $item->link);
		$this->setRedirect(Route::_($url, false));

		// Flush the data from the session.
		$app->setUserState('com_gacalevents.edit.event.data', null);
		$app->setUserState('com_gacalevents.edit.event.id', null);
	}

	/**
	 * Method to remove data
	 * @return void
	 * @throws Exception
     * @since 1.6
	 */
	public function remove()
    {
        $app   = Factory::getApplication();
        $model = $this->getModel('Eventform', 'Site');
        $pk    = $app->input->getInt('id');

        // Attempt to save the data
        try
        {
            $return = $model->delete($pk);

            // Check in the profile
            $model->checkin($return);

            // Clear the profile id from the session.
            $app->setUserState('com_gacalevents.edit.event.id', null);

            $item = $app->getMenu()->getActive();
            $url = (empty($item->link) ? 'index.php?option=com_gacalevents&view=events' : $item->link);

            // Redirect to the list screen
            $this->setMessage(Text::_('COM_GACALEVENTS_ITEM_DELETED_SUCCESSFULLY'));
            $this->setRedirect(Route::_($url, false));

            // Flush the data from the session.
            $app->setUserState('com_gacalevents.edit.event.data', null);
        }
        catch (\Exception $e)
        {
            $errorType = ($e->getCode() == '404') ? 'error' : 'warning';
            $this->setMessage($e->getMessage(), $errorType);
            $this->setRedirect('index.php?option=com_gacalevents&view=events');
        }
    }

	/**
	 * Method to send all attendees an email
	 * @return void
	 * @throws Exception
	 * @since  4.0.0
	 */
	public function sendEmail()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Eventform', 'Site');

		// Get the passed data.
		$data = Factory::getApplication()->input->get('jform', array(), 'array');
		$event_id = $data['event_id'];

		// Check for errors.
		if (!$event_id) {
			$app->enqueueMessage(Text::_('COM_GACALEVENTS_NO_EVENT_ID'), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gacalevents&view=events', false));
		} else {

			// Attempt to save the data.
			$event = $model->sendEmail($data);

			// Check for errors.
			if ($event === false) {
				// Redirect back to the trip list screen.
				$app->enqueueMessage(Text::_('COM_GACALEVENTS_MESSAGE_FAILED'), 'warning');
				$this->setRedirect(Route::_('index.php?option=com_gacalevents&view=events', false));
			}

			// Clear the session data and go back to the trip screen.
			$app->setUserState('com_gacalevents.edit.event.id', null);
			$app->enqueueMessage(Text::_('COM_GACALEVENTS_MESSAGESENT_SUCCESSFULLY'), 'notice');
			$this->setRedirect(Route::_('index.php?option=com_gacalevents&view=events', false));

			// Flush the data from the session.
			$app->setUserState('com_gacalevents.edit.event.data', null);
		}
	}

}
