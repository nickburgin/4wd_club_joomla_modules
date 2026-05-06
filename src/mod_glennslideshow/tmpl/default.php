<?php
/*
 * ------------------------------------------------------------------------
 * @version     4.7
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * Author:      Glenn Arkell
 * Website:    http://www.glennarkell.com.au
 * ------------------------------------------------------------------------
*/

// no direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('bootstrap.carousel');


$moduleclass_sfx	= $params->get('moduleclass_sfx', '');
$show_navigation	= $params->get('show_navigation', '0');
$cycle_time	= $params->get('cycle_time', '5000');
$set_captioncolour	= $params->get('set_captioncolour', '0');
$caption_bg	= $params->get('caption_bg', 'rgba(0, 0, 0, 0.75)');
$caption_txt	= $params->get('caption_txt');
$use_category_folder = $params->get('use_category_folder',0); // 0 = category, 1 = folder
$default_fold = $params->get('default_fold', 'images');
$default_cat = $params->get('default_cat','uncategorised');
$use_caption = $params->get('use_caption', 0);
$websitelink = $params->get('use_websitelink', 0);
$show_indicators = $params->get('show_indicators', 0);
$indicator_color = $params->get('indicator_color', '#a85b03');
$modalheight = $params->get('modalheight', 500);
$maxheight = $params->get('maxheight', 0);
$ht = $params->get('header_tag');
$slide_fade = $params->get('slide_fade', 0);  // 0 = slide , 1 = fade
if ($slide_fade) { $sf_class = 'carousel-fade'; } else { $sf_class = ''; }

// set up some styling for image
$setWidth100 = $params->get('set_width100',0) ? 'width:100%;' : 'width:auto;';
$cntrImg = $params->get('cntr_image',1) ? 'margin:0 auto;' : '';

$imgStyle =  $setWidth100.$cntrImg;

$resultString = '';
$carouselindic = '';
$carouselitems = '';
$counter = 0;
$class = 'active';
$websitelinkurl = null;

if ($maxheight) {
	$setmaxheight = $maxheight;
} else {
	$setmaxheight = '100';
}
/*
echo '<pre>Test<br />';
print_r($articleItems);
echo '</pre>';
*/
?>
<?php if ($use_caption) : ?>
    <style>
        .carousel-caption {
            background: none repeat scroll 0 0 <?php echo $caption_bg; ?>;
            color: <?php echo $caption_txt; ?>;
	        text-align: center;
            bottom: 0;
            right: 0;
            left: 0;
            padding-top: 5px;
            padding-bottom: 5px;
        }
    </style>
<?php endif ; ?>
<?php if (!$show_indicators) : ?>
    <style>
        .carousel-indicators {
            display: none;
        }
    </style>
<?php else : ?>
    <style>
        button.carousel-ind-color {
			color: <?php echo $indicator_color; ?> !important;
		}
        .carousel-indicators [data-bs-target],
		.carousel-indicators .active {
            background-color: <?php echo $indicator_color; ?>;
            padding-top:5px;
        }
    </style>
<?php endif ; ?>

<div class="mod_glennslideshow<?php echo $moduleclass_sfx; ?>" >

    <div id="myCarousel<?php echo $module->id; ?>"
		class="carousel slide <?php echo $sf_class; ?>"
		data-bs-ride="carousel" data-bs-interval="<?php echo $cycle_time; ?>"
		>

        <div class="carousel-indicators">
            <?php if (is_array($articleItems) || is_object($articleItems)) : ?>
				<?php foreach ($articleItems as $key => $article) : ?>
	                <button class="carousel-ind-color <?php if ($key == 0) {echo 'active'; } ?>"
						type="button"
						data-bs-target="#myCarousel<?php echo $module->id; ?>"
						data-bs-slide-to="<?php echo $key; ?>">
					</button>
	            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="carousel-inner">
            <?php if (is_array($articleItems) || is_object($articleItems)) : ?>
	            <?php foreach ($articleItems as $key => $article) : ?>
	                <?php
	                    if ($use_category_folder == 0) {
	                        $imagesInfo = json_decode($article->images);
	                        $fimage = HTMLHelper::cleanImageURL($imagesInfo->{'image_fulltext'});
	            		    $fullImage = $fimage->url;
	                        $urlInfo = json_decode($article->urls);
	            		    $websitelinkurl = $urlInfo->{'urla'};
	            		    $websitelinkurl = str_replace('http://','',$websitelinkurl);
	                    } else {
	                        $fullImage = $article->images;
	                    }
	                ?>
                    <div class="carousel-item <?php if ($key == 0) {echo 'active'; } ?>">
                        <?php if ($websitelink) : ?>
                        	<?php if (isset($websitelinkurl)) : ?>
	                            <a href="http://<?php echo $websitelinkurl; ?>" target="_blank" alt="<?php echo $article->title; ?>">
                            <?php endif; ?>
                        <?php endif; ?>
                        <p style="text-align: center;" class="center">
						<img src="<?php echo $fullImage; ?>" class="d-block h-<?php echo $setmaxheight; ?> w-auto" style="<?php echo $imgStyle; ?>" title="<?php echo $article->title; ?>" alt="<?php echo $article->title; ?>" />
                        </p>
						<?php if ($use_caption) : ?>
							<div class="carousel-caption d-none d-md-block">
                                <<?php echo $ht; ?> class="center"><?php echo $article->title; ?></<?php echo $ht; ?>>
                            </div>
                        <?php endif; ?>
                        <?php if ($websitelink && (isset($websitelinkurl))) : ?>
                            </a>
                        <?php endif; ?>
                    </div>

	            <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($show_navigation) : ?>
        		<!-- Carousel nav -->
	        	<button class="carousel-control-prev"
					data-bs-target="#myCarousel<?php echo $module->id; ?>" type="button" data-bs-slide="prev">
	                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
	        		<span class="visually-hidden">Previous</span>
	        	</button>
	            <button class="carousel-control-next"
					data-bs-target="#myCarousel<?php echo $module->id; ?>" type="button" data-bs-slide="next">
	                <span class="carousel-control-next-icon" aria-hidden="true"></span>
	                <span class="visually-hidden">Next</span>
	        	</button>
            <?php endif ; ?>
    	</div>
	</div>

	<div style="clear:both;"></div>
</div>