<?php
/*
# ------------------------------------------------------------------------
# @version     5.4
# @copyright   Copyright (C) 2014. All rights reserved.
# @license     GNU General Public License version 2 or later; see LICENSE.txt
# Author:      Glenn Arkell
# Websites:    https://www.glennarkell.com.au
# ------------------------------------------------------------------------
*/
// no direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gausers\Administrator\Helper\GainvoiceHelper;

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('mod_gausers', 'mod_gausers/default.css');

$moduleclass_sfx	= $params->get('moduleclass_sfx', '');
$ht = $params->get('header_tag', 'h4');
$hc = $params->get('header_class', 'center');
$set_listheight = $params->get('set_listheight', 0);
$list_height = $params->get('list_height', 400);
$intro_text = $params->get('intro_text', '');

?>
<?php if ($set_listheight) : ?>
    <style>
        table.events-inner {
    		max-height: <?php echo $list_height; ?>px;
    	}
    </style>
<?php endif ; ?>

<div class="mod_gausers">
    <div id="members<?php echo $module->id; ?>">
		<?php echo $intro_text; ?>
		<table class="events-inner" width="100%">
			<tbody>
			<?php if (is_array($items)) : ?>
				<?php foreach ($items as $i => $member) : ?>
					<?php 
    					if ($params->get('prof_mship', 1)) {
                            //get last mship
                            $lastInv = GainvoiceHelper::getLastInvoiceMship($member->user_id);
                            if (!empty($lastInv)) {
                                //$dispYear = substr($lastInv->end_date,0,4);
                                $dispYear = HtmlHelper::date($lastInv->end_date, Text::_('MOD_GAUSERS_STD_YEAR'));
                            } else {
                                $dispYear = '';
                            }
                        } else {
                            $dispYear = $member->death_year;
                        }
                    ?>
                    <tr class="row<?php echo $i % 2; ?>" style="border-bottom:2px solid #000;">
						<td width="100%">
							<?php if ($member->mbr_image) : ?>
								<div class="clearfix"></div>
								<p class="<?php echo $hc; ?>"><img src="<?php echo $member->mbr_image; ?>" alt="<?php echo $member->name; ?>" /></p>
								<div class="clearfix"></div>
							<?php endif ; ?>
							<<?php echo $ht; ?> class="<?php echo $hc; ?>"><?php echo $member->name; ?></<?php echo $ht; ?>>
							<p class="<?php echo $hc; ?>"><?php echo $member->join_year; ?> - <?php echo $dispYear; ?></p>
						</td>
					</tr>
		        <?php endforeach; ?>
	        <?php endif ; ?>
			</tbody>
        </table>
	</div>
</div>
