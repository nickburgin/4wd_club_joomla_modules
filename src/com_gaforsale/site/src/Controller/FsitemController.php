<?php

/**
 * @version    3.0.09
 * @package    Com_Gaforsale
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  Copyright (C) 2013. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace GlennArkell\Component\Gaforsale\Site\Controller;

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
use \GlennArkell\Component\Gaforsale\Administrator\Helper\GaforsaleHelper;

/**
 * Single controller class.
 * @since  1.6
 */
class FsitemController extends BaseController
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
		$previousId = (int) $app->getUserState('com_gaforsale.edit.fsitem.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gaforsale.edit.fsitem.id', $editId);

		// Get the model.
		$model = $this->getModel('Fsitem', 'Site');

		// Check out the item
		if ($editId) {
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId && $previousId !== $editId) {
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gaforsale&view=fsitemform&layout=edit', false));
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
		$user = GaforsaleHelper::getSpecificUser();

		if ($user->authorise('core.edit', 'com_gaforsale') || $user->authorise('core.edit.state', 'com_gaforsale')) {
			$model = $this->getModel('Fsitem', 'Site');

			// Get the user data.
			$id    = $app->input->getInt('id');
			$state = $app->input->getInt('state', 1);

			// Attempt to save the data.
			$return = $model->publish($id, $state);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf('Save failed: %s', $model->getError()), 'warning');
			}

			// Clear the profile id from the session.
			$app->setUserState('com_gaforsale.edit.fsitem.id', null);

			// Flush the data from the session.
			$app->setUserState('com_gaforsale.edit.fsitem.data', null);

			// Redirect to the list screen.
			$this->setMessage(Text::_('COM_GAFORSALE_ITEM_SAVED_SUCCESSFULLY'));
			$menu = Factory::getApplication()->getMenu();
			$item = $menu->getActive();

			if (!$item) {
				// If there isn't any menu item active, redirect to list view
				$this->setRedirect(Route::_('index.php?option=com_gaforsale&view=fsitems', false));
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

		// Checking if the user can remove object
		$user = GaforsaleHelper::getSpecificUser();

		if ($user->authorise('core.delete', 'com_gaforsale')) {
			$model = $this->getModel('Fsitem', 'Site');

			// Get the user data.
			$id = $app->input->getInt('id', 0);

			// Attempt to save the data.
			$return = $model->delete($id);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf('Delete failed', $model->getError()), 'warning');
			} else {
				// Check in the record.
				if ($return) {
					$model->checkin($return);
				}

				// Clear the id from the session.
				$app->setUserState('com_gaforsale.edit.fsitem.id', null);

				// Flush the data from the session.
				$app->setUserState('com_gaforsale.edit.fsitem.data', null);

				$this->setMessage(Text::_('COM_GAFORSALE_ITEM_DELETED_SUCCESSFULLY'));
			}

			// Redirect to the list screen.
			$menu = Factory::getApplication()->getMenu();
			$item = $menu->getActive();
			$this->setRedirect(Route::_($item->link, false));
		} else {
			throw new \Exception(500);
		}
	}

	/**
	 * Method to change the state to unpublished which marks the item as sold.
	 */
	public function marksold() {
        
		$app = Factory::getApplication();
        $soldId	= $app->input->get('id');
        
        $app->setUserState('com_gaforsale.marksold.data', $soldId);

		// Get the model.
		$model = $this->getModel('Fsitem', 'Site');

		// Mark the item as sold
		if ($soldId) {
            $model->marksold($soldId);
			$app->enqueueMessage(Text::_('COM_GAFORSALE_FSITEM_MARKEDSOLD'), 'notice');
		}
		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gaforsale&view=fsitems', false));

	}

	/**
	 * Remove data
	 * @return void
	 * @throws Exception
	 */
	public function removeFile()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Get the user data.
		$id = $app->input->getInt('id', 0);

		// Checking if the user can remove object
		$user    = GaforsaleHelper::getSpecificUser();
		$canDelete = $user->authorise('core.delete', 'com_gaforsale');
		$canEdit = GaforsaleHelper::canUserEdit($id);
		if (!$canDelete && $canEdit) {
			$canDelete = true;
		}

		if ($canDelete)
		{
			$model = $this->getModel('Fsitem', 'Site');

			// Attempt to update the data.
			$return = $model->deletefile($id);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf('Delete failed', $model->getError()), 'warning');
			} else {
                $app->setUserState('com_gaforsale.edit.fsitem.id', null);
                $app->setUserState('com_gaforsale.edit.fsitem.data', null);
                $app->redirect(Route::_('index.php?option=com_gaforsale&task=fsitemform.edit&id='.(int) $id, false));
			}

			// Redirect to the item form screen.
			$url  = 'index.php?option=com_gaforsale&task=fsitemform.edit&id='.(int) $id;
			$this->setRedirect(Route::_($url, false));
		} else {
			throw new \Exception(500);
		}
	}
}
