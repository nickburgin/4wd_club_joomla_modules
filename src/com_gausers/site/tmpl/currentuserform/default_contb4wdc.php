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

$data = $displayData;
$item = $data['view']->get('item');
$params = $data['view']->get('params');
$form = $data['view']->get('form');
$localProfile = $data['view']->localProfile;
$canAdmin = $data['view']->canAdmin;
$ignorArray = $data['view']->ignorArray;
// this is the user id of member viewing the form
$user_id = $data['view']->user_id;

$incl_partner = $params->get( 'incl_partner' );
$trg_group = $params->get('trg_group',0);
$use_clubnumber = $params->get('use_clubnumber',0);
$fld_clubnumber = $params->get('fld_clubnumber',0);

$baseURL = 'index.php?';
$remFile = GausersHelper::getHTTPQuery(null, 'task', 'currentuserform.removeImgFile', 'id', $item->id);
$remMFile = GausersHelper::getHTTPQuery($remFile, null, null, 'file', 'm');
$remMURL = $baseURL.\http_build_query($remMFile, '', '&amp;');
$remPFile = GausersHelper::getHTTPQuery($remFile, null, null, 'file', 'm');
$remPURL = $baseURL.\http_build_query($remFile, '', '&amp;');

?>
	            <?php /* test for administrator or owner of record for updating purposes */ ?>
			    <?php if ($canAdmin || $item->id == $user_id) : ?>

	                <?php echo $form->renderField('name'); ?>
	                <?php echo $form->renderField('email'); ?>
					<?php if (!in_array('phone',$ignorArray)) {echo $form->renderField('phone');} ?>

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

					<?php if (!in_array('partner',$ignorArray)) {echo $form->renderField('partner');} ?>
					<?php if (!in_array('altemail',$ignorArray)) {echo $form->renderField('altemail');} ?>
					<?php if (!in_array('altphone',$ignorArray)) {echo $form->renderField('altphone');} ?>

					<?php if (!isset($item->p_img_disp) || !$item->p_img_disp) : ?>
						<?php if (!in_array('p_img',$ignorArray)) { echo $form->renderField('p_img');} ?>
						<input type="hidden" name="jform[p_img_disp]" value="" />
					<?php else : ?>
						<?php if (!in_array('p_img',$ignorArray)) : ?>
							<div class="control-group">
								<div class="control-label"><?php echo $form->getLabel('p_img_disp'); ?></div>
								<div class="controls"><?php echo $form->getInput('p_img_disp'); ?>
									<a class="btn btn-secondary" href="<?php echo Route::_($remPURL); ?>"
										title="<?php echo Text::_('JREMOVE_FILE_DESC'); ?>">
										<i class="icon-trash"></i> <?php echo Text::_('JREMOVE_FILE'); ?>
									</a>
								</div>
						    </div>
					    <?php endif ; ?>
					<?php endif ; ?>

					<?php if (!in_array('inc_altemail',$ignorArray)) {echo $form->renderField('inc_altemail');} ?>
					<?php if (!in_array('kids',$ignorArray)) {echo $form->renderField('kids');} ?>
					<?php if (!in_array('2nd_phone',$ignorArray)) {echo $form->renderField('2nd_phone');} ?>
					<?php if (!in_array('2nd_email',$ignorArray)) {echo $form->renderField('2nd_email');} ?>

				        </div>
			        </div>
		            <?php echo HTMLHelper::_('uitab.endTab'); ?>

					<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'address', Text::_('COM_GAUSERS_MEMBER_ADDRESS', true)); ?>
		            <div class="row-fluid">
		                <div class="span12 form-horizontal">
					<h4><?php echo Text::_('COM_GAUSERS_ADDITIONAL_CONTACT_HEADER'); ?></h4>
					<?php if (!in_array('address1',$ignorArray)) {echo $form->renderField('address1');} ?>
					<?php if (!in_array('address2',$ignorArray)) {echo $form->renderField('address2');} ?>
					<?php if (!in_array('city',$ignorArray)) {echo $form->renderField('city');} ?>
					<?php if (!in_array('region',$ignorArray)) {echo $form->renderField('region');} ?>
					<?php if (!in_array('postal_code',$ignorArray)) {echo $form->renderField('postal_code');} ?>
					<?php if (!in_array('country',$ignorArray)) {echo $form->renderField('country');} ?>
					<?php if (!in_array('use_post',$ignorArray)) {echo $form->renderField('use_post');} ?>
					<?php if (!in_array('postal_address1',$ignorArray)) {echo $form->renderField('postal_address1');} ?>
					<?php if (!in_array('postal_address2',$ignorArray)) {echo $form->renderField('postal_address2');} ?>
					<?php if (!in_array('postal_city',$ignorArray)) {echo $form->renderField('postal_city');} ?>
					<?php if (!in_array('postal_region',$ignorArray)) {echo $form->renderField('postal_region');} ?>
					<?php if (!in_array('postal_post_code',$ignorArray)) {echo $form->renderField('postal_post_code');} ?>

	            <?php /* NOT an administrator or owner of record */ ?>
				<?php else : ?>

					<div class="control-group">
						<div class="control-label"><?php echo $form->getLabel('name'); ?></div>
						<div class="controls"><input type="text" name="jform[name]" value="<?php echo $item->name; ?>" readonly="true" class="readonly" /></div>
					</div>
					<input type="hidden" name="jform[email]" value="<?php echo $item->email; ?>" />
					<input type="hidden" name="jform[address1]" value="<?php echo $item->address1; ?>" />
					<input type="hidden" name="jform[address2]" value="<?php echo $item->address2; ?>" />
					<input type="hidden" name="jform[city]" value="<?php echo $item->city; ?>" />
					<input type="hidden" name="jform[region]" value="<?php echo $item->region; ?>" />
					<input type="hidden" name="jform[postal_code]" value="<?php echo $item->postal_code; ?>" />
					<input type="hidden" name="jform[use_post]" value="<?php echo $item->use_post; ?>" />
					<input type="hidden" name="jform[postal_address1]" value="<?php echo $item->postal_address1; ?>" />
					<input type="hidden" name="jform[postal_address2]" value="<?php echo $item->postal_address2; ?>" />
					<input type="hidden" name="jform[postal_city]" value="<?php echo $item->postal_city; ?>" />
					<input type="hidden" name="jform[postal_region]" value="<?php echo $item->postal_region; ?>" />
					<input type="hidden" name="jform[postal_post_code]" value="<?php echo $item->postal_post_code; ?>" />
					<input type="hidden" name="jform[phone]" value="<?php echo $item->phone; ?>" />
					<input type="hidden" name="jform[country]" value="<?php echo $item->country; ?>" />
					<input type="hidden" name="jform[m_club_position]" value="<?php echo $item->m_club_position; ?>" />
					<div class="control-group">
						<div class="control-label"><?php echo $form->getLabel('partner'); ?></div>
						<div class="controls">
						<input type="text" name="jform[partner]" value="<?php echo $item->partner; ?>" readonly="true" class="readonly" />
						</div>
					</div>
					<input type="hidden" name="jform[p_club_position]" value="<?php echo $item->p_club_position; ?>" />
					<input type="hidden" name="jform[m_img]" value="<?php echo $item->m_img; ?>" />
					<input type="hidden" name="jform[kids]" value="<?php echo $item->kids; ?>" />
					<input type="hidden" name="jform[altphone]" value="<?php echo $item->altphone; ?>" />
					<input type="hidden" name="jform[altemail]" value="<?php echo $item->altemail; ?>" />
					<input type="hidden" name="jform[2nd_phone]" value="<?php echo $item->second_phone; ?>" />
					<input type="hidden" name="jform[2nd_email]" value="<?php echo $item->second_email; ?>" />
					<input type="hidden" name="jform[inc_altemail]" value="<?php echo $item->inc_altemail; ?>" />
			    <?php endif; ?>
