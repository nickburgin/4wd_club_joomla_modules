<?php
/**
 * @version     5.1.6
 * @package     com_gausers
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gausers\Site\Controller;

// No direct access
\defined('_JEXEC') or die;

use \Joomla\CMS\Application\SiteApplication;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Multilanguage;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Controller\FormController;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Uri\Uri;
use \Joomla\Utilities\ArrayHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaimgmgmntHelper;

/**
 * Form class.
 * @since  1.6.0
 */
class CurrentuserformController extends FormController
{

	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 * @since	1.6
	 */
	public function edit($key = null, $urlVar = null)
	{
		$app			= Factory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_gausers.edit.currentuser.id');
		$editId	= Factory::getApplication()->input->getInt('id', 0);
        $layout = Factory::getApplication()->input->get('layout');
        if (!isset($layout)) { $layout = 'edit'; }

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gausers.edit.currentuser.id', $editId);

		// Get the model.
		$model = $this->getModel('Currentuserform', 'Site');

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentuserform&layout='.$layout, false));
	}

	/**
	 * Method to save a user's profile data.
	 * @return	void
	 * @since	1.6
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app	= Factory::getApplication();
		$model = $this->getModel('Currentuserform', 'Site');

		// Get the user data.
		$data = Factory::getApplication()->input->get('jform', array(), 'array');
		$id = $data['id'];
		$wwk_reg = $data['wwk_reg'];
		$wwk_regp = $data['wwk_regp'];

		$files = Factory::getApplication()->input->files->get('jform', '', 'array');
        
		if (is_array($files) && !empty($files)) {
            $data = GaimgmgmntHelper::idAndSetFiles($data, $files);
		} else {
			// do nothing because no images added
		}

		// save the submitted data to the session
        $app->setUserState('com_gausers.edit.currentuser.data', $data);

		// set ref data not shown on form and clear out other elements
		$user = GausersHelper::getSpecificUser();

		// Validate the posted data.
		$form = $model->getForm();
		if (!$form) {
			throw new \Exception(500, $model->getError());
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
			$app->setUserState('com_gausers.edit.currentuser.data', Factory::getApplication()->input->get('jform', array(), 'array'));

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gausers.edit.currentuser.id');
			$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentuserform&layout=edit&id='.$id, false));
			return false;
		}

		// Attempt to save the data.
		$data['wwk_reg'] = $wwk_reg;
		$data['wwk_regp'] = $wwk_regp;
		$newId	= $model->save($data);

		// Check for errors.
		if ($newId === false) {
			// Save the data in the session.
			$app->setUserState('com_gausers.edit.currentuser.data', $data);

			// Redirect back to the edit screen.
			$id = (int)$app->getUserState('com_gausers.edit.currentuser.id');
			$app->setUserState('com_gausers.edit.currentuser.id', null);
			$this->setMessage(Text::sprintf('Save failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentuserform&layout=edit&id='.$id, false));
			return false;
		}

        // Clear the id from the session.
        $app->setUserState('com_gausers.edit.currentuser.id', null);
        $app->setUserState('com_gausers.edit.currentuser.data', null);
        $app->setUserState('com_users.edit.profile.id', null);
        $app->setUserState('com_users.edit.profile', null);

        // now setup an invoice if a new member record
        if (!$id) {
            $inv	= $model->generateInvoice($id, $newId);
        }

        // Redirect to the list screen.
		$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentuser&id='.$newId, false));

		// Flush the data from the session.
		$app->setUserState('com_gausers.edit.currentuser.data', null);
	}
    
    
    public function cancel($key = null)
    {
		// Initialise variables.
		$app	= Factory::getApplication();
		$fromScrn = $app->getUserState('com_gausers.edit.currentuser.fromScrn');
		$app->setUserState('com_gausers.edit.currentuser.fromScrn', null);

		$app->setUserState('com_gausers.edit.currentuser.data', null);
		
        if (isset($fromScrn) && $fromScrn) {
            $this->setMessage(Text::_('COM_GAUSERS_CANCELLED_SAFELY'));
            $this->setRedirect(Route::_('index.php?option=com_gausers&view=currentuser&id='.$fromScrn, false));
        } else {
            echo '<h2>'.Text::_('COM_GAUSERS_CANCELLED_SAFELY').'</h2>';
        }
    }

	public function remove()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app	= Factory::getApplication();
		$model = $this->getModel('Currentuserform', 'Site');

		// Get the user data.
		$data = Factory::getApplication()->input->get('jform', array(), 'array');

		// Validate the posted data.
		$form = $model->getForm();
		if (!$form) {
			throw new \Exception(500, $model->getError());
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
			$app->setUserState('com_gausers.edit.currentuser.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gausers.edit.currentuser.id');
			$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentuser&layout=edit&id='.$id, false));
			return false;
		}

		// Attempt to delete the data.
		$return	= $model->delete($data);

		// Check for errors.
		if ($return === false) {
			// Save the data in the session.
			$app->setUserState('com_gausers.edit.currentuser.data', $data);

			// Redirect back to the edit screen.
			$id = (int)$app->getUserState('com_gausers.edit.currentuser.id');
			$this->setMessage(Text::sprintf('Delete failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentuser&layout=edit&id='.$id, false));
			return false;
		}

        // Clear the profile id from the session.
        $app->setUserState('com_gausers.edit.currentuser.id', null);

        // Redirect to the list screen.
        $this->setMessage(Text::_('COM_GAUSERS_ITEM_DELETED_SUCCESSFULLY'));
        $item = Factory::getApplication()->getMenu()->getActive();
        $this->setRedirect(Route::_($item->link, false));

		// Flush the data from the session.
		$app->setUserState('com_gausers.edit.currentuser.data', null);
	}
    
	public function markAsLeft()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app	= Factory::getApplication();
		$model = $this->getModel('Currentuserform', 'Site');

		// Get the user data.
		$data = Factory::getApplication()->input->get('jform', array(), 'array');

		// Validate the posted data.
		$form = $model->getForm();
		if (!$form) {
			throw new \Exception(500, $model->getError());
			return false;
		}

		// Attempt to delete the data.
		$return	= $model->markAsLeft($data);

		// Check for errors.
		if ($return === false) {
			// Save the data in the session.
			$app->setUserState('com_gausers.edit.currentuser.data', $data);

			// Redirect back to the edit screen.
			$id = (int)$app->getUserState('com_gausers.edit.currentuser.id');
			$this->setMessage(Text::sprintf('Modal form failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentusers', false));
			return false;
		}

        // Clear the profile id from the session.
        $app->setUserState('com_gausers.edit.currentuser.id', null);

        // Redirect to the list screen.
        //$item = Factory::getApplication()->getMenu()->getActive();
        //$this->setRedirect(Route::_($item->link, false));
        $this->setRedirect(Route::_('index.php?option=com_gausers&view=currentusers', false));

		// Flush the data from the session.
		$app->setUserState('com_gausers.edit.currentuser.data', null);
	}

	public function markAsReturned()
	{
		// Check for request forgeries.
		$this->checkToken('get');

		// Initialise variables.
		$app	= Factory::getApplication();
		$model = $this->getModel('Currentuserform', 'Site');
        $id	= Factory::getApplication()->input->get('id');

		// Attempt to delete the data.
		$return	= $model->markAsReturned($id);

        // Clear the profile id from the session.
        $app->setUserState('com_gausers.edit.currentuser.id', null);

        // Redirect to the list screen.
        $this->setRedirect(Route::_('index.php?option=com_gausers&view=currentusers', false));

	}

	public function markAsDied()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app	= Factory::getApplication();
		$model = $this->getModel('Currentuserform', 'Site');

		// Get the user data.
		$data = Factory::getApplication()->input->get('jform', array(), 'array');

		// Validate the posted data.
		$form = $model->getForm();
		if (!$form) {
			throw new \Exception(500, $model->getError());
			return false;
		}

		// Attempt to delete the data.
		$return	= $model->markAsDied($data);

		// Check for errors.
		if ($return === false) {
			// Save the data in the session.
			$app->setUserState('com_gausers.edit.currentuser.data', $data);

			// Redirect back to the edit screen.
			$id = (int)$app->getUserState('com_gausers.edit.currentuser.id');
			$this->setMessage(Text::sprintf('Modal form failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentusers', false));
			return false;
		}

        // Clear the profile id from the session.
        $app->setUserState('com_gausers.edit.currentuser.id', null);

        // Redirect to the list screen.
        $item = Factory::getApplication()->getMenu()->getActive();
        $this->setRedirect(Route::_($item->link, false));

		// Flush the data from the session.
		$app->setUserState('com_gausers.edit.currentuser.data', null);
	}

    public function uplattachfile()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app	= Factory::getApplication();
		$model = $this->getModel('Currentuserform', 'Site');

		// Get the user data.
		$data = Factory::getApplication()->input->get('jform', array(), 'array');
		$files = Factory::getApplication()->input->files->get('jform', '', 'array');
        foreach ($files as $file) {
			$data['mship_file'] = $file;
		}

		// Attempt to upload the data.
		$return	= $model->uplattachfile($data);

		// Check for errors.
		if ($return === false) {
			// Redirect back to the list screen.
			$this->setMessage(Text::sprintf('COM_GAUSERS_FILE_UPLOAD_FAILED', $model->getError()), 'warning');
		}

        // Clear the profile id from the session.
        $app->setUserState('com_gausers.edit.currentuser.id', null);

        // Redirect to the list screen.
        $this->setRedirect(Route::_('index.php?option=com_gausers&view=currentusers', false));

		// Flush the data from the session.
		$app->setUserState('com_gausers.edit.currentuser.data', null);
    }

	/**
	 * Remove data
	 * @return void
	 * @throws Exception
	 */
	public function removeImgFile()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		$model = $this->getModel('Currentuserform', 'Site');

