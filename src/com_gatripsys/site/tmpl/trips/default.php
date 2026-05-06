<?php
/**
 * @version    5.1.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Date\Date;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Access\Access;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GainvoiceHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GamodalHelper;

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gatripsys.gatripsyspreset');

// Load language file
$lang = Factory::getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR, 'en-GB', true);
$lang->load('com_gatripsys', JPATH_SITE, 'en-GB', true);

$tc_group = $this->params->get('trip_coord');
$admin_id = $this->params->get('admin_id');
$block_book_full = $this->params->get('block_book_full');
$hlite_book_full = $this->params->get('hlite_book_full');
$block_regby = $this->params->get('block_regby');
$show_editor = $this->params->get('show_editor', 0);
$editor_gp = $this->params->get('editor_gp', 0);
$mship_single = $this->params->get('mship_single', 0);
$disp_daysago = $this->params->get('disp_daysago', 0);

$todaysDate = GatripsysHelper::getTodaysDate();
//$todaysDate = HtmlHelper::date($todaysDate, Text::_('COM_GATRIPSYS_NORMAL_DATE'));
$user       = GatripsysHelper::getSpecificUser();

$listOrder  = $this->state->get('list.ordering', 'a.dept_date');
$listDirn   = $this->state->get('list.direction', 'asc');

$canCreate  = $user->authorise('core.create', 'com_gatripsys');
$canEdit    = $user->authorise('core.edit', 'com_gatripsys');
$canEditOwn    = $user->authorise('core.edit.own', 'com_gatripsys');
$canCheckin = $user->authorise('core.manage', 'com_gatripsys');
$canChange  = $user->authorise('core.edit.state', 'com_gatripsys');
$canDelete  = $user->authorise('core.delete', 'com_gatripsys');

$canTrip  = $user->authorise('core.trip', 'com_gatripsys');
$canTrip = ($canTrip || $admin_id == $user->id) ? 1 : 0;

$canLead = 0;

$canAdmin = (in_array($tc_group,$user->groups, FALSE)) ? 1 : 0;
$canAdmin = (!$canAdmin && $admin_id == $user->id) ? 1 : 0;
$canEditor = (in_array($editor_gp,$user->groups, FALSE)) ? 1 : 0;

$path = 'images/trips/Trip';

// get the current date-time based on timezone
$today = new Date();
// set date based on parameter setting - if 0, then just trips 5 days old or younger
if ($disp_daysago) {
	$today5 = new Date( 'now -' . $disp_daysago . ' day');
} else {
	$today5 = $today;
}

// clear the any previously viewed trip id - used in modal views
Factory::getApplication()->setUserState('com_gatripsys.view.trip.id',null);
$menu = Factory::getApplication()->getUserState('com_gatripsys.menuitem.id');

/*
echo '<pre>Test<br />';
print_r($this->items);
echo '</pre>';
print_r(Factory::getApplication()->getUserState('com_gatripsys.test.data'));
*/
?>

<h2><?php echo $this->params->get('page_heading'); ?></h2>

<p><span style="color:<?php echo $this->params->get('ht_color'); ?>;"><?php echo $this->params->get('header_text'); ?></span></p>

