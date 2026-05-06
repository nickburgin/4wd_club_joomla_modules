<?php
/**
 * @version     6.0.0
 * @package     com_gausers
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gausers\Site\Controller;

// No direct access
\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;

/**
 * Item class.
 * @since  1.6.0
 */
class CurrentuserController extends BaseController
{

	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 * @return void
	 * @since    1.6
     * @throws Exception
	 */
	public function edit()
	{
		$app			= Factory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_gausers.edit.currentuser.id');
		$editId	= $app->input->getInt('id', 0);
        $layout = $app->input->get('layout');
        $fromScrn = $app->input->get('frm');
        if (!isset($layout)) { $layout = 'edit'; }

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gausers.edit.currentuser.id', $editId);

		if (isset($fromScrn) && $fromScrn) {
            $app->setUserState('com_gausers.edit.currentuser.fromScrn', $editId);
        } else {
            $app->setUserState('com_gausers.edit.currentuser.fromScrn', 0);
        }

		// Get the model.
		$model = $this->getModel('Currentuser', 'Site');

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentuserform&layout='.$layout, false));
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
		$user = Factory::getApplication()->getIdentity();

		if ($user->authorise('core.edit', 'com_gausers') || $user->authorise('core.edit.state', 'com_gausers')) {
			$model = $this->getModel('Currentuser', 'Site');

			// Get the user data.
			$id    = $app->input->getInt('id');
			$state = $app->input->getInt('state');

			// Attempt to save the data.
			$return = $model->publish($id, $state);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf('Save failed: %s', $model->getError()), 'warning');
			}

			// Flush the data from the session.
			$app->setUserState('com_gausers.edit.currentuser.id', null);
			$app->setUserState('com_gausers.edit.currentuser.data', null);

