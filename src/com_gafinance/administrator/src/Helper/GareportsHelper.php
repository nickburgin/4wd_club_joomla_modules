<?php
/**
 * @version    5.2.3
 * @package    Com_Gafinance
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2020 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gafinance\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date;
use Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GafinanceHelper;

/**
 * Gafinance helper.
 */
class GareportsHelper
{

	/** ---------------------------------------------- Cash Book Summary Report ------------------------------
	/**
	 * Get the data for the Cash Book Summary report
	 */
	public static function rptCashBookSummary($data)
	{
	    $app		= Factory::getApplication();
		$openbal = 0;
		$closebal = 0;
		$cntr = 0;
		$pldata = array();
		$rptFull = '';
		$params = ComponentHelper::getParams('com_gafinance');
        $combine_accnts = $params->get('combine_accnts', 0);
        $combine_rpt = $params->get('combine_rpt', 0);
		$accnts = $params->get('select_accnts');

        $rptTitle = '<div class="page-header"><h2>Cash Book Summary</h2></div>';
        $rptTitle .= '<p>For period '.$data['req_dtfr_disp'].' to '.$data['req_dtto_disp'].' (inclusive)</p>';

        if (!$data['rpt_accnt']) {
            if (is_array($accnts) && !empty($accnts)) {
                if ($combine_rpt) {
                    $accntNumbers = implode(',', $accnts);
                    foreach ($accnts as $accnt) {
                        $obal = GafinanceHelper::getOpeningBalance($data['start_date'], $data['end_date'], $accnt);
                        $openbal = $openbal + $obal;
                		$transVal = GafinanceHelper::getClosingBalance($data['start_date'], $data['end_date'], $accnt);
                        $cbal = $obal + $transVal;
                        $closebal = $closebal + $cbal;
                    }
                    $tdata = GafinanceHelper::getAllTransactions($data['start_date'], $data['end_date'], $accntNumbers);
                    $rptFull .= self::setupCashBookSummaryLayout($openbal, $closebal, $tdata);
                } else {
                    foreach ($accnts as $accnt) {
                        $obal = GafinanceHelper::getOpeningBalance($data['start_date'], $data['end_date'], $accnt);
                        $openbal = $openbal + $obal;
                		$transVal = GafinanceHelper::getClosingBalance($data['start_date'], $data['end_date'], $accnt);
                        $cbal = $obal + $transVal;
                        $closebal = $closebal + $cbal;
                		$tdata = GafinanceHelper::getAllTransactions($data['start_date'], $data['end_date'], $accnt);
                		if (!is_array($tdata) || empty($tdata)) { $tdata = GafinanceHelper::getAccount($accnt)->accnt_name; }
                        $rptFull .= self::setupCashBookSummaryLayout($obal, $cbal, $tdata);
                    }
                }
            } else {
                $rptTitle .= '<table class="finreport"><tr><td colspan="6">'.Text::_('COM_GAFINANCE_NO_ITEM_SELECTED').'</td></tr></table>';
                $app->setUserState('com_gafinance.rptprint.data', $rptTitle);
                return true;
            }
        } else {
            $openbal = GafinanceHelper::getOpeningBalance($data['start_date'], $data['end_date'], $data['rpt_accnt']);
            $transVal = GafinanceHelper::getClosingBalance($data['start_date'], $data['end_date'], $data['rpt_accnt']);
            $closebal = $openbal + $transVal;
            $pldata = GafinanceHelper::getAllTransactions($data['start_date'], $data['end_date'], $data['rpt_accnt']);
            $rptFull = self::setupCashBookSummaryLayout($openbal, $closebal, $pldata);
        }

        $rptFull = $rptTitle.$rptFull;

		$app->setUserState('com_gafinance.rptprint.data', $rptFull);

        return true;
    }

