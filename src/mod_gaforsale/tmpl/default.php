<?php
/*
# ------------------------------------------------------------------------
# @version     3.0.09
# @copyright   Copyright (C) 2014. All rights reserved.
# @license     GNU General Public License version 2 or later; see LICENSE.txt
# Author:      Glenn Arkell
# Websites:    http://www.glennarkell.com.au
# ------------------------------------------------------------------------
*/
// no direct access
defined('_JEXEC') or die;

JHtml::_('bootstrap.framework');

$disp_fsitems = $params->get('disp_fsitems', 0);

$moduleclass_sfx	= $params->get('moduleclass_sfx', '');
$ht = $params->get('header_tag');

/*
echo '<pre><br />';
print_r($module);
echo '</pre>';
*/
?>
<?php if ($disp_fsitems) : ?>
	<div class="mod_gaforsale<?php echo $moduleclass_sfx; ?>" >
	
	    <div id="fsitemdetails<?php echo $module->id; ?>">
	
	        <<?php echo $ht; ?> class="fsitemdetails">
				<p>There are <?php echo $fsitems; ?> items for sale.</p>
				<p class="small">Version: 3.0.09</p>
			</<?php echo $ht; ?>>
	
		</div>
	</div>
<?php endif ; ?>