			// Redirect to the list screen.
			$this->setMessage(Text::_('COM_GAUSERS_ITEM_SAVED_SUCCESSFULLY'));
			$item = Factory::getApplication()->getMenu()->getActive();
			if (!$item) {
				$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentusers', false));
			} else {
                $this->setRedirect(Route::_('index.php?Itemid='. $item->id, false));
			}
		} else {
			throw new \Exception(500);
		}
	}

    public function cancel($key = null) {
		// Initialise variables.
		$app	= Factory::getApplication();
		$fromScrn = $app->getUserState('com_gausers.edit.currentuser.fromScrn');
		$app->setUserState('com_gausers.edit.currentuser.fromScrn', null);

		if (isset($fromScrn) && $fromScrn) {
            $this->setMessage(Text::_('COM_GAUSERS_CANCELLED_SAFELY'));
            $this->setRedirect(Route::_('index.php?option=com_gausers&view=currentuser&id='.$fromScrn, false));
        } else {
            $user = Factory::getApplication()->getIdentity();
            $app->enqueueMessage(Text::_('COM_GAUSERS_CANCELLED_SAFELY'), 'message');
            if ($user->authorise('core.manage', 'com_gausers')) {
                $this->setRedirect(Route::_('index.php?option=com_gausers&view=currentusers', false));
            }
        }
    }
    
	public function remove()
	{
		// Initialise variables.
		$app	= Factory::getApplication();
		$model = $this->getModel('Currentuser', 'Site');

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

            
        // Check in the profile.
        if ($return) {
            $model->checkin($return);
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
    
    public function bulkBlockUsers()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Currentuser', 'Site');

		// Attempt to block all users with outstanding invoices.
		$return = $model->bulkBlockUsers();

		// Redirect to the list screen.
		if ($return) {
			$this->setMessage(Text::_('COM_GAUSERS_BULKBLOCK_SUCCESSFULLY'));
		}
		$this->setRedirect(Route::_('index.php?option=com_gausers&view=invoices', false));

    }

    public function delAction()
	{
		// Check for request forgeries.
		$this->checkToken('get');

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Currentuserform', 'Site');
        $act_id	= $app->input->get('act_id');

		// Attempt to block all users with outstanding invoices.
		$return = $model->delAction($act_id);

		// Redirect to the list screen.
		if ($return) {
			$this->setMessage(Text::_('COM_GAUSERS_ITEM_DELETED_SUCCESSFULLY'));
		}
		$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentuser&id='.$return, false));

    }

    public function sendwelcome()
	{
		// Check for request forgeries.
		$this->checkToken('get');

		// Initialise variables.
		$model = $this->getModel('Currentuserform', 'Site');
        $id	= Factory::getApplication()->input->get('id');

		// Process the request.
		$return = $model->sendWelcome($id);

		// Redirect to the list screen.
		$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentusers', false));

    }

    public function passedaway()
	{
		// Check for request forgeries.
		$this->checkToken('get');

		// Initialise variables.
		$model = $this->getModel('Currentuserform', 'Site');
        $id	= Factory::getApplication()->input->get('id');

		// Process the request.
		$return = $model->memberPassedAway($id);

		// Redirect to the list screen.
		$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentusers', false));

    }

    public function convertMship()
	{
		// Check for request forgeries.
		$this->checkToken('get');

		// Initialise variables.
		$model = $this->getModel('Currentuserform', 'Site');
        $id	= Factory::getApplication()->input->get('id');

		// Process the request.
		$return = $model->convertMship($id);

		// Redirect to the list screen.
		$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentusers', false));

    }

    public function update()
	{
		// Check for request forgeries.
		$this->checkToken('get');

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Currentuserform', 'Site');
        $id	= $app->input->get('id');

		// Process the request.
		$return = $model->changeBlockStatus($id);

		if ($return) {
			// Flush the data from the session.
			$app->setUserState('com_gausers.edit.currentuser.id', null);
			$app->setUserState('com_gausers.edit.currentuser.data', null);

			// Redirect to the list screen.
			$this->setMessage(Text::_('COM_GAUSERS_ITEM_SAVED_SUCCESSFULLY'));
			$item = Factory::getApplication()->getMenu()->getActive();
			if (!$item) {
				$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentusers', false));
			} else {
                $this->setRedirect(Route::_('index.php?Itemid='. $item->id, false));
			}
		}
		// Redirect to the list screen.
		$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentusers', false));

    }

    public function leftgroup()
	{
		// Check for request forgeries.
		$this->checkToken('get');

		// Initialise variables.
		$model = $this->getModel('Currentuserform', 'Site');
        $id	= Factory::getApplication()->input->get('id');

		// Process the request.
		$return = $model->leftgroup($id);

		// Redirect to the list screen.
		$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentusers', false));

    }

    public function genextract()
	{
		// Check for request forgeries.
		$this->checkToken('get');

		// Initialise variables.
		$model = $this->getModel('Currentuserform', 'Site');

		// Process the request.
		$return = $model->generateExtract();

		// Redirect to the list screen.
		$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentusers', false));

    }

    public function genInvoice()
	{
		// Check for request forgeries.
		$this->checkToken('get');

		// Initialise variables.
		$model = $this->getModel('Currentuserform', 'Site');
        $id	= Factory::getApplication()->input->get('id');

		// Process the request.
		$return = $model->generateInvoice($id, $id);

        // Clear the user data from the session.
		Factory::getApplication()->setUserState('com_gausers.user.data',null);
		// Redirect to the list screen.
		$this->setRedirect(Route::_('index.php?option=com_gausers&view=invoices', false));

    }

    public function genMembersDirectory()
	{
		// Check for request forgeries.
		$this->checkToken('get');

		// Initialise variables.
		$model = $this->getModel('Currentuserform', 'Site');

		// Process the request.
		$return = $model->genMembersDirectory();

		// Redirect to the list screen.
		$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentusers', false));

    }

	/**
	 * Method to generate a members attendance list
	 * @params    $finstatus filter on financial status
	 * @params    $mship filter on membership type
	 * @return    void
	 */
    public function genMembersList()
	{
		// Check for request forgeries.
		$this->checkToken('get');
		$finstatus	= Factory::getApplication()->input->get('finstatus');
		$mship	= Factory::getApplication()->input->get('mship');

		// Initialise variables.
		$model = $this->getModel('Currentuserform', 'Site');

		// Process the request.
		$return = $model->genMembersList($finstatus, $mship);

		// Redirect to the list screen.
		$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentusers', false));

    }

    public function genAddressList()
	{
		// Check for request forgeries.
		$this->checkToken('get');

		// Initialise variables.
		$model = $this->getModel('Currentuserform', 'Site');

		// Process the request.
		$return = $model->genAddressList();

		// Redirect to the list screen.
		$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentusers', false));

    }

    public function genMailChimpList()
	{
		// Check for request forgeries.
		$this->checkToken('get');

		// Initialise variables.
		$model = $this->getModel('Currentuserform', 'Site');

		// Process the request.
		$return = $model->genMailChimpList();

		// Redirect to the list screen.
		$this->setRedirect(Route::_('index.php?option=com_gausers&view=currentusers', false));

    }

}
