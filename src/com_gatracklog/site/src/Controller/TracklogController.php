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
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use \GlennArkell\Component\Gatracklog\Administrator\Helper\GatracklogHelper;

/**
 * Item class.
 * @since  1.6.0
 */
class TracklogController extends BaseController
{
	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 * @return void
	 * @since    1.6
     * @throws Exception
	 */
	public function edit()
	{
		$app = Factory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_gatracklog.edit.tracklog.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the record id to edit in the session.
		$app->setUserState('com_gatracklog.edit.tracklog.id', $editId);

		// Get the model.
		$model = $this->getModel('Tracklog', 'Site');

		// Check out the record
		if ($editId) {
			$model->checkout($editId);
		}

		// Check in the previous record.
		if ($previousId && $previousId !== $editId) {
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gatracklog&view=tracklogform&layout=edit', false));
	}

	/**
	 * Method to publish record
	 * @return    void
	 * @throws Exception
	 * @since    1.6
	 */
	public function publish()
	{
		// Initialise variables.
		$app = Factory::getApplication();
		// Set up Redirect URL.
        $menu = $app->getMenu()->getActive();
        $url = (empty($menu->link) ? 'index.php?option=com_gatracklog&view=tracklogs' : $menu->link.'&Itemid='.$menu->id);

		// Get the user data.
		$id    = $app->input->getInt('id');
		$state = $app->input->getInt('state');

		// Checking if the user can action the object
		$user = Factory::getApplication()->getIdentity();
		$item = GatracklogHelper::getTracklog($id);
		$canEdit = GatracklogHelper::canUserEdit($user, $item);

		if ($canEdit)
		{
			$model = $this->getModel('Tracklog', 'Site');

			// Attempt to save the data.
			$return = $model->publish($id, $state);

			// Check for errors.
			if ($return === false)
			{
				$app->enqueueMessage(Text::sprintf('COM_GAGATRACKLOG_SAVE_FAILED', $model->getError()), 'warning');
			}

			// Clear the record id from the session.
			$app->setUserState('com_gatracklog.edit.tracklog.id', null);

			// Flush the data from the session.
			$app->setUserState('com_gatracklog.edit.tracklog.data', null);

			// Redirect to the list screen.
			$app->enqueueMessage(Text::_('COM_GAGATRACKLOG_ITEM_SAVED_SUCCESSFULLY'), 'success');
		} else {
			throw new \Exception(500);
		}
		// Redirect to the list screen.
		$this->setRedirect(Route::_($url, false));
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
		// Set up Redirect URL.
        $item = $app->getMenu()->getActive();
        $url = (empty($item->link) ? 'index.php?option=com_gatracklog&view=tracklogs' : $item->link.'&Itemid='.$item->id);

		// Checking if the user can remove object
		$user = Factory::getApplication()->getIdentity();

		if ($user->authorise('core.delete', 'com_gatracklog'))
		{
			$model = $this->getModel('Tracklog', 'Site');

			// Get the user submitted data.
			$id = $app->input->getInt('id', 0);

			// Attempt to save the data.
			$return = $model->delete($id);

			// Check for errors.
			if ($return === false) {
				$app->enqueueMessage(Text::sprintf(Text::_('COM_GAGATRACKLOG_ITEM_DELETED_FAILED'), $model->getError()), 'warning');
			} else {
				// Check in the record.
				if ($return) {
					$model->checkin($return);
				}

                $app->setUserState('com_gatracklog.edit.tracklog.id', null);
                $app->setUserState('com_gatracklog.edit.tracklog.data', null);

                $app->enqueueMessage(Text::_('COM_GAGATRACKLOG_ITEM_DELETED_SUCCESSFULLY'), 'success');
                $app->redirect(Route::_('index.php?option=com_gatracklog&view=tracklogs', false));
			}

			// Redirect to the list screen.
			$this->setRedirect(Route::_($url, false));

		} else {
			throw new \Exception(500);
		}
	}

	/**
	 * Remove data
	 * @return void
	 * @throws Exception
	 */
	public function removeComment()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = GatracklogHelper::getSpecificUser();

		if ($user->authorise('core.delete', 'com_gatracklog')) {
			$model = $this->getModel('Tracklog', 'Site');

			// Get the user data.
			$id = $app->input->getInt('id', 0);
			$track_id = $app->input->getInt('track_id', 0);

			// Attempt to save the data.
			$return = $model->deleteComment($id);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf(Text::_('COM_GATRACKLOG_ITEM_SENT_FAILED'), $model->getError()), 'warning');
			} else {
				$this->setMessage(Text::_('COM_GATRACKLOG_ITEM_DELETED_SUCCESSFULLY'));
			}

		} else {
			$track_id = $app->getUserState('com_gatracklog.edit.tracklog.id');
			throw new \Exception(500);
		}
		$this->setRedirect(Route::_('index.php?option=com_gatracklog&view=tracklog&id=' . $track_id, false));
	}

	/**
	 * Method to send the data file
	 */
	public function sendtrklog()
	{
		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Tracklog', 'Site');

		// Get the user data.
		$data       = array();
		$data['id'] = $app->input->getInt('id');

		// Attempt to send the file.
		$return = $model->sendtrklog($data);

		// Check for errors.
		if ($return === false) {
			$this->setMessage(Text::_('COM_GATRACKLOG_ITEM_SENT_FAILED'));
			$this->setRedirect(Route::_('index.php?option=com_gatracklog&view=tracklogs', false));
		}

		// Clear the profile id from the session.
		$app->setUserState('com_gatracklog.edit.tracklog.id', null);

		// Redirect to the list screen.
		$this->setMessage(Text::_('COM_GATRACKLOG_ITEM_SENT_SUCCESSFULLY'));
		$menu = Factory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = (empty($item->link) ? 'index.php?option=com_gatracklog&view=tracklogs' : $item->link);
		$this->setRedirect(Route::_($url, false));

		// Flush the data from the session.
		$app->setUserState('com_gatracklog.edit.tracklog.data', null);
	}
}
