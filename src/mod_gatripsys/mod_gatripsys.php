<?php
/**
 * @package    mod_gatripsys
 * @version    5.0.4
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Helper\ModuleHelper;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Module\Gatripsys\Site\Helper\GatripsysHelper;

HTMLHelper::stylesheet(URI::base() . 'media/mod_gatripsys/css/style.css');
HTMLHelper::script(URI::base() . 'media/mod_gatripsys/js/script.js');

$trips = GatripsysHelper::getList( $params );

require ModuleHelper::getLayoutPath( 'mod_gatripsys' );

?>
