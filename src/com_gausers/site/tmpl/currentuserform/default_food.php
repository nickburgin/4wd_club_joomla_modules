<?php
/**
 * @version     6.0.0
 * @package     com_gausers
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\HTML\HTMLHelper;

$lang = Factory::getApplication()->getLanguage();
$lang->load('plg_user_profilefood', JPATH_SITE);

$data = $displayData;
$item = $data['view']->get('item');
$params = $data['view']->get('params');
$form = $data['view']->get('form');
$localProfile = $data['view']->localProfile;
$canAdmin = $data['view']->canAdmin;
$ignorArray = $data['view']->ignorArray;
// this is the user id of member viewing the form
$user_id = $data['view']->user_id;


?>
			<?php echo $form->renderField('bread'); ?>
			<?php echo $form->renderField('bun'); ?>
			<?php echo $form->renderField('cheese'); ?>
			<?php echo $form->renderField('chocolate'); ?>
			<?php echo $form->renderField('juice'); ?>
			<?php echo $form->renderField('milk'); ?>
			<?php echo $form->renderField('wine'); ?>
			<?php echo $form->renderField('whisky'); ?>
			<?php echo $form->renderField('nok'); ?>
			<?php echo $form->renderField('relship'); ?>
			<?php echo $form->renderField('ambo'); ?>
			<?php echo $form->renderField('racv'); ?>
			<?php echo $form->renderField('organ'); ?>
			<?php echo $form->renderField('medinsure'); ?>
			<?php echo $form->renderField('medinsure_name'); ?>
			<?php echo $form->renderField('health'); ?>
