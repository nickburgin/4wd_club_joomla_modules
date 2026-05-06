<?php
/**
 * @version    5.2.3
 * @package    Com_Gafinance
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GafinanceHelper;

$wa = $this->document->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_gafinance');
$wa->useScript('keepalive')
	->useScript('form.validate');

// format the data elements
$pre_record = json_decode($this->item->pre_record);
$preRec = (array) $pre_record;
ksort($preRec);
$post_record = json_decode($this->item->post_record);
$postRec = (array) $post_record;
ksort($postRec);

// add some reference information
if (isset($preRec[0]) && $preRec[0] == 'New Record') {
	$accnt_name = GafinanceHelper::getAccount($postRec['accnt_id'])->accnt_name;
	$user_name = GafinanceHelper::getSpecificUser($postRec['user_id'])->name;
} else {
	$accnt_name = $preRec['accnt_name'];
	$user_name = $preRec['user_name'];
}
$cat_name = isset($this->item->trans->cat_name) ? $this->item->trans->cat_name : '';
$tran_ref = isset($this->item->trans->tran_ref) ? $this->item->trans->tran_ref : '';
$tran_desc = isset($this->item->trans->tran_desc) ? $this->item->trans->tran_desc : '';
$created_date = isset($this->item->trans->created_date) ? $this->item->trans->created_date : '';

// clean up viewable data for ease of reading
unset($preRec['accnt_name']);
unset($preRec['user_name']);
unset($preRec['cat_name']);
unset($preRec['checked_out']);
unset($preRec['checked_out_time']);
unset($preRec['ordering']);
unset($preRec['created_by']);
unset($preRec['created_date']);

unset($postRec['gst_flag']);
unset($postRec['ordering']);
unset($postRec['created_by']);
unset($postRec['created_date']);

// get the comparison difference
if (isset($preRec[0]) && $preRec[0] == 'New Record') {
	$result = 'No comparison done because a new record';
} else {
	$result = array_diff($postRec,$preRec);
}

?>
<form
	action="<?php echo Route::_('index.php?option=com_gafinance&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="audit-form" class="form-validate form-horizontal">

	
	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_GAFINANCE_AUDIT_DETAILS', true)); ?>
		<div class="row-fluid">
			<div class="span11 form-horizontal">
                <h2>These details can not be altered</h2>
                <p><strong>Some details to help identify record:</strong><br />
					<strong>Acount: </strong><?php echo $accnt_name; ?><br />
					<strong>User: </strong><?php echo $user_name; ?><br />
					<strong>Category: </strong><?php echo $cat_name; ?><br />
					<strong>TransRef: </strong><?php echo $tran_ref; ?><br />
					<strong>TransDesc: </strong><?php echo $tran_desc; ?> <br />
					<strong>TransCreated: </strong><?php echo $created_date; ?>
				</p>
                <div style="width:48%;float:left;">
					<pre>Pre Update Data<br />
						<?php print_r($preRec); ?>
					</pre>
				</div>
	            <div style="width:48%;float:right;">
					<pre>Post Update Data<br />
						<?php print_r($postRec);?>
					</pre>
				</div>
				<div class="clearfix"></div>
				<pre>Difference<br />
					<?php print_r($result);?>
				</pre>
			</div>
		</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'extrainfo', Text::_('COM_GAFINANCE_XTRAINFO', true)); ?>
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset name="extrainfo" class="adminform">
					<?php echo $this->form->renderFieldset('extrainfo'); ?>
				</fieldset>
			</div>
		</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'sysinfo', Text::_('COM_GAFINANCE_SYSINFO', true)); ?>
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset name="sysinfo" class="adminform">
					<?php echo $this->form->renderFieldset('sysinfo'); ?>
				</fieldset>
			</div>
		</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>

</form>
