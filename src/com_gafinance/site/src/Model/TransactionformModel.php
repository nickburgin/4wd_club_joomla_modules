<?php
/**
 * @version    5.1.0
 * @package    Com_Gafinance
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gafinance\Site\Model;
// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Date\Date;
use \Joomla\CMS\MVC\Model\FormModel;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Component\ComponentHelper;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GafinanceHelper;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GareportsHelper;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GaauditHelper;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GaactlogHelper;

/**
 * Form model.
 * @since  1.6
 */
class TransactionformModel extends FormModel
{
    private $item = null;

    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     * @return void
     * @since  1.6
     * @throws Exception
     */
    protected function populateState()
    {
        $app = Factory::getApplication('com_gafinance');

        // Load state from the request userState on edit or from the passed variable on default
        if (Factory::getApplication()->input->get('layout') == 'edit') {
                $id = Factory::getApplication()->getUserState('com_gafinance.edit.transaction.id');
        } else {
                $id = Factory::getApplication()->input->get('id');
                Factory::getApplication()->setUserState('com_gafinance.edit.transaction.id', $id);
        }

        $this->setState('transaction.id', $id);

        // Load the parameters.
        $params       = $app->getParams();
        $params_array = $params->toArray();

        if (isset($params_array['item_id'])) {
                $this->setState('transaction.id', $params_array['item_id']);
        }

        $this->setState('params', $params);
    }

    /**
     * Method to get an ojbect.
     * @param   integer $id The id of the object to get.
     * @return Object|boolean Object on success, false on failure.
     * @throws Exception
     */
    public function getItem($id = null)
    {
        if ($this->item === null) {
            $this->item = false;

            if (empty($id)) {
                    $id = $this->getState('transaction.id');
            }

            // Get a level row instance.
            $table = $this->getTable();

            if ($table !== false && $table->load($id) && !empty($table->id)) {
                $user = GafinanceHelper::getSpecificUser();
                $id   = $table->id;

                $canEdit = $user->authorise('core.edit', 'com_gafinance') || $user->authorise('core.create', 'com_gafinance');

                if (!$canEdit && $user->authorise('core.edit.own', 'com_gafinance')) {
                        $canEdit = $user->id == $table->created_by;
                }

                if (!$canEdit) {
                        throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
                }

                // Check published state.
                if ($published = $this->getState('filter.published')) {
                        if (isset($table->state) && $table->state != $published) {
                                return $this->item;
                        }
                }

                // Convert the Table to a clean Object.
                $properties = $table->getProperties(1);
                $this->item = ArrayHelper::toObject($properties, 'stdClass');
                
				if (isset($this->_item->accnt_id)) {
					$this->_item->accnt_id_name = GafinanceHelper::getRecordValue($this->_item->accnt_id, '#__gafinance_accounts', 'accnt_name');
				}

				if ($this->item->gst_amt == 0) {
					$this->item->gst_flag = 0; 
				} else { 
					$this->item->gst_flag = 1;
				}

				if (!empty($this->item->tran_file)) {
					$this->item->tran_file_txt = $this->item->tran_file;
				} else { 
					$this->item->tran_file_txt = 0;
				}


            }
        }

        return $this->item;
    }

    /**
     * Method to get the table
     * @param   string $type   Name of the Table class
     * @param   string $prefix Optional prefix for the table class name
     * @param   array  $config Optional configuration array for Table object
     * @return  Table|boolean Table if found, boolean false on failure
     */
    public function getTable($type = 'Transaction', $prefix = 'Administrator', $config = array())
    {
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Get an item by alias
     * @param   string $alias Alias string
     * @return int Element id
     */
    public function getItemIdByAlias($alias)
    {
        $table      = $this->getTable();
        $properties = $table->getProperties();

        if (!in_array('alias', $properties)) {
                return null;
        }

        $table->load(array('alias' => $alias));
        $id = $table->id;

        return $id;
        
    }

    /**
     * Method to check in an item.
     * @param   integer $id The id of the row to check out.
     * @return  boolean True on success, false on failure.
     * @since    1.6
     */
    public function checkin($id = null)
    {
        // Get the id.
        $id = (!empty($id)) ? $id : (int) $this->getState('transaction.id');
        
        $table = $this->getTable();
        $checkedOutField = $table->getColumnAlias('checked_out');
        $checkedOutTimeField = $table->getColumnAlias('checked_out_time');
    
        // If there is no checked_out or checked_out_time field, just return true.
        if (!$checkedOutField || !$checkedOutTimeField)
        {
            return true;
        }

        if ($id) {
            // Initialise the table
            $table->load($id);

            $table->checked_out = 0;
            $table->checked_out_time = NULL;
            // Attempt to check the row in.
            if (!$table->store()) {
                return false;
            }
        }

        return true;
        
    }

    /**
     * Method to check out an item for editing.
     * @param   integer $id The id of the row to check out.
     * @return  boolean True on success, false on failure.
     * @since    1.6
     */
    public function checkout($id = null)
    {
        // Get the user id.
        $id = (!empty($id)) ? $id : (int) $this->getState('transaction.id');
        
        if ($id) {
            // Initialise the table
            $table = $this->getTable();

            // Get the current user object.
            $user = GafinanceHelper::getSpecificUser();

            // Attempt to check the row out.
            if (method_exists($table, 'checkout')) {
                if (!$table->checkout($user->get('id'), $id)) {
                    return false;
                }
            }
        }

        return true;
        
    }

    /**
     * Method to get the profile form.
     * The base form is loaded from XML
     * @param   array   $data     An optional array of data for the form to interogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     * @return    JForm    A JForm object on success, false on failure
     * @since    1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_gafinance.transaction', 'transactionform', array(
                        'control'   => 'jform',
                        'load_data' => $loadData
                )
        );

        if (empty($form)) {
                return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     * @return    array  The default data is an empty array.
     * @since    1.6
     */
    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_gafinance.edit.transaction.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        if ($data) {
            return $data;
        }

        return array();
    }

