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

$data = $displayData;
$item = $data['view']->get('item');
$params = $data['view']->get('params');
$form = $data['view']->get('form');
$localProfile = $data['view']->localProfile;
$canAdmin = $data['view']->canAdmin;
$ignorArray = $data['view']->ignorArray;
$ignorStdArray = $data['view']->ignorStdArray;
// this is the user id of member viewing the form
$user_id = $data['view']->user_id;

$use_clubnumber = $params->get('use_clubnumber',0);
$fld_clubnumber = $params->get('fld_clubnumber',0);

?>
            <?php /* test for administrator or owner of record for updating purposes */ ?>
			<?php if ($canAdmin || $item->id == $user_id) : ?>

				<?php if (!$canAdmin) : ?>
    				<?php $form->setFieldAttribute('mdod', 'type', 'hidden'); ?>
    				<?php $form->setFieldAttribute('mship_id', 'type', 'hidden'); ?>
                <?php else : ?>
    				<?php $form->setFieldAttribute('mship_id', 'readonly', 'true'); ?>
                <?php endif; ?>

                <?php echo $form->renderField('mship_id'); ?>
                <?php echo $form->renderField('mdod'); ?>
                <?php echo $form->renderField('id'); ?>
                <?php echo $form->renderField('name'); ?>
                <?php echo $form->renderField('email'); ?>

				<?php if (!in_array('address1',$ignorStdArray)) {echo $form->renderField('address1');} ?>
				<?php if (!in_array('address2',$ignorStdArray)) {echo $form->renderField('address2');} ?>
				<?php if (!in_array('city',$ignorStdArray)) {echo $form->renderField('city');} ?>
				<?php if (!in_array('postal_code',$ignorStdArray)) {echo $form->renderField('postal_code');} ?>
				<?php if (!in_array('phone',$ignorStdArray)) {echo $form->renderField('phone');} ?>
				<?php if (!in_array('mphone',$ignorArray)) {echo $form->renderField('mphone');} ?>
				<?php if (!in_array('wphone',$ignorArray)) {echo $form->renderField('wphone');} ?>
				<?php if (!in_array('altemail',$ignorArray)) {echo $form->renderField('altemail');} ?>
				<?php if (!in_array('inc_altemail',$ignorArray)) {echo $form->renderField('inc_altemail');} ?>
				<?php if (!in_array('aboutme',$ignorStdArray)) {echo $form->renderField('aboutme');} ?>

            <?php /* NOT an administrator or owner of record */ ?>
			<?php else : ?>

				<input type="hidden" name="jform[id]" value="<?php echo $item->id; ?>" />
				<input type="hidden" name="jform[address1]" value="<?php echo $item->address1; ?>" />
				<input type="hidden" name="jform[address2]" value="<?php echo $item->address2; ?>" />
				<input type="hidden" name="jform[city]" value="<?php echo $item->city; ?>" />
				<input type="hidden" name="jform[postal_code]" value="<?php echo $item->postal_code; ?>" />
				<input type="hidden" name="jform[phone]" value="<?php echo $item->phone; ?>" />
				<input type="hidden" name="jform[mphone]" value="<?php echo $item->mphone; ?>" />
				<input type="hidden" name="jform[wphone]" value="<?php echo $item->wphone; ?>" />
				<input type="hidden" name="jform[altemail]" value="<?php echo $item->altemail; ?>" />
				<input type="hidden" name="jform[inc_altemail]" value="<?php echo $item->inc_altemail; ?>" />
				<input type="hidden" name="jform[aboutme]" value="<?php echo $item->aboutme; ?>" />

                <?php $form->setFieldAttribute('name', 'readonly', 'true'); ?>
                <?php $form->setFieldAttribute('email', 'readonly', 'true'); ?>
                <?php echo $form->renderField('name'); ?>
                <?php echo $form->renderField('email'); ?>

			<?php endif; ?>
