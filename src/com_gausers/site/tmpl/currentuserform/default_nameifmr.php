<?php
/**
 * @version     5.1.6
 * @package     com_gausers
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

defined('JPATH_BASE') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Session\Session;
use \Joomla\CMS\Plugin\PluginHelper;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;

//Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gausers', JPATH_ADMINISTRATOR);
$lang->load('plg_user_profileifmr', JPATH_ADMINISTRATOR);
$lang->load('plg_user_profilertry', JPATH_ADMINISTRATOR);

$data = $displayData;
$item = $data['view']->get('item');
$params = $data['view']->get('params');
$form = $data['view']->get('form');
$ignorArray = $data['view']->ignorArray;
$ignorStdArray = $data['view']->ignorStdArray;
//$localProfile = $data['view']->localProfile;
$canAdmin = $data['view']->canAdmin;
// this is the user id of member viewing the form
$user_id = $data['view']->user_id;

$form->setFieldAttribute('id', 'type', 'hidden');
$form->setFieldAttribute('mship_id', 'type', 'hidden');
$form->setFieldAttribute('mship_id', 'default', 1);

if (!$canAdmin) {
    if ($item->id != $user_id) {
        $form->setFieldAttribute('name', 'readonly', 'true');
        $form->setFieldAttribute('email', 'readonly', 'true');
        $form->setFieldAttribute('phone', 'type', 'hidden');
        $form->setFieldAttribute('partner', 'type', 'hidden');
        $form->setFieldAttribute('mobile', 'type', 'hidden');
        $form->setFieldAttribute('workphone', 'type', 'hidden');
        $form->setFieldAttribute('bike', 'type', 'hidden');
        $form->setFieldAttribute('aboutme', 'type', 'hidden');
    }
}
?>
					<?php echo $form->renderField('id'); ?>
					<?php echo $form->renderField('mship_id'); ?>
					<?php echo $form->renderField('name'); ?>
					<?php echo $form->renderField('email'); ?>
					<?php if (!in_array('phone',$ignorStdArray)) {echo $form->renderField('phone');} ?>
					<?php if (!in_array('partner',$ignorArray)) {echo $form->renderField('partner');} ?>
					<?php if (!in_array('mobile',$ignorArray)) {echo $form->renderField('mobile');} ?>
					<?php if (!in_array('workphone',$ignorArray)) {echo $form->renderField('workphone');} ?>
					<?php if (!in_array('bike',$ignorArray)) {echo $form->renderField('bike');} ?>
					<?php if (!in_array('aboutme',$data['view']->ignorStdArray)) {echo $form->renderField('aboutme');} ?>

