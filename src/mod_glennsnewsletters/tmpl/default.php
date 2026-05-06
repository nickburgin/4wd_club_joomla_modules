<?php
/*
 * ------------------------------------------------------------------------
 * @version     4.4
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * Author:      Glenn Arkell
 * Website:     http://www.glennarkell.com.au
 * ------------------------------------------------------------------------
*/
// no direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;

HTMLHelper::stylesheet(Uri::base().'media/mod_glennsnewsletters/css/default.css');

$moduleclass_sfx	= $params->get('moduleclass_sfx', '');
$ht = $params->get('header_tag');
$set_listheight = $params->get('set_listheight', 1);
$list_height = $params->get('list_height', 200);
$ct = $params->get('content_tag', 'p');

$sort_prefix = $params->get('sort_prefix', '');
$len = strlen($sort_prefix);
if ($sort_prefix > '') { $trimPref = true; } else { $trimPref = false; }

?>

<?php if ($set_listheight) : ?>
    <style>
        div.newsletter-inner {
    		max-height: <?php echo $list_height; ?>px;
        }
    </style>
<?php endif ; ?>

<div class="mod_glennsnewsletters<?php echo $moduleclass_sfx; ?>" >
    <div id="myNewsletter<?php echo $module->id; ?>" class="newsletter">

		<div class="newsletter-inner">
            <?php if (is_array($newsItems)) : ?>
				<?php foreach ($newsItems as $fitem) : ?>
	                <?php if ($trimPref) { $title = substr($fitem->title, $len); } else { $title = $fitem->title; } ?>
					<<?php echo $ct; ?> class="newsletter">
						<a class="newsletter" href="<?php echo $fitem->newsfile; ?>" target="_blank" alt="<?php echo $title; ?>">
							<?php echo $title; ?>
						</a>
					</<?php echo $ct; ?>>
	
	            <?php endforeach; ?>
            <?php endif ; ?>

    	</div>
	</div>
	<div style="clear:both;"></div>
</div>