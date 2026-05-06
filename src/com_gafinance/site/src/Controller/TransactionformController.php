<?php
/**
 * @version    5.2.3
 * @package    Com_Gafinance
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gafinance\Site\Controller;

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
 * Form class.
 * @since  1.6.0
 */
class TransactionformController extends FormController
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
		$previousId = (int) $app->getUserState('com_gafinance.edit.transaction.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gafinance.edit.transaction.id', $editId);

		// Get the model.
		$model = $this->getModel('Transactionform', 'Site');

		// Check out the item
		if ($editId) {
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId) {
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gafinance&view=transactionform&layout=edit', false));
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
		$model = $this->getModel('Transactionform', 'Site');

		// Get the user data.
		$data = Factory::getApplication()->input->get('jform', array(), 'array');
		$files = Factory::getApplication()->input->files->get('jform', '', 'array');
        
        foreach ($files as $file) {
			if ($file['size']) {
				$data['tran_file'] = $file;
			} else {
				if (empty($data['tran_file_txt'])) {
					$data['tran_file'] = '';
				} else {
					$data['tran_file'] = $data['tran_file_txt'];
				}	
			}
		}

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form) {
			throw new \Exception($model->getError(), 500);
		}

		// Validate the posted data.
		$data = $model->validate($form, $data);

		// check date format entered
        $goodDateFormat = $model->isValidDate($data['tran_date']);
		if (!$goodDateFormat) { 
            $data = false; 
            $app->enqueueMessage('COM_GAFINANCE_BAD_DATE_FORMAT', 'danger');
        }

		// Check for errors.
		if ($data === false) {
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
			$app->setUserState('com_gafinance.edit.transaction.data', $jform);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gafinance.edit.transaction.id');
			$this->setRedirect(Route::_('index.php?option=com_gafinance&view=transactionform&layout=edit&id=' . $id, false));

			$this->redirect();
		}

		// Attempt to save the data.
		$return = $model->save($data);

		// Check for errors.
		if ($return === false) {
			// Save the data in the session.
			$app->setUserState('com_gafinance.edit.transaction.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gafinance.edit.transaction.id');
			$this->setMessage(Text::sprintf('Save failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gafinance&view=transactionform&layout=edit&id=' . $id, false));
		}

		// Check in the record.
		if ($return) {
			$model->checkin($return);
			$app->enqueueMessage(Text::_('COM_GAFINANCE_ITEM_SAVED_SUCCESSFULLY'), 'success');
		}

		// Clear the profile id from the session.
		$app->setUserState('com_gafinance.edit.transaction.id', null);

		// Redirect to the list screen.
		$menu = Factory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = (empty($item->link) ? 'index.php?option=com_gafinance&view=transactions' : $item->link);
		$this->setRedirect(Route::_($url, false));

		// Flush the data from the session.
		$app->setUserState('com_gafinance.edit.transaction.data', null);
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
		$editId = (int) $app->getUserState('com_gafinance.edit.transaction.id');

		// Get the model.
		$model = $this->getModel('Transactionform', 'Site');

		// Check in the item
		if ($editId) {
			$model->checkin($editId);
		}

		$menu = Factory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = (empty($item->link) ? 'index.php?option=com_gafinance&view=transactions' : $item->link);
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
        $model = $this->getModel('Transaction', 'Site');
        $pk    = $app->input->getInt('id');

        // Attempt to save the data
        try
        {
            $return = $model->delete($pk);

            // Check in the profile
            $model->checkin($return);

            // Clear the profile id from the session.
            $app->setUserState('com_gafinance.edit.transaction.id', null);

            $menu = $app->getMenu();
            $item = $menu->getActive();
            $url = (empty($item->link) ? 'index.php?option=com_gafinance&view=transactions' : $item->link);

            // Redirect to the list screen
            $this->setMessage(Text::_('COM_GAFINANCE_ITEM_DELETED_SUCCESSFULLY'));
            $this->setRedirect(Route::_($url, false));

            // Flush the data from the session.
            $app->setUserState('com_gafinance.edit.transaction.data', null);
        }
        catch (\Exception $e)
        {
            $errorType = ($e->getCode() == '404') ? 'error' : 'warning';
            $this->setMessage($e->getMessage(), $errorType);
            $this->setRedirect('index.php?option=com_gafinance&view=transactions');
        }
    }

	/**
	 * Method to run report based on submitted parameters
	 */
	public function finreport()
	{
		// Initialise variables.
        $app   = Factory::getApplication();
        $model = $this->getModel('Transactionform', 'Site');

		// Get the data from the form POST
		$data = $app->input->get('jform', array(), 'array');

        $data	= $model->setDateRangeForDatabase($data);

    	// check report type
        if ($data['rpt_type'] == 1) {
            $rpt_ok	= $model->rptListCBS($data);   // Cash Book Summary
        } elseif ($data['rpt_type'] == 2) {
            $rpt_ok	= $model->rptListBS($data);    // Balance Sheet
        } elseif ($data['rpt_type'] == 3) {
            $rpt_ok	= $model->rptListPL($data);    // Profit and Loss
        } elseif ($data['rpt_type'] == 4) {
            $rpt_ok	= $model->rptListGST($data);   // GST
        } elseif ($data['rpt_type'] == 5) {
            $rpt_ok	= $model->rptListDS($data);    // Depreciation Schedule
        } elseif ($data['rpt_type'] == 6) {
            $rpt_ok	= $model->rptListBR($data);    // Bank Reconciliation
        } elseif ($data['rpt_type'] == 7) {
            $rpt_ok	= $model->rptListTL($data);    // Travel Log
        } else {
			$app->enqueueMessage(Text::_('COM_GAFINANCE_REPORT_FAILED'), 'danger');
			$rpt_ok	= FALSE;
		}

        if ($rpt_ok) {
            $app->enqueueMessage(Text::_('COM_GAFINANCE_REPORT_SUCCESSFUL'), 'success');
			$this->setRedirect(Route::_('index.php?option=com_gafinance&view=transactionform&layout=finreport&print=0', false));
        } else {
			echo "<h2>".Text::_('COM_GAFINANCE_REPORT_FAILED')."</h2>";
			$app->enqueueMessage(Text::_('COM_GAFINANCE_REPORT_FAILED'), 'danger');
		}

		return true;
	}
}