	/** ---------------------------------------------- Balance Sheet Report ------------------------------
	/**
	 * Get the data for the Balance Sheet report
	 */
	public static function rptBalanceSheet($data)
	{
	    $app		= Factory::getApplication();
        $sitename   = $app->get('sitename');
        $today  = date_format(Factory::getDate(), 'Y-m-d');
        $reportDate = $data['end_date'] > $today ? HTMLHelper::date($today, Text::_('COM_GAFINANCE_DISPLAY_DATE'), 'UTC') : $data['req_dtto_disp'];
        $params = ComponentHelper::getParams('com_gafinance');
        $inc_invoices = $params->get('inc_invoices', 2);
        $ownerCat = $params->get('ownerCat');
        $exch   = $params->get( 'exchange_rates' );
        $paypalau   = $params->get( 'paypalau' );
        $paypalus   = $params->get( 'paypalus' );
        $acctpay    = $params->get( 'acctpay', 0.00 );
        $combine_accnts = $params->get('combine_accnts', 0);
        $combine_rpt = $params->get('combine_rpt', 0);
		$accnts = $params->get('select_accnts');
		$accounts = implode(',', $accnts);
		$totar = 0;
		$totCash = 0;

        $paypal = ($paypalau + ($paypalus * $exch));
        
        // setup report header information
        $rptdata = '<div class="page-header"><h2>Balance Sheet</h2></div><p> &nbsp; &nbsp; &nbsp; &nbsp;';
        $rptdata .= ' for '.$sitename.' as at '.$reportDate.'</p>';
        $rptdata .= '<table class="finreport">';
        $rptdata .= '<tr><td class="trantype"><strong>Assets</strong></td><td class="trancat">&nbsp;</td>';
        $rptdata .= '<td class="trandesc">&nbsp;</td><td class="tranamt">&nbsp;</td><td class="tranamt">&nbsp;</td></tr>';

        // setup Cash in the bank figures fro each account if combining
        if (!$data['rpt_accnt'] && $combine_accnts) {
            $rptdata = self::setupBSTable($rptdata, '<strong>Cash at Bank</strong>', '', '');
            foreach ( $accnts as $accnt) {
                // Get the closing balance figure calculated from all transactions since the last reconciliation record.
                $tranVal = GafinanceHelper::getClosingBalance($data['start_date'], $data['end_date'], $accnt);
                $openbal = GafinanceHelper::getOpeningBalance($data['start_date'], $data['end_date'], $accnt);
                $closebal = $openbal + $tranVal;
                $accnt_name = GafinanceHelper::getAccount($accnt)->accnt_name;
                $rptdata = self::setupBSTable($rptdata, '', $accnt_name, $closebal);
                $totCash = $totCash + $closebal;
            }
        } else {
            // Get the closing balance figure calculated from all transactions since the last reconciliation record.
            $tranVal = GafinanceHelper::getClosingBalance($data['start_date'], $data['end_date'], $data['rpt_accnt']);
            $openbal = GafinanceHelper::getOpeningBalance($data['start_date'], $data['end_date'], $data['rpt_accnt']);
            $closebal = $openbal + $tranVal;
            $accnt_name = GafinanceHelper::getAccount($data['rpt_accnt'])->accnt_name;
            $rptdata = self::setupBSTable($rptdata, '<strong>Cash at Bank</strong>', $accnt_name, $closebal);
            $totCash = $totCash + $closebal;
        }


        // get all the assets for reporting
        $assets = GafinanceHelper::getAllAssets();
        $currentassetvalue = 0;
        foreach ($assets AS $asset) {
			$currentassetvalue = $currentassetvalue + $asset->asset_value;
		}


        $rptdata = self::setupBSTable($rptdata, '<strong>'.Text::_('COM_GAFINANCE_EQUIPMENT_LABEL').'</strong>', '', '');
        $rptdata = self::setupBSTable($rptdata, '', 'All registered assets', $currentassetvalue);
        //$rptdata = self::setupBSTable($rptdata, 'Cash at Bank', $closebal);
        if ($paypal > 0) {
            $rptdata = self::setupBSTable($rptdata, Text::_('COM_GAFINANCE_PAYPALAU_LABEL'), '', '');
            $rptdata = self::setupBSTable($rptdata, '', 'Total in PayPal', $paypal);
        }
        $rptdata = self::setupBSTable($rptdata, '<strong>Accounts Receivable</strong>', '', '');

        if ($inc_invoices == 1) {   
            // this is for getting unpaid invoices and work to be invoiced from the timesheets system
            // Create a new query object to get the outstanding invoices
            $db = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query->clear();
            $query->select( ' b.name, sum(a.invoice_amt) as inv_amt ' );
            $query->from('#__gatimesheet_invoices AS a, #__users AS b ');
            $query->where(' a.state = 1 ');
            $query->where(' a.user_id = b.id ');
            $query->where(' (a.paid_date IS NULL OR a.paid_date = "0000-00-00") ' );
            $query->group(' b.name ');
            $query->order(' b.name ');
    
            $db->setQuery((string)$query);
		    try {
		        $acctrecs = $db->loadObjectList();
		    } catch (RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage($e->getMessage());
		        return false;
		    }
            foreach ( $acctrecs as $acctrec ) {
                $rptdata = self::setupBSTable($rptdata, '', $acctrec->name, $acctrec->inv_amt);
                $totar = $totar + $acctrec->inv_amt;
            }

            // Create a new query object to get the work performed but not invoiced
            $db = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query->clear();
            $query->select( ' b.name, sum(((HOUR(TIMEDIFF(a.end_time,a.start_time))*c.work_rate)+(MINUTE(TIMEDIFF(a.end_time,a.start_time))*(c.work_rate / 60)))) AS charge_amt ' );
            $query->from('#__gatimesheet_times AS a, #__users AS b, #__gatimesheet_rates AS c ');
            $query->where(' a.state = 1 ');
            $query->where(' a.cid = b.id ');
            $query->where(' a.work_rate = c.id ');
            $query->where(' a.invoiced = 2 ' );
            $query->group(' b.name ');
            $query->order(' b.name ');
    
            $db->setQuery((string)$query);
		    try {
		        $acctrecs = $db->loadObjectList();
		    } catch (RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage($e->getMessage());
		        return false;
		    }

            $rptdata .= '<tr><td class="trantype">&nbsp;</td><td class="trancat"><strong>Accounts Receivable to be invoiced</strong></td><td class="trandesc">&nbsp;</td><td class="tranamt">&nbsp;</td><td class="tranamt">&nbsp;</td></tr>';

            foreach ( $acctrecs as $acctrec ) {
                if ($acctrec->charge_amt != 0 ) {
                    $rptdata = self::setupBSTable($rptdata, '', $acctrec->name, $acctrec->charge_amt);
                }
                $totar = $totar + $acctrec->charge_amt;
            }
            $rptdata = self::setupBSTable($rptdata, '', '', $totar);

        } else {
            $rptdata = self::setupBSTable($rptdata, '', 'Yet to be invoiced', 0);
        }

        $totass = ($currentassetvalue + $paypal + $totar + $totCash);

        $rptdata .= '<tr><td class="trantype" colspan="5">&nbsp;</td></tr>';
        $rptdata = self::setupBSTable($rptdata, '', '<strong>Total Assets</strong>', $totass);
        $rptdata .= '<tr><td class="trantype" colspan="5">&nbsp;</td></tr>';

        // now calculate the liabilities based on unpaid expenses
        if ($combine_rpt) {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query->clear();
            $query->select( ' a.tran_desc, sum(a.tran_amount) AS tran_amt ' );
            $query->from('#__gafinance_transactions AS a ');
            $query->where(' a.state = 1 ');
            $query->where(' a.accnt_id IN ('.$accounts.')');
            //$query->where(' a.created_date >= (select max(tran_date) from #__gafinance_transactions AS b where b.tran_type = "Z" and b.state = 1 and accnt_id = '.(int) $data['rpt_accnt'].' ) ' );
            $query->where(' ( a.tran_date IS NULL OR a.tran_date = "0000-00-00" )' );
            $query->where(' a.tran_type = "E"' );
            $query->group(' a.tran_desc' );
    
            $db->setQuery((string)$query);
    	    try {
    	        $accounts_pay = $db->loadObjectList();
    	    } catch (RuntimeException $e) {
    	        Factory::getApplication()->enqueueMessage($e->getMessage());
    	        return false;
    	    }
	    }

        $rptdata .= '<tr><td class="trantype"><strong>Liabilities</strong></td><td class="trancat">&nbsp;</td><td class="trandesc">&nbsp;</td><td class="tranamt">&nbsp;</td></tr>';
        $rptdata = self::setupBSTable($rptdata, '<strong>Accounts Payable</strong>', '', '');
		$rptdata = self::setupBSTable($rptdata, '', Text::_('COM_GAFINANCE_PREPAID_LABEL'), $acctpay);

        $totlib = $acctpay;

        foreach ($accounts_pay as $ap) {
            $rptdata = self::setupBSTable($rptdata, '', $ap->tran_desc, $ap->tran_amt);
            $totlib = $totlib + $ap->tran_amt ;
        }

        $gtass = ($totass - $totlib);

        $rptdata .= '<tr><td colspan="5">&nbsp;</td></tr>';
        $rptdata = self::setupBSTable($rptdata, '', '<strong>Total Liabilities</strong>', $totlib);
        $rptdata .= '<tr><td colspan="5">&nbsp;</td></tr>';
        $rptdata = self::setupBSTable($rptdata, '', '<strong>Net Assets</strong>', $gtass);
        $rptdata .= '<tr><td colspan="5">&nbsp;</td></tr><tr><td colspan="5">&nbsp;</td></tr></table>';
        
        $app->setUserState('com_gafinance.rptprint.data', $rptdata);

        return true;
    }


