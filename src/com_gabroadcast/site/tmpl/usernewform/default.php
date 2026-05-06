<?php
/**
 * @version     4.3.3
 * @package     com_gabroadcast
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Access\Access;
use GlennArkell\Component\Gabroadcast\Administrator\Helper\GabroadcastHelper;

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gabroadcast.gabroadcastpreset');

//Load admin language file
Factory::getApplication()->getLanguage()->load('com_gabroadcast', JPATH_ADMINISTRATOR);

$user = Factory::getApplication()->getIdentity();
$canEdit = GabroadcastHelper::canUserEdit($this->item, $user);

$filterFin = $this->params->get('include_userfilter', 0);
$ret_address = $this->params->get('ret_address',0);
$retaddr_group = $this->params->get('retaddr_group',0);
$retaddr_user = $this->params->get('retaddr_user',0);
$profile_suffix = $this->params->get('profile_suffix','b4wdc');
$locProf = 'profile'.$profile_suffix;
$locGrps = $this->params->get('prof_field','profilebrb.locgrp');
$locGrp = explode('.',$locGrps);

// set up additional filtering if required
if ($this->params->get('filter_users',0)) {
    if ($this->pc_filter) {
        // 0 = profile, 1 = custom
        if ($this->params->get('filter_type','c') == 'c') {
            // hide profile
            $this->form->setFieldAttribute('user_proffld', 'type', 'hidden');
        } else {
            // hide custom
            $this->form->setFieldAttribute('user_custfld', 'type', 'hidden');
            // set default profile field
            $profile = UserHelper::getProfile($user->id);
            if (isset($profile->$locProf[$locGrp[1]])) {
                $userArea = $profile->$locProf[$locGrp[1]];
                $this->form->setFieldAttribute('user_proffld', 'default', $userArea);
                $this->form->setFieldAttribute('user_proffld', 'readonly', 'true');
            }
        }
        $this->form->setFieldAttribute('user_retaddr', 'default', $user->id);
    } else {
        $this->form->setFieldAttribute('user_proffld', 'type', 'hidden');
        //$this->form->setFieldAttribute('user_proffld', 'default', '');
        $this->form->setFieldAttribute('user_custfld', 'type', 'hidden');
        //$this->form->setFieldAttribute('user_custfld', 'default', '');
    }
} else {
    $this->form->setFieldAttribute('user_custfld', 'type', 'hidden');
    //$this->form->setFieldAttribute('user_custfld', 'default', '');
    $this->form->setFieldAttribute('user_proffld', 'type', 'hidden');
    //$this->form->setFieldAttribute('user_proffld', 'default', '');
}

if ($ret_address == 1) {
	$showRetAddr = in_array($retaddr_group, $user->groups) ? 1 : 0;
} elseif ($ret_address == 2 && $retaddr_user) {
	$showRetAddr = 1;
} elseif ($ret_address == 2 && !$retaddr_user) {
	$showRetAddr = 0;
	$this->form->setFieldAttribute('user_retaddr', 'default', $user->id);
	$this->form->setFieldAttribute('user_retaddr', 'readonly', 'true');
} else {
	$showRetAddr = 0;
}
// set the fields to be hidden if necessary
$this->form->setFieldAttribute('id', 'type', 'hidden');

if (!$filterFin) {
    $this->form->setFieldAttribute('fin_users_only', 'type', 'hidden');
    $this->form->setFieldAttribute('fin_users_only', 'default', '1');
}
if (!$this->sendto_filter) {
    $this->form->setFieldAttribute('usergroup_only', 'type', 'hidden');
    $this->form->setFieldAttribute('usergroup_only', 'default', 0);
}
if (!$showRetAddr) {
    $this->form->setFieldAttribute('user_retaddr', 'type', 'hidden');
}
if (!$this->broadcast_type) {
    $this->form->setFieldAttribute('attach_file', 'type', 'hidden');
}

//GabroadcastHelper::print_r2(Factory::getApplication()->getUserState('com_gabroadcast.test.data'));
?>

<div class="usernew-edit front-end-edit">
	<?php if (!$canEdit) : ?>
		<h3>
			<?php throw new \Exception(Text::_('COM_GABROADCAST_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
		</h3>
	<?php else : ?>
		<?php if (!empty($this->item->id)): ?>
			<h2><?php echo Text::sprintf('COM_GABROADCAST_EDIT_ITEM_TITLE', $this->item->id); ?></h2>
		<?php else: ?>
			<h2><?php echo $this->params->get('page_heading'); ?></h2>
		<?php endif; ?>

		<form id="form-usernew"
			  action="<?php echo Route::_('index.php?option=com_gabroadcast&task=usernewform.save'); ?>"
			  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
			
            <?php echo $this->form->renderField('id'); ?>
            <?php echo $this->form->renderFieldset('details'); ?>

			<div class="control-group">
				<div class="controls">
					<?php if ($this->canSave): ?>
						<button type="submit" class="validate btn btn-primary">
							<?php echo Text::_('JSUBMIT'); ?>
						</button>
					<?php endif; ?>
					<a class="btn btn-secondary"
					   href="<?php echo Route::_('index.php?option=com_gabroadcast&task=usernewform.cancel'); ?>"
					   title="<?php echo Text::_('JCANCEL'); ?>">
						<?php echo Text::_('JCANCEL'); ?>
					</a>
				</div>
			</div>

			<input type="hidden" name="option" value="com_gabroadcast"/>
			<input type="hidden" name="task" value="usernewform.save"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	<?php endif; ?>
</div>
