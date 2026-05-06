<?php

/**
 * @version    4.0.7
 * @package    Com_Gamerchandise
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gamerchandise\Site\Controller;

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
use \GlennArkell\Component\Gamerchandise\Administrator\Helper\GamerchandiseHelper;

/**
 * Sale controller class.
 *
 * @since  1.6
 */
class SaleController extends BaseController
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
		$previousId = (int) $app->getUserState('com_gamerchandise.edit.sale.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gamerchandise.edit.sale.id', $editId);

		// Get the model.
		$model = $this->getModel('Sale', 'Site');

		// Check out the item
		if ($editId) {
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId && $previousId !== $editId) {
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gamerchandise&view=saleform&layout=edit', false));
	}

	/**
	 * Method to save data.
	 * @return    void
	 * @throws Exception
	 * @since    1.6
	 */
	public function publish()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = GamerchandiseHelper::getSpecificUser();

		if ($user->authorise('core.edit', 'com_gamerchandise') || $user->authorise('core.edit.state', 'com_gamerchandise'))
		{
			$model = $this->getModel('Sale', 'Site');

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
			$app->setUserState('com_gamerchandise.edit.sale.id', null);

			// Flush the data from the session.
			$app->setUserState('com_gamerchandise.edit.sale.data', null);

			// Redirect to the list screen.
			$this->setMessage(Text::_('COM_GAMERCHANDISE_ITEM_SAVED_SUCCESSFULLY'));
			$menu = Factory::getApplication()->getMenu();
			$item = $menu->getActive();

			if (!$item) {
				// If there isn't any menu item active, redirect to list view
				$this->setRedirect(Route::_('index.php?option=com_gamerchandise&view=sales', false));
			} else {
				$this->setRedirect(Route::_($item->link, false));
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
		$user = GamerchandiseHelper::getSpecificUser();

		if ($user->authorise('core.delete', 'com_gamerchandise') || $user->authorise('core.edit.own', 'com_gamerchandise')) {
			$model = $this->getModel('Sale', 'Site');

			// Get the user entered data.
			$id = $app->input->getInt('id', 0);

			// Attempt to save the data.
			$return = $model->delete($id);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf('Delete failed', $model->getError()), 'warning');
			} else {
				// Clear the profile id from the session.
				$app->setUserState('com_gamerchandise.edit.sale.id', null);

				// Flush the data from the session.
				$app->setUserState('com_gamerchandise.edit.sale.data', null);

				$this->setMessage(Text::_('COM_GAMERCHANDISE_ITEM_DELETED_SUCCESSFULLY'));
			}

			// Redirect to the list screen.
			$this->setRedirect(Route::_('index.php?option=com_gamerchandise&view=sales', false));

		} else {
			throw new \Exception(500);
		}
	}

	/**
	 * Mark as Paid
	 * @return void
	 * @throws Exception
	 */
	public function markPaid()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = GamerchandiseHelper::getSpecificUser();

		if ($user->authorise('core.manage', 'com_gamerchandise')) {
			$model = $this->getModel('Sale', 'Site');
			$id = $app->input->get('id', 0);

			// Attempt to save the data.
			$return = $model->markPaid($id);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf('Paid failed', $model->getError()), 'warning');
			} else {
				$this->setMessage(Text::_('Paid Successfully'));
			}

			// Redirect to the list screen.
			$this->setRedirect(Route::_('index.php?option=com_gamerchandise&view=sales', false));

		} else {
			throw new \Exception(500);
		}
	}

	/**
	 * Mark as Paid
	 * @return void
	 * @throws Exception
	 */
	public function markOrdered()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = GamerchandiseHelper::getSpecificUser();

		if ($user->authorise('core.manage', 'com_gamerchandise')) {
			$model = $this->getModel('Sale', 'Site');
			$id = $app->input->get('id', 0);

			// Attempt to save the data.
			$return = $model->markOrdered($id);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf('Order failed', $model->getError()), 'warning');
			} else {
				$this->setMessage(Text::_('Ordered Successfully'));
			}

			// Redirect to the list screen.
			$this->setRedirect(Route::_('index.php?option=com_gamerchandise&view=sales', false));

		} else {
			throw new \Exception(500);
		}
	}

	/**
	 * Mark as Paid
	 * @return void
	 * @throws Exception
	 */
	public function markDelivered()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = GamerchandiseHelper::getSpecificUser();

		if ($user->authorise('core.manage', 'com_gamerchandise')) {
			$model = $this->getModel('Sale', 'Site');
			$id = $app->input->get('id', 0);

			// Attempt to save the data.
			$return = $model->markDelivered($id);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf('Delivered failed', $model->getError()), 'warning');
			} else {
				$this->setMessage(Text::_('Delivered Successfully'));
			}

			// Redirect to the list screen.
			$this->setRedirect(Route::_('index.php?option=com_gamerchandise&view=sales', false));

		} else {
			throw new \Exception(500);
		}
	}

	/**
	 * Commit data
	 * @return void
	 * @throws Exception
	 */
	public function commitPurchases()
	{
		// Initialise variables.
		$app = Factory::getApplication();
		// Get the user submitted sales record id.
		$id    = $app->input->getInt('id', 0);

		// Checking if the user can remove object
		$user = GamerchandiseHelper::getSpecificUser();

		if ($user->authorise('core.create', 'com_gamerchandise')) {

			$model = $this->getModel('Sale', 'Site');

			// Attempt to save the data.
			$return = $model->commitPurchases($id);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf('COM_GAMERCHANDISE_ITEM_COMMIT_FAILED', $model->getError()), 'warning');
			} else {
				$app->enqueueMessage(Text::_('COM_GAMERCHANDISE_ITEM_COMMITTED_SUCCESSFULLY'));
			}

			// Redirect to the list screen.
			$this->setRedirect(Route::_('index.php?option=com_gamerchandise&view=sales', false));

		} else {
			$app->enqueueMessage(Text::_('COM_GAMERCHANDISE_NOT_AUTORISED'), 'warning');
		}
	}
}
