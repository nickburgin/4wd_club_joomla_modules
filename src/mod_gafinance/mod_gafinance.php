<?php

/**
 * @version     5.0.2
 * @package     com_gafinance
 * @subpackage  mod_gafinance
 * @author      Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright   2021 Glenn Arkell
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Helper\ModuleHelper;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Module\Gafinance\Site\Helper\GafinanceHelper;

// load any assets required
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('modstyle', 'mod_gafinance/style.css', [], [], []);
$wa->registerAndUseScript('modscript','mod_gafinance/script.js', [], [], []);
$wa->usePreset('com_gafinance.gafinancepreset');

$items = GafinanceHelper::getFinance( $params );

require ModuleHelper::getLayoutPath('mod_gafinance', $params->get('layout', 'default'));
