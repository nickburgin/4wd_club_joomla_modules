<?php

/**
 * @version    4.0.7
 * @package    com_gamerchandise
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gamerchandise\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Installer\Installer;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\Filesystem\Path;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\CMS\Helper\UserGroupsHelper;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\User\UserFactoryInterface;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\User\User;
use \Joomla\CMS\Date\Date;
use \Joomla\CMS\Access\Access;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gamerchandise\Administrator\Helper\GapdfHelper;

/**
 * Comonent helper.
 * @since  1.6
 */
class GamerchandiseHelper
{

	/**
	 * Build the HTTP query array
	 * @param array of the http query if the http query already prepared and we want to add more
	 * @param string view or a task
	 * @param string control method to be used
	 * @param string reference indicator
	 * @param string/int reference string or id
	 * @return array
	 * @ hint - this is used with http_build_query($query_string, '', '&amp;') to build a clean url
	 */
	public static function getHTTPQuery($existQ = null, $viewTask = 'view', $contModel = 'product', $ref = 'id', $linkId = 0)
	{
		if (!$existQ) {
			$query_string = array();
			$query_string['option'] = 'com_gamerchandise';
			$query_string[$viewTask] = $contModel;
			if ($ref) {
				$query_string[$ref] = $linkId;
			}
		} else {
			$query_string = $existQ;
			$query_string[$ref] = $linkId;
		}

		return $query_string;
	}

    /**
     * Gets todays date based on global timezone settings
     */
    public static function getTodaysDate()
	{
		$tz = Factory::getConfig()->get('offset');
		$date = Factory::getDate('now', $tz);
		$today = date_format($date,'Y-m-d H:i:s');
		
		return $today;
	}

    /**
     * Gets the user record for the specific id reference
     */
    public static function getSpecificUser($id = 0)
	{
		if ($id) {
			$container = Factory::getContainer();
			$userFactory = $container->get(UserFactoryInterface::class);
			$user = $userFactory->loadUserById($id);
		} else {
			$user = Factory::getApplication()->getIdentity();
		}

		return $user;
	}

	/**
	 * Gets the value of a field
	 * @param   int     $pk     The item's id
	 * @param   string  $table  The table's name
	 * @param   string  $field  The field's name
	 * @return  string  value of the field
	 */
	public static function getRecordValue($pk, $table, $field)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query
			->select($field)
			->from($table)
			->where('id = ' . (int) $pk);

		$db->setQuery($query);