<form action="<?php echo Route::_('index.php?option=com_gatripsys&view=trips'); ?>" 
      name="adminForm" id="adminForm" class="com-content-category__articles">

	<?php /* echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); */ ?>
    <?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>

    <?php if(empty($this->items)) : ?>
        <?php echo Text::_('COM_GATRIPSYS_NO_ITEMS'); ?>
    <?php else : ?>
        <div class="table-responsive com-contact-categories categories-list">
    	<table class="table table-striped" id="tripList">
    		<thead>
    		<tr>
    			<th class='small'>
    				<?php echo Text::_('COM_GATRIPSYS_TRIPS_TITLE'); ?>
    			</th>
    			<th class='small hidden-phone'>
    				<?php echo Text::_('COM_GATRIPSYS_TRIPS_RATING'); ?>
    			</th>
    			<th class='small hidden-phone'>
    				<?php echo Text::_('COM_GATRIPSYS_STATE'); ?>
    			</th>
    			<th class='small'>
    				<?php echo Text::_('COM_GATRIPSYS_TRIPS_LEADER'); ?>
    			</th>
    			<th class='small hidden-phone'>
    				<?php echo Text::_('COM_GATRIPSYS_TRIPS_REGBY_DATE'); ?>
    			</th>
    			<th class='center small hidden-phone'>
    				<?php echo Text::_('COM_GATRIPSYS_TRIPS_MINMAX'); ?>
    			</th>
    			<th class='center small'>
    				<?php echo HTMLHelper::_('grid.sort',  'COM_GATRIPSYS_TRIPS_DEPT_DATE', 'a.dept_date', $listDirn, $listOrder); ?>
    			</th>
    			<th class='center small'>
    				<?php echo Text::_('COM_GATRIPSYS_TRIPS_RET_DATE'); ?>
    			</th>
    			<th class="center small hidden-phone">
    				<?php echo Text::_('COM_GATRIPSYS_TRIPS_ACTIONS'); ?>
    			</th>
    
    		</tr>
    		</thead>
    		<tfoot>
    		<tr>
    			<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
    				<?php echo $this->pagination->getListFooter(); ?>
    			</td>
    		</tr>
    		</tfoot>
    		<tbody>
    		<?php
               	// Trip Status verses filter option
        		// 1=Proposed and needs to be approved by trip coord,
        		// 2=Bookings - Approved and taking Bookings - waiting for trip leader to close trip,
        		// 3=Trip in progress - not used except in filtering (using dates and status of 2),
        		// 4=Closed and waiting on trip-coord to finalise,
        		// 5=Cancelled,
        		// 6=Finalised or completed,
        		// 0=Unpublished - not used?
    			foreach ($this->items as $i => $item) :
    
    				$canLead = $item->leader == $user->id;

                    if (!$canEdit && $canEditOwn) {
    					$canEdit = ($user->id == $item->created_by || $item->leader == $user->id);
    				}

    				// check if user can see this record
    				if (!$canEdit && $item->state == 1) { continue; }

                    $leader_phone = str_replace('"', '', $item->leader_phone ?? '');
    				$link_phone = str_replace(' ', '', $leader_phone ?? '');
    				$finalised = false; 
                    $booking = true;
                    $triprpt = $path . str_pad($item->id, 6, '0', STR_PAD_LEFT) .'.pdf';
    
    				// get the status name
    				$state = GatripsysHelper::getStatusName($item->state);

					if ($item->state == 2) {
						if ($item->regby_date < substr($todaysDate,0,10) && $item->dept_date >= substr($todaysDate,0,10) && $block_regby) {
							$state = 'Pre-Depart';
							$booking = false;
						} elseif ($item->dept_date < substr($todaysDate,0,10) && $block_regby) {
							$state = 'Post-Depart';
							$booking = false;
						}
					} elseif ($item->state == 6) { 
                        $finalised = true;
    				}

    				$cat_params = json_decode($item->cat_params ?? '');
    
    				// get attendees of each trip and calc if full
    				$attends = GatripsysHelper::getAttendeeCount($item->id);
    				if (!empty($attends)) {
                        $trip_full = ($item->max_no && $attends->vehicles >= $item->max_no) ? 1 : 0;
        				$trip_full = (!$trip_full && $item->max_people && $attends->persons >= $item->max_people) ? 1 : $trip_full;
                        $vehicles = $attends->vehicles;
                        $persons = $attends->persons;
    				} else {
                        $vehicles = 0;
                        $persons = 0;
                        $trip_full = false;
                    }                
    				$tripClass = ($hlite_book_full && $trip_full) ? ' red' : '';

    				$blockBook = ($trip_full && $block_book_full) ? 1 : 0;

    				$pastRego = $block_regby && $item->regby_date < substr($todaysDate,0,10) ? 1 : 0;

    				// Check for mship name
    				if ($mship_single) {
    					$member = GainvoiceHelper::breakdownNamesFromUserID($item->leader);
    					$item->leader_name = GainvoiceHelper::combineNames($member);
    				}
    				$attendees = GatripsysHelper::getTripAttendees($item->id);
    				$att_names = '';
    				foreach ($attendees as $att) {
    					if ($mship_single) {
    						$member = GainvoiceHelper::breakdownNamesFromUserID($att->user_id);
    						$attend_name = GainvoiceHelper::combineNames($member);
    					} else {
    						$attend_name = $att->attend_name;
    					}
    					$att_names .= $attend_name." (".$att->in_party.")\r\n";
    				}
    
    		        // setup the booking modal parameters
                    $bhtml = GamodalHelper::setupModalButton('task', 'attendeeform.edit', 'trip_id', $item->id, 'modal', 'myModal', 'success', '', 'COM_GATRIPSYS_BOOK_ON_TRIP', 'fas fa-user', $user->name);

    				$viewLink = GatripsysHelper::getHTTPQuery(null, 'view', 'trip', 'id', $item->id);
    				$editLink = GatripsysHelper::getHTTPQuery(null, 'task', 'tripform.edit', 'id', $item->id);

    				$pubLink = GatripsysHelper::getHTTPQuery(null, 'task', 'trip.publish', 'id', $item->id);
    				$apprvLink = GatripsysHelper::getHTTPQuery($pubLink, null, null, 'state', 2);
    				$closeLink = GatripsysHelper::getHTTPQuery($pubLink, null, null, 'state', 4);
    				$canLink = GatripsysHelper::getHTTPQuery($pubLink, null, null, 'state', 5);
    				$finLink = GatripsysHelper::getHTTPQuery($pubLink, null, null, 'state', 6);
    			?>

    			<tr class="row<?php echo $i % 2; ?>">
    
    				<td class="trips small">
    					<?php if (isset($item->checked_out) && $item->checked_out) : ?>
    						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'trips.', $canCheckin); ?>
    					<?php endif; ?>
    					<a href="<?php echo Route::_('index.php?'.http_build_query($viewLink, '', '&amp;')); ?>">
    						<span title="<?php echo $att_names; ?>"><?php echo $this->escape($item->title); ?></span>
    					</a>
    					<?php if ($item->trip_cost > '0.00') : ?>
    						<br /><span class="small" style="color:green;"><em><?php echo '( Cost = $'.number_format($item->trip_cost,2).' )'; ?></em></span>
    					<?php endif; ?>
    				</td>
    				<td class="trips small hidden-phone<?php echo $tripClass; ?>">
    					<?php if ($item->rating) : ?>
    						<?php if ($cat_params->image != '') : ?>
    							<img src="<?php echo $cat_params->image; ?>" style="width:14px;" 
                                    alt="<?php echo $cat_params->image_alt; ?>"
                                    title="<?php echo$cat_params->image_alt; ?>"/>
    						<?php else : ?>
    							<?php echo $cat_params->image_alt; ?>
    						<?php endif; ?>
    					<?php else : ?>
    						Not<br />Rated
    					<?php endif; ?>
    				</td>
    				<td class="trips small hidden-phone<?php echo $tripClass; ?>">
    					<?php echo $state; ?>
    				</td>
    				<td class="trips small<?php echo $tripClass; ?>">
    					<?php echo $item->leader_name; ?><br /><a href="tel:<?php echo $link_phone; ?>" alt=""><?php echo $leader_phone; ?></a>
    				</td>
    				<td class="trips small hidden-phone<?php echo $tripClass; ?>">
                        <?php echo !empty($item->regby_date) ? HTMLHelper::date($item->regby_date, Text::_('COM_GATRIPSYS_DISPLAY_DATETXT')) : ''; ?>
    				</td>
    				<td class="center trips small hidden-phone<?php echo $tripClass; ?>">
    					<span title="<?php echo '( '.$vehicles.' Vehicles & '.$persons.' People)'; ?>">
    						<?php 
                                $maxVeh = $item->max_no == 0 ? 'No Vehs Limit' : 'Vehs: '.$item->min_no.' - '.$item->max_no;
                                $maxPer = $item->max_people == 0 ? 'No Pers Limit' : 'Pers: '.$item->max_people;
                                echo $maxVeh.'<br />'.$maxPer;
                            ?>
    					</span>
    				</td>
    				<td class="trips small<?php echo $tripClass; ?>">
    					<?php echo !empty($item->dept_date) ? HTMLHelper::date($item->dept_date, Text::_('COM_GATRIPSYS_DISPLAY_DATETXT')) : ''; ?>
    				</td>
    				<td class="trips small<?php echo $tripClass; ?>">
    					<?php echo !empty($item->ret_date) ? HTMLHelper::date($item->ret_date, Text::_('COM_GATRIPSYS_DISPLAY_DATETXT')) : ''; ?>
    				</td>
    
                    <?php 	// Trip Status -
                    		// 1=Proposed and needs to be approved by trip coord,
                    		// 2=Bookings - Approved and taking Bookings - waiting for trip leader to close trip,
                    		// 3=Trip in progress - not used except in filtering (using dates and status of 2),
                    		// 4=Closed and waiting on trip-coord to finalise,
                    		// 5=Cancelled,
                    		// 6=Finalised or completed,
                    		// 0=Unpublished - not used?
            		 ?>
    				<?php /* setup relevant actions available */ ?>
                    <td class="center trips small hidden-phone">
        				<?php /* -----------  Approved Trip - can book  ---------------   */ ?>
                        <?php if ($item->state == 2):  /* Bookings are allowed */ ?>
        					<?php if ($item->ret_date >= substr($todaysDate,0,10) && !$blockBook && $booking && !$pastRego ):  /* Bookings are allowed */ ?>
                                <?php echo $bhtml; ?>
                            <?php endif; ?>
        				<?php endif; ?>
    
        				<?php /* -----------  Approved manager of trip  ---------------   */ ?>
                        <?php if (($item->state == 1 || $item->state == 2) && ($canEdit || $canLead || $canTrip || $canAdmin)): ?>
        					<a href="<?php echo Route::_('index.php?'.http_build_query($editLink, '', '&amp;'), false); ?>"
        						class="btn btn-secondary" type="button" title="<?php echo Text::_('COM_GATRIPSYS_EDIT_ITEM'); ?>"><i class="icon-edit" ></i>
        					</a>

        					<?php if ($item->state == 1 && ($canTrip || $canAdmin)): ?>
        						<a href="<?php echo Route::_('index.php?'.http_build_query($apprvLink, '', '&amp;'), false); ?>"
        							class="btn btn-info" type="button" title="<?php echo Text::_('COM_GATRIPSYS_APPROVE_TRIP'); ?>"><i class="icon-publish" ></i>
        						</a>
        					<?php endif; ?>

        					<a href="<?php echo Route::_('index.php?'.http_build_query($canLink, '', '&amp;'), false); ?>"
        						class="btn btn-danger" type="button" title="<?php echo Text::_('COM_GATRIPSYS_CANCEL_TRIP'); ?>"><i class="icon-trash" ></i>
        					</a>
        				<?php endif; ?>
    
                		<?php if ($canLead || $canTrip || $canAdmin): ?>
    						<?php if ($item->state == 2 && $item->ret_date < $today5): ?>
    							<a href="<?php echo Route::_('index.php?'.http_build_query($closeLink, '', '&amp;'), false); ?>"
    								class="btn btn-secondary" type="button" title="<?php echo Text::_('COM_GATRIPSYS_CLOSE_TRIP'); ?>">
                                    <i class="fas fa-door-closed" ></i>
    							</a>
    						<?php endif; ?>
    
    						<?php if ($item->state == 4 && !$finalised && $canTrip):  /* Trips in progress - highlight showing closed but not finalised */ ?>
    							<a href="<?php echo Route::_('index.php?'.http_build_query($finLink, '', '&amp;'), false); ?>"
    								type="button" class="btn btn-warning" title="<?php echo Text::_('COM_GATRIPSYS_NEEDS_FINAL'); ?>" >
                                    <i class="icon-warning-2"></i>
        						</a>
    						<?php endif; ?>
    					<?php endif; ?>
    				</td>
    
    			</tr>
    
    			<?php
    				/* This resets the flags for the next record. */
    				$canEdit    = $user->authorise('core.edit', 'com_gatripsys');
    			?>
    
    		<?php endforeach; ?>
    		</tbody>
    	</table>
    	</div>
    	<?php if ($hlite_book_full) : ?>
    		<p class="small"><em>Note: </em> Highlighted trips indicate the max number of participants has been reached.</p>
    	<?php endif; ?>
	<?php endif; ?>
	<?php if ($canCreate) : ?>
		<div style="clear:all;margin-top:20px;">
            <a href="<?php echo Route::_('index.php?option=com_gatripsys&task=tripform.edit&id=0', false); ?>"
    		    class="btn btn-success"><i class="icon-plus"></i> <?php echo Text::_('COM_GATRIPSYS_ADD_NEW_TRIP'); ?>
    		</a>
        	<?php if ($show_editor && $canEditor) : ?>
        		<a href="<?php echo Route::_('index.php?option=com_gatripsys&view=trips&layout=default_editor'); ?>" class="btn btn-warning"><i class="icon-search"></i>
        			<?php echo Text::_('COM_GATRIPSYS_SHOW_EDITOR'); ?>
        		</a>
        	<?php endif; ?>
		</div>
	<?php endif; ?>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

