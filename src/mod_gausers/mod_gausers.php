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
defined('_JEXEC') or die( 'Restricted access' );

use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Helper\ModuleHelper;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Module\Gausers\Site\Helper\GausersHelper;

HTMLHelper::stylesheet(Uri::base().'media/mod_gausers/css/default.css');

$members = GausersHelper::getMembers( $params );

require( ModuleHelper::getLayoutPath( 'mod_gausers' ) );

?>
