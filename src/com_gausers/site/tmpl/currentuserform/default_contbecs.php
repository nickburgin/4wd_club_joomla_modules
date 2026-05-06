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
	            <?php /* test for administrator or owner of record for updating purposes */ ?>
			    <?php if ($canAdmin || $item->id == $user_id) : ?>
                    <?php // don't tweek any fields  ?>
	            <?php /* NOT an administrator or owner of record */ ?>
				<?php else : ?>
					<?php $form->setFieldAttribute('deathdate', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('botydate', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('inductdate', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('inductwho', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('firstaid', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('workwithkids', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('foodhandle', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('emergcontact', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('emergcontactno', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('emergmobileno', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('relationship', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('competencelevel', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('tradequal', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('currentskill', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('desiredskill', 'type', 'hidden'); ?>
			    <?php endif; ?>

				<?php if (!in_array('deathdate',$ignorArray)) {echo $form->renderField('deathdate');} ?>
				<?php if (!in_array('botydate',$ignorArray)) {echo $form->renderField('botydate');} ?>
				<?php if (!in_array('inductdate',$ignorArray)) {echo $form->renderField('inductdate');} ?>
				<?php if (!in_array('inductwho',$ignorArray)) {echo $form->renderField('inductwho');} ?>
				<?php if (!in_array('firstaid',$ignorArray)) {echo $form->renderField('firstaid');} ?>
				<?php if (!in_array('workwithkids',$ignorArray)) {echo $form->renderField('workwithkids');} ?>
				<?php if (!in_array('foodhandle',$ignorArray)) {echo $form->renderField('foodhandle');} ?>
				<?php if (!in_array('emergcontact',$ignorArray)) {echo $form->renderField('emergcontact');} ?>
				<?php if (!in_array('emergcontactno',$ignorArray)) {echo $form->renderField('emergcontactno');} ?>
				<?php if (!in_array('emergmobileno',$ignorArray)) {echo $form->renderField('emergmobileno');} ?>
				<?php if (!in_array('relationship',$ignorArray)) {echo $form->renderField('relationship');} ?>
				<?php if (!in_array('competencelevel',$ignorArray)) {echo $form->renderField('competencelevel');} ?>
				<?php if (!in_array('tradequal',$ignorArray)) {echo $form->renderField('tradequal');} ?>
				<?php if (!in_array('currentskill',$ignorArray)) {echo $form->renderField('currentskill');} ?>
				<?php if (!in_array('desiredskill',$ignorArray)) {echo $form->renderField('desiredskill');} ?>
