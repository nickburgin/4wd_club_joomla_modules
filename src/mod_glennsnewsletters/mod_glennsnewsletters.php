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

namespace GlennArkell\Module\Glennsnewsletters\Site;

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

use \Joomla\CMS\Helper\ModuleHelper;
use \GlennArkell\Module\Glennsnewsletters\Site\Helper\GlennsnewslettersHelper;


$newsItems = GlennsnewslettersHelper::getNewsletters( $params );

require ModuleHelper::getLayoutPath( 'mod_glennsnewsletters', $params->get('layout', 'default' ) );

?>