	/** ---------------------------------------------- Profit and Loss Report ------------------------------
	 * Get the data for the Profit & Loss report
	 */
	public static function rptProfitLoss($data)
	{
	    $app		= Factory::getApplication();
        $sitename   = $app->get('sitename');
        $params = ComponentHelper::getParams('com_gafinance');
        $ownerCat = $params->get('ownerCat', 0);
        $combine_accnts = $params->get('combine_accnts', 0);
        $combine_rpt = $params->get('combine_rpt', 0);
		$accnts = $params->get('select_accnts');
		$accounts = implode(',', $accnts);
		$allAccnts = !$data['rpt_accnt'] ? $accounts : $data['rpt_accnt'];
        $today  = date_format(Factory::getDate(), 'Y-m-d');
        $reportDate = $data['end_date'] > $today ? HTMLHelper::date($today, Text::_('COM_GAFINANCE_DISPLAY_DATE'), 'UTC') : $data['req_dtto_disp'];

        $pldata = GafinanceHelper::getAllTransactions($data['start_date'], $data['end_date'], $allAccnts);

        $extraLine = '<tr><td colspan="5">&nbsp;</td></tr>';

        $revenue = '';
        $expenses = '';
        $prevAccnt = '';
        $cntr = 0;

        $rptdata = '<div class="page-header"><h2>Profit &amp; Loss Statement</h2></div>';
        $rptdata .= '<p>For '.$sitename.' within period '.$data['req_dtfr_disp'].' to '.$reportDate.'</p>';
        $rptdata .= '<table class="finreport">';

        foreach ($pldata as $profloss) {
            $cntr++;
            if ($prevAccnt == '') {
                // this is the first time through so set the account name
                $prevAccnt = $profloss->accnt_id_name;
                $accnthead = '<tr><tdcolspan="5"><h4>Account: '.$profloss->accnt_id_name.'</h4></td></tr>';
                $revenue .= $extraLine.'<tr><td class="trantype"><strong>Revenue</strong></td><td colspan="4">&nbsp;</td></tr>';
                $expenses .= $extraLine.'<tr><td class="trantype"><strong>Expenses</strong></td><td colspan="4">&nbsp;</td></tr>';
            }

            if ($profloss->accnt_id_name == $prevAccnt) {
                if ($profloss->tran_type == "I") {
                    $totrev = $totrev + $profloss->tranAmt;
                    $revenue .= '<tr><td class="trantype">&nbsp;</td><td class="trancat">'.$profloss->title.'</td><td class="trandesc">'.$profloss->tran_desc.'</td>';
                    $revenue .= '<td class="tranamt">'.number_format($profloss->tranAmt,2).'</td><td class="tranamt">&nbsp;</td></tr>';
                } else {
                    $expenses .= '<tr><td class="trantype">&nbsp;</td><td class="trancat">'.$profloss->title.'</td><td class="trandesc">'.$profloss->tran_desc.'</td>';
                    $expenses .= '<td class="tranamt">'.number_format($profloss->tranAmt,2).'</td><td class="tranamt">&nbsp;</td></tr>';
                    $totexp = $totexp + $profloss->tranAmt;
                }
            } else {
                $rptdata .= $accnthead;
                // revenue heasding and line items
                $rptdata .= $revenue;
                $rptdata .= $extraLine.'<tr><td class="trantype">&nbsp;</td><td colspan="2"><strong>Total Revenue</strong></td>';
                $rptdata .= '<td class="tranamt"><strong>'.number_format($totrev,2).'</strong></td><td class="tranamt">&nbsp;</td></tr>';
                $rptdata .= '<tr><td colspan="2">&nbsp;</td><td class="trandesc"><strong>Gross Profit</strong></td>';
                $rptdata .= '<td class="tranamt">&nbsp;</td><td class="tranamt trantot"><strong>'.number_format($totrev,2).'</strong></td></tr>';
                $rptdata .= $extraLine.$extraLine;
                // expenses heasding and line items
                $rptdata .= $expenses;
                $rptdata .= $extraLine.'<tr><td class="trantype">&nbsp;</td><td colspan="2"><strong>Total Expenses</strong></td>';
                $rptdata .= '<td class="tranamt"><strong>'.number_format($totexp,2).'</strong></td><td class="tranamt">&nbsp;</td></tr>';
                $rptdata .= $extraLine.$extraLine;
                // total net
                $rptdata .= '<tr><td colspan="2">&nbsp;</td><td class="trandesc"><strong>Net Profit</strong></td>';
                $rptdata .= '<td class="tranamt">&nbsp;</td><td class="tranamt trantot"><strong>'.number_format(($totrev + $totexp),2).'</strong></td></tr>';
                $rptdata .= $extraLine.$extraLine;
                // now reset revenue and expenses for the next account
                $prevAccnt = $profloss->accnt_id_name;
                $accnthead = '<tr><td class="trantype" colspan="5"><h4>Account: '.$profloss->accnt_id_name.'</h4></td></tr>';
                $revenue = $extraLine.'<tr><td class="trantype"><strong>Revenue</strong></td><td colspan="4">&nbsp;</td></tr>';
                $expenses = $extraLine.'<tr><td class="trantype"><strong>Expenses</strong></td><td colspan="4">&nbsp;</td></tr>';
                $totrev = 0;
                $totexp = 0;
                if ($profloss->tran_type == "I") {
                    $totrev = $totrev + $profloss->tranAmt;
                    $revenue .= '<tr><td class="trantype">&nbsp;</td><td class="trancat">'.$profloss->title.'</td><td class="trandesc">'.$profloss->tran_desc.'</td>';
                    $revenue .= '<td class="tranamt">'.number_format($profloss->tranAmt,2).'</td><td class="tranamt">&nbsp;</td></tr>';
                } else {
                    $expenses .= '<tr><td class="trantype">&nbsp;</td><td class="trancat">'.$profloss->title.'</td><td class="trandesc">'.$profloss->tran_desc.'</td>';
                    $expenses .= '<td class="tranamt">'.number_format($profloss->tranAmt,2).'</td><td class="tranamt">&nbsp;</td></tr>';
                    $totexp = $totexp + $profloss->tranAmt;
                }
            }
        }

        // revenue heasding and line items
        $rptdata .= $accnthead;
        $rptdata .= $revenue;
        $rptdata .= $extraLine.'<tr><td class="trantype">&nbsp;</td><td colspan="2"><strong>Total Revenue</strong></td>';
        $rptdata .= '<td class="tranamt"><strong>'.number_format($totrev,2).'</strong></td><td class="tranamt">&nbsp;</td></tr>';
        $rptdata .= '<tr><td colspan="2">&nbsp;</td><td class="trandesc"><strong>Gross Profit</strong></td>';
        $rptdata .= '<td class="tranamt">&nbsp;</td><td class="tranamt trantot"><strong>'.number_format($totrev,2).'</strong></td></tr>';
        $rptdata .= $extraLine.$extraLine;

        // expenses heasding and line items
        $rptdata .= $expenses;
        $rptdata .= $extraLine.'<tr><td class="trantype">&nbsp;</td><td colspan="2"><strong>Total Expenses</strong></td>';
        $rptdata .= '<td class="tranamt"><strong>'.number_format($totexp,2).'</strong></td><td class="tranamt">&nbsp;</td></tr>';
        $rptdata .= $extraLine.$extraLine;

        // total net
        $rptdata .= '<tr><td colspan="2">&nbsp;</td><td class="trandesc"><strong>Net Profit</strong></td>';
        $rptdata .= '<td class="tranamt">&nbsp;</td><td class="tranamt trantot"><strong>'.number_format(($totrev + $totexp),2).'</strong></td></tr>';
        $rptdata .= $extraLine.$extraLine.'</table>';

        $app->setUserState('com_gafinance.rptprint.data', $rptdata);

        return true;
    }

