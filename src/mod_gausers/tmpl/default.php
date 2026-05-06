<?php
/*
# ------------------------------------------------------------------------
# @version     5.1.6
# @copyright   Copyright (C) 2014. All rights reserved.
# @license     GNU General Public License version 2 or later; see LICENSE.txt
# Author:      Glenn Arkell
# Websites:    http://www.glennarkell.com.au
# ------------------------------------------------------------------------
*/
// no direct access
defined('_JEXEC') or die;

$moduleclass_sfx	= $params->get('moduleclass_sfx', '');
$ht = $params->get('header_tag');
$hc = $params->get('header_class');
$set_listheight = $params->get('set_listheight');
$list_height = $params->get('list_height');
$intro_text = $params->get('intro_text');

?>
<?php if ($set_listheight) : ?>
    <style>
        table.events-inner {
		max-height: <?php echo $list_height; ?>px;
		overflow:hidden;
		overflow-y:scroll;
		width: 100%;
	}
    </style>
<?php endif ; ?>

<div class="mod_gausers">
    <div id="members<?php echo $module->id; ?>">
		<?php echo $intro_text; ?>
		<table class="events-inner" width="100%">
			<tbody>
			<?php if (is_array($members)) : ?>
				<?php foreach ($members as $i => $member) : ?>
					<tr class="row<?php echo $i % 2; ?>" style="border-bottom:2px solid #000;">
						<td width="100%">
							<?php if ($member->mbr_image) : ?>
								<div class="clearfix"></div>
								<p class="center"><img src="<?php echo $member->mbr_image; ?>" alt="" /></p>
								<div class="clearfix"></div>
							<?php endif ; ?>
							<h4 class="center"><?php echo $member->name; ?></h4>
							<p class="center"><?php echo $member->join_year; ?> - <?php echo $member->death_year; ?></p>
						</td>
					</tr>
		        <?php endforeach; ?>
	        <?php endif ; ?>
			</tbody>
        </table>
	</div>
</div>
