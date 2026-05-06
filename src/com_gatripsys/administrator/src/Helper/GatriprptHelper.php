<?php

/**
 * @version    5.3.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatripsys\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\Data\DataObject;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\Filesystem\Path;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Date\Date;
use Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GanotificationsHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GapdfHelper;


// Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR);

/**
 * Printout helper.
 * @since  1.6
 */
class GatriprptHelper
{
	/**
	* Create pdf report based on the trip data
	* @param trip record id
	* @param final flag to indicate final report
	* @return true
	*/
	public static function createTripReport($trip = 0, $final = 0)
	{
		$app = Factory::getApplication();
		$incidents = GatripsysHelper::getTripIncidents($trip);
		$incid_cntr = count($incidents);
		//$incid_cntr = GatripsysHelper::countTripIncidents($trip);
		$tripdata = GatripsysHelper::getTripInformation($trip);
		$attendees = GatripsysHelper::getTripAttendees($trip);
        $app->setUserState('com_gatripsys.trip.data',$tripdata);

		$params  = ComponentHelper::getParams('com_gatripsys');
		$show_comment = $params->get('show_comment');
		$incl_email = $params->get('incl_email');
		$checkbox_pers = $params->get('checkbox_pers');
		$trip = str_pad($trip, 6, '0', STR_PAD_LEFT);

		// set up if waitlist needed
		$attendCount = 0;
        if(!empty($attendees)) {
            $attendCount = count($attendees);
        }
        
        // set up vehicle labelling
        $vmake = Text::_('COM_GATRIPSYS_FORM_LBL_VEH_MAKE').': ';
        $vmod = Text::_('COM_GATRIPSYS_FORM_LBL_VEH_MODEL').': ';
        $vyear = Text::_('COM_GATRIPSYS_FORM_LBL_VEH_YEAR').': ';
        $vfuel = Text::_('COM_GATRIPSYS_FORM_LBL_VEH_FUEL').': ';
        $vtrans = Text::_('COM_GATRIPSYS_FORM_LBL_VEH_TRANS').': ';
        $vrego = Text::_('COM_GATRIPSYS_FORM_LBL_VEH_REGO').': ';

        $pdf=new GapdfHelper("P","mm","A4");

		$pdf->SetMargins(10,10,10);

        $app->setUserState('com_gatripsys.triprpt.header','Trip Outline');
        $pdf->AddPage();
        $lh = 4;

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $x = $x + 140;
        if (!empty($tripdata->trip_img)) {
			$trip_img = HTMLHelper::cleanImageURL($tripdata->trip_img);
            if (\file_exists(JPATH_SITE.'/'.$trip_img->url)) {
                $img_type = substr($trip_img->url,-3);
                if ($img_type != 'pdf') {
                    $pdf->Image(JPATH_SITE.'/'.$trip_img->url, $x, $y, 0, 30, $img_type, $urllink);
                }
            }
        }
        // trip header information
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(35, $lh, "Title", 0, 0, "L"); // Sets an indent of 20mm
        $pdf->SetFont('Arial','',10);
        //$pdf->Cell(120, $lh, $tripdata->title, 0, 1, "L");
        $pdf->MultiCell(115, $lh, $tripdata->title, 0, "L");
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(35, $lh, "Rating", 0, 0, "L"); // Sets an indent of 20mm
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(115, $lh, $tripdata->rating, 0, 1, "L");
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(35, $lh, "Min/Max Vehicles", 0, 0, "L"); // Sets an indent of 20mm
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(115, $lh, $tripdata->max_no, 0, 1, "L");
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(35, $lh, "Suited", 0, 0, "L"); // Sets an indent of 20mm
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(115, $lh, $tripdata->suited_for, 0, 1, "L");
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(35, $lh, "Type", 0, 0, "L"); // Sets an indent of 20mm
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(115, $lh, $tripdata->trip_type, 0, 1, "L");
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(35, $lh, "Register By", 0, 0, "L"); // Sets an indent of 20mm
        $pdf->SetFont('Arial','',10);
		// format the date for display purposes
		$jdate = new Date($tripdata->regby_date);
		$tripdata->regby_date_disp = $jdate->format(Text::_('DATE_FORMAT_LC3'));
        $pdf->Cell(115, $lh, $tripdata->regby_date_disp, 0, 1, "L");
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(35, $lh, "Depart Date", 0, 0, "L"); // Sets an indent of 20mm
        $pdf->SetFont('Arial','',10);
		$jdate = new Date($tripdata->dept_date);
		$tripdata->dept_date_disp = $jdate->format(Text::_('DATE_FORMAT_LC3'));
        $pdf->Cell(115, $lh, $tripdata->dept_date_disp, 0, 1, "L");
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(35, $lh, "Return Date", 0, 0, "L"); // Sets an indent of 20mm
        $pdf->SetFont('Arial','',10);
		$jdate = new Date($tripdata->ret_date);
		$tripdata->ret_date_disp = $jdate->format(Text::_('DATE_FORMAT_LC3'));
        $pdf->Cell(115, $lh, $tripdata->ret_date_disp, 0, 1, "L");

        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(35, $lh, "Start From", 0, 0, "L"); // Sets an indent of 20mm
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(115, $lh, $tripdata->dept_loc, 0, 1, "L");
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(35, $lh, "Return To", 0, 0, "L"); // Sets an indent of 20mm
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(115, $lh, $tripdata->ret_loc, 0, 1, "L");

        $pdf->Cell(0, $lh, "", 0, 1, "C");
		$pdf->SetFont('Arial','B',10);
        $pdf->Cell(35, $lh, "Where we'll go", 0, 0, "L"); // Sets an indent of 20mm
        $pdf->SetFont('Arial','',10);
        $pdf->MultiCell(155, $lh, str_replace("<p>","\r\n",str_replace('</p>','',str_replace("<br />","\n",$tripdata->where_go))), 0, "L");
        $pdf->Cell(0, $lh, "", 0, 1, "C");
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(35, $lh, "What we'll do", 0, 0, "L"); // Sets an indent of 20mm
        $pdf->SetFont('Arial','',10);
        $pdf->MultiCell(155, $lh, str_replace("<p>","\r\n",str_replace('</p>','',str_replace("<br />","\n",$tripdata->what_do))), 0, "L");
        $pdf->Cell(0, $lh, "", 0, 1, "C");
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(35, $lh, "What to bring", 0, 0, "L"); // Sets an indent of 20mm
        $pdf->SetFont('Arial','',10);
        $pdf->MultiCell(155, $lh, str_replace("<p>","\r\n",str_replace('</p>','',str_replace("<br />","\n",$tripdata->equip))), 0, "L");
        $pdf->Cell(0, $lh, "", 0, 1, "C");

		// rip out all html tags
		//$trip->details = $pdf->WriteHTML($trip->details);
        $pdf->SetFont('Arial','B',10);
		$pdf->Cell(35, $lh, "Details", 0, 0, "L"); // Sets an indent of 20mm
        //$pdf->WriteHTML($trip->details);
        //$pdf->Cell(0, $lh, "", 0, 1, "C");
        $pdf->SetFont('Arial','',10);
        $pdf->MultiCell(155, $lh, str_replace("<p>","\r\n",str_replace('</p>','',str_replace("<br />","\n",$tripdata->details))), 0, "L");
        $pdf->Cell(0, $lh, "", "", 1, "C");
        $pdf->Cell(0, $lh, "", "T", 1, "C");

		if ($final) {
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell(35, $lh, "Trip Comments", 0, 0, "L");
			$pdf->SetFont('Arial','',10);
			$pdf->MultiCell(155, $lh, $tripdata->comment, 0, "L");
	        $pdf->Cell(0, $lh, "", "", 1, "C");
			$pdf->Cell(0, $lh, "", "T", 1, "C");
		}

		// force a page break here
        $app->setUserState('com_gatripsys.triprpt.header','Trip Attendees');
        $pdf->AddPage();

		// list of attendees on the trip
        if ($incl_email ) {
			$pdf->SetFont('Arial','B',10);
	        $pdf->Cell(45, $lh, "Attendee", 0, 0, "L");
	        $pdf->Cell(2, $lh, " ", 0, 0, "L"); // Sets an spacer
	        $pdf->Cell(53, $lh, "Vehicle", 0, 0, "L");
	        $pdf->Cell(2, $lh, " ", 0, 0, "L"); // Sets an spacer
	        $pdf->Cell(20, $lh, "Contact #", 0, 0, "C");
	        $pdf->Cell(2, $lh, " ", 0, 0, "L"); // Sets an spacer
	        $pdf->Cell(45, $lh, "Email", 0, 0, "L");
	        $pdf->Cell(2, $lh, " ", 0, 0, "L"); // Sets an spacer
	        $pdf->Cell(14, $lh, "# in Car", 0, 0, "C");
	        $pdf->Cell(5, $lh, "", 0, 1, "C");
	        $pdf->SetFont('Arial','',8); // font-family, font-weight (B), font-size
     	} else {
			$pdf->SetFont('Arial','B',10);
	        $pdf->Cell(60, $lh, "Attendee", 0, 0, "L");
	        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
	        $pdf->Cell(65, $lh, "Vehicle", 0, 0, "L");
	        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
	        $pdf->Cell(30, $lh, "Contact #", 0, 0, "C");
	        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
	        $pdf->Cell(15, $lh, "# in Car", 0, 0, "C");
	        $pdf->Cell(5, $lh, "", 0, 1, "C");
	        $pdf->SetFont('Arial','',8); // font-family, font-weight (B), font-size
	    }

		$att_cntr = 0;
		foreach ($attendees as $attend) {
			$att_cntr++;
			if($pdf->GetY()+$lh > 260) {
                $pdf->AddPage();
            }

			// rip out quotes from standard profile data
			$primary_contact = str_replace('"','',$attend->primary_contact);
			$vehicle_make = str_replace('"','',$attend->vehicle_make);
			$vehicle_model = str_replace('"','',$attend->vehicle_model);
			$vehicle_rego = str_replace('"','',$attend->vehicle_rego);
			$vehicle_year = str_replace('"','',$attend->vehicle_year);
			$vehicle_trans = str_replace('"','',$attend->vehicle_trans);
			$vehicle_fuel = str_replace('"','',$attend->vehicle_fuel);

	        if ($incl_email ) {
		        $pdf->Cell(45, $lh, $attend->attend_name, 0, 0, "L");
		        $pdf->Cell(2, $lh, " ", 0, 0, "L"); // Sets an spacer
				$x = $pdf->GetX();
				$y = $pdf->GetY();
		        $pdf->MultiCell(65, $lh, $vmake.$vehicle_make." - ".$vmod.$vehicle_model."\n".$vrego.$vehicle_rego." - ".$vyear.$vehicle_year."\n".$vfuel.$vehicle_fuel." - ".$vtrans.$vehicle_trans, 0, "L", false);
				$w = $pdf->GetY();
		        $pdf->SetXY($x + 53, $y);
				$pdf->Cell(2, $lh, " ", 0, 0, "L"); // Sets an spacer
		        $pdf->Cell(20, $lh, $primary_contact, 0, 0, "C");
				$pdf->Cell(2, $lh, " ", 0, 0, "L"); // Sets an spacer
		        $pdf->Cell(45, $lh, $attend->attend_email, 0, 0, "L");
		        $pdf->Cell(2, $lh, " ", 0, 0, "L"); // Sets an spacer
		        $pdf->Cell(14, $lh, $attend->in_party, 0, 0, "C");
		        $pdf->Cell(2, $lh, " ", 0, 0, "L"); // Sets an spacer
		        $pdf->SetFont('ZapfDingbats','', 10);
				$pdf->Cell(3, $lh, "p", 0, 1, "C");
		        $pdf->SetFont('Arial','',8);
		        $x = $pdf->GetX();
		        $pdf->SetXY($x, $w);
				if ($show_comment && !empty($attend->comment)) {
					$pdf->Cell(30, $lh, " ", 0, 0, "L"); // Sets an spacer
					$pdf->MultiCell(160, $lh, '( '.$attend->comment.' )', 0, "L", false);
	    		}
	        } else {
		        $pdf->Cell(60, $lh, $attend->attend_name, 0, 0, "L");
		        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
				$x = $pdf->GetX();
				$y = $pdf->GetY();
		        $pdf->MultiCell(65, $lh, $vmake.$vehicle_make." - ".$vmod.$vehicle_model."\n".$vrego.$vehicle_rego." - ".$vyear.$vehicle_year."\n".$vfuel.$vehicle_fuel." - ".$vtrans.$vehicle_trans, 0, "L", false);
				$w = $pdf->GetY();
		        $pdf->SetXY($x + 65, $y);
		        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
		        $pdf->Cell(30, $lh, $primary_contact, 0, 0, "C");
		        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
		        $pdf->Cell(15, $lh, $attend->in_party, 0, 0, "C");
		        $pdf->Cell(3, $lh, " ", 0, 0, "L"); // Sets an spacer
		        $pdf->SetFont('ZapfDingbats','', 10);
				$pdf->Cell(2, $lh, "p", 0, 1, "C");
		        $pdf->SetFont('Arial','',8);
		        $x = $pdf->GetX();
		        $pdf->SetXY($x, $w);
		        if ($show_comment && !empty($attend->comment)) {
					$pdf->Cell(30, $lh, " ", 0, 0, "L"); // Sets an spacer
					$pdf->MultiCell(160, $lh, '( '.$attend->comment.' )', 0, "L", false);
	    		}
			}

    		// set up if waitlist needed
            if($tripdata->max_no && $att_cntr == $tripdata->max_no && $att_cntr <= $attendCount) {
		        $pdf->Cell(0, $lh, "", "T", 1, "C");
		        $pdf->SetFillColor(70,130,180);
                //$pdf->SetDrawColor(25,25,12);
                //$pdf->SetTextColor(255,255,255);
                $pdf->Cell(190, $lh, Text::_('COM_GATRIPSYS_WAITLIST'), 0, 1, "C");
		        $pdf->SetFillColor(255,255,255);
		        $pdf->Cell(0, $lh, "", "T", 1, "C");
            }

  		} // end foreach

        $pdf->Ln(4);
        $pdf->SetFont('Arial','',8);
        $pdf->SetTextColor(255,0,0); // r,g,b
        $pdf->MultiCell(190, $lh, $checkbox_pers, 0, "C", false);
        $pdf->SetTextColor(64,64,64); // r,g,b
        $pdf->SetFont('Arial','',10);
        $pdf->Ln(4);

		if ($final) {
			
			if ($incid_cntr) {
		        $app->setUserState('com_gatripsys.triprpt.header','Trip Incidents');
				$cntr=0;
				// force a page break here
		        $pdf->AddPage();
	
				foreach ($incidents as $incident) {
					
					$cntr++;

			        $pdf->SetFont('Arial','B',10);
			        $pdf->Cell(30, $lh, "Author", 0, 0, "L");
			        $pdf->SetFont('Arial','',8);
			        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
			        $pdf->Cell(60, $lh, $incident->user_id_name, 0, 0, "L");
			        $pdf->SetFont('Arial','B',10);
			        $pdf->Cell(30, $lh, "Incident Date", 0, 0, "L");
			        $pdf->SetFont('Arial','',8);
			        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
			        $pdf->Cell(60, $lh, $incident->incid_date_display, 0, 1, "L");
			        $pdf->Cell(0, $lh, "", "", 1, "C");
	
			        $pdf->SetFont('Arial','B',10);
			        $pdf->Cell(30, $lh, "Map Reference", 0, 0, "L");
			        $pdf->SetFont('Arial','',8);
			        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
			        $pdf->Cell(155, $lh, $incident->map_ref, 0, 1, "L");
			        $pdf->Cell(0, $lh, "", "", 1, "C");
			        $pdf->SetFont('Arial','B',10);
			        $pdf->Cell(30, $lh, "GPS Reference", 0, 0, "L");
			        $pdf->SetFont('Arial','',8);
			        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
			        //$pdf->Cell(155, $lh, '<a href="https://www.google.com.au/maps?t=h&q=loc:'.$incident->gps_ref.'&z=17" target="_blank">'.$incident->gps_ref.'</a>', 0, 1, "L");
			        $pdf->Cell(155, $lh, $incident->gps_ref, 0, 1, "L");
			        $pdf->Cell(0, $lh, "", "", 1, "C");

			        $pdf->SetFont('Arial','B',10);
			        $pdf->Cell(30, $lh, "Location", 0, 0, "L");
			        $pdf->SetFont('Arial','',8);
			        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
			        $location = str_replace('</p>', " \r\n", $incident->location);
			        $location = str_replace('<br />', " \r\n", $location);
			        $location = strip_tags($location);
					$pdf->MultiCell(155, $lh, $location, 0, "L");
			        $pdf->Cell(0, $lh, "", "", 1, "C");
	
			        $pdf->SetFont('Arial','B',10);
			        $pdf->Cell(30, $lh, "Persons Involved", 0, 0, "L");
			        $pdf->SetFont('Arial','',8);
			        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
			        $pers_involved = str_replace('</p>', " \r\n", $incident->pers_involved);
			        $pers_involved = str_replace('<br />', " \r\n", $pers_involved);
			        $pers_involved = strip_tags($pers_involved);
					$pdf->MultiCell(155, $lh, $pers_involved, 0, "L");
			        $pdf->Cell(0, $lh, "", "", 1, "C");
	
			        $pdf->SetFont('Arial','B',10);
			        $pdf->Cell(30, $lh, "Witnesses", 0, 0, "L");
			        $pdf->SetFont('Arial','',8);
			        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
			        $witnesses = str_replace('</p>', " \r\n", $incident->witnesses);
			        $witnesses = str_replace('<br />', " \r\n", $witnesses);
			        $witnesses = strip_tags($witnesses);
					$pdf->MultiCell(155, $lh, $witnesses, 0, "L");
			        $pdf->Cell(0, $lh, "", "", 1, "C");
	
			        $pdf->SetFont('Arial','B',10);
			        $pdf->Cell(30, $lh, "Description", 0, 0, "L");
			        $pdf->SetFont('Arial','',8);
			        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
			        $comment = str_replace('</p>', " \r\n", $incident->comment);
			        $comment = str_replace('<br />', " \r\n", $comment);
			        $comment = strip_tags($comment);
					$pdf->MultiCell(155, $lh, $comment, 0, "L");
			        $pdf->Cell(0, $lh, "", "", 1, "C");
	
			        $pdf->Cell(0, $lh, "", "", 1, "C");
			        $pdf->Cell(0, $lh, "", "T", 1, "C");
			        
			        if ($cntr < $incid_cntr ) {
						$pdf->AddPage();
					}
				}
			}
  		}

        $pdf->Ln(4);

        $path = Path::clean( JPATH_SITE . '/images/trips' );
        if ($final) {
			$pdf->Output($path . "/Trip".$trip."_final.pdf", "F");
        	GanotificationsHelper::finaliseTripCoord($tripdata);
		} else {
			$pdf->Output($path . "/Trip".$trip.".pdf", "F");
		}

		return true;
	}

}