	/** ---------------------------------------------- Bank Reconciliation Report ------------------------------
	/**
	 * Get the data for the Bank Reconciliation report
	 */
	public static function rptBankRec($data)
	{
	    $app		= Factory::getApplication();
        $sitename   = $app->get('sitename');
        $params = ComponentHelper::getParams('com_gafinance');
        $combine_accnts = $params->get('combine_accnts', 0);
        $combine_rpt = $params->get('combine_rpt', 0);
		$accnts = $params->get('select_accnts');
		$accounts = implode(',', $accnts);
		$allAccnts = $combine_rpt ? $accounts : $data['rpt_accnt'];

        // Get all the transactions between the requested dates for the requested account.
        $pldata = GafinanceHelper::getAllTransactions($data['start_date'], $data['end_date'], $allAccnts);
        $openbal = GafinanceHelper::getLastReconciliation($data['end_date'], $data['rpt_accnt']);

        $balance = $openbal->tran_amount;
        $rptdata = '<div class="page-header"><h2>Bank Reconciliation Statement</h2></div>';
        $rptdata .= '<p>For '.$sitename.' within period '.$data['req_dtfr_disp'].' to '.$data['req_dtto_disp'].'</p>';
        $rptdata .= '<table class="finreport">';
        $rptdata .= '<tr><td class="trantype" colspan="4"><strong>Opening Balance at start of reporting period</strong</td>';
        $rptdata .= '<td class="tranamt">'.number_format($openbal,2).'</td></tr>';
        $rptdata .= '<tr><td class="trantype" colspan="5">&nbsp;</td></tr>';
        $rptdata .= '<tr><td class="trantype"><strong>Date</strong></td><td class="trandet"><strong>Transaction</strong></td>';
        $rptdata .= '<td class="tranamt"><strong>Expenses</strong></td><td class="tranamt"><strong>Income</strong></td><td class="tranamt"><strong>Balance</strong></td></tr>';
        $rptdata .= '<tr><td class="trantype" colspan="5">&nbsp;</td></tr>';

        foreach ($pldata as $profloss) {
            $balance = $balance + $profloss->tran_amount;
            if ($profloss->tran_type == "I") {
                $rptdata .= '<tr><td class="trantype" style="font-size:0.8em;">'.$profloss->tran_date.'</td><td class="trandet" style="font-size:0.8em;">'.$profloss->tran_desc.' ('.$profloss->name.')</td>';
                $rptdata .= '<td class="tranamt" style="font-size:0.8em;">&nbsp;</td><td class="tranamt" style="font-size:0.8em;">'.number_format($profloss->tran_amount,2).'</td>';
                $rptdata .= '<td class="tranamt" style="font-size:0.8em;">'.number_format($balance,2).'</td></tr>';
            } else {
                $rptdata .= '<tr><td class="trantype" style="font-size:0.8em;">'.$profloss->tran_date.'</td><td class="trandet" style="font-size:0.8em;">'.$profloss->tran_desc.' ('.$profloss->name.')</td>';
                $rptdata .= '<td class="tranamt" style="font-size:0.8em;">'.number_format($profloss->tran_amount,2).'</td><td class="tranamt" style="font-size:0.8em;">&nbsp;</td>';
                $rptdata .= '<td class="tranamt" style="font-size:0.8em;">'.number_format($balance,2).'</td></tr>';

            }
        }

        $rptdata .= '<tr><td class="trantype" colspan="5">&nbsp;</td></tr>';
        $rptdata .= '<tr><td class="trantype" colspan="4"><strong>Closing Balance at end of reporting period</strong</td>';
        $rptdata .= '<td class="tranamt">'.number_format($closebal,2).'</td></tr></table>';

        $app->setUserState('com_gafinance.rptprint.data', $rptdata);

        return true;
    }

	/** ---------------------------------------------- Depreciation Report ------------------------------
	/**
	 * Get the data for the Depreciation Schedule report
	 */
	public static function rptDepSchedule($data)
	{
        $dep_ok	= self::getDepreciation($data);
        if ($dep_ok) {
            return true;
        } else {
            return false;
        }
    }