		return $db->loadResult();
	}

    /**
     * Gets the edit permission for an user
     * @param   mixed  $item  The item
     * @return  bool
     */
    public static function canUserEdit($item)
    {
        $permission = false;
        $user       = self::getSpecificUser();
        
		if (isset($item->created_by)) {
			$created_by = $item->created_by;
		} else {
			$created_by = $item;
		}

        if ($user->authorise('core.edit', 'com_gamerchandise')) {
            $permission = true;
        } else {
            if (isset($item->created_by)) {
                if ($user->authorise('core.edit.own', 'com_gamerchandise') && $created_by == $user->id) {
                    $permission = true;
                }
            } else {
                $permission = true;
            }
        }

        return $permission;
    }

	public static function getListOptions($table = '#__gamerchandise_products', $label = 'Product', $field = 'prod_name' )
	{

		// get the user records in the supplier group
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "0" as "value", " - Select '.$label.' - " as "text" UNION SELECT a.id as value, a.'.$field.' as text ');
		$query->from( $table . ' AS a ' );
		if ($table == '#__users') {
			$query->where(' a.block = 0' );
		} else {
			$query->where(' a.state = 1' );
		}
		$query->order(' text ASC ' );
		$db->setQuery((string)$query);

	    try {
	        $options = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

		return $options;
	}

	/**
	 * Get the version of the component
	 * @return version element of manifest
	 */
	public static function getComponentVersion()
	{
		$componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/com_gamerchandise/gamerchandise.xml'));

		return $componentXML['version'];
	}

	/**
	 * Returns valid contexts
	 * @return  array
	 * @since   3.7.0
	 */
	public static function getContexts()
	{
		Factory::getLanguage()->load('com_gamerchandise', JPATH_ADMINISTRATOR);

		$contexts = array(
			'com_gamerchandise.product' => Text::_('COM_GAMERCHANDISE_TITLE_PRODUCTS'),
		);

		return $contexts;
	}

	public static function validateSection($section, $item)
	{
		if (Factory::getApplication()->isClient('site') && $section == 'form') {
			return 'gamerchandise';
		}
		if ($section != 'gamerchandise' && $section != 'form') {
			return null;
		}

		return $section;
	}

	public static function getProduct($id = 0)
	{
		// get the name and contact of the supplier id
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.prod_name ');
		$query->from(' #__gamerchandise_products AS a ');
		$query->where(' a.id = '.(int) $id );
		$query->where(' a.state = 1 ' );
		$db->setQuery((string)$query);

		return $db->loadResult();
	}

	public static function getProductDetails($id = 0)
	{
		// get the name and contact of the supplier id
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.* ');
		$query->from(' #__gamerchandise_products AS a ');
		if ($id) {
			$query->where(' a.id = '.(int) $id );
		}
		$query->where(' a.state = 1 ' );
		$db->setQuery((string)$query);

		if ($id) {
			return $db->loadObject();
		} else {
			return $db->loadObjectList();
		}
	}

	public static function getSaleItems($id = 0)
	{
		// get the sales records for this product
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.*, u.name AS user_id_name, c.title AS cat_colour_id_name, s.title AS cat_size_id_name ');
		$query->from(' #__gamerchandise_sales AS a');
	    $query->join('LEFT', ' #__users AS u ON u.id = a.user_id ');
	    $query->join('LEFT', ' #__categories AS c ON c.id = a.cat_colour_id ');
	    $query->join('LEFT', ' #__categories AS s ON s.id = a.cat_size_id ');
		$query->where(' a.prod_id = '.(int) $id );
		$query->where(' a.state = 2 ' );
		$db->setQuery((string)$query);

		return $db->loadObjectList();
	}

	public static function getSaleItem($id = 0)
	{
		// get the sales record
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.*, p.prod_name, p.cost, p.price ');
		$query->from(' #__gamerchandise_sales AS a');
	    $query->join('LEFT', ' #__gamerchandise_products AS p ON p.id = a.prod_id ');
		$query->where(' a.id = '.(int) $id );
		$db->setQuery((string)$query);

		return $db->loadObject();
	}

	public static function getMembershipOptions($name = 'Membership')
	{
		$params  = ComponentHelper::getParams('com_gamerchandise');
		$mship_single = $params->get('mship_single',0);
		$profsuf = $params->get('prof_pref','b4wdc');

		if ($mship_single) {
			$options = array();
			$options[] = HTMLHelper::_('select.option', 0, ' - Select '.$name.' - ');

	        $db		= Factory::getContainer()->get('DatabaseDriver');
	        $query = $db->getQuery(true);
	        $query->select(' a.id, a.name, a.email, a.block, a.registerDate, a.lastvisitDate ' );
	        $query->select(' substr(name, 1, LOCATE(" ",name)) AS firstname ');
	        $query->select(' if(substr(name, (LOCATE(" ",name)+1), 1)="&",substr(name, LOCATE(" ",name,(LOCATE(" ",name)+3))+1),substr(name, LOCATE(" ",name)+1)) AS surname ');
	        $query->select(' if(h.profile_value IS NULL, "", h.profile_value) AS partner ');
	        $query->select(' substr(h.profile_value, 1, LOCATE(" ",h.profile_value)) AS firstnamep ');
	        $query->select(' substr(h.profile_value, LOCATE(" ",h.profile_value)+1) AS surnamep ');
	        $query->from('#__users AS a');
	        $query->join('LEFT', ' #__user_profiles AS h ON a.id = h.user_id AND h.profile_key = "profile'.$profsuf.'.partner" ');
	        $query->where(' block = 0 ' );
	        $query->order(' surname ASC ' );
	        $db->setQuery((string)$query);
			try {
				$members =  $db->loadObjectList();
			} catch (RuntimeException $e) {
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
		        return false;
		    }

			foreach ($members as $member) {
				$mbr_name = GamerchandiseHelper::combineNames($member);
				$options[] = HTMLHelper::_('select.option', $member->id, $mbr_name);
			}
		} else {
			// get the list of records
	        $db		= Factory::getContainer()->get('DatabaseDriver');
			$query	= $db->getQuery(true);
	        $query->clear();
			$query->select(' "0" as "value", " - Select '.$name.' - " as "text" UNION SELECT id as value, name as text ');
			$query->from(' #__users ');
			$query->where(' block = 0 ' );
			$query->order(' text ASC ' );
			$db->setQuery((string)$query);

		    try {
		        $options = $db->loadObjectList();
		    } catch (RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
		        return false;
		    }
		}

		return $options;
	}

	/**
	 * Get all the name fields as an object
	 * @param   id  $user id.
	 * @return  object	membership names object
	 */
    public static function breakdownNamesFromUserID($id = null)
	{
		$params = ComponentHelper::getParams('com_gamerchandise');
		$profsuf  = $params->get( 'prof_pref' );

        $db		= Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select(' a.id, a.name, a.email, a.block, a.registerDate, a.lastvisitDate ' );
        $query->select(' substr(name, 1, LOCATE(" ",name)) AS firstname ');
        $query->select(' if(substr(name, (LOCATE(" ",name)+1), 1)="&",substr(name, LOCATE(" ",name,(LOCATE(" ",name)+3))+1),substr(name, LOCATE(" ",name)+1)) AS surname ');
        $query->select(' if(h.profile_value IS NULL, "", h.profile_value) AS partner ');
        $query->select(' substr(h.profile_value, 1, LOCATE(" ",h.profile_value)) AS firstnamep ');
        $query->select(' substr(h.profile_value, LOCATE(" ",h.profile_value)+1) AS surnamep ');
        $query->from('#__users AS a');
        $query->join('LEFT', ' #__user_profiles AS h ON a.id = h.user_id AND h.profile_key = "profile'.$profsuf.'.partner" ');
        $query->where('a.id = ' . (int) $id );
        $db->setQuery($query);
		try {
			$member =  $db->loadObject();
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
			$member = false;
		}

		return $member;

	}

	/**
	 * Break up the name fields and form a display name version
	 * @param   object  $member  data including name, partner and the breakup firstnames and surnames.
	 * @return  string	combined name for the membership display
	 */
    public static function combineNames($member = null)
	{
		if (empty($member->partner) || $member->partner == '' || $member->partner == ' ') {
			$result = $member->name;
		} else {
			if (trim($member->surname) == trim($member->surnamep)) {
				$result = trim($member->firstname). ' & ' .trim($member->firstnamep) . ' ' . trim($member->surname);
			} else {
				$result = trim($member->name). ' & ' .trim($member->partner);
			}
		}

		return $result;

	}

	public static function getOrderAmount($id = 0, $qty = 1)
	{
		if ($id) {
			// get the order amount
        $db		= Factory::getContainer()->get('DatabaseDriver');
			$query	= $db->getQuery(true);
	        $query->clear();
			$query->select('(a.price * '. (int) $qty .') AS ord_amt');
			$query->from(' #__gamerchandise_products AS a ');
			$query->where(' a.id = '.(int) $id );
			$db->setQuery((string)$query);

			return $db->loadResult();
		} else {
			return 0;
		}
	}

    public static function generatePDF($sales = null)
	{
        //$sales = Factory::getApplication()->getUserState('com_gamerchandise.sales.data');
        // cycle through the sales records to get the last id reference as the order number
		foreach ($sales as $sale) { $order_no = $sale->id; }
        Factory::getApplication()->setUserState('com_gamerchandise.po.data',$order_no);
        Factory::getApplication()->setUserState('com_gamerchandise.po_user.data',$sale->user_id);

		$params  = ComponentHelper::getParams('com_gamerchandise');
    	$poemail = $params->get('poemail');
    	$po_insttxt = $params->get('po_insttxt');
    	$mbr_disc = $params->get('mbr_disc');
    	$disc_amt = $params->get('disc_amt');
        $order_no  = str_pad($order_no, 8, '0', STR_PAD_LEFT);
        $parent = 'images';
        $folder = 'merchandise';
        $path = Path::clean( JPATH_SITE . '/' . $parent . '/' . $folder );
        $tot_cost = 0;
        $lh = 5;
        $lh1 = 1;
        $lh2 = 2;

        //class instantiation
        //$pdf=new PDF("P","in","Letter"); // Legal Letter size
        //$pdf=new PDF("L","mm","A4");     // Landscape
        $pdf=new GapdfHelper("P","mm","A4");

        $pdf->SetMargins(10,10,10);

        $pdf->AddPage();

        if ($sales) {
            $pdf->SetFont('Arial','B',12);
            $pdf->Cell(110, $lh, "Product", "B", 0, "L");
            $pdf->Cell(2, $lh, "", "B", 0, "L");
            $pdf->Cell(20, $lh, "Qty", "B", 0, "C");
            $pdf->Cell(2, $lh, "", "B", 0, "L");
            $pdf->Cell(29, $lh, "Size", "B", 0, "C");
            $pdf->Cell(2, $lh, "", "B", 0, "L");
            $pdf->Cell(25, $lh, "Price", "B", 1, "R");
            $pdf->SetFont('Arial','',12); // font-family, font-weight (B), font-size

            $i = 1;
            foreach ($sales as $sale) {
                $tot_cost = $tot_cost + $sale->ord_amt;

                // check if not first record so a grey line can be added between items
                if ($i == 1) {
                    $pdf->Cell(0, $lh1, "", "", 1, "C");
                } else {
                    $pdf->SetDrawColor(204,204,204);
                    $pdf->Cell(0, $lh1, "", "T", 1, "C");
                }

                $pdf->Cell(110, $lh, $sale->prod_name.' (Colour: '.$sale->prod_colour.')', 0, 0, "L");
                $pdf->Cell(2, $lh, "", 0, 0, "L");
                $pdf->Cell(20, $lh, number_format($sale->order_qty,0), 0, 0, "C");
                $pdf->Cell(2, $lh, "", 0, 0, "L");
		        $pdf->Cell(29, $lh, $sale->prod_size, 0, 0, "C");
                $pdf->Cell(2, $lh, "", 0, 0, "L");
                $pdf->Cell(25, $lh, number_format($sale->ord_amt,2), 0, 1, "R");

                if ($sale->comment) {
                    // add item comment here
                    $pdf->Cell(0, $lh2, "", "", 1, "C");
                    $pdf->Cell(20, $lh, "", 0, 0, "L");
                    $pdf->MultiCell(140, $lh, $sale->comment, 0, "L", false);
                    $pdf->Cell(0, $lh2, "", "", 1, "C");
                }

                $i++;
            }
            $pdf->Ln(4);
        }

    	if ($mbr_disc) {
	    	$disc_cost = (($tot_cost * ($disc_amt / 100)) * -1);
	    	$tot_cost = ($tot_cost  +  $disc_cost);
	        $lh = 5;
	        $pdf->SetDrawColor(0,0,0);
	        $pdf->SetFont('Arial','B',12);
	        $pdf->Cell(168, $lh, "Member Discount ", "T", 0, "R");
	        $pdf->Cell(2, $lh, "", "T", 0, "L");
	        $pdf->Cell(20, $lh, number_format($disc_cost,2), "T", 1, "R");
	        $pdf->Cell(190, $lh, "", "T", 1, "L");
		}

        $lh = 5;
        $pdf->SetDrawColor(0,0,0);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(168, $lh, "Total Cost ", "T", 0, "R");
        $pdf->Cell(2, $lh, "", "T", 0, "L");
        $pdf->Cell(20, $lh, number_format($tot_cost,2), "T", 1, "R");
        $pdf->Cell(190, $lh, "", "T", 1, "L");

        $pdf->SetFont('Arial','B',12);
        $pdf->SetTextColor(0,100,148); // r,g,b
        $pdf->MultiCell(0, $lh, $po_insttxt, 0, "C", false);
        $pdf->SetTextColor(64,64,64); // r,g,b

        $pdf->Output($path . "/ORD".$order_no.".pdf", "F");

		$attachment = $path . "/ORD".$order_no.".pdf";
        
		return $attachment;
	}

    public static function sendEmail($attachfile = null, $subject = "Merchandise Order")
	{

        $user = self::getSpecificUser();
        $app		= Factory::getApplication();
        $mailfrom	= $app->get('mailfrom');       // system email address
        $fromname	= $app->get('fromname');       // Site name or system name

        $params  = ComponentHelper::getParams('com_gamerchandise');
        $contact  = $params->get('po_contact');
        $con_phone  = $params->get('po_con_phone');
        $emailtxt  = $params->get('po_emailtxt');
        $po_insttxt  = $params->get('po_insttxt');
        $poemail = $params->get('poemail');
        $notify_qm = $params->get('notify_qm');
        $merchandise_email  = $params->get('merchandise_email');
        $send_email = $params->get('send_email', 1);

        if ($params->get('mship_single',0)) {
			$member = self::breakdownNamesFromUserID($user->id);
			$name = self::combineNames($member);
		} else {
			$name = $user->name;
		}

        $body = '<p>Dear '.$name.', </p>';
        $body .= '<p>'.$emailtxt.'</p>';
        $body .= '<p>'.$po_insttxt.'</p>';
        $body .= '<p>Any questions can be directed to '.$contact.' on '.$con_phone.'.</p>';
        $body .= '<p>'.$fromname.'</p>';

        $recipients = array();
        if ($notify_qm) {
	        $recipients[] = $merchandise_email;
		}
        if (!empty($user->email)) {
			$recipients[] = $user->email;
		}

        // Build the email and send
        $mail = Factory::getMailer();
        $mail->isHTML(true);
        $mail->addRecipient($recipients);
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($subject);
		$mail->setBody($body);
		if (is_file($attachfile)) {
            $mail->addAttachment($attachfile);
        }

		if ($send_email) {
            $sent = $mail->Send();
    		if ($sent) {
    			Factory::getApplication()->enqueueMessage('Email Sent Successfully', 'Success');
    		} else { 
    			Factory::getApplication()->enqueueMessage('Email Failed to be sent', 'Warning!');
    		}
        }

        return true;

	}

	/**
	 * Create a finance transaction
	 * @param   id  sales id reference.
	 * @return  boolean	true on success
	 */
    public static function createFinanceTrans($id = 0, $tran_type = 'I', $fin_cat = 0)
	{
    	$user = self::getSpecificUser();
		$today = self::getTodaysDate();

		$sale = self::getSaleItem($id);

		$fin = new \stdClass();
		$fin->id = 0;
		$fin->created_by = $user->id;
		$fin->created_date = $sale->paid_date;
		$fin->user_id = $sale->user_id;
		$fin->tran_type = $tran_type;
		$fin->tran_desc = 'Merchandise';
		$fin->tran_ref = 'Order '.$sale->id;

		if ($tran_type == 'I') {
			$fin->tran_amount = $sale->ord_amt;
			$fin->tran_date = $sale->paid_date;
		} else {
			$fin->tran_amount = ($sale->cost * $sale->order_qty);
			$fin->tran_date = $today;
		}

		$fin->cat_id = $fin_cat;
		$fin->accnt_id = 1;
		$fin->comment = 'Auto Loaded from Merchandise Sale';

		// Insert the object into the user profile table.
		$result = Factory::getContainer()->get('DatabaseDriver')->insertObject('#__gafinance_transactions', $fin);

	}
}
