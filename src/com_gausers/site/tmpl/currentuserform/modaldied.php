<?php
/**
 * @version     6.0.0
 * @package     com_gausers
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Session\SessionInterface;
use Joomla\CMS\Application\SiteApplication;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GanamesHelper;

//Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gausers', JPATH_ADMINISTRATOR);

$wa = $this->document->getWebAssetManager()
    ->usePreset('com_gausers.gauserspreset');
GausersHelper::loadTmplStyleModal($wa);

// get the current date-time based on timezone
$today = Factory::getDate()->toSql();

if (!isset($this->item->id)) {
    $id = $this->state->get('currentuser.id');
} else {
    $id = $this->item->id;
}
$fullnames = GanamesHelper::breakdownNamesFromUserID($id, 'partner');

$this->form->setFieldAttribute('id', 'default', $id);
$this->form->setFieldAttribute('id', 'type', 'hidden');
$this->form->setFieldAttribute('user_id', 'default', $id);
$this->form->setFieldAttribute('user_id', 'type', 'hidden');
$this->form->setFieldAttribute('user_name', 'default', $this->item->name);
$this->form->setFieldAttribute('user_name', 'type', 'hidden');
$this->form->setFieldAttribute('dec_name', 'default', $this->item->name);

/*
echo '<pre>Test<br />';
print_r($this->item);
echo '</pre>';
print_r(Factory::getApplication()->getUserState('com_gausers.test.data'));
*/
?>
<style>
	.modal-body {
		max-height:450px;
	}
	input[type="checkbox"] {
		width: 24px;
		height: 24px;
		float: left;
	}
</style>

<div class="front-end-edit">
<form
	id="form-gauser" action="<?php echo Route::_('index.php?option=com_gausers&task=currentuserform.markAsDied&tmpl=component'); ?>"
	method="post" class="form-validate form-horizontal" enctype="multipart/form-data" target="_parent">

    <h3>Mark member as died - <?php echo $fullnames->fullname . ' ('.$id.')'; ?></h3>
    <div class="row-fluid">
        <div class="span12 form-horizontal">
        
			<?php echo $this->form->renderField('id'); ?>
			<?php echo $this->form->renderField('user_id'); ?>
			<?php echo $this->form->renderField('user_name'); ?>
			<?php echo $this->form->renderField('left_date'); ?>

			<?php
				if (isset($this->item->partner) && $this->item->partner > ' ') {
					echo '<p style="color:red;">Partner\'s Name: '.$this->item->partner.'.<br />';
					echo Text::_('COM_GAUSERS_PARTNER_WARNING_DESC').'</p>';
				}
			?>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('single_user'); ?></div>
				<div class="controls">
					<?php echo $this->form->getInput('single_user'); ?>
					<span class="small" style="margin:0 10px;float:left;padding-bottom:5px;">
                        <?php echo Text::_('COM_GAUSERS_SINGLE_USER_DESC'); ?>
                    </span>
				</div>
			</div>

			<?php echo $this->form->renderField('dec_name'); ?>
			<?php echo $this->form->renderField('left_comment'); ?>

        </div>
        <p class="center">
            <button type="submit" class="validate btn btn-primary"><?php echo Text::_('JSUBMIT'); ?></button>
        </p>
    </div>
    <div>
        <input type="hidden" name="task" value="currentuserform.markAsDied" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
</div>