	/**
	 * Get the data for the Depreciation Schedule report
	 */
	public static function getDepreciation($data)
	{
	    $app		= Factory::getApplication();
        $sitename   = $app->get('sitename');

        // get the name of the user requesting this update
        $user	= GafinanceHelper::getSpecificUser();
        $user_id	= $user->id;
        $currentassetvalue = 0;
        $rptdata = '';

        // Create a new query object to get assets information based on depreciation setting
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->clear();
        $query->select( ' a.dep_term, a.asset_name, a.asset_date, a.asset_value, a.asset_desc ' );
        $query->select( "CASE WHEN MONTH(now())>=7 THEN concat(YEAR(now()), '-',YEAR(now())+1) ELSE concat(YEAR(now())-1,'-', YEAR(now())) END AS current_finyear" );
        $query->select( "IF(a.asset_date>'0000-00-00 00:00:00',CASE WHEN MONTH(a.asset_date)>=7 THEN concat(YEAR(a.asset_date), '-',YEAR(a.asset_date)+1) ELSE concat(YEAR(a.asset_date)-1,'-', YEAR(a.asset_date)) END,0) AS purchased_finyear" );
        $query->select( "IF(a.asset_date>'0000-00-00 00:00:00',CASE WHEN MONTH(a.asset_date)>=7 THEN concat(YEAR(a.asset_date)+1, '-',YEAR(a.asset_date)+2) ELSE concat(YEAR(a.asset_date),'-', YEAR(a.asset_date)+1) END,0) AS finyear1" );
        $query->select( "IF(a.asset_date>'0000-00-00 00:00:00',CASE WHEN MONTH(a.asset_date)>=7 THEN concat(YEAR(a.asset_date)+2, '-',YEAR(a.asset_date)+3) ELSE concat(YEAR(a.asset_date)+1,'-', YEAR(a.asset_date)+2) END,0) AS finyear2" );
        $query->select( "IF(a.asset_date>'0000-00-00 00:00:00',CASE WHEN MONTH(a.asset_date)>=7 THEN concat(YEAR(a.asset_date)+3, '-',YEAR(a.asset_date)+4) ELSE concat(YEAR(a.asset_date)+2,'-', YEAR(a.asset_date)+3) END,0) AS finyear3" );
        $query->select( "IF(a.asset_date>'0000-00-00 00:00:00',CASE WHEN MONTH(a.asset_date)>=7 THEN concat(YEAR(a.asset_date)+4, '-',YEAR(a.asset_date)+5) ELSE concat(YEAR(a.asset_date)+3,'-', YEAR(a.asset_date)+4) END,0) AS finyear4" );
        $query->select( "IF(a.asset_date>'0000-00-00 00:00:00',CASE WHEN MONTH(a.asset_date)>=7 THEN concat(YEAR(a.asset_date)+5, '-',YEAR(a.asset_date)+6) ELSE concat(YEAR(a.asset_date)+4,'-', YEAR(a.asset_date)+5) END,0) AS finyear5" );
        $query->select( "IF(a.asset_date>'0000-00-00 00:00:00',CASE WHEN MONTH(a.asset_date)>=7 THEN concat(YEAR(a.asset_date)+6, '-',YEAR(a.asset_date)+7) ELSE concat(YEAR(a.asset_date)+5,'-', YEAR(a.asset_date)+6) END,0) AS finyear6" );
        $query->from('#__gafinance_busassets AS a ');
        $query->where(' a.state = 1 ');
        $query->order(' purchased_finyear ASC');

        $db->setQuery((string)$query);
	    try {
	        $assetdata = $db->loadRowList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }

        if (!empty($assetdata)) {
            $pfyear1 = $assetdata[0][6];
            $finyears = array();
            array_push($finyears, $pfyear1);
    
            foreach ($assetdata as $asset) {
                $pfyear2 = $asset[6];
                if ($asset[7] > $pfyear1) {
                    array_push($finyears, $asset[7]);
                    $pfyear1 = $asset[7];
                }
                if ($asset[8] > $pfyear1) {
                    array_push($finyears, $asset[8]);
                    $pfyear1 = $asset[8];
                }
                if ($asset[9] > $pfyear1) {
                    array_push($finyears, $asset[9]);
                    $pfyear1 = $asset[9];
                }
                if ($asset[10] > $pfyear1) {
                    array_push($finyears, $asset[10]);
                    $pfyear1 = $asset[10];
                }
                if ($asset[11] > $pfyear1) {
                    array_push($finyears, $asset[11]);
                    $pfyear1 = $asset[11];
                }
                if ($asset[12] > $pfyear1) {
                    array_push($finyears, $asset[12]);
                    $pfyear1 = $asset[12];
                }
            }
            
            $colhead = '<tr><td class="asstype">&nbsp;</td><td class="dephead">'.$finyears[0].'</td>';
            $colhead .= '<td class="dephead">'.$finyears[1].'</td><td class="dephead">'.$finyears[2].'</td>';
            $colhead .= '<td class="dephead">'.$finyears[3].'</td><td class="dephead">'.$finyears[4].'</td>';
            $colhead .= '<td class="dephead">'.$finyears[5].'</td><td class="dephead">'.$finyears[6].'</td><tr>';
    
            $assets = '';
    
            foreach ($assetdata as $asset) {
            
                if ($asset[3] == 0 OR $asset[0] == 0) {
                    $yearly_figure = 0;
                } else {
                    $yearly_figure = ($asset[3] / $asset[0]);
                }
                $currfinyear = $asset[5];
                $pfinyear = $asset[6];
                $assetval1 = 0;
                $assetval2 = 0;
                $assetval3 = 0;
                $assetval4 = 0;
                $assetval5 = 0;
                $assetval6 = 0;
                $assetval7 = 0;
                $currentassetvalue = 0;
                $j = 0;
            
                for ($i = 1; $i <= $asset[0]; $i++) {
                    if ( $pfinyear == $finyears[0] ) {
                        $assetval1 = ($asset[3] - ($yearly_figure * $i));
                        $pfinyear = $finyears[($i+$j)];
                        $j++;
                    }
                    elseif ( $pfinyear == $finyears[1] ) {
                        $assetval2 = ($asset[3] - ($yearly_figure * $i));
                        if ($j == 0 and $i = 1) {$j = 1; }
                        $pfinyear = $finyears[($i+$j)];
                    }
                    elseif ( $pfinyear == $finyears[2] ) {
                        $assetval3 = ($asset[3] - ($yearly_figure * $i));
                        if ($j == 0 and $i = 1) {$j = 2; }
                        $pfinyear = $finyears[($i+$j)];
                    }
                    elseif ( $pfinyear == $finyears[3] ) {
                        $assetval4 = ($asset[3] - ($yearly_figure * $i));
                        if ($j == 0 and $i = 1) {$j = 3; }
                        $pfinyear = $finyears[($i+$j)];
                    }
                    elseif ( $pfinyear == $finyears[4] ) {
                        $assetval5 = ($asset[3] - ($yearly_figure * $i));
                        if ($j == 0 and $i = 1) {$j = 4; }
                        $pfinyear = $finyears[($i+$j)];
                    }
                    elseif ( $pfinyear == $finyears[5] ) {
                        $assetval6 = ($asset[3] - ($yearly_figure * $i));
                        if ($j == 0 and $i = 1) {$j = 5; }
                        $pfinyear = $finyears[($i+$j)];
                    }
                    elseif ( $pfinyear == $finyears[6] ) {
                        $assetval7 = ($asset[3] - ($yearly_figure * $i));
                        if ($j == 0 and $i = 1) {$j = 6; }
                        $pfinyear = $finyears[($i+$j)];
                    }
                    
                }
    
                    // gather the asset values for the current financial year
                    if ($currfinyear == $finyears[0]) {
                        $currentassetvalue = $currentassetvalue + $assetval1;
                    } elseif ($currfinyear == $finyears[1]) {
                        $currentassetvalue = $currentassetvalue + $assetval2;
                    } elseif ($currfinyear == $finyears[2]) {
                        $currentassetvalue = $currentassetvalue + $assetval3;
                    } elseif ($currfinyear == $finyears[3]) {
                        $currentassetvalue = $currentassetvalue + $assetval4;
                    } elseif ($currfinyear == $finyears[4]) {
                        $currentassetvalue = $currentassetvalue + $assetval5;
                    } elseif ($currfinyear == $finyears[5]) {
                        $currentassetvalue = $currentassetvalue + $assetval6;
                    } elseif ($currfinyear == $finyears[6]) {
                        $currentassetvalue = $currentassetvalue + $assetval7;
                    }
    
                $assets .= '<tr class="deprec"><td class="asstype">'.$asset[1].'<br />'.$asset[4].'</td>';
                $assets .= '<td class="depamt">'.number_format($assetval1,0).'</td>';
                $assets .= '<td class="depamt">'.number_format($assetval2,0).'</td>';
                $assets .= '<td class="depamt">'.number_format($assetval3,0).'</td>';
                $assets .= '<td class="depamt">'.number_format($assetval4,0).'</td>';
                $assets .= '<td class="depamt">'.number_format($assetval5,0).'</td>';
                $assets .= '<td class="depamt">'.number_format($assetval6,0).'</td>';
                $assets .= '<td class="depamt">'.number_format($assetval7,0).'</td></tr>';
            }

            $rptdata = '<div class="page-header"><h2>Depreciation Schedule for '.$sitename.'</h2></div><p>The following table shows the value of the asset at the close of each financial year.</p>';
            $rptdata .= '<table class="finreport"><tr><td class="asstype"><strong>Assets</strong></td><td class="assamt" colspan="6">&nbsp;</td></tr>';
            $rptdata .= $colhead;
            $rptdata .= '<tr><td class="asstype" colspan="7">&nbsp;</td></tr>';
            $rptdata .= $assets;
            $rptdata .= '<tr><td class="asstype" colspan="7">&nbsp;</td></tr><tr><td class="asstype" colspan="7">&nbsp;</td></tr></table>';
            
        }

        // Set the data into the session.
        $app->setUserState('com_gafinance.currentassetvalue.data', $currentassetvalue);
        $app->setUserState('com_gafinance.rptprint.data', $rptdata);


        return true;
    }