    /**
     * Method to save the form data.
     * @param   array $data The form data
     * @return bool
     * @throws Exception
     * @since 1.6
     */
    public function save($data)
    {
        $id    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('transaction.id');
        $state = (!empty($data['state'])) ? 1 : 0;
        $user = GafinanceHelper::getSpecificUser();

        if (empty($data['tran_date']) || $data['tran_date'] == '') { $data['tran_date'] = Factory::getDate()->toSql(); }

        if ($id) {
            // Check the user can edit this item
            $authorised = $user->authorise('core.edit', 'com_gafinance') || $authorised = $user->authorise('core.edit.own', 'com_gafinance');
        } else {
            // Check the user can create new items in this section
            $authorised = $user->authorise('core.create', 'com_gafinance');
        }

        if ($authorised !== true) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

		$params = ComponentHelper::getParams('com_gafinance');
		$claim_gst = $params->get('claim_gst');
		$auto_neg = $params->get('auto_neg', 0);
		if ($claim_gst) {
			$gst_rate = $params->get('gst_rate');
			$gst_rate = 1 + $gst_rate;
	        $gst_amt = ($data['tran_amount'] / ($gst_rate * 10));
	        $gst_amt = (round($gst_amt / 0.01, 0)) * 0.01;
		} else {
			$gst_amt = 0.00;
		}
		if ($data['gst_flag'] == 0) { $data['gst_amt'] = '0.00'; } else { $data['gst_amt'] = $gst_amt; }
		if (!isset($data['accnt_id']) || $data['accnt_id'] == 0 || $data['accnt_id'] == '') { $data['accnt_id'] = 1; }

		if (!empty($data['tran_file'])) {
			$data['tran_file'] = $this->uploadAttachment($data['tran_file'], $params);
		} else {
			if (isset($data['tran_file_txt'])) {
				$data['tran_file'] = $data['tran_file_txt'];
			} else {
				$data['tran_file'] = '';
			}
		}

		if ($data['tran_amount'] >= 0 && $data['tran_type'] == 'E' && $auto_neg) {
			$data['tran_amount'] = ($data['tran_amount'] * -1);
		}

        $auditID = GaauditHelper::recordAuditTrail($user, $id, $data);

        $table = $this->getTable();

        if ($table->save($data) === true) {
			$data['id'] = $table->id;
			$auditOK = GaauditHelper::addRefAuditTrail($auditID, $table->id);
			/* ---------------------------------------------------------------- */
			// trigger transaction log if required
			$act_log = $params->get('act_log', 0);
			if ($act_log && $table->id) {
				// gather information and log in a new action log record
				$actID = GaactlogHelper::recordActionLog($user, $table->id, $data['id']);
			}
			/* ---------------------------------------------------------------- */

            return $table->id;
        } else {
            return false;
        }
        
    }

    /**
     * Method to delete data
     * @param   int $pk Item primary key
     * @return  int  The id of the deleted item
     * @throws Exception
     * @since 1.6
     */
    public function delete($pk)
    {
        $user = GafinanceHelper::getSpecificUser();

        if (empty($pk)) {
            $pk = (int) $this->getState('transaction.id');
        }

        if ($pk == 0 || $this->getItem($pk) == null) {
            throw new \Exception(Text::_('COM_GAFINANCE_ITEM_DOESNT_EXIST'), 404);
        }

        if ($user->authorise('core.delete', 'com_gafinance') !== true) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $table = $this->getTable();

        if ($table->delete($pk) !== true) {
            throw new \Exception(Text::_('JERROR_FAILED'), 501);
        }

        return $pk;

    }

