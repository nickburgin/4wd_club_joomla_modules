<?php
/**
 * @package     com_gatripsys
 * @subpackage  mod_gatripsys
 * @version     4.0.1
 * @author      Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright   2021 Glenn Arkell
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Module\Gatripsys\Site\Helper\GatripsysHelper;

$lang = Factory::getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR, 'en-GB', true);

$disp_trips = $params->get('disp_trips', 1);
$disp_dates = $params->get('disp_dates', 0);
$dateformat = $params->get('dateformat', 'MOD_GATRIPSYS_DISPLAY_DATETXT');
$trips_history = $params->get('trips_history', 0);

$moduleclass_sfx	= $params->get('moduleclass_sfx', '');
$ht = $params->get('header_tag');
$hc = $params->get('header_class');
$hcolour = $params->get('header_colour');
$set_listheight = $params->get('set_listheight');
$list_height = $params->get('list_height');
$header_txt = $params->get('header_txt');
$contact_email = $params->get('contact_email');

// extra details to include
$inclSuit = $params->get('incl_suit', 0);
$inclEquip = $params->get('incl_equip', 0);
$inclWhere = $params->get('incl_where', 0);
$inclWhat = $params->get('incl_what', 0);

?>

<?php if ($set_listheight) : ?>
    <style>
        div.trips-inner {
		max-height: <?php echo $list_height; ?>px;
		overflow:hidden;
		overflow-y:scroll;
	}
    </style>
<?php endif ; ?>
<?php if ($hcolour) : ?>
    <style>
        h5.trips {
		color: <?php echo $hcolour; ?>;
	}
    </style>
<?php endif ; ?>

<?php if ($disp_trips) : ?>
	<div class="mod_gatripsys" >

	    <div id="trips<?php echo $module->id; ?>">

			<div class="trips-inner">
	            <?php if (!empty($header_txt)) : ?>
		            <?php echo '<p>'.$header_txt.'<br />'; ?>
                    <?php if (!empty($contact_email)) : ?>
                        <?php echo '<a href="mailto:'.$contact_email.'" alt="Contact Email">Contact Email</a></p>'; ?>
                    <?php endif ; ?>
	            <?php endif ; ?>

		        <?php foreach ($trips as $trip) : ?>
			        <h5 class="trips"><?php echo $trip->title; ?></h5>
			        <?php if ($disp_dates) : ?>
						<?php $dept_date = HTMLHelper::date($trip->dept_date, Text::_($dateformat)); ?>
						<?php $ret_date = HTMLHelper::date($trip->ret_date, Text::_($dateformat)); ?>
                        <p class="trips">Depart: <strong><?php echo $dept_date; ?></strong> and <br />Return: <strong><?php echo $ret_date; ?></strong></p>
					<?php endif; ?>

			        <?php if ($inclSuit) : ?>
    			        <p><strong>Trip Type: </strong><?php echo $trip->trip_type_name; ?><br />
                            <strong>Rating: </strong><?php echo $trip->rating_name; ?><br />
                            <strong>Suitable For: </strong><?php echo $trip->suited_for_name; ?>
                        </p>
    				<?php endif; ?>
			        <?php if ($inclEquip && !empty($trip->equip)) : ?>
    			        <p><strong><?php echo Text::_('COM_GATRIPSYS_TITLE_EQUIP'); ?></strong></p>
                        <?php echo $trip->equip; ?>
                        <p> </p>
					<?php endif; ?>
			        <?php if ($inclWhere && !empty($trip->where_go)) : ?>
    			        <p><strong><?php echo Text::_('COM_GATRIPSYS_TITLE_WHERE'); ?></strong></p>
    			        <?php echo $trip->where_go; ?>
                        <p> </p>
					<?php endif; ?>
			        <?php if ($inclWhat && !empty($trip->what_to)) : ?>
    			        <p><strong><?php echo Text::_('COM_GATRIPSYS_TITLE_WHAT'); ?></strong></p>
    			        <?php echo $trip->what_to; ?>
                        <p> </p>
					<?php endif; ?>
                    <hr width="100%">
		        <?php endforeach; ?>

	        </div>

		</div>
	</div>
<?php else : ?>
	<div class="mod_gatripsys" >
	    <div id="trips<?php echo $module->id; ?>">
			<div class="trips-inner">
				<p>Trips flagged not to display</p>
	        </div>
		</div>
	</div>
<?php endif ; ?>