		// Get the user data.
		$id = $app->input->getInt('id', 0);
		$fileDel = $app->input->get('file', 'm');

		// Attempt to update the data.
		$return = $model->deleteImgFile($id, $fileDel);

		// Check for errors.
		if ($return === false) {
			$this->setMessage(Text::sprintf('COM_GAUSERS_FILE_DELETED_FAILED', $model->getError()), 'warning');
		} else {
            $app->enqueueMessage(Text::_('COM_GAUSERS_FILE_DELETED_SUCCESSFULLY'), 'success');
		}
        // Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gausers&task=currentuserform.edit&id='.$id, false));

	}

	public function updpw()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app	= Factory::getApplication();
		$model = $this->getModel('Currentuserform', 'Site');

		// Get the user data.
		$data = Factory::getApplication()->input->get('jform', array(), 'array');

		// Validate the posted data.
		$form = $model->getForm();
		if (!$form) {
			throw new \Exception(500, $model->getError());
			return false;
		}

		// Attempt to delete the data.
		$return	= $model->updPword($data);

		// Check for errors.
		if ($return === false) {
			// Save the data in the session.
			$app->setUserState('com_gausers.edit.currentuser.data', $data);

			// Redirect back to the edit screen.
			$id = (int)$app->getUserState('com_gausers.edit.currentuser.id');
			$this->setMessage(Text::sprintf('Modal form failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentuser&id='.$data['id'], false));
			return false;
		}

        // Clear the profile id from the session.
        $app->setUserState('com_gausers.edit.currentuser.id', null);
        $app->enqueueMessage(Text::_('COM_GAUSERS_PWUPDATED_SUCCESSFULLY'), 'success');

        // Redirect to the list screen.
        $this->setRedirect(Route::_('index.php?option=com_gausers&view=currentuser&id='.$data['id'], false));

		// Flush the data from the session.
		$app->setUserState('com_gausers.edit.currentuser.data', null);
	}

	public function createAction()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app	= Factory::getApplication();
		$model = $this->getModel('Currentuserform', 'Site');

		// Get the user data.
		$data = Factory::getApplication()->input->get('jform', array(), 'array');

		// Validate the posted data.
		$form = $model->getForm();
		if (!$form) {
			throw new \Exception(500, $model->getError());
			return false;
		}

		// Attempt to delete the data.
		$return	= $model->createAction($data);

		// Check for errors.
		if ($return === false) {
			// Save the data in the session.
			$app->setUserState('com_gausers.edit.currentuser.data', $data);

			// Redirect back to the edit screen.
            $app->enqueueMessage(Text::sprintf('COM_GAUSERS_NEW_ACTION_FAILED', $model->getError()), 'danger');
			$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentuser&id='.$data['user_id'], false));
			return false;
		}

        // Clear the id from the session.
        $app->setUserState('com_gausers.edit.currentuser.id', null);
        $app->enqueueMessage(Text::_('COM_GAUSERS_ITEM_SAVED_SUCCESSFULLY'), 'success');

        // Redirect to the list screen.
        $this->setRedirect(Route::_('index.php?option=com_gausers&view=currentuser&id='.$data['user_id'], false));

		// Flush the data from the session.
		$app->setUserState('com_gausers.edit.currentuser.data', null);
	}

}
