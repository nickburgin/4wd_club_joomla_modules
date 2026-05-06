<?php
/**
 * @version     5.1.0
 * @package     com_gafinance
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com> - http://www.glennarkell.com
 */


// no direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Date\Date;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Component\ComponentHelper;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GafinanceHelper;

// load any assets required
// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gafinance.gafinancepreset');

//Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gafinance', JPATH_ADMINISTRATOR);

// clear any old session data saved
$app = Factory::getApplication();
$app->setUserState('com_gafinance.currentassetvalue.data', null);
$app->setUserState('com_gafinance.rptprint.data', null);
$combine_accnts = $this->params->get('combine_accnts', 0);

?>
<div class="page-header">
    <h2><?php echo Text::_('COM_GAFINANCE_SELECT_REPORT'); ?></h2>
</div>
<div class="transaction-edit front-end-edit">
<form id="form-transaction"
	  action="<?php echo Route::_('index.php?option=com_gafinance&task=transactionform.finreport'); ?>"
	  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

	<div class="form-horizontal">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'rptreq')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'rptreq', Text::_('COM_GAFINANCE_REPORT_PARAMS', true)); ?>
				<div class="row-fluid">
					<div class="span10 form-horizontal">
						<fieldset name="rptreq" class="adminform">
							<?php echo $this->form->renderFieldset('rptreq'); ?>
							<?php if (!$combine_accnts) : ?>
								<?php echo $this->form->renderField('accnt_id'); ?>
							<?php endif; ?>
						</fieldset>
					</div>
				</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

		<div class="control-group">
			<div class="controls">
				<button type="submit" class="validate btn btn-primary">
					<span><?php echo Text::_('JSUBMIT'); ?></span>
				</button>
			</div>
		</div>
		<input type="hidden" name="option" value="com_gafinance" />
		<input type="hidden" name="task" value="transactionform.finreport" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>

</form>
</div>
<div class="clr"></div>
