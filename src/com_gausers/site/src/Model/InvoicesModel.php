<?php

/**
 * @version     6.0.0
 * @package     com_gausers
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gausers\Site\Model;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\User\User;
use Joomla\Filesystem\Path;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\User\UserHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaemailHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GainvoiceHelper;

/**
 * Methods supporting a list of records.
 * @since  1.6
 */
class InvoicesModel extends ListModel
{
	/**
	 * Constructor.
	 * @param   array  $config  An optional associative array of configuration settings.
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'ordering', 'a.ordering',
				'state', 'a.state',
				'created_by', 'a.created_by',
				'created_date', 'a.created_date',
				'user_id', 'a.user_id',
				'paid_date', 'a.paid_date',
				'invoice_amt', 'a.invoice_amt',
				'pay_type', 'a.pay_type',
				'comment', 'a.comment',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 * @return void
	 * @throws Exception
	 * @since    1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
        $app  = Factory::getApplication();
		$list = $app->getUserState($this->context . '.list');

		$ordering  = isset($list['filter_order'])     ? $list['filter_order']     : null;
		$direction = isset($list['filter_order_Dir']) ? $list['filter_order_Dir'] : null;

		if(empty($ordering)) {
			$ordering = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', $app->get('filter_order'));
			if (!in_array($ordering, $this->filter_fields)) {
				$ordering = "a.id";
			}
			$this->setState('list.ordering', $ordering);
		}
		if(empty($direction)) {
			$direction = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', $app->get('filter_order_Dir'));
			if (!in_array(strtoupper($direction ?? ''), array('ASC', 'DESC', ''))) {
				$direction = "ASC";
			}
			$this->setState('list.direction', $direction);
		}

		$list['limit']     = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $app->get('list_limit'), 'uint');
		$list['start']     = $app->input->getInt('start', 0);
		$list['ordering']  = $ordering;
		$list['direction'] = $direction;
		
		$app->setUserState($this->context . '.list', $list);

        // List state information.
        parent::populateState($ordering, $direction);

        $context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $context);
        $status = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state');
        $this->setState('filter.state', $status);
        $pay_type = $this->getUserStateFromRequest($this->context.'.filter.pay_type', 'filter_pay_type');
        $this->setState('filter.pay_type', $pay_type);

	}

	/**
	 * Build an SQL query to load the list data.
	 * @return   JDatabaseQuery
	 * @since    1.6
	 */
	protected function getListQuery()
	{
        $params = ComponentHelper::getParams('com_gausers');
        $orderBy = $params->get('order_inv', 1); // 0 = name 1 = inv number
        $noEmail = $params->get('exclude_email_pref', 'noemail');
        $noemailLen = strlen($noEmail);

		// Create a new query object.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select($this->getState( 'list.select', 'DISTINCT a.*' ) );
        $query->from('#__gausers_invoices AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS uEditor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the created by field 'created_by'
		$query->select('c.name AS created_by_name');
		$query->join('LEFT', '#__users AS c ON c.id = a.created_by');

		// Join over the user field 'user_id'
		$query->select('u.name AS user_id_name, u.block, u.email, u.registerDate');
		$query->select('IF(SUBSTRING(u.email,1,'.(int) $noemailLen.') = '. $db->Quote($noEmail) . ',1,0) AS snailMail ' );
		$query->join('LEFT', '#__users AS u ON u.id = a.user_id');

		// Join over the created by field 'created_by'
		$query->select('m.mship_term, m.term_type ');
		$query->join('LEFT', '#__gausers_mshiptypes AS m ON m.id = a.mship_id');

		// Filter by published state
        $status = $this->getState('filter.state');
		if (is_numeric($status)) {
			$query->where('a.state = ' . (int) $status);
		} elseif ($status === '*') {
			// show all and don't filter on status
		} else {
    		$query->where(" (a.paid_date = ".$db->Quote('0000-00-00 00:00:00')." OR a.paid_date IS NULL )" );
			$query->where('a.state = 1');
        }

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( u.name LIKE ' . $search . ' OR a.invoice_amt LIKE ' . $search . ' )');
            }
        }

        // Filter by payment type
        $pay_type = $this->getState('filter.pay_type');
        if (!empty($pay_type) && $pay_type) {
            $query->where('a.pay_type = ' . (int) $pay_type);
        }

