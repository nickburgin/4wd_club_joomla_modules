<?php
/**
 * @version    5.3.0
 * @package    com_gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatripsys\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Form class.
 * @since  1.6.0
 */
class TripformController extends FormController
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
		$previousId = (int) $app->getUserState('com_gatripsys.edit.trip.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gatripsys.edit.trip.id', $editId);

		// Get the model.
		$model = $this->getModel('Tripform', 'Site');

		// Check out the record
		if ($editId) {
			$model->checkout($editId);
		}

		// Check in the previous record.
		if ($previousId) {
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=tripform&layout=edit', false));
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
		$model = $this->getModel('Tripform', 'Site');

		// Get the user data.
		$data = $app->input->get('jform', array(), 'array');
		$files = $app->input->files->get('jform', '', 'array');

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form) {
			throw new \Exception($model->getError(), 500);
		}

		// Validate the posted data.
		$data = $model->validate($form, $data);
        
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

			$input = $app->input;
			$jform = $input->get('jform', array(), 'ARRAY');

			// Save the data in the session.
			$app->setUserState('com_gatripsys.edit.trip.data', $jform);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gatripsys.edit.trip.id');
			$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=tripform&layout=edit&id=' . $id, false));

			$this->redirect();
		}

		// Attempt to load images.
		$params = ComponentHelper::getParams('com_gatripsys');
		$cntr = 0;
        foreach ($files as $file) { $cntr++;
			if ($cntr == 1) {  // test for trip_img
				if ($file['size']) {
					$data['trip_img'] = $model->uploadAttachment($file, $params, 'trip');
				} else {
					if (empty($data['trip_img_disp'])) {
						$data['trip_img'] = '';
					} else {
						$data['trip_img'] = $data['trip_img_disp'];
					}
				}
			} else {
				if ($file['size']) {
					$data['trip_plan'] = $model->uploadAttachment($file, $params, 'tripplan');
				}
			}
		}
		$data['trip_plan'] = $data['trip_plan'] == -1 ? '' : $data['trip_plan'];

		// Attempt to save the data.
		$return = $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_gatripsys.edit.trip.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gatripsys.edit.trip.id');
			$this->setMessage(Text::sprintf('Save failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=tripform&layout=edit&id=' . $id, false));
		//} else {
			//$this->setMessage(Text::sprintf('Save did not return false - ', $model->getError()), 'info');
		}

		// Check in the record.
		if ($return) {
			$model->checkin($return);
		}

		// Clear the record id from the session.
		$app->setUserState('com_gatripsys.edit.trip.id', null);

		// Redirect to the list screen.
		$app->enqueueMessage(Text::_('COM_GATRIPSYS_ITEM_SAVED_SUCCESSFULLY'), 'message');
		$menu = Factory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = (empty($item->link) ? 'index.php?option=com_gatripsys&view=trips' : $item->link);
		$this->setRedirect(Route::_($url, false));

		// Flush the data from the session.
		$app->setUserState('com_gatripsys.edit.trip.data', null);
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
		$editId = (int) $app->getUserState('com_gatripsys.edit.trip.id');

		// Get the model.
		$model = $this->getModel('Tripform', 'Site');

		// Check in the record
		if ($editId) {
			$model->checkin($editId);
		}

		$menu = Factory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = (empty($item->link) ? 'index.php?option=com_gatripsys&view=trips' : $item->link);
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
        $model = $this->getModel('Tripform', 'Site');
        $pk    = $app->input->getInt('id');

        // Attempt to save the data
        try
        {
            $return = $model->delete($pk);

            // Check in the record
            $model->checkin($return);

            // Clear the record id from the session.
            $app->setUserState('com_gatripsys.edit.trip.id', null);

            $menu = $app->getMenu();
            $item = $menu->getActive();
            $url = (empty($item->link) ? 'index.php?option=com_gatripsys&view=trips' : $item->link);

            // Redirect to the list screen
            $this->setMessage(Text::_('COM_GATRIPSYS_ITEM_DELETED_SUCCESSFULLY'));
            $this->setRedirect(Route::_($url, false));

            // Flush the data from the session.
            $app->setUserState('com_gatripsys.edit.trip.data', null);
        }
        catch (\Exception $e)
        {
            $errorType = ($e->getCode() == '404') ? 'error' : 'warning';
            $this->setMessage($e->getMessage(), $errorType);
            $this->setRedirect('index.php?option=com_gatripsys&view=trips');
        }
    }

	/**
	 * Method to remove data
	 * @return void
	 * @throws Exception
     * @since 1.6
	 */
	public function saveComment()
    {
		$this->checkToken();

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Tripform', 'Site');

		// Get the user data.
		$data = $app->input->get('jform', array(), 'array');
        $url = 'index.php?option=com_gatripsys&view=trip&id='.$data['id'];

        // Attempt to save the data
        try
        {
            $return = $model->addIncident($data);
            // Redirect to the list screen
            $app->enqueueMessage(Text::_('COM_GATRIPSYS_ITEM_SAVED_SUCCESSFULLY'), 'notice');

            // Flush the data from the session.
            $app->setUserState('com_gatripsys.edit.trip.data', null);
        }
        catch (\Exception $e)
        {
            $errorType = ($e->getCode() == '404') ? 'error' : 'warning';
            $app->enqueueMessage($e->getMessage(), $errorType);
        }
        $this->setRedirect(Route::_($url, false));
        $this->redirect();
    }
}
