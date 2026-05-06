<?php
/**
 * @version     2.1.2
 * @package     com_gacalevents
 * @subpackage  mod_gacalevents
 * @author      Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright   2021 Glenn Arkell
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Helper\ModuleHelper;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Module\Gacalevents\Site\Helper\GacaleventsHelper;

HTMLHelper::stylesheet(Uri::base().'media/mod_gacalevents/css/style.css');
HTMLHelper::script(Uri::base().'media/mod_gacalevents/js/script.js');

$eventlist = GacaleventsHelper::getEvents( $params );

require ModuleHelper::getLayoutPath('mod_gacalevents');