    /**
     * Check if data can be saved
     * @return bool
     */
    public function getCanSave()
    {
        $table = $this->getTable();

        return $table !== false;
    }
    
    /**
     * Method to upload an attachment
     */
    public function uploadAttachment($tran_file = null, $params = null)
    {
        $safeFileOptions  = $params->get( 'safe_files' );
        $max_size  = $params->get( 'max_size', 300000 );
		$file_ext = substr($tran_file['name'],-3);


		if (!in_array($file_ext, $safeFileOptions)) {
			Factory::getApplication()->enqueueMessage(Text::_('File format ('.$file_ext.') not allowed'), 'danger');
			return false;
		}

        if (file_exists('file://'.$tran_file['tmp_name'])) {
			$tooBig = $tran_file['size'] > $max_size ? true : false;
			if ($tooBig) {
				Factory::getApplication()->enqueueMessage(Text::_('File Size Too Large'), 'danger');
				return null;
			}
			$fileName = File::makeSafe($tran_file['name']);
			$fileName = str_replace(' ', '_', $fileName);
			$src = $tran_file['tmp_name'];
			$fileName = 'images/finances/receipts/'.$fileName;

			$path = Path::clean( JPATH_SITE . '/' );
			$destfile = $path.'/'.$fileName;

			if ( File::upload($src, $destfile, false, false, $safeFileOptions) ) {
				Factory::getApplication()->enqueueMessage(Text::_('File ('.$fileName.') Uploaded Successfully'), 'success');
				return $fileName;
			} else {
				Factory::getApplication()->enqueueMessage(Text::_('File ('.$fileName.') Upload Failed'), 'danger');
				return null;
			}
  		} else {
			Factory::getApplication()->enqueueMessage(Text::_('File ('.$fileName.') Does Not Exist'), 'danger');
			return null;
		}
    }

	/**
	 * Get the passed dates for date conversions for all reports
	 */
	public static function setDateRangeForDatabase($data)
	{
        $lang = Factory::getLanguage();
        $lang->load('com_gafinance', JPATH_ADMINISTRATOR);
		// get the current date
		$jdate = new Date();      // system date NOT user timezone adjusted date
		$default_sdate = $jdate->format('Y-m-01 00:00:00');
		$default_edate = $jdate->format('Y-m-t 23:59:59');
        $data['rpt_accnt'] = $data['accnt_id'];
        
		if ($data['start_date'] == "") {
	        $data['req_dtfr_disp'] = HTMLHelper::date($default_sdate, Text::_('COM_GAFINANCE_DISPLAY_DATE'));
			$data['start_date'] = $default_sdate;
		} else {
	        $data['req_dtfr_disp'] = HTMLHelper::date($data['start_date'], Text::_('COM_GAFINANCE_DISPLAY_DATE'));   // user timezone adjusted date
			$data['start_date'] = $data['start_date']." 00:00:00";
		}

		if ($data['end_date'] == "") {
			$data['req_dtto_disp'] = HTMLHelper::date($default_edate, Text::_('COM_GAFINANCE_DISPLAY_DATE'));   // user timezone adjusted date
			$data['end_date'] = $default_edate;
		} else {
			$data['req_dtto_disp'] = HTMLHelper::date($data['end_date'], Text::_('COM_GAFINANCE_DISPLAY_DATE'));   // user timezone adjusted date
			$data['end_date'] = $data['end_date']." 23:59:59";
		}

		return $data;
    }
	/**
	 * Get the data for the Balance Sheet report
	 */
	public function rptListBS($data)
	{
        $rpt_ok = GareportsHelper::rptBalanceSheet($data);

        return $rpt_ok;
    }

	/**
	 * Get the data for the Profit & Loss report
	 */
	public function rptListPL($data)
	{
        $rpt_ok = GareportsHelper::rptProfitLoss($data);

        return $rpt_ok;
    }

	/**
	 * Get the data for the Bank Reconciliation report
	 */
	public function rptListBR($data)
	{
        $rpt_ok = GareportsHelper::rptBankRec($data);

        return $rpt_ok;
    }

	/**
	 * Get the data for the Depreciation Schedule report
	 */
	public function rptListDS($data)
	{
        $rpt_ok = GareportsHelper::rptDepSchedule($data);

        return $rpt_ok;
    }

	/**
	 * Get the data for the Travel Log report
	 */
	public function rptListTL($data)
	{
        $rpt_ok = GareportsHelper::rptTravelLog($data);

        return $rpt_ok;
    }

	/**
	 * Get the data for the Cash Book Summary report
	 */
	public function rptListCBS($data)
	{
        $rpt_ok = GareportsHelper::rptCashBookSummary($data);

        return $rpt_ok;
    }

	/**
	 * Get the data for the Cash Book Summary report
	 */
	public function rptListGST($data)
	{
        $rpt_ok = GareportsHelper::rptGST($data);

        return $rpt_ok;
    }

}
