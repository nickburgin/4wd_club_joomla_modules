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
 * Product controller class.
 * @since  1.6
 */
class ProductController extends BaseController
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
		$previousId = (int) $app->getUserState('com_gamerchandise.edit.product.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gamerchandise.edit.product.id', $editId);

		// Get the model.
		$model = $this->getModel('Product', 'Site');

		// Check out the item
		if ($editId) {
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId && $previousId !== $editId) {
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gamerchandise&view=productform&layout=edit', false));
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
		$user = GamerchandiseHelper::getSpecificUser();

		if ($user->authorise('core.edit', 'com_gamerchandise') || $user->authorise('core.edit.state', 'com_gamerchandise'))
		{
			$model = $this->getModel('Product', 'Site');

			// Get the user data.
			$id    = $app->input->getInt('id');
			$state = $app->input->getInt('state');

			// Attempt to save the data.
			$return = $model->publish($id, $state);

			// Check for errors.
			if ($return === false)
			{
				$this->setMessage(Text::sprintf('Save failed: %s', $model->getError()), 'warning');
			}

			// Clear the profile id from the session.
			$app->setUserState('com_gamerchandise.edit.product.id', null);

			// Flush the data from the session.
			$app->setUserState('com_gamerchandise.edit.product.data', null);

			// Redirect to the list screen.
			$this->setMessage(Text::_('COM_GAMERCHANDISE_ITEM_SAVED_SUCCESSFULLY'));
			$menu = Factory::getApplication()->getMenu();
			$item = $menu->getActive();

			if (!$item) {
				// If there isn't any menu item active, redirect to list view
				$this->setRedirect(Route::_('index.php?option=com_gamerchandise&view=products', false));
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

		if ($user->authorise('core.delete', 'com_gamerchandise')) {
			$model = $this->getModel('Product', 'Site');

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

				// Clear the profile id from the session.
				$app->setUserState('com_gamerchandise.edit.product.id', null);

				// Flush the data from the session.
				$app->setUserState('com_gamerchandise.edit.product.data', null);

				$this->setMessage(Text::_('COM_GAMERCHANDISE_ITEM_DELETED_SUCCESSFULLY'));
			}

			// Redirect to the list screen.
			$this->setRedirect(Route::_('index.php?option=com_gamerchandise&view=products', false));
		} else {
			throw new \Exception(500);
		}
	}

	/**
	 * Use data and generate PO
	 * @return void
	 * @throws Exception
	 */
	public function genPDF()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = GamerchandiseHelper::getSpecificUser();

		if ($user->authorise('core.manage', 'com_gamerchandise')) {
			$model = $this->getModel('Product', 'Site');

			// Get the user data.
			$id = $app->input->getInt('id', 0);

			// Attempt to save the data.
			$return = $model->createPDF($id);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf('PDF failed', $model->getError()), 'warning');
			} else {

				// Clear the profile id from the session.
				$app->setUserState('com_gamerchandise.edit.product.id', null);

				// Flush the data from the session.
				$app->setUserState('com_gamerchandise.edit.product.data', null);

				$this->setMessage(Text::_('COM_GAMERCHANDISE_ITEM_PDF_SUCCESSFULLY'));
			}

			// Redirect to the list screen.
			$this->setRedirect(Route::_('index.php?option=com_gamerchandise&view=product&id='.(int) $id, false));
		} else {
			throw new \Exception(500);
		}
	}

	/**
	 * Remove PDF
	 * @return void
	 * @throws Exception
	 */
	public function deletePDF()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = GamerchandiseHelper::getSpecificUser();

		if ($user->authorise('core.create', 'com_gamerchandise')) {
			$model = $this->getModel('Product', 'Site');

			// Get the user data.
			$id = $app->input->getInt('id', 0);

			// Attempt to save the data.
			$return = $model->deletePDF($id);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::_('COM_GAMERCHANDISE_ITEM_DELETEDPDF_FAILED'));
			} else {
				$this->setMessage(Text::_('COM_GAMERCHANDISE_ITEM_DELETEDPDF_SUCCESSFULLY'));
			}

			// Redirect to the item screen.
			$this->setRedirect(Route::_('index.php?option=com_gamerchandise&view=product&id='.(int) $id, false));
		} else {
			throw new \Exception(500);
		}
	}

	/**
	 * Use data and send PO
	 * @return void
	 * @throws Exception
	 */
	public function sendPO()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = GamerchandiseHelper::getSpecificUser();

		if ($user->authorise('core.manage', 'com_gamerchandise')) {
			$model = $this->getModel('Product', 'Site');

			// Get the user data.
			$id = $app->input->getInt('id', 0);

			// Attempt to save the data.
			$return = $model->sendPO($id);

			// Redirect to the list screen.
			$this->setRedirect(Route::_('index.php?option=com_gamerchandise&view=product&id='.(int) $id, false));
		} else {
			throw new \Exception(500);
		}
	}

}
