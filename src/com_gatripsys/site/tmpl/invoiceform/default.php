<?php
/**
 * @version    5.3.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

//Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR);

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gatripsys.gatripsyspreset');

$user    = GatripsysHelper::getSpecificUser();
$canEdit = GatripsysHelper::canUserEdit($this->item, $user);

$filterFin = $this->params->get('include_userfilter');
$ret_address = $this->params->get('ret_address',0);
$retaddr_group = $this->params->get('retaddr_group',0);
$retaddr_user = $this->params->get('retaddr_user',0);

$auto_users = $this->params->get('auto_users',0);
$joining_fee = $this->params->get('joining_fee',0);
$membership_dues = $this->params->get('membership_dues',0);
$discount_allowed = $this->params->get('discount_allowed',0);
$discount_dues = $this->params->get('discount_dues',0);
$allow_prorata = $this->params->get('allow_prorata',0);
$year_prorata = $this->params->get('year_prorata',2);
$min_prorata = $this->params->get('min_prorata',0);



if ($ret_address == 1) {
	$showRetAddr = in_array($retaddr_group, $user->groups) ? 1 : 0;
} elseif ($ret_address == 2 && $retaddr_user) {
	$showRetAddr = 1;
} else {
	$showRetAddr = 0;
}

/*
echo '<pre>Test<br />';
print_r(Factory::getApplication()->getUserState('com_gatripsys.test1.data'));
echo '</pre>';
print_r($retaddr_group);
*/

?>

<div class="invoice-edit front-end-edit">
	<?php if (!$canEdit) : ?>
		<h3>
			<?php throw new \Exception(Text::_('COM_GATRIPSYS_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
		</h3>
	<?php else : ?>
		<?php if (!empty($this->item->id)): ?>
			<h2><?php echo Text::sprintf('COM_GATRIPSYS_EDIT_ITEM_TITLE', $this->item->id); ?></h2>
		<?php else: ?>
			<h2><?php echo Text::_('COM_GATRIPSYS_CREATE_NEW_USER'); ?></h2>
		<?php endif; ?>

		<form id="form-invoice"
			  action="<?php echo Route::_('index.php?option=com_gatripsys&task=invoiceform.save'); ?>"
			  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
			
			<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
			<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
			<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
			<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
			<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />

            <?php if ($auto_users): ?>
				<input type="hidden" name="jform[invoice_amt]" value="0.00" />
				<input type="hidden" name="jform[user_id]" value="0" />
				<?php echo $this->form->renderField('nm_name'); ?>
				<?php echo $this->form->renderField('nm_email'); ?>
				<?php echo $this->form->renderField('nm_address'); ?>
				<?php echo $this->form->renderField('nm_suburb'); ?>
				<?php echo $this->form->renderField('nm_postcode'); ?>
				<?php echo $this->form->renderField('nm_phone'); ?>
		        <div class="clearfix"> </div>
		
            <?php else: ?>
				<input type="hidden" name="jform[invoice_amt]" value="0.00" />
				<?php echo $this->form->renderField('user_id'); ?>
		        <div class="clearfix"> </div>

			<?php endif; ?>

			<div class="control-group">
				<div class="controls">
					<?php if ($this->canSave): ?>
						<button type="submit" class="validate btn btn-primary"><?php echo Text::_('JSUBMIT'); ?></button>
					<?php endif; ?>
					<a class="btn"
					   href="<?php echo Route::_('index.php?option=com_gatripsys&task=invoiceform.cancel'); ?>"
					   title="<?php echo Text::_('JCANCEL'); ?>">
						<?php echo Text::_('JCANCEL'); ?>
					</a>
				</div>
			</div>

			<input type="hidden" name="option" value="com_gatripsys"/>
			<input type="hidden" name="task" value="invoiceform.save"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	<?php endif; ?>
</div>
