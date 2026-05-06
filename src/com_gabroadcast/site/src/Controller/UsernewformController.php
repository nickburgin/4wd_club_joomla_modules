<?php

/**
 * @version     4.3.3
 * @package     com_gabroadcast
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gabroadcast\Site\Controller;

// No direct access
\defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\MVC\Controller\FormController;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Session\Session;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\MVC\View\GenericDataException;
use \GlennArkell\Component\Gabroadcast\Administrator\Helper\GabroadcastHelper;

/**
 * Form controller class.
 * @since  1.6
 */
class UsernewformController extends FormController
{
	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	public function edit($key = NULL, $urlVar = NULL)
	{
		$app = Factory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_gabroadcast.edit.usernew.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the record id for the record to edit in the session.
		$app->setUserState('com_gabroadcast.edit.usernew.id', $editId);

		// Get the model.
		$model = $this->getModel('Usernewform', 'Site');

		// Check out the item
		if ($editId)
		{
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId)
		{
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gabroadcast&view=usernewform&layout=edit', false));
	}

	/**
	 * Method to save record data.
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since  1.6
	 */
	public function save($key = NULL, $urlVar = NULL)
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Usernewform', 'Site');
		$params		= ComponentHelper::getParams('com_gabroadcast');
        $limit_set = $params->get('limit_set',0);

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
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			$input = $app->input;
			$jform = $input->get('jform', array(), 'ARRAY');

			// Save the data in the session.
			$app->setUserState('com_gabroadcast.edit.usernew.data', $jform);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gabroadcast.edit.usernew.id');
			$this->setRedirect(Route::_('index.php?option=com_gabroadcast&view=usernewform&layout=edit&id=' . $id, false));

			$this->redirect();
		}

		// Attempt to save the data.
		$return = $model->save($data);

		// Check for errors.
		if ($return === false) {
			// Save the data in the session.
			$app->setUserState('com_gabroadcast.edit.usernew.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gabroadcast.edit.usernew.id');
			$this->setMessage(Text::sprintf('Save failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gabroadcast&view=usernewform&layout=edit&id=' . $id, false));
		}

		// Check in the record.
		if ($return) {
			$model->checkin($return);
		}

		// Clear the record id from the session.
		$app->setUserState('com_gabroadcast.edit.usernew.id', null);

		// Redirect to the list screen.
		$Itemid = $app->getUserState('com_gabroadcast.menu.Itemid');
		$app->enqueueMessage(Text::_('COM_GABROADCAST_MESSAGESENT_SUCCESSFULLY'), 'message');

 		if ($limit_set) {
 			//$this->setRedirect(Route::_('index.php?option=com_gabroadcast&view=usernews&view_type=5', false));
 			echo 'ExecTime: '. \ini_get('max_execution_time');
 		}
 		//GabroadcastHelper::print_r2($app->getUserState('com_gabroadcast.test.data'));

		// Flush the data from the session.
		$app->setUserState('com_gabroadcast.edit.usernew.data', null);
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
		$editId = (int) $app->getUserState('com_gabroadcast.edit.usernew.id');

		// Get the model.
		$model = $this->getModel('Usernewform', 'Site');

		// Check in the item
		if ($editId)
		{
			$model->checkin($editId);
		}

		$app->enqueueMessage(Text::_('COM_GABROADCAST_CANCELLED_SAFELY'), 'message');
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
        $model = $this->getModel('Usernewform', 'Site');
        $pk    = $app->input->getInt('id');
		$Itemid = Factory::getApplication()->getUserState('com_gabroadcast.menu.Itemid');

        // Attempt to save the data
        try
        {
            $return = $model->delete($pk);

            // Check in the profile
            $model->checkin($return);

            // Clear the record id from the session.
            $app->setUserState('com_gabroadcast.edit.usernew.id', null);

            $menu = $app->getMenu();
            $item = $menu->getActive();
            $url = (empty($item->link) ? 'index.php?option=com_gabroadcast&view=usernews' : $item->link);

            // Redirect to the list screen
            $this->setMessage(Text::_('COM_GABROADCAST_ITEM_DELETED_SUCCESSFULLY'));
            $this->setRedirect(Route::_($url, false));

            // Flush the data from the session.
            $app->setUserState('com_gabroadcast.edit.usernew.data', null);
        }
        catch (Exception $e)
        {
            $errorType = ($e->getCode() == '404') ? 'error' : 'warning';
            $this->setMessage($e->getMessage(), $errorType);
            $this->setRedirect('index.php?option=com_gabroadcast&view=usernews');
        }
    }

    public function uplattachfile() {
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app	= Factory::getApplication();
		$model = $this->getModel('Usernewform', 'Site');

		// Get the user data.
		$data = Factory::getApplication()->input->get('jform', array(), 'array');
		$files = Factory::getApplication()->input->files->get('jform', '', 'array');
        foreach ($files as $file) {
			$data['bcfile_name'] = $file;
		}
		// Validate the posted data.
		$form = $model->getForm();
		if (!$form) {
			throw new GenericDataException(500, $model->getError());
			return false;
		}

		// Validate the posted data.
		$data = $model->validate($form, $data);

		// Check for errors.
		if ($data === false) {
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_gabroadcast.edit.usernew.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(Route::_('index.php?option=com_gabroadcast&view=usernewform', false));
			return false;
		}

		// Attempt to upload the file.
		$return	= $model->uplattachfile($data);

		// Check for errors.
		if ($return === false) {
			// Save the data in the session.
			$app->setUserState('com_gabroadcast.edit.usernew.data', $data);

			// Redirect back to the edit screen.
			$this->setMessage(Text::sprintf('Upload failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gabroadcast&view=usernewform&layout=upload', false));
			return false;
		}

        // Clear the record id from the session.
        $app->setUserState('com_gabroadcast.edit.usernew.id', null);

		// Flush the data from the session.
		$app->setUserState('com_gabroadcast.edit.usernew.data', null);
    }

}