        // Add the list ordering clause.
        $orderDirn = 'ASC';
        if ($orderBy) {
			$query->order('a.id ' . $orderDirn);
		} else {
			$query->order(' if(substr(u.name, (LOCATE(" ",u.name)+1), 1)="&",substr(u.name, LOCATE(" ",u.name,(LOCATE(" ",u.name)+3))+1),substr(u.name, LOCATE(" ",u.name)+1)) ' . $orderDirn);
		}

        return $query;
	}

	/**
	 * Method to get an array of data items
	 * @return  mixed An array of data on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();


		return $items;
	}

	/**
	 * Overrides the default function to check Date fields format, identified by
	 * "_dateformat" suffix, and erases the field if it's not correct.
	 * @return void
	 */
	protected function loadFormData()
	{
		$app              = Factory::getApplication();
		$filters          = $app->getUserState($this->context . '.filter', array());
		$error_dateformat = false;

		foreach ($filters as $key => $value) {
			if (strpos($key, '_dateformat') && !empty($value) && $this->isValidDate($value) == null) {
				$filters[$key]    = '';
				$error_dateformat = true;
			}
		}

		if ($error_dateformat) {
			$app->enqueueMessage(Text::_("COM_GAUSERS_SEARCH_FILTER_DATE_FORMAT"), "warning");
			$app->setUserState($this->context . '.filter', $filters);
		}

		return parent::loadFormData();
	}

	/**
	 * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
	 * @param   string  $date  Date to be checked
	 * @return bool
	 */
	private function isValidDate($date)
	{
		$date = str_replace('/', '-', $date);
		return (date_create($date)) ? Factory::getDate($date)->format("Y-m-d") : null;
	}

	/**
	 * Method to get unpaid mship invoices and block/unblock the corresponding user
	 * @return bool
	 */
	public function bulkBlockUsers($block = 1)
	{
		// get all unpaid invoice records
		$invs = GainvoiceHelper::getUnpaidInvoices();
        $data = '';
		foreach ($invs AS $inv) {
            $data .= $inv->user_id.',';
        }
        $data = substr($data, 0, -1);
        $blocked = $this->blockUser($data, $block);
        
        return $blocked;
	}

	/**
	 * Method to block a user
	 * @param   string of id references
	 * @return  bool
	 */
	public function blockUser($data, $block)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery('UPDATE #__users SET block = '.(int)$block.' WHERE id IN ('.$data.')');
		Try {
            $db->execute();
            Factory::getApplication()->enqueueMessage(Text::_("COM_GAUSERS_BLOCK_SUCCESS"), "success");
            return true;
        } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage(Text::_("COM_GAUSERS_BLOCK_FAILED"), "warning");
            return false;
	    }

	}

	/**
	 * Method to get unpaid mship invoices and block/unblock the corresponding user
	 * @return bool
	 */
	public function reminderInv()
	{
		$params = ComponentHelper::getParams('com_gausers');
        $inv_loc  = $params->get('invoice_loc', 'images/members/invoices');
        $path = Path::clean( JPATH_SITE . '/' . $inv_loc );
        $sitename = Factory::getApplication()->get('fromname');
        $exclude_email_pref  = $params->get('exclude_email_pref');
        $preLen = strlen($exclude_email_pref);
        $cntr = 0;

        // get all unpaid invoice records
		$invs = GainvoiceHelper::getUnpaidInvoices();
		foreach ($invs AS $inv) {
            $nextinv  = str_pad($inv->id, 6, '0', STR_PAD_LEFT);
            $attachfile = $path.'/Invoice'.$nextinv.'.pdf';
            if (!is_file($attachfile) && $attachfile) {
                Factory::getApplication()->enqueueMessage(Text::sprintf('COM_GAUSERS_INVFILE_MISSING', $attachfile), 'warning');
                continue;
            }
            $user = GausersHelper::getSpecificUser($inv->user_id);
            
            if (substr($user->email,0,$preLen) != $exclude_email_pref) {
                $subject = Text::_('COM_GAUSERS_INVOICE_EMAIL_RESENDSUBJECT');
                $body = Text::sprintf('COM_GAUSERS_INVOICE_EMAIL_SALUTATION',$user->name);
                $body .= Text::_('COM_GAUSERS_INVOICE_EMAIL_BODY');
                $body .= Text::sprintf('COM_GAUSERS_INVOICE_EMAIL_SIGNOFF',$sitename);
                $sentOK = GaemailHelper::sendEmail(array($user->email), $body, $subject, $attachfile);
                
                if ($sentOK) { $cntr++; }
            }

        }
        Factory::getApplication()->enqueueMessage(Text::sprintf('COM_GAUSERS_REMINDERS_SENT', $cntr), 'info');
        
        return true;
	}

}