	/** ---------------------------------------------- Travel Log Report ------------------------------
	/**
	 * Get the data for the Travel Log report
	 */
	public static function rptTravelLog($data)
	{
	    $app		= Factory::getApplication();
        $sitename   = $app->get('sitename');
        // get the name of the user requesting this update
        $user	= GafinanceHelper::getSpecificUser();
        $user_id	= $user->id;

        $params  = ComponentHelper::getParams('com_gafinance');
        //Factory::getApplication()->setUserState('com_gafinance.params.data', $tc_values);
        $cents_pk  = $params->get('cents_pk');
        $category  = $params->get('travel_cat');
        $tran_ref = 'klms @ '.$cents_pk.' pk';
        $i = strlen($tran_ref)-1;

        $req_dtfr = $data['start_date'];
        $req_dtto = $data['end_date'];

        // Create a new query object to get all the transactions between to the requested dates.
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->clear();
        $query->select( ' b.name AS client, sum(substr(a.tran_ref,1,(LENGTH(a.tran_ref)-'.(int)$i.'))) AS distance, SUM(a.tran_amount) AS travelcost ' );
        $query->from('#__gafinance_transactions AS a, #__users AS b ');
        $query->where(' a.state = 1 ');
        $query->where(' a.accnt_id = '.(int) $data['rpt_accnt']);
        $query->where(' a.cat_id = '.(int)$category );
        $query->where(' a.tran_type <> "Z" ');
        $query->where(' a.tran_date >= '.$db->Quote($req_dtfr) );
        $query->where(' a.tran_date <= '.$db->Quote($req_dtto) );
        $query->where(' a.user_id = b.id ' );
        $query->group(' a.user_id ' );

        $db->setQuery((string)$query);
        $travdata = $db->loadObjectList();
	    try {
	        $travdata = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }

        $totcost = 0;
        $totdist = 0;

        foreach ($travdata as $travel) {
            $travelcost = $travel->travelcost * -1;
            $distance = str_replace($tran_ref,'',$travel->distance);
            $travel_data .= '<tr><td class="trandesc">'.$travel->client.'</td><td class="tranamt">'.$distance.'</td>';
            $travel_data .= '<td class="tranamt">'.number_format($travelcost,2).'</td></tr>';
            $totcost = $totcost + $travelcost;
            $totdist = $totdist + $distance;
        }

        $rptdata = '<div class="page-header"><h2>Travel Log</h2></div><p>For '.$sitename.' within period '.$req_dtfr.' to '.$req_dtto.'</p>';
        $rptdata .= '<table class="finreport"><tr><td class="trandesc">Client</td><td class="tranamt">Distance</td>';
        $rptdata .= '<td class="tranamt">Travel Cost</td></tr><tr><td class="trantype" colspan="3">&nbsp;</td></tr>';
        $rptdata .= $travel_data;
        $rptdata .= '<tr><td class="trantype" colspan="3">&nbsp;</td></tr>';
        $rptdata .= '<tr><td class="trantype" colspan="3">&nbsp;</td></tr>';
        $rptdata .= '<tr><td class="trandesc" style="text-align:right;"><strong>Total &nbsp; </strong></td>';
        $rptdata .= '<td class="tranamt"><strong>'.$totdist.'</strong></td>';
        $rptdata .= '<td class="tranamt"><strong>'.number_format($totcost,2).'</strong></td></tr>';
        $rptdata .= '<tr><td class="trantype" colspan="3">&nbsp;</td></tr><tr><td class="trantype" colspan="3">&nbsp;</td></tr></table>';
        
        $app->setUserState('com_gafinance.rptprint.data', $rptdata);

        return true;
    }

	/** ---------------------------------------------- GST Report ------------------------------
	/**
	 * Get the GST amounts for each Account
	 */
	public static function rptGST($data)
	{
	    $app		= Factory::getApplication();
        $sitename   = $app->get('sitename');
        $params = ComponentHelper::getParams('com_gafinance');
        $gst_rate = $params->get('gst_rate');
        $gst_rate_name = ($gst_rate * 100).'%';

        // get the name of the user requesting this update
        $user	= GafinanceHelper::getSpecificUser();
        $user_id	= $user->id;

        $req_dtfr = $data['start_date'];
        $req_dtto = $data['end_date'];

        // Create a new query object to get all the transactions between the requested dates.
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->clear();
        $query->select( ' accnt.accnt_name, cat.title, a.tran_desc, SUM(a.gst_amt) as tranAmt, a.tran_type ' );
        $query->from('#__gafinance_transactions AS a ');
        $query->join('LEFT','#__gafinance_accounts AS accnt ON accnt.id = a.accnt_id');
        $query->join('LEFT','#__categories AS cat ON cat.id = a.cat_id');
        $query->where(' a.state = 1 ');
        $query->where(' a.tran_type <> "Z" ');
        $query->where(' a.tran_date >= '.$db->Quote($req_dtfr) );
        $query->where(' a.tran_date <= '.$db->Quote($req_dtto) );
		// filter on the menu parameter of account id
        $query->where(' a.accnt_id = '.(int) $data['rpt_accnt']);
        $query->group(' a.tran_type, a.cat_id ' );
        $query->order(' cat.title ASC ' );

        $db->setQuery((string)$query);
	    try {
	        $gstdata = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }
        $expenses = '';
        $revenue = '';

        foreach ($gstdata as $gst) {
            if ($gst->tran_type == 'E') {
				$expenses .= '<tr><td class="trantype">&nbsp;</td><td class="trandesc">'.$gst->title.'</td>';
	            $expenses .= '<td class="tranamt">'.number_format($gst->tranAmt,2).'</td></tr>';
	            $totexp = $totexp + $gst->tranAmt;
            } else {
				$revenue .= '<tr><td class="trantype">&nbsp;</td><td class="trandesc">'.$gst->title.'</td>';
	            $revenue .= '<td class="tranamt">'.number_format($gst->tranAmt,2).'</td></tr>';
	            $totrev = $totrev + $gst->tranAmt;
	        }
        }

        $rptdata = '<div class="page-header"><h2>GST Statement</h2></div><p>For '.$sitename.' within period '.$req_dtfr.' to '.$req_dtto.'</p><table class="finreport">';
        $rptdata .= '<tr><td class="trantype" colspan="3">&nbsp;</td></tr><tr><td class="trantype" colspan="3">GST Charged Out on Income</td></tr>';
        $rptdata .= $revenue;
        $rptdata .= '<tr><td class="trantype" colspan="3">&nbsp;</td></tr><tr><td class="trantype">&nbsp;</td><td class="trandesc"><strong>Total GST Collected</strong></td>';
        $rptdata .= '<td class="tranamt"><strong>'.number_format($totrev,2).'</strong></td></tr>';
        $rptdata .= '<tr><td class="trantype" colspan="3">&nbsp;</td></tr><tr><td class="trantype" colspan="3">&nbsp;</td></tr>';
        $rptdata .= '<tr><td class="trantype" colspan="3">&nbsp;</td></tr><tr><td class="trantype" colspan="3">GST Paid Out on Expenses</td></tr>';
        $rptdata .= $expenses;
        $rptdata .= '<tr><td class="trantype" colspan="3">&nbsp;</td></tr><tr><td class="trantype">&nbsp;</td><td class="trandesc"><strong>Total GST Expenses Paid</strong></td>';
        $rptdata .= '<td class="tranamt"><strong>'.number_format($totexp,2).'</strong></td></tr>';
        $rptdata .= '<tr><td class="trantype" colspan="3">&nbsp;</td></tr><tr><td class="trantype" colspan="3">&nbsp;</td></tr>';
        $rptdata .= '<tr><td class="trantype" colspan="3">&nbsp;</td></tr><tr><td class="trantype" colspan="3">&nbsp;</td></tr></table>';

        $app->setUserState('com_gafinance.rptprint.data', $rptdata);

        return true;
    }

