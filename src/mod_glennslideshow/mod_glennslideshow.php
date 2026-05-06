<?php
/*
 * ------------------------------------------------------------------------
 * @version     4.6
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * Author:      Glenn Arkell
 * Website:    http://www.glennarkell.com.au
 * ------------------------------------------------------------------------
*/

namespace GlennArkell\Module\GlennSlideshow\Site;

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

use \Joomla\CMS\Helper\ModuleHelper;
use \GlennArkell\Module\GlennSlideshow\Site\Helper\GlennSlideshowHelper;

$articleItems = GlennSlideshowHelper::getSlideshow( $params );

require ModuleHelper::getLayoutPath( 'mod_glennslideshow', $params->get('layout', 'default') );
?>