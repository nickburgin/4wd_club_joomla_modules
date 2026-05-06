<?php
/**
 * @version    5.3.0
 * @package    com_gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Layout\LayoutHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GainvoiceHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GamodalHelper;

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gatripsys.gatripsyspreset');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR);
$lang->load('com_gatripsys', JPATH_SITE);

$this->item->today = substr(GatripsysHelper::getTodaysDate(), 0, 10);
$this->item->status_name = GatripsysHelper::getStatusName($this->item->state);

$this->item->admin_id = $this->params->get('admin_id');

$trip_in_prog = ($this->item->today > $this->item->dept_date && $this->item->ret_date > $this->item->today) ? true : false;
$trip_past = ($this->item->today > $this->item->ret_date) ? true : false;

$user       = GatripsysHelper::getSpecificUser();
$this->item->canEdit = $user->authorise('core.edit', 'com_gatripsys');
if (!$this->item->canEdit && $user->authorise('core.edit.own', 'com_gatripsys')) {
	$this->item->canEdit = $user->id == $this->item->created_by;
}
if (!$this->item->canEdit) {
	$this->item->canEdit = $user->id == $this->item->leader;
}
$this->item->canInvoice  = $user->authorise('core.invoice', 'com_gatripsys');
$this->item->canTrip  = $user->authorise('core.trip', 'com_gatripsys');
$this->item->canTrip = ($this->item->canTrip || $this->item->admin_id == $user->id ) ? 1 : 0;

$this->item->canLead = (($user->id == $this->item->leader) || $this->item->canTrip) ? 1 : 0;

$this->item->integ_calendar = $this->params->get('integ_calendar');
$this->item->show_comment = $this->params->get('show_comment');

$this->item->trip = 'images/trips/Trip'.str_pad($this->item->id, 6, '0', STR_PAD_LEFT) . '.pdf';
$this->item->tripf = 'images/trips/Trip'.str_pad($this->item->id, 6, '0', STR_PAD_LEFT) . '_final.pdf';
$this->item->tripplandir = $this->params->get( 'tripplan_dir', 'images/trips/trip_plans' );
$this->item->block_book_full = $this->params->get('block_book_full');
$this->item->waitlist_avail = $this->params->get('waitlist_avail', 0);
$this->item->waitlist_all = $this->params->get('waitlist_all', 0);
$this->item->hlite_book_full = $this->params->get('hlite_book_full');
$this->item->block_regby = $this->params->get('block_regby');
$this->item->manual_inv = $this->params->get('manual_inv', 0);
$this->item->mship_single = $this->params->get('mship_single', 0);
$this->item->invOnApproval = $this->params->get('inv_approval', 0);
$this->item->exclude_email = $this->params->get('exclude_email', 'noemail');

// get attendees of each trip and calc if full
$this->item->attends = GatripsysHelper::getAttendeeCount($this->item->id);
$attends = isset($this->item->attends) ? $this->item->attends : 0;
$this->item->maxedVeh = $this->item->max_no && $attends && $attends->vehicles >= $this->item->max_no ? 1 : 0;
$this->item->maxedPer = $this->item->max_people && $attends && $attends->persons >= $this->item->max_people ? 1 : 0;
$this->item->trip_full = $this->item->maxedVeh || $this->item->maxedPer ? 1 : 0;
$this->item->tripClass = ($this->item->hlite_book_full && $this->item->trip_full) ? ' red' : '';

// calculate if booking is allowed
$this->item->blockBook = $this->item->trip_full && $this->item->block_book_full && !$this->item->waitlist_avail ? 1 : 0;
$this->item->blockDate = $this->item->block_regby && $this->item->regby_date < $this->item->today ? 1 : 0;
if ($this->item->state == 2) {
    if ($this->item->canTrip || $this->item->canLead) {
        $showBookBtn = 1;
    } else {
        if ($this->item->blockBook) {
            $showBookBtn = 0;
        } else {
            if ($this->item->blockDate) {
                $showBookBtn = 0;
            } else {
                $showBookBtn = 1;
            }
        }
    }
} else {
    $showBookBtn = 0;
}
//echo '<pre>Test<br />'; print_r($showBookBtn); echo '</pre>';

$this->item->email_collection = 'mailto:'.$user->email.'?bcc=';

// set the trip id to session for use in modal views
Factory::getApplication()->setUserState('com_gatripsys.view.trip.id',$this->item->id);

if (is_file($this->item->trip)) { $this->item->agendaExists = true; } else { $this->item->agendaExists = false; }
if (is_file($this->item->tripf)) { $this->item->finalExists = true; } else { $this->item->finalExists = false; }

if ($this->item->mship_single) {
	$this->item->member = GainvoiceHelper::breakdownNamesFromUserID($this->item->leader);
	$this->item->leader_name = GainvoiceHelper::combineNames($this->item->member);
}
// --------- Set up all the links and buttons  -------------
// setup the booking modal parameters
$bhtml = GamodalHelper::setupModalButton('view', 'attendeeform', 'trip_id', $this->item->id, 'modal', 'myModal', 'success', 'COM_GATRIPSYS_BOOK_ON_TRIP', 'COM_GATRIPSYS_BOOK_ON_TRIP', 'fas fa-user', '');
// setup the incident modal parameters
$ihtml = GamodalHelper::setupModalButton('view', 'incidentform', 'trip_id', $this->item->id, '', 'myIncidModal', 'incident', 'COM_GATRIPSYS_TITLE_INCIDENT', 'COM_GATRIPSYS_TITLE_INCIDENT', 'icon-lightning', '');
// setup the comment modal parameters
$chtml = GamodalHelper::setupModalButton('view', 'tripform', 'id', $this->item->id, 'default_comment', 'myCommModal', 'success', 'COM_GATRIPSYS_ADD_COMMENT', 'COM_GATRIPSYS_ADD_COMMENT', 'icon-file-2', '');

// set up Trip Buttons
$stateLink = GatripsysHelper::getHTTPQuery(null, 'task', 'trip.publish', 'id', $this->item->id);
$apprvLink = GatripsysHelper::getHTTPQuery($stateLink, null, null, 'state', '2');
$closeLink = GatripsysHelper::getHTTPQuery($stateLink, null, null, 'state', '4');
$canLink = GatripsysHelper::getHTTPQuery($stateLink, null, null, 'state', '5');
$editLink = GatripsysHelper::getHTTPQuery(null, 'task', 'tripform.edit', 'id', $this->item->id);

$rptLink = GatripsysHelper::getHTTPQuery(null, 'task', 'trip.genRpt', 'id', $this->item->id);
$rpt1Link = GatripsysHelper::getHTTPQuery($rptLink, null, null, 'rpt', '1');
$rpt0Link = GatripsysHelper::getHTTPQuery($rptLink, null, null, 'rpt', '0');

$delRptLink = GatripsysHelper::getHTTPQuery(null, 'task', 'trip.deletePDF', 'id', $this->item->id);
$del1Link = GatripsysHelper::getHTTPQuery($delRptLink, null, null, 'rpt', '1');
$del0Link = GatripsysHelper::getHTTPQuery($delRptLink, null, null, 'rpt', '0');

?>
<?php if ($user->id > 0) : ?>
	<div class="item_fields">
		<h2>
            <?php echo $this->item->title; ?>
			<?php if (!empty($this->item->status_name)) { echo ' &nbsp; <span class="small">(Status: '.$this->item->status_name.')</span>'; } ?>
        </h2>

		<table class="table">

			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_TRIP_RATING'); ?></th>
				<td>
					<?php if ($this->item->rating) : ?>
						<?php $cat_params = json_decode($this->item->cat_params); ?>
						<?php echo '<img src="'.$cat_params->image.'" style="width:22px;" alt="'.$cat_params->image_alt.'" title="'.$cat_params->image_alt.'"/>'; ?>
			  			<?php echo $this->item->rating_name; ?>
		  			<?php else : ?>
						Not Rated
					<?php endif; ?>
		  		</td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_TRIP_LEADER'); ?></th>
				<?php $leader_phone = str_replace('"', '', $this->item->leader_phone ?? ''); ?>
				<?php $link_phone = str_replace(' ', '', $leader_phone ?? ''); ?>
				<td><?php echo $this->item->leader_name; ?> &nbsp; ( <a href="tel:<?php echo $link_phone; ?>" alt=""><?php echo $leader_phone; ?></a> )</td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_TRIP_TEC'); ?></th>
				<td><?php echo $this->item->tec_name; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_TRIP_TRIP_TYPE'); ?></th>
				<td><?php echo $this->item->trip_type_name; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_TRIP_SUITED_FOR'); ?></th>
				<td><?php echo $this->item->suited_for_name;  ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_TRIPS_MINMAX'); ?></th>
				<td>
                    <?php
                        echo $max_no = $this->item->max_no == 0 ? Text::_('COM_GATRIPSYS_TRIPS_UNLIMTED') : $this->item->min_no.' - '.$this->item->max_no;
                        echo '&nbsp; &nbsp; &nbsp; Max Persons: '.$this->item->max_people;
                    ?>
                </td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_TRIP_REGBY_DATE'); ?></th>
				<td><?php echo $this->item->regby_date_disp; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_TRIP_DEPT_DATE'); ?></th>
				<td><?php echo $this->item->dept_date_disp; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_TRIP_RET_DATE'); ?></th>
				<td><?php echo $this->item->ret_date_disp; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_TRIP_DEPT_LOC'); ?></th>
				<td><?php echo $this->item->dept_loc; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_TRIP_RET_LOC'); ?></th>
				<td><?php echo $this->item->ret_loc; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_TRIP_WHERE_GO'); ?></th>
				<td><?php echo $this->item->where_go; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_TRIP_WHAT_DO'); ?></th>
				<td><?php echo $this->item->what_do; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_TRIP_EQUIP'); ?></th>
				<td><?php echo $this->item->equip; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_TRIP_DETAILS'); ?></th>
				<td><?php echo $this->item->details; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_TRIP_TRIP_IMG'); ?></th>
				<td><?php echo '<img src="'.$this->item->trip_img.'" style="width:110px;" alt="No Image" />'; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_TRIP_TRIP_PLAN'); ?></th>
				<td>
					<?php if (isset($this->item->trip_plan) && $this->item->trip_plan > '' && $this->item->trip_plan != -1) : ?>
						<a href="<?php echo $this->item->tripplandir.'/'.$this->item->trip_plan; ?>" target="_blank" alt="" /><?php echo Text::_('COM_GATRIPSYS_TRIP_PLAN_DOWNLOAD'); ?></a><br />
						<span class="small" style="color:#f00;">(A trip plan exists and it might pay to check it out for more details - save it to your device for easy reference.)</span>
					<?php else : ?>
						No plan available
					<?php endif; ?>
				</td>
			</tr>
			<?php if ($this->params->get('charge_trip') && $this->item->trip_cost > '0.00') : ?>
				<tr>
					<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_TRIP_TRIP_COST'); ?></th>
					<td><?php echo $this->item->trip_cost; ?></td>
				</tr>
			<?php endif; ?>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_TRIP_PUBLIC_VIEW'); ?></th>
				<td><?php if ($this->item->public_view) { echo 'Yes'; } else { echo 'No'; } ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_TRIP_COMMENT'); ?></th>
				<td><?php echo $this->item->comment; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_INSURANCE_CAT'); ?></th>
				<td><?php echo $this->item->insurance_cat_name; ?></td>
			</tr>
			<?php if ($this->item->incidents): ?>
				<tr>
					<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_INCIDENTS'); ?></th>
					<td><h3><?php echo Text::_('COM_GATRIPSYS_INCIDENTS_RAISED'); ?></h3></td>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<td>
						<?php foreach ($this->item->incidents as $i => $incident) : ?>
							<?php echo $incident->id . ' - ' . $incident->user_id_name.' - '.$incident->incid_date_display; ?>
							<?php if (!empty($incident->gps_ref)): ?>
								&nbsp;at 
                                <a href="https://www.google.com.au/maps?t=h&q=loc:<?php echo $incident->gps_ref; ?>&z=17" target="_blank">
                                    <?php echo $incident->gps_ref; ?>
                                </a>
							<?php endif; ?>
							<?php if($this->item->state == 2) : ?>
								<?php if(($this->item->canLead || $this->item->canTrip) && $incident->user_id == $user->id) : ?>
									<a class="btn btn-mini"
										href="<?php echo Route::_('index.php?option=com_gatripsys&task=incidentform.edit&id='.$incident->id); ?>"
										title="<?php echo Text::_('COM_GATRIPSYS_EDIT_INCIDENT'); ?>">
										<i class="icon-edit"></i>
									</a> &nbsp;
									<a class="btn btn-mini btn-danger"
										href="<?php echo Route::_('index.php?option=com_gatripsys&task=incident.remove&id='.$incident->id.'&trip_id='.$this->item->id); ?>"
										title="<?php echo Text::_('COM_GATRIPSYS_DELETE_INCIDENT'); ?>">
										<i class="icon-unpublish"></i>
									</a> &nbsp;
								<?php endif; ?>
							<?php else : ?>
								<i class="icon-locked"></i> <span class="small">(Trip Closed so incident is locked.)</span>
							<?php endif; ?>
							<a class="btn btn-mini btn-info" 
								href="<?php echo Route::_('index.php?option=com_gatripsys&view=incident&id='.$incident->id.'&trip_id='.$this->item->id); ?>"
								title="<?php echo Text::_('COM_GATRIPSYS_VIEW_INCIDENT'); ?>">
								<i class="icon-search"></i>
							</a>
							<br />
						<?php endforeach; ?>
					</td>
				</tr>
			<?php endif; ?>

		</table>
	</div>

    <div class="item_fields">
		<h2>Current Bookings on this trip</h2>
		<?php echo LayoutHelper::render('default_attendees', array('view' => $this->item), dirname(__FILE__)); ?>

	</div>

	<?php /* -----------------------------  Always show the return button  ---------------------------------- */ ?>
	<?php $returnLink = GatripsysHelper::getHTTPQuery(null, 'view', 'trips', null, null); ?>
	<a href="<?php echo Route::_('index.php?'.http_build_query($returnLink, '', '&amp;')); ?>" class="btn btn-warning" type="button" title="Trips List" >
		<i class="icon-undo-2"></i> <?php echo Text::_('Return'); ?>
	</a>

	<?php if (!$this->direct_email_link) : /* this tests if the view is a result of clicking on a link in an email in which case no buttons */ ?>
        <?php
		// Status -
		// 1=Bookings - Approved and taking Bookings - waiting for trip leader to close trip,
		// 2=Closed and waiting on trip-coord to finalise,
		// 3=Proposed and needs to be approved by trip coord,
		// 4=Cancelled,
		// 5=Finalised or completed,
		// 0=Unpublished?
		
		?>
		<?php /* -----------------------------  Status of Published = ready to book  ---------------------------------- */ ?>
		<?php if ($showBookBtn): ?>
			<?php echo $bhtml; ?>
			<?php /* echo $bhtml .= HTMLHelper::_('bootstrap.renderModal', $bmodname, $bookparams); */ ?>
		<?php endif; ?>

		<?php /* -----------------------------  Leader or Co-ord ONLY  ---------------------------------- */ ?>
		<?php if($this->item->canEdit || $this->item->canLead || $this->item->canTrip): ?>
			<?php if($this->item->checked_out == 0 && $this->item->state != 6): ?>
				<a class="btn btn-secondary" href="<?php echo Route::_('index.php?'.http_build_query($editLink, '', '&amp;')); ?>">
					<i class="icon-edit"></i> <?php echo Text::_("COM_GATRIPSYS_EDIT_ITEM"); ?>
				</a>
				<?php if ($this->item->state == 1 && $this->item->canTrip): ?>
					<a href="<?php echo Route::_('index.php?'.http_build_query($apprvLink, '', '&amp;')); ?>" class="btn btn-info" type="button" >
						<i class="icon-publish"></i> <?php echo Text::_('COM_GATRIPSYS_APPROVE_TRIP'); ?>
					</a>
				<?php endif; ?>
			<?php endif; ?>
			<?php /* -----------------------------  Status of Bookings for Leader to Comment, Agenda, Incident or Close ------------------- */ ?>
			<?php if ($this->item->state == 2): ?>
    			<?php echo $chtml; ?>

				<?php if(!$this->item->agendaExists ): ?>
					<a class="btn btn-info" href="<?php echo Route::_('index.php?option=com_gatripsys&task=trip.genRpt&id='.$this->item->id.'&rpt=0'); ?>">
						<i class="icon-print"></i> <?php echo Text::_("COM_GATRIPSYS_GEN_AGENDA"); ?>
					</a>
				<?php else : ?>
					<a class="btn btn-info" href="<?php echo $this->item->trip; ?>" target="_blank" alt="Review Trip Agenda">
						<i class="icon-print"></i> <?php echo Text::_("COM_GATRIPSYS_PRINT"); ?>
					</a>
				<?php endif; ?>
				<?php if($this->item->dept_date < $this->item->today ): ?>
					<?php echo $ihtml; ?>
					<a href="<?php echo Route::_('index.php?'.http_build_query($closeLink, '', '&amp;')); ?>" class="btn btn-info" type="button" >
						<i class="icon-unpublish"></i> <?php echo Text::_('COM_GATRIPSYS_CLOSE_TRIP'); ?>
					</a>
				<?php endif; ?>
			<?php /* -----------------------------  Menu Parameter - In Progress Trips and NOT Closed ---------------------------------- */ ?>
			<?php elseif ($this->item->state == 2 && $trip_in_prog): ?>
				<?php if($this->item->finalExists): ?>
					<a class="btn btn-inverse" href="<?php echo $this->item->tripf; ?>" target="_blank" alt="Review Final Report">
						<i class="icon-search"></i> <?php echo Text::_("COM_GATRIPSYS_PRINT"); ?>
					</a>
				<?php else : ?>
					<a class="btn btn-info" href="<?php echo $this->item->trip; ?>" target="_blank" alt="Review Trip Agenda">
						<i class="icon-print"></i> <?php echo Text::_("COM_GATRIPSYS_PRINT"); ?>
					</a>
				<?php endif; ?>
			<?php /* -----------------------------  Menu Parameter - Past Trips and Closed ---------------------------------- */ ?>
			<?php elseif ($this->item->state == 4 && $trip_past && $this->item->canTrip): ?>
				<?php if(!$this->item->finalExists): ?>
        			<?php echo $chtml; ?>
        			<?php echo $ihtml; ?>
					<a class="btn btn-inverse" href="<?php echo Route::_('index.php?'.http_build_query($rpt1Link, '', '&amp;')); ?>">
						<i class="icon-archive"></i> <?php echo Text::_("COM_GATRIPSYS_GEN_FINAL"); ?>
					</a>
				<?php else : ?>
					<a class="btn btn-inverse" href="<?php echo $this->item->tripf; ?>" target="_blank" alt="Review Final Report">
						<i class="icon-print"></i> <?php echo Text::_("COM_GATRIPSYS_PRINT"); ?>
					</a>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>

		<?php /* -----------------------------  Leader or Co-ord ONLY - Delete Buttons ---------------------------------- */ ?>
		<?php if($this->item->canLead):?>
			<?php if (!$this->item->finalExists) : ?>
				<a class="btn btn-danger pull-right"
					href="<?php echo Route::_('index.php?'.http_build_query($canLink, '', '&amp;')); ?>"
					title="<?php echo Text::_('COM_GATRIPSYS_CANCEL_TRIP'); ?>">
					<i class="icon-trash"></i> <?php echo Text::_("COM_GATRIPSYS_CANCEL_TRIP"); ?>
				</a>
				<?php if ($this->item->agendaExists) : ?>
					<a class="btn btn-info pull-right" style="margin-right: 5px;"
						href="<?php echo Route::_('index.php?'.http_build_query($del0Link, '', '&amp;')); ?>"
						title="<?php echo Text::_('COM_GATRIPSYS_DELETE_FINALPDF_DESC'); ?>">
						<i class="icon-trash"></i> <?php echo Text::_("COM_GATRIPSYS_DELETE_PDF"); ?>
					</a>
				<?php endif; ?>
			<?php else : ?>
				<a class="btn btn-secondary pull-right" style="margin-right: 5px;"
					href="<?php echo Route::_('index.php?'.http_build_query($del1Link, '', '&amp;')); ?>"
					title="<?php echo Text::_('COM_GATRIPSYS_DELETE_FINALPDF_DESC'); ?>">
					<i class="icon-trash"></i> <?php echo Text::_("COM_GATRIPSYS_DELETE_FINALPDF"); ?>
				</a>
			<?php endif; ?>
		<?php endif; ?>
	<?php endif; ?>
	<div class="clearfix"></div>
	<p> </p>
<?php else : ?>
	<?php Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_NOT_AUTHORISED'), 'warning'); ?>
<?php endif; ?>
