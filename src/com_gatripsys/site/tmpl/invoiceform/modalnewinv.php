<?php
/**
 * @version     5.3.0
 * @package     com_gatripsys
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

//Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR);
$new_users = $this->params->get('new_users', 0);
$auto_users = $this->params->get('auto_users',0);

$wa = $this->document->getWebAssetManager();
$tmpl = Factory::getApplication()->getTemplate(true);
if ($tmpl->params->get('colorName') == 'colors_white') {
    GatripsysHelper::loadTmplStyleModal($wa);
}

/*
echo '<pre>Test<br />';
print_r(Factory::getApplication()->getUserState('com_gatripsys.test.data'));
echo '</pre>';

*/
?>


<div class="usernew-edit front-end-edit">
    <?php if (!empty($this->item->id)): ?>
        <h2>Edit <?php echo $this->item->id; ?></h2>
    <?php else: ?>
        <h2>Create Membership Invoice</h2>
    <?php endif; ?>

    <form id="form-invoice" action="<?php echo Route::_('index.php'); ?>" method="post" class="form-validate" enctype="multipart/form-data" target="_parent">
        <div class="span12 form-horizontal">
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
		
		        <div>
		            <button type="submit" class="validate btn btn-primary"><span><?php echo Text::_('JSUBMIT'); ?></span></button>
		
		            <input type="hidden" name="option" value="com_gatripsys" />
		            <input type="hidden" name="task" value="invoice.newinv" />
		            <?php echo HTMLHelper::_('form.token'); ?>
		        </div>
            <?php else: ?>
				<input type="hidden" name="jform[invoice_amt]" value="0.00" />
				<?php echo $this->form->renderField('user_id'); ?>
		        <div class="clearfix"> </div>

		        <div>
		            <button type="submit" class="validate btn btn-primary"><span><?php echo Text::_('JSUBMIT'); ?></span></button>

		            <input type="hidden" name="option" value="com_gatripsys" />
		            <input type="hidden" name="task" value="invoice.newinv" />
		            <?php echo HTMLHelper::_('form.token'); ?>
		        </div>
			<?php endif; ?>

        </div>

    </form>
</div>
