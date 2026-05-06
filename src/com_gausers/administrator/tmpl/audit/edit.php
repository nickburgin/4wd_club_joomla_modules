<?php
/**
 * @version    5.1.6
 * @package    Com_Gausers
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2017 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Layout\LayoutHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');


$pre_update = json_decode($this->item->pre_update);
$preRec = (array) $pre_update;
$post_update = json_decode($this->item->post_update);
$postRec = (array) $post_update;
//$result = array_merge(array_diff($postRec,$preRec),array_diff($preRec,$postRec));
$new_result = array();
foreach($preRec as $key => $value) {
    if(isset($postRec[$key]) && $value != $postRec[$key]) {
		$new_result[$key] = $postRec[$key];
	}
}
foreach($postRec as $key => $value) {
    if(!isset($preRec[$key])) {
		$new_result[$key] = $value;
	}
}
?>

<form
	action="<?php echo Route::_('index.php?option=com_gausers&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="audit-form" class="form-validate">

	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_GAUSERS_TITLE_AUDIT', true)); ?>
		<div class="row-fluid">
			<div class="span10 form-horizontal">

				<fieldset class="adminform">

					<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
					<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
					<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
					<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
					<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />

					<?php echo $this->form->renderField('user_id'); ?>
					<?php echo $this->form->renderField('comment'); ?>

				</fieldset>
			</div>
			<div class="span2">
				<fieldset class="adminform">
					<?php echo $this->form->renderField('created_by'); ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('created_date'); ?></div>
						<div class="controls"><?php echo $this->item->created_date; ?></div>
					</div>

					<?php if ($this->state->params->get('save_history', 1)) : ?>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('version_note'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('version_note'); ?></div>
						</div>
					<?php endif; ?>
				</fieldset>
			</div>

			<div class="clearfix"></div>

            <div class="form-horizontal" style="width:45%; float:left;">
				<pre>Pre Update Data<br />
					<?php print_r($preRec); ?>
				</pre>
			</div>
            <div class="form-horizontal" style="width:45%; float:right;">
				<pre>Post Update Data<br />
					<?php print_r($postRec);?>
				</pre>
			</div>

			<div class="clearfix"></div>
			<pre>Difference<br />
				<?php print_r($new_result);?>
			</pre>

		</div>
		</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
