<?php

/**
 * @version    4.3.3
 * @package    Com_Gabroadcast
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gabroadcast\Site\Controller;

// No direct access
\defined('_JEXEC') or die;

use \Joomla\CMS\Application\SiteApplication;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Multilanguage;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Controller\BaseController;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Uri\Uri;
use \Joomla\Utilities\ArrayHelper;
use \GlennArkell\Component\Gabroadcast\Administrator\Helper\GabroadcastHelper;
use \Joomla\CMS\MVC\View\GenericDataException;

/**
 * Single controller class.
 * @since  1.6
 */
class UsernewController extends BaseController
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
		$previousId = (int) $app->getUserState('com_gabroadcast.edit.usernew.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gabroadcast.edit.usernew.id', $editId);

		// Get the model.
		$model = $this->getModel('Usernew', 'Site');

		// Check out the item
		if ($editId) {
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId && $previousId !== $editId) {
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gabroadcast&view=usernewform&layout=edit', false));
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

		if ($user->authorise('core.edit', 'com_gabroadcast') || $user->authorise('core.edit.state', 'com_gabroadcast')) {
			$model = $this->getModel('Usernew', 'Site');
			$Itemid = Factory::getApplication()->getUserState('com_gabroadcast.menu.Itemid');

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
			$app->setUserState('com_gabroadcast.edit.usernew.id', null);

			// Flush the data from the session.
			$app->setUserState('com_gabroadcast.edit.usernew.data', null);

			// Redirect to the list screen.
			$this->setMessage(Text::_('COM_GABROADCAST_ITEM_SAVED_SUCCESSFULLY'));
			$menu = Factory::getApplication()->getMenu();
			$item = $menu->getActive();

			if (!$item) {
				// If there isn't any menu item active, redirect to list view
				$this->setRedirect(Route::_('index.php?option=com_gabroadcast&view=usernews&Itemid='.(int)$Itemid, false));
			} else {
                $this->setRedirect(Route::_('index.php?Itemid='. $item->id, false));
			}
		} else {
			throw new GenericDataException(500);
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
		$user = Factory::getApplication()->getIdentity();
		$Itemid = $app->getUserState('com_gabroadcast.menu.Itemid');

		if ($user->authorise('core.delete', 'com_gabroadcast')) {
			$model = $this->getModel('Usernew', 'Site');

			// Get the user data.
			$id = $app->input->getInt('id', 0);

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

                $app->setUserState('com_gabroadcast.edit.inventory.id', null);
                $app->setUserState('com_gabroadcast.edit.inventory.data', null);

                $app->enqueueMessage(Text::_('COM_GABROADCAST_ITEM_DELETED_SUCCESSFULLY'), 'success');
                $app->redirect(Route::_('index.php?option=com_gabroadcast&view=usernews&Itemid='.(int)$Itemid, false));
			}

			// Redirect to the list screen.
			$url  = 'index.php?option=com_gabroadcast&view=usernews&Itemid='.(int)$Itemid;
			$this->setRedirect(Route::_($url, false));
		} else {
			throw new GenericDataException(500);
		}
	}

	/**
	 * Method to abort current operation
	 * @return void
	 * @throws Exception
	 */
	public function cancel()
	{
		$app = Factory::getApplication();

		// flush stored session data
		$app->setUserState('com_gabroadcast.edit.usernew.id', null);
        $Itemid = $app->getUserState('com_gabroadcast.menu.Itemid');
		$url  = 'index.php?option=com_gabroadcast&view=usernews&Itemid='.(int)$Itemid;
		$this->setRedirect(Route::_($url, false));
	}

	/**
	 * Send a Pending broadcast message
	 * @return void
	 */
	public function sendOut()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = Factory::getApplication()->getIdentity();

		if ($user->authorise('core.manage', 'com_gabroadcast')) {
			$model = $this->getModel('Usernew', 'Site');

			// Get the user data.
			$id = $app->input->getInt('id', 0);

			// Attempt to send the data.
			$return = $model->sendOut($id);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf('Send Failed', $model->getError()), 'warning');
			}
		} else {
			$app->enqueueMessage(Text::_('COM_GABROADCAST_ERROR_MESSAGE_NOT_AUTHORISED'), 'warning');
		}
		// Redirect to the list screen.
        $Itemid = $app->getUserState('com_gabroadcast.menu.Itemid');
		$url  = 'index.php?option=com_gabroadcast&view=usernews&Itemid='.(int)$Itemid;
		$this->setRedirect(Route::_($url, false));
	}

}
