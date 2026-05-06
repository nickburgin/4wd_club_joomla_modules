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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

/**
 * Form class.
 * @since  1.6.0
 */
class AttendeeformController extends FormController
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
		$previousId = (int) $app->getUserState('com_gacalevents.edit.attendee.id');
		$editId     = $app->input->getInt('id', 0);
		$event_id     = $app->input->getInt('event_id', 0);
		$attendee     = $app->input->getInt('attendee', 0);
		$layout     = !empty($app->input->get('layout')) ? $app->input->get('layout') : 'edit';

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gacalevents.edit.attendee.id', $editId);
		$app->setUserState('com_gacalevents.edit.attendee.event_id', $event_id);
		$app->setUserState('com_gacalevents.edit.attendee.attendee', $attendee);

		// Get the model.
		$model = $this->getModel('Attendeeform', 'Site');
		//Factory::getApplication()->setUserState('com_gacalevents.test.data', $event_id);

		// Check out the item
		if ($editId) {
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId) {
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gacalevents&view=attendeeform&layout='.$layout, false));
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
		$model = $this->getModel('Attendeeform', 'Site');

		// Get the user data.
		$data = Factory::getApplication()->input->get('jform', array(), 'array');
        
//         $setRedir = false;
//         if (!empty($data['from_modal']) && $data['from_modal']) {
//             // set redirect
//             $setRedir = true;
//         }

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
			$app->setUserState('com_gacalevents.edit.attendee.data', $jform);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gacalevents.edit.attendee.id');
			$this->setRedirect(Route::_('index.php?option=com_gacalevents&view=attendeeform&layout=edit&id=' . $id, false));

			$this->redirect();
		}

		// Attempt to save the data.
		$return = $model->save($data);

		// Check for errors.
		if ($return === false) {
			// Save the data in the session.
			$app->setUserState('com_gacalevents.edit.attendee.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gacalevents.edit.attendee.id');
			$this->setMessage(Text::sprintf('Save failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gacalevents&view=attendeeform&layout=edit&id=' . $id, false));
		}

		// Check in the record.
		if ($return) {
			$model->checkin($return);
		}

		// Clear the id reference data from the session.
		$app->setUserState('com_gacalevents.edit.attendee.id', null);
		$app->setUserState('com_gacalevents.edit.attendee.event_id', null);
		$app->setUserState('com_gacalevents.edit.attendee.attendee', null);
		// Flush the data from the session.
		$app->setUserState('com_gacalevents.edit.attendee.data', null);

		// Redirect to the list screen.
        $this->setRedirect(Route::_('index.php?option=com_gacalevents&view=events', false));

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
		$editId = (int) $app->getUserState('com_gacalevents.edit.attendee.id');

		// Get the model.
		$model = $this->getModel('Attendeeform', 'Site');

		// Check in the item
		if ($editId)
		{
			$model->checkin($editId);
		}

		$item = Factory::getApplication()->getMenu()->getActive();
		$url  = (empty($item->link) ? 'index.php?option=com_gacalevents&view=events' : $item->link);
		$this->setRedirect(Route::_($url, false));

		// Flush the data from the session.
		$app->setUserState('com_gacalevents.edit.attendee.data', null);
		$app->setUserState('com_gacalevents.edit.attendee.id', null);
		$app->setUserState('com_gacalevents.edit.attendee.event_id', null);
		$app->setUserState('com_gacalevents.edit.attendee.attendee', null);
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
        $model = $this->getModel('Attendeeform', 'Site');
        $pk    = $app->input->getInt('id');

        // Attempt to save the data
        try
        {
            $return = $model->delete($pk);

            // Check in the profile
            $model->checkin($return);

            // Clear the reference data from the session.
			$app->setUserState('com_gacalevents.edit.attendee.id', null);
			$app->setUserState('com_gacalevents.edit.attendee.event_id', null);
			$app->setUserState('com_gacalevents.edit.attendee.attendee', null);

            $item = $app->getMenu()->getActive();
            $url = (empty($item->link) ? 'index.php?option=com_gacalevents&view=events' : $item->link);

            // Redirect to the list screen
            $this->setMessage(Text::_('GACALEVENTS_ITEM_DELETED_SUCCESSFULLY'));
            $this->setRedirect(Route::_($url, false));

            // Flush the data from the session.
            $app->setUserState('com_gacalevents.edit.attendee.data', null);
        }
        catch (\Exception $e)
        {
            $errorType = ($e->getCode() == '404') ? 'error' : 'warning';
            $this->setMessage($e->getMessage(), $errorType);
            $this->setRedirect('index.php?option=com_gacalevents&view=events');
			$app->setUserState('com_gacalevents.edit.attendee.id', null);
			$app->setUserState('com_gacalevents.edit.attendee.event_id', null);
			$app->setUserState('com_gacalevents.edit.attendee.attendee', null);
        }
    }
}