	/** ---------------------------------------------- Layouts ------------------------------
	/**
	 * Layout for Balance Sheet
	 * Setup the Balance Sheet Table with relevant data based on input data
	 */
	public static function setupBSTable($output, $assetlabel, $account, $assetvalue)
	{
        $output .= '<tr><td class="trantype">&nbsp;</td><td class="trancat">'.$assetlabel.'</td><td class="trandesc">'.$account.'</td>';
        if ($assetvalue == '') {
            $output .= '<td class="tranamt">&nbsp;</td><td class="tranamt">&nbsp;</td></tr>';
        } else {
            $output .= '<td class="tranamt">'.number_format($assetvalue,2).'</td><td class="tranamt">&nbsp;</td></tr>';
        }

        return $output;
    }

	/**
	/**
	 * Layout for Cash Summary
	 */
	public static function setupCashBookSummaryLayout($openBal, $closeBal, $transactions)
	{
        $rptHead = '<table class="finreport">';
        $rptHead .= '<tr><td class="trantype">&nbsp;</td><td class="trancat">&nbsp;</td><td class="trandesc">&nbsp;</td>';
        $rptHead .= '<td class="tranamt">&nbsp;</td><td class="tranamt">&nbsp;</td><td class="tranamt">&nbsp;</td></tr>';

        $params = ComponentHelper::getParams('com_gafinance');
        $combine_rpt = $params->get('combine_rpt', 0);
        // reset all variables
		$rptFull = '';
        $rptFoot = '';
        $revenue = '';
        $totrev = 0;
        $expenses = '';
        $totexp = 0;
        $journals = '';
        $totjnl = 0;
        $totcat = 0;
        $prev_cat = '';
        $prev_type = '';
        $cntr = 0;

        if (!is_array($transactions)) { // ie an empty list, set up header
			$rptHead .= '<tr style="border-top:1px solid;"><td colspan="6"><h4>Account: '.$transactions.'</h4></td></tr>';
            $rptHead .= '<tr><td colspan="6"><h5>No transactions retrieved for the reporting period</h5></td></tr>';
            $rptHead .= '<tr><td colspan="4"><strong>Opening Balance</strong></td>';
            $rptHead .= '<td class="tranamt trantot" colspan="2"><strong>'.number_format($openBal,2).'</strong></td></tr>';
            $rptHead .= '<tr><td colspan="6"><strong>Revenue</strong></td></tr>';
        } else {

            foreach ($transactions as $tran) {
                $cntr++;
                if ($cntr === 1) {
                    $accnt_id_name = $combine_rpt ? Text::_('COM_GAFINANCE_COMBINE_ACCNTS_LABEL') : $tran->accnt_id_name;
    				$rptHead .= '<tr style="border-top:1px solid;"><td colspan="6"><h4>Account: '.$accnt_id_name.'</h4></td></tr>';
    		        $rptHead .= '<tr><td class="trantype">&nbsp;</td><td class="trancat">&nbsp;</td><td class="trandesc">&nbsp;</td>';
    		        $rptHead .= '<td class="tranamt">&nbsp;</td><td class="tranamt">&nbsp;</td><td class="tranamt">&nbsp;</td></tr>';
    		        $rptHead .= '<tr><td colspan="4"><strong>Opening Balance</strong></td>';
    		        $rptHead .= '<td class="tranamt trantot" colspan="2"><strong>'.number_format($openBal,2).'</strong></td></tr>';
    		        $rptHead .= '<tr><td colspan="6"><strong>Revenue</strong></td></tr>';
                    $prev_type = $tran->tran_type;
    		    }

    		    if ($tran->tran_type != $prev_type && $cntr > 1) {
    				if ($prev_type == 'I') {
    					// set the final row
    					$revenue .= '<tr><td colspan="4">&nbsp;</td><td class="tranamt" style="border-bottom:1px #000 solid;">'.number_format($totcat,2).'</td><td class="tranamt">&nbsp;</td></tr>';
    					$totcat = 0;
    					$prev_cat = '';
    					$cntr = 1;
    				} elseif ($prev_type == 'E') {
    					// set the final row
    					$expenses .= '<tr><td colspan="4">&nbsp;</td><td class="tranamt" style="border-bottom:1px #000 solid;">'.number_format($totcat,2).'</td><td class="tranamt">&nbsp;</td></tr>';
    					$totcat = 0;
    					$prev_cat = '';
    					$cntr = 1;
    				} else {
    					// set the final row
    					$journals .= '<tr><td colspan="4">&nbsp;</td><td class="tranamt" style="border-bottom:1px #000 solid;">'.number_format($totcat,2).'</td><td class="tranamt">&nbsp;</td></tr>';
    					$totcat = 0;
    					$prev_cat = '';
    					$cntr = 1;
    				}
    				$prev_type = $tran->tran_type;
    			}
    
                // Set up income or expense line item based on the transaction amount due to Journal Entries (D) being introduced
    			if ($tran->tran_type == "I") {
    
                    if ($tran->state == 0) {
    					$unpresent1 = ' <span style="color:red;">';
    					$unpresent2 = '</span>';
    				} else {
    					$unpresent1 = ''; 
    					$unpresent2 = ''; 
    					$totrev = $totrev + $tran->tranAmt;
    				}
    
    				if ($tran->title != $prev_cat) {
    					if ($cntr > 1) {
    						$revenue .= '<tr><td colspan="4">&nbsp;</td><td class="tranamt" style="border-bottom:1px #000 solid;">'.number_format($totcat,2).'</td><td class="tranamt">&nbsp;</td></tr>';
    					}
    					$totcat = 0;
    					$prev_cat = $tran->title;
                        if ($tran->state != 0) {
    						$totcat = $totcat + $tran->tranAmt;
    					}
    	                $revenue .= '<tr><td>&nbsp;</td><td colspan="5">'.$tran->title.'</td></tr>';
    					$revenue .= '<tr><td colspan="2">&nbsp;</td><td>'.$unpresent1.$tran->tran_desc.$unpresent2.'</td>';
    					$revenue .= '<td class="tranamt">'.$unpresent1.number_format($tran->tranAmt,2).$unpresent2.'</td><td class="tranamt">&nbsp;</td><td class="tranamt">&nbsp;</td></tr>';
                    } else {
                        if ($tran->state != 0) {
    						$totcat = $totcat + $tran->tranAmt;
    					}
    					$revenue .= '<tr><td colspan="2">&nbsp;</td><td>'.$unpresent1.$tran->tran_desc.$unpresent2.'</td>';
    	                $revenue .= '<td class="tranamt">'.$unpresent1.number_format($tran->tranAmt,2).$unpresent2.'</td><td class="tranamt">&nbsp;</td><td class="tranamt">&nbsp;</td></tr>';
    				}
    
                } elseif ($tran->tran_type == "E") {
    
                    if ($tran->state == 0) {
    					$unpresent1 = ' <span style="color:red;">';
    					$unpresent2 = '</span>';
    				} else {
    					$unpresent1 = ''; 
    					$unpresent2 = ''; 
    					$totexp = $totexp + $tran->tranAmt;
    				}
    
    				if ($tran->title != $prev_cat) {
    					if ($cntr > 1) {
    						$expenses .= '<tr><td colspan="4">&nbsp;</td><td class="tranamt" style="border-bottom:1px #000 solid;">'.number_format($totcat,2).'</td><td class="tranamt">&nbsp;</td></tr>';
    					}
    					$totcat = 0;
    					$prev_cat = $tran->title;
                        if ($tran->state != 0) {
    						$totcat = $totcat + $tran->tranAmt;
    					}
    	                $expenses .= '<tr><td>&nbsp;</td><td colspan="5">'.$tran->title.'</td></tr>';
    					$expenses .= '<tr><td colspan="2">&nbsp;</td><td>'.$unpresent1.$tran->tran_desc.$unpresent2.'</td>';
    					$expenses .= '<td class="tranamt">'.$unpresent1.number_format($tran->tranAmt,2).$unpresent2.'</td><td class="tranamt">&nbsp;</td><td class="tranamt">&nbsp;</td></tr>';
                    } else {
                        if ($tran->state != 0) {
    						$totcat = $totcat + $tran->tranAmt;
    					}
    					$expenses .= '<tr><td colspan="2">&nbsp;</td><td>'.$unpresent1.$tran->tran_desc.$unpresent2.'</td>';
    	                $expenses .= '<td class="tranamt">'.$unpresent1.number_format($tran->tranAmt,2).$unpresent2.'</td><td class="tranamt">&nbsp;</td><td class="tranamt">&nbsp;</td></tr>';
    				}
    			} elseif ($tran->tran_type == "D") {
    
                    if ($tran->state == 0) {
    					$unpresent1 = ' <span style="color:red;">';
    					$unpresent2 = '</span>';
    				} else {
    					$unpresent1 = ''; 
    					$unpresent2 = ''; 
    					$totjnl = $totjnl + $tran->tranAmt;
    				}
    
    				if ($tran->title != $prev_cat) {
    					if ($cntr > 1) {
    						$journals .= '<tr><td colspan="4">&nbsp;</td><td class="tranamt" style="border-bottom:1px #000 solid;">'.number_format($totcat,2).'</td><td class="tranamt">&nbsp;</td></tr>';
    					}
    					$totcat = 0;
    					$prev_cat = $tran->title;
                        if ($tran->state != 0) {
    						$totcat = $totcat + $tran->tranAmt;
    					}
    	                $journals .= '<tr><td>&nbsp;</td><td colspan="5">'.$tran->title.'</td></tr>';
    					$journals .= '<tr><td colspan="2">&nbsp;</td><td>'.$unpresent1.$tran->tran_desc.$unpresent2.'</td>';
    					$journals .= '<td class="tranamt">'.$unpresent1.number_format($tran->tranAmt,2).$unpresent2.'</td><td class="tranamt">&nbsp;</td><td class="tranamt">&nbsp;</td></tr>';
                    } else {
                        if ($tran->state != 0) {
    						$totcat = $totcat + $tran->tranAmt;
    					}
    					$journals .= '<tr><td colspan="2">&nbsp;</td><td>'.$unpresent1.$tran->tran_desc.$unpresent2.'</td>';
    	                $journals .= '<td class="tranamt">'.$unpresent1.number_format($tran->tranAmt,2).$unpresent2.'</td><td class="tranamt">&nbsp;</td><td class="tranamt">&nbsp;</td></tr>';
    				}
                }   // end of plus or minus amount
    		}  // end of foreach
		}  // end of if empty

        // final row of expenses or revenue for the category totals
        if ($tran->tran_type == "I") {
			$revenue .= '<tr><td colspan="4">&nbsp;</td><td class="tranamt" style="border-bottom:1px #000 solid;">'.number_format($totcat,2).'</td><td class="tranamt">&nbsp;</td></tr>';
		} elseif ($tran->tran_type == "E") {
			$expenses .= '<tr><td colspan="4">&nbsp;</td><td class="tranamt" style="border-bottom:1px #000 solid;">'.number_format($totcat,2).'</td><td class="tranamt">&nbsp;</td></tr>';
		} elseif ($tran->tran_type == "D") {
			$journals .= '<tr><td colspan="4">&nbsp;</td><td class="tranamt" style="border-bottom:1px #000 solid;">'.number_format($totcat,2).'</td><td class="tranamt">&nbsp;</td></tr>';
		}

        $rptdata .= $revenue;
        $rptdata .= '<tr><td colspan="2">&nbsp;</td><td class="trandesc" colspan="3"><strong>Total Revenue</strong></td><td class="tranamt trantot"><strong>'.number_format($totrev,2).'</strong></td></tr>';
        $rptdata .= '<tr><td colspan="6"><strong>Expenses</strong></td></tr>';
        $rptdata .= $expenses;
        $rptdata .= '<tr><td colspan="2">&nbsp;</td><td class="trandesc" colspan="3"><strong>Total Expenses</strong></td><td class="tranamt trantot"><strong>'.number_format($totexp,2).'</strong></td></tr>';
        $rptdata .= '<tr><td colspan="6"><strong>Journal Entries</strong></td></tr>';
        $rptdata .= $journals;
        $rptFoot .= '<tr><td colspan="2">&nbsp;</td><td class="trandesc" colspan="3"><strong>Total Journal Entries</strong></td><td class="tranamt trantot"><strong>'.number_format($totjnl,2).'</strong></td></tr>';
        $rptFoot .= '<tr><td colspan="6">&nbsp;</td></tr>';
        $rptFoot .= '<tr><td colspan="4"><strong>Closing Balance</strong></td><td class="tranamt trantot" colspan="2"><strong>'.number_format($closeBal,2).'</strong></td></tr>';
        $rptFoot .= '<tr style="border-bottom:2px solid;"><td colspan="6">&nbsp;</td></tr>';
        $rptFoot .= '</table>';
        $rptFoot .= '<p class="center small"><span style="color:red;">Entries in red indicate unpresented cheques or unbanked funds.</p>';

	    $rptFull .= $rptHead.$rptdata.$rptFoot;

	    return $rptFull;

	}


}
