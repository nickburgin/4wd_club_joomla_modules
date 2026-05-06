<?php
/*
 * ------------------------------------------------------------------------
 * @version     5.3
 * @package     pkg_gacalevents
 * @subpackage  mod_gacalevents
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * Author:      Glenn Arkell
 * Websites:    https://www.glennarkell.com.au
 * ------------------------------------------------------------------------
*/
// no direct access
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$app = Factory::getApplication();
$wa = $app->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('mod_gacalevents', 'mod_gacalevents/style.css');

$disp_events = $params->get('disp_events', 0);
$disp_mthonly	= $params->get('disp_mthonly', 0);
$details_len = $params->get('details_len', 0);

$moduleclass_sfx	= $params->get('moduleclass_sfx', '');
$hcolour = $params->get('header_colour');
$ht = $params->get('header_tag');
$set_listheight = $params->get('set_listheight');
$list_height = $params->get('list_height');

?>
<?php if ($set_listheight) : ?>
    <style>
        div.events-inner {
    		max-height: <?php echo $list_height; ?>px;
    		overflow:hidden;
    		overflow-y:scroll;
    	}
    	div.narrow-content {
    		padding-right: 0.6em;
    	}
    </style>
<?php endif ; ?>
<?php if ($hcolour) : ?>
    <style>
        h5.events {
    		color: <?php echo $hcolour; ?>;
    	}
    </style>
<?php endif ; ?>

<?php if ($disp_events) : ?>
<div class="mod_gaevents" >

    <div id="events<?php echo $module->id; ?>">

		<div class="events-inner">
			<div class="narrow-content">
                <?php foreach ($items as $event) : ?>
                    <?php
                        if ($disp_mthonly) {
                            $depart_date = HTMLHelper::date($event->depart_date, Text::_('MOD_GACALEVENTS_DISPLAY_DATE_MONTH'));
                        } else {
                            $depart_date = HTMLHelper::date($event->depart_date, Text::_('MOD_GACALEVENTS_DISPLAY_DATE'));
                        }
                        // count length of details info
                        $lenDetails = strlen($event->event_details);
                        if ($details_len && $lenDetails >= $details_len) {
                            $event->edetails = substr($event->edetails,-4) == '</p>' ? substr($event->edetails,0,-4) : $event->edetails;
                            $event->edetails = substr($event->edetails,-3) == '</p' ? substr($event->edetails,0,-3) : $event->edetails;
                            $event->edetails = substr($event->edetails,-2) == '</' ? substr($event->edetails,0,-2) : $event->edetails;
                            $event->edetails = substr($event->edetails,-1) == '<' ? substr($event->edetails,0,-1) : $event->edetails;
                            $details = $event->edetails.' . . .</p>';
                         } else {
                            $details = $event->event_details;
                         }
                    ?>
    				<h5 class="events"><?php echo $depart_date; ?></h5>
    		        <p><strong><?php echo $event->title; ?></strong><br /><?php echo $event->brief_desc; ?></p>
					<?php echo $details; ?>
    	        <?php endforeach; ?>
	        </div>
        </div>

	</div>
</div>
<?php endif ; ?>
