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
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;

//Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gausers', JPATH_ADMINISTRATOR);

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

$baseURL = 'index.php?';
$remFile = GausersHelper::getHTTPQuery(null, 'task', 'currentuserform.removeImgFile', 'id', $item->id);
$remMFile = GausersHelper::getHTTPQuery($remFile, null, null, 'file', 'm');
$remMURL = $baseURL.\http_build_query($remMFile, '', '&amp;');

?>
				<input type="hidden" name="jform[id]" value="<?php echo $item->id; ?>" />

	            <?php /* test for administrator or owner of record for updating purposes */ ?>
			    <?php if ($canAdmin || $item->id == $user_id) : ?>

                    <?php if (!$canAdmin) : ?>
    					<?php $form->setFieldAttribute('mdod', 'type', 'hidden'); ?>
    					<?php $form->setFieldAttribute('mship_id', 'type', 'hidden'); ?>
					<?php endif; ?>

	            <?php /* NOT an administrator or owner of record */ ?>
				<?php else : ?>
					<?php $form->setFieldAttribute('name', 'readonly', 'true'); ?>
					<?php $form->setFieldAttribute('email', 'readonly', 'true'); ?>
					<?php $form->setFieldAttribute('phone', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('emailnews', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('partner', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('altemail', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('altphone', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('inc_altemail', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('kids', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('2nd_phone', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('2nd_email', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('m_img', 'type', 'hidden'); ?>
			    <?php endif; ?>

				<?php echo $form->renderField('mship_id'); ?>
				<?php echo $form->renderField('mdod'); ?>
                <?php echo $form->renderField('name'); ?>
				<?php echo $form->renderField('email'); ?>

    			<?php if (!in_array('phone',$ignorArray)) {echo $form->renderField('phone');}; ?>
    			<?php if (!in_array('partner',$ignorArray)) {echo $form->renderField('partner');}; ?>
    			<?php if (!in_array('altemail',$ignorArray)) {echo $form->renderField('altemail');}; ?>
    			<?php if (!in_array('inc_altemail',$ignorArray)) {echo $form->renderField('inc_altemail');}; ?>
    			<?php if (!in_array('altphone',$ignorArray)) {echo $form->renderField('altphone');}; ?>
    			<?php if (!in_array('kids',$ignorArray)) {echo $form->renderField('kids');}; ?>
    			<?php if (!in_array('2nd_phone',$ignorArray)) {echo $form->renderField('2nd_phone');}; ?>
    			<?php if (!in_array('2nd_email',$ignorArray)) {echo $form->renderField('2nd_email');}; ?>

				<?php if (!isset($item->m_img_disp) || !$item->m_img_disp) : ?>
					<?php if (!in_array('m_img',$ignorArray)) { echo $form->renderField('m_img');} ?>
					<input type="hidden" name="jform[m_img_disp]" value="" />
				<?php else : ?>
					<?php if (!in_array('m_img',$ignorArray)) : ?>
						<div class="control-group">
							<div class="control-label"><?php echo $form->getLabel('m_img_disp'); ?></div>
							<div class="controls"><?php echo $form->getInput('m_img_disp'); ?>
								<a class="btn btn-secondary" href="<?php echo Route::_($remMURL); ?>"
									title="<?php echo Text::_('JREMOVE_FILE_DESC'); ?>">
									<i class="icon-trash"></i> <?php echo Text::_('JREMOVE_FILE'); ?>
								</a>
							</div>
						</div>
						<input type="hidden" name="jform[m_img]" value="" />
					<?php endif ; ?>
				<?php endif ; ?>
