<?php
/**
 * @version    4.2.0
 * @package    pkg_mypackage
 * @subpackage com_gatracklog
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatracklog\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

/**
 * Form class.
 * @since  1.6.0
 */
class TracklogformController extends FormController
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
		$previousId = (int) $app->getUserState('com_gatracklog.edit.tracklog.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gatracklog.edit.tracklog.id', $editId);

		// Get the model.
		$model = $this->getModel('Tracklogform', 'Site');

		// Check out the record
		if ($editId) {
			$model->checkout($editId);
		}

		// Check in the previous record.
		if ($previousId) {
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gatracklog&view=tracklogform&layout=edit', false));
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
		// Set up Redirect URL.
        $item = $app->getMenu()->getActive();
        $url = (empty($item->link) ? 'index.php?option=com_gatracklog&view=tracklogs' : $item->link.'&Itemid='.$item->id);

		$model = $this->getModel('Tracklogform', 'Site');

		// Get the user data.
		$data = $app->input->get('jform', array(), 'ARRAY');

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form) {
			throw new \Exception($model->getError(), 500);
		}

		// Validate the posted data.
		$data = $model->validate($form, $data);
		
		// check date format entered
        $goodDateFormat = $model->isValidDate($data['tran_date']);
		if (!$goodDateFormat) { 
            $data = false; 
            $app->enqueueMessage('COM_GAGATRACKLOG_BAD_DATE_FORMAT', 'danger');
        }

		// Check for errors.
		if ($data === false)
		{
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

			$jform = $app->input->get('jform', array(), 'ARRAY');

			// Save the data in the session.
			$app->setUserState('com_gatracklog.edit.tracklog.data', $jform);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gatracklog.edit.tracklog.id');
			$this->setRedirect(Route::_('index.php?option=com_gatracklog&view=tracklogform&layout=edit&id=' . $id, false));

			$this->redirect();
		}

		// Attempt to save the data.
		$return = $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_gatracklog.edit.tracklog.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gatracklog.edit.tracklog.id');
			$app->enqueueMessage(Text::sprintf(Text::_('COM_GAGATRACKLOG_SAVE_FAILED'), $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gatracklog&view=tracklogform&layout=edit&id=' . $id, false));
		}

		// Check in the record.
		if ($return) {
			$model->checkin($return);
		}

		// Clear the record id from the session.
		$app->setUserState('com_gatracklog.edit.tracklog.id', null);

		// Redirect to the list screen.
		$app->enqueueMessage(Text::_('COM_GAGATRACKLOG_ITEM_SAVED_SUCCESSFULLY'), 'success');
		$this->setRedirect(Route::_($url, false));

		// Flush the data from the session.
		$app->setUserState('com_gatracklog.edit.tracklog.data', null);
	}

	/**
	 * Method to abort current operation
	 * @return void
	 * @throws Exception
	 */
	public function cancel($key = NULL)
	{
		$app = Factory::getApplication();
		// Set up Redirect URL.
        $item = $app->getMenu()->getActive();
        $url = (empty($item->link) ? 'index.php?option=com_gatracklog&view=tracklogs' : $item->link.'&Itemid='.$item->id);

		// Get the current edit id.
		$editId = (int) $app->getUserState('com_gatracklog.edit.tracklog.id');

		// Get the model.
		$model = $this->getModel('Tracklogform', 'Site');

		// Check in the record
		if ($editId) {
			$model->checkin($editId);
		}

		$this->setRedirect(Route::_($url, false));
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
		// Set up Redirect URL.
        $item = $app->getMenu()->getActive();
        $url = (empty($item->link) ? 'index.php?option=com_gatracklog&view=tracklogs' : $item->link.'&Itemid='.$item->id);

        $model = $this->getModel('Tracklogform', 'Site');
        $pk    = $app->input->getInt('id');

        // Attempt to save the data
        try
        {
            $return = $model->delete($pk);

            // Check in the record
            $model->checkin($return);

            // Clear the record id from the session.
            $app->setUserState('com_gatracklog.edit.tracklog.id', null);

            // Redirect to the list screen
            $app->enqueueMessage(Text::_('COM_GAGATRACKLOG_ITEM_DELETED_SUCCESSFULLY'), 'success');

            // Flush the data from the session.
            $app->setUserState('com_gatracklog.edit.tracklog.data', null);
        }
        catch (\Exception $e)
        {
            $errorType = ($e->getCode() == '404') ? 'error' : 'warning';
            $app->enqueueMessage($e->getMessage(), $errorType);
        }

        $this->setRedirect(Route::_($url, false));
    }

	/**
	 * Method to send the data file
	 */
	public function saveComment()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Tracklogform', 'Site');

		// Get the user data.
		$data = Factory::getApplication()->input->get('jform', array(), 'array');

		// Attempt to send the file.
		$return = $model->saveComment($data);

		// Check for errors.
		if ($return === false) {
			$this->setMessage(Text::_('COM_GATRACKLOG_ITEM_SAVED_FAILED'));
		}

		// Clear the profile id from the session.
		$app->setUserState('com_gatracklog.edit.tracklog.id', null);
		$app->setUserState('com_gatracklog.edit.tracklog.data', null);

		// show message.
		$this->setMessage(Text::_('COM_GATRACKLOG_ITEM_SAVED_SUCCESSFULLY'));
		$this->setRedirect(Route::_('index.php?option=com_gatracklog&view=tracklog&id=' . $data['track_id'], false));

	}

}
