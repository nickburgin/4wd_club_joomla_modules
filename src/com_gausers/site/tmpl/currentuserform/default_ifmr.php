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

$lang = Factory::getLanguage();
$lang->load('plg_user_profileifmr', JPATH_ADMINISTRATOR);
$lang->load('plg_user_profilertry', JPATH_ADMINISTRATOR);

$data = $displayData;
$item = $data['view']->get('item');
$params = $data['view']->get('params');
$form = $data['view']->get('form');
$localProfile = $data['view']->localProfile;
$canAdmin = $data['view']->canAdmin;
//$ignorArray = $data['view']->ignorArray;
// this is the user id of member viewing the form
$user_id = $data['view']->user_id;


if (isset($item->m_img)) {
    if ($item->m_img > '' && $item->m_img > 0) {
        $form->setFieldAttribute('m_img', 'type', 'hidden');
        $form->setFieldAttribute('m_img_disp', 'default', $item->m_img);
        $baseURL = 'index.php?';
        $remFile = GausersHelper::getHTTPQuery(null, 'task', 'currentuserform.removeImgFile', 'file', 'm');
        $remFile = GausersHelper::getHTTPQuery($remFile, null, null, 'id', $item->id);
        $remURL = $baseURL.\http_build_query($remFile, '', '&amp;');
        $btnDisp = '<a class="btn btn-secondary" href="'.Route::_($remURL).'"title="'.Text::_('JREMOVE_FILE_DESC').'">';
        $btnDisp .= '<i class="icon-trash"></i> '. Text::_('JREMOVE_FILE').'</a>';
    } else {
        $form->setFieldAttribute('m_img_disp', 'type', 'hidden');
        $btnDisp = '';
    }
} else {
    $form->setFieldAttribute('m_img_disp', 'type', 'hidden');
    $btnDisp = '';
}

if (!$canAdmin) {
    if ($item->id == $user_id) {
        $form->setFieldAttribute('rego_by', 'readonly', 'true');
        $form->setFieldAttribute('district', 'required', 'required');
        $form->setFieldAttribute('rotaryclub', 'required', 'required');
    } else {
        $form->setFieldAttribute('rego_by', 'type', 'hidden');
        $form->setFieldAttribute('district', 'type', 'hidden');
        $form->setFieldAttribute('rotaryclub', 'type', 'hidden');
    }
}
?>
	            <h4><?php echo Text::_('COM_GAUSERS_IFMR_HEADER'); ?></h4>
	    		<?php echo $form->renderField('m_img'); ?>
	    		<?php echo $form->renderField('m_img_txt'); ?>
				<?php //if (!in_array('m_img',$data['view']->ignorArray)) : ?>
					<div class="control-group">
						<div class="control-label"><?php echo $form->getLabel('m_img_disp'); ?></div>
						<div class="controls"><?php echo $form->getInput('m_img_disp'); ?> <?php echo $btnDisp; ?></div>
					</div>
				<?php //endif ; ?>
	    		<?php if (!in_array('rego_by',$data['view']->ignorArray)) {echo $form->renderField('rego_by');} ?>
				<?php if (!in_array('district',$data['view']->ignorArray)) {echo $form->renderField('district');} ?>
				<?php if (!in_array('rotaryclub',$data['view']->ignorArray)) {echo $form->renderField('rotaryclub');} ?>

