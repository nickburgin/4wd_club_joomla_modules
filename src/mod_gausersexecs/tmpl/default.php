<?php
/*
# ------------------------------------------------------------------------
# @version     5.3
# @copyright   Copyright (C) 2014. All rights reserved.
# @license     GNU General Public License version 2 or later; see LICENSE.txt
# Author:      Glenn Arkell
# Websites:    http://www.glennarkell.com.au
# ------------------------------------------------------------------------
*/

// no direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Date\Date;
use \Joomla\CMS\HTML\HTMLHelper;

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('mod_gausersexecs', 'mod_gausersexecs/default.css');

$moduleclass_sfx	= $params->get('moduleclass_sfx', '');
$ht = $params->get('header_tag', 'h4');
$hc = $params->get('header_class', 'center');
$set_listheight = $params->get('set_listheight', 0);
$list_height = $params->get('list_height', 400);
$intro_text = $params->get('intro_text', '');
$show_past = $params->get('show_past', 0);
$show_title = $params->get('showtitle', 0);
$show_pos = $params->get('show_pos', 0);
$show_exec = $params->get('show_exec', 0);
$cntr = 0;

if (!$show_pos) {
    // this means all data shown so get the club info as well
} else {
    // this means only show the positions information - NOT club data
}

/*
echo '<pre>Test<br />';
print_r($execs);
echo '</pre>';
print_r(Factory::getApplication()->getUserState('mod_gausers.test.data'));
*/
?>
<?php if ($set_listheight) : ?>
    <style>
        table.events-inner {
    		max-height: <?php echo $list_height; ?>px;
    	}
    </style>
<?php endif ; ?>

