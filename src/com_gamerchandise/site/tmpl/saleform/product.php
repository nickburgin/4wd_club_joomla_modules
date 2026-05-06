<?php
/**
 * @version    3.0.00
 * @package    Com_Gamerchandise
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gamerchandise', JPATH_SITE);

$user = Factory::getUser();
$canManage = $user->authorise('core.manage', 'com_gamerchandise');

?>

<div class="sale-edit front-end-edit">
	<h2>Create Order </h2>

	<form id="form-sale"
		  action="<?php echo Route::_('index.php?option=com_gamerchandise&task=saleform.setproduct'); ?>"
		  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

		<input type="hidden" name="jform[id]" value="0" />
		<input type="hidden" name="jform[order_qty]" value="1" />

		<?php echo $this->form->renderField('prod_id'); ?>

		<div class="control-group">
			<div class="controls">
				<?php if ($this->canSave): ?>
					<button type="submit" class="validate btn btn-primary"> <?php echo Text::_('COM_GAMERCHANDISE_ADD_TO_CART'); ?> </button>
				<?php endif; ?>
				<a class="btn" href="<?php echo Route::_('index.php?option=com_gamerchandise&task=saleform.cancel'); ?>" title="<?php echo Text::_('JCANCEL'); ?>">
					<?php echo Text::_('JCANCEL'); ?>
				</a>
			</div>
		</div>

		<input type="hidden" name="option" value="com_gamerchandise"/>
		<input type="hidden" name="task" value="saleform.setproduct"/>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
