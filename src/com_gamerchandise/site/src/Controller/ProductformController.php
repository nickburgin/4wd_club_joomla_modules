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

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

/**
 * Product controller class.
 * @since  1.6
 */
class ProductformController extends FormController
{
	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 * @return void
	 * @since    1.6
	 */
	public function edit($key = NULL, $urlVar = NULL)
	{
		$app = Factory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_gamerchandise.edit.product.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gamerchandise.edit.product.id', $editId);

		// Get the model.
		$model = $this->getModel('Productform', 'Site');

		// Check out the item
		if ($editId) {
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId) {
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gamerchandise&view=productform&layout=edit', false));
	}

	/**
	 * Method to save a user's submitted data.
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
		$model = $this->getModel('Productform', 'Site');

		// Get the user entered data.
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
			$app->setUserState('com_gamerchandise.edit.product.data', $jform);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gamerchandise.edit.product.id');
			$this->setRedirect(Route::_('index.php?option=com_gamerchandise&view=productform&layout=edit&id=' . $id, false));
		}

		// Attempt to save the data.
		$return = $model->save($data);

		// Check for errors.
		if ($return === false) {
			// Save the data in the session.
			$app->setUserState('com_gamerchandise.edit.product.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gamerchandise.edit.product.id');
			$this->setMessage(Text::sprintf('Save failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gamerchandise&view=productform&layout=edit&id=' . $id, false));
		}

		// Check in the record.
		if ($return) {
			$model->checkin($return);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_gamerchandise.edit.product.id', null);

		// Redirect to the list screen.
		$this->setMessage(Text::_('COM_GAMERCHANDISE_ITEM_SAVED_SUCCESSFULLY'));
		//$this->setRedirect(Route::_('index.php?option=com_gamerchandise&view=saleform&layout=edit&prod_id=' . $return, false));
		//$menu = Factory::getApplication()->getMenu();
		//$item = $menu->getActive();
		//$url  = (empty($item->link) ? 'index.php?option=com_gamerchandise&view=products' : $item->link);
		$url  = 'index.php?option=com_gamerchandise&view=products';
		$this->setRedirect(Route::_($url, false));

		// Flush the data from the session.
		$app->setUserState('com_gamerchandise.edit.product.data', null);
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
		$editId = (int) $app->getUserState('com_gamerchandise.edit.product.id');

		// Get the model.
		$model = $this->getModel('Productform', 'Site');

		// Check in the item
		if ($editId) {
			$model->checkin($editId);
		}

		$menu = Factory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = (empty($item->link) ? 'index.php?option=com_gamerchandise&view=products' : $item->link);
		$this->setRedirect(Route::_($url, false));
	}

	/**
	 * Method to remove data
	 * @return void
	 * @throws Exception
	 */
	public function remove()
	{
		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Productform', 'Site');

		// Get the user data.
		$data       = array();
		$data['id'] = $app->input->getInt('id');

		// Check for errors.
		if (empty($data['id'])) {
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

			// Save the data in the session.
			$app->setUserState('com_gamerchandise.edit.product.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gamerchandise.edit.product.id');
			$this->setRedirect(Route::_('index.php?option=com_gamerchandise&view=product&layout=edit&id=' . $id, false));
		}

		// Attempt to save the data.
		$return = $model->delete($data);

		// Check for errors.
		if ($return === false) {
			// Save the data in the session.
			$app->setUserState('com_gamerchandise.edit.product.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gamerchandise.edit.product.id');
			$this->setMessage(Text::sprintf('Delete failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gamerchandise&view=product&layout=edit&id=' . $id, false));
		}

		// Check in the profile.
		if ($return) {
			$model->checkin($return);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_gamerchandise.edit.product.id', null);

		// Redirect to the list screen.
		$this->setMessage(Text::_('COM_GAMERCHANDISE_ITEM_DELETED_SUCCESSFULLY'));
		$menu = Factory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = (empty($item->link) ? 'index.php?option=com_gamerchandise&view=products' : $item->link);
		$this->setRedirect(Route::_($url, false));

		// Flush the data from the session.
		$app->setUserState('com_gamerchandise.edit.product.data', null);
	}
}