<div class="mod_gausers_execs<?php echo $moduleclass_sfx; ?>">
    <div id="execs<?php echo $module->id; ?>">
		<?php echo $intro_text; ?>

		<?php if (!empty($items) && !$show_pos) : ?>
			<div class="item_fields">
				<table class="table events-inner">
                    <?php if (!empty($items[0]->club_address)) : ?>
                        <tr>
    						<td width="98%">
                                <p><?php echo '<strong>'.Text::_('MOD_GAUSERSEXECS_CLUB_ADDRESS') . '</strong><br />' . $items[0]->club_address . '<br />' . $items[0]->club_suburb . ', ' . $items[0]->club_pcode; ?></p>
                            </td>
    					</tr>
					<?php endif; ?>
                    <?php if (!empty($items[0]->club_phone)) : ?>
    					<tr>
    						<td width="98%">
        						<p><?php echo '<strong>'.Text::_('MOD_GAUSERSEXECS_CLUB_PHONE') . ':</strong> ' . $items[0]->club_phone; ?></p>
                            </td>
    					</tr>
					<?php endif; ?>
                    <?php if (!empty($items[0]->club_email)) : ?>
    					<tr>
    						<td width="98%">
        						<p><?php echo '<strong>'.Text::_('MOD_GAUSERSEXECS_CLUB_EMAIL') . '</strong><br />' . $items[0]->club_email; ?></p>
                            </td>
    					</tr>
					<?php endif; ?>
					<?php if (isset($items[0]->meeting_time) && $items[0]->meeting_time > '00:00:00') : ?>
    					<tr>
    						<td width="98%">
        						<p><?php echo '<strong>'.Text::_('MOD_GAUSERSEXECS_MEETING_TIME') . ' - </strong>' . $items[0]->meeting_time; ?></p>
                            </td>
    					</tr>
					<?php endif; ?>
					<?php if (isset($items[0]->meeting_info) && $items[0]->meeting_info > '') : ?>
    					<tr>
    						<td width="98%">
        						<p><?php echo '<strong>'.Text::_('MOD_GAUSERSEXECS_MEETING_INFO') . '</strong><br />' . $items[0]->meeting_info; ?></p>
                            </td>
    					</tr>
					<?php endif; ?>
					<?php if (isset($items[0]->meeting_address) && $items[0]->meeting_address > '') : ?>
    					<tr>
    						<td width="98%">
        						<p><?php echo '<strong>'.Text::_('MOD_GAUSERSEXECS_MEETING_ADDRESS') . '</strong><br />' . $items[0]->meeting_address; ?></p>
                            </td>
    					</tr>
					<?php endif; ?>
					<?php if (isset($items[0]->comment) && $items[0]->comment > '') : ?>
    					<tr>
    						<td width="98%">
        						<p><?php echo '<strong>'.Text::_('MOD_GAUSERSEXECS_COMMENT') . '</strong><br />' . nl2br($items[0]->comment); ?></p>
                            </td>
    					</tr>
					<?php endif; ?>
                    <?php if (!empty($items[0]->end_term)) : ?>
    					<tr>
    						<th width="98%">
    							<p>Term End Date: <?php echo HTMLHelper::date($items[0]->end_term, Text::_('MOD_GAUSERSEXECS_DISPLAY_DATE'), 'UTC'); ?></p>
    						</th>
    					</tr>
					<?php endif; ?>
					<tr>
						<td width="98%">
							<p>
                            <strong>President: </strong> &nbsp; <?php echo $items[0]->pres_name; ?><br />
							<strong>Vice Pres: </strong> &nbsp; <?php echo $items[0]->vpres_name; ?><br />
							<strong>Secretary: </strong> &nbsp; <?php echo $items[0]->secr_name; ?><br />
							<strong>Treasurer: </strong> &nbsp; <?php echo $items[0]->tres_name; ?><br />
							<?php if (!$show_exec) : ?>
    							<strong>Editor: </strong> &nbsp; <?php echo $items[0]->edit_name; ?><br />
    							<strong>Delegate: </strong> &nbsp; <?php echo $items[0]->delg_name; ?><br />
    							<strong>Regional Rep: </strong> &nbsp; <?php echo $items[0]->regr_name; ?><br />
    							<strong>Instructor Coord: </strong> &nbsp; <?php echo $items[0]->istc_name; ?><br />
							<?php endif; ?>
							</p>
						</td>
					</tr>
				</table>
			</div>
		<?php elseif (!empty($items) && $show_pos) : ?>
			<div class="item_fields">
    			<table class="table events-inner">
                    <?php if ($show_past) : ?>
                        <?php foreach ($items AS $i => $exec) : ?>
        					<tr>
        						<th width="98%">
        							<p>Term End Date: <?php echo HTMLHelper::date($exec->end_term, Text::_('MOD_GAUSERSEXECS_DISPLAY_DATE'), 'UTC'); ?></p>
        						</th>
        					</tr>
        					<tr>
        						<td width="98%">
        							<p>
                                    <strong>President: </strong> &nbsp; <?php echo $exec->pres_name; ?><br />
        							<strong>Vice Pres: </strong> &nbsp; <?php echo $exec->vpres_name; ?><br />
        							<strong>Secretary: </strong> &nbsp; <?php echo $exec->secr_name; ?><br />
        							<strong>Treasurer: </strong> &nbsp; <?php echo $exec->tres_name; ?><br />
        							<?php if (!$show_exec) : ?>
            							<strong>MagEditor: </strong> &nbsp; <?php echo $exec->edit_name; ?><br />
            							<strong>Delegate : </strong> &nbsp; <?php echo $exec->delg_name; ?><br />
            							<strong>Regl Rep : </strong> &nbsp; <?php echo $exec->regr_name; ?><br />
            							<strong>InstCoord: </strong> &nbsp; <?php echo $exec->istc_name; ?><br />
        							<?php endif; ?>
        							</p>
        						</td>
        					</tr>
    					<?php endforeach; ?>
        			<?php else : ?>
    					<tr>
    						<th width="98%">
        						<p>Term End Date: <?php echo HTMLHelper::date($items[0]->end_term, Text::_('MOD_GAUSERSEXECS_DISPLAY_DATE'), 'UTC'); ?></p>
    						</th>
    					</tr>
    					<tr>
    						<td width="98%">
    							<p>
                                <strong>President: </strong> &nbsp; <?php echo $items[0]->pres_name; ?><br />
    							<strong>Vice Pres: </strong> &nbsp; <?php echo $items[0]->vpres_name; ?><br />
    							<strong>Secretary: </strong> &nbsp; <?php echo $items[0]->secr_name; ?><br />
    							<strong>Treasurer: </strong> &nbsp; <?php echo $items[0]->tres_name; ?><br />
    							<?php if (!$show_exec) : ?>
        							<strong>MagEditor: </strong> &nbsp; <?php echo $items[0]->edit_name; ?><br />
        							<strong>Delegate : </strong> &nbsp; <?php echo $items[0]->delg_name; ?><br />
        							<strong>Regl Rep : </strong> &nbsp; <?php echo $items[0]->regr_name; ?><br />
        							<strong>InstCoord: </strong> &nbsp; <?php echo $items[0]->istc_name; ?><br />
    							<?php endif; ?>
    							</p>
    						</td>
    					</tr>
        			<?php endif; ?>
				</table>
			</div>
			
        <?php else : ?>
	        <p>No data present for the club.</p>
        <?php endif; ?>
	</div>
</div>
