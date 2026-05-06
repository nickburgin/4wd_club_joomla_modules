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

//Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gausers', JPATH_ADMINISTRATOR);
$lang->load('plg_user_profileifmr', JPATH_ADMINISTRATOR);
$lang->load('plg_user_profilertry', JPATH_ADMINISTRATOR);

$data = $displayData;
$item = $data['view']->get('item');
$params = $data['view']->get('params');
$form = $data['view']->get('form');
$localProfile = $data['view']->localProfile;
$canAdmin = $data['view']->canAdmin;
$ignorArray = $data['view']->ignorArray;
// this is the user id of member viewing the form
$user_id = $data['view']->user_id;

if (!$canAdmin) {
    if ($item->id != $user_id) {
        $form->setFieldAttribute('address1', 'type', 'hidden');
        $form->setFieldAttribute('address2', 'type', 'hidden');
        $form->setFieldAttribute('city', 'type', 'hidden');
        $form->setFieldAttribute('region', 'type', 'hidden');
        $form->setFieldAttribute('postal_code', 'type', 'hidden');
        $form->setFieldAttribute('country', 'type', 'hidden');
    }
}        
?>
					<?php if (!in_array('address1',$ignorArray)) {echo $form->renderField('address1');} ?>
					<?php if (!in_array('address2',$ignorArray)) {echo $form->renderField('address2');} ?>
					<?php if (!in_array('city',$ignorArray)) {echo $form->renderField('city');} ?>
					<?php if (!in_array('region',$ignorArray)) {echo $form->renderField('plain_region');} ?>
					<?php if (!in_array('postal_code',$ignorArray)) {echo $form->renderField('postal_code');} ?>
					<?php if (!in_array('country',$ignorArray)) {echo $form->renderField('country');} ?>
