<?php
/**
 * @version     5.1.0
 * @package     com_gatripsys
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */
// no direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

// load any assets required
$wa = $this->document->getWebAssetManager();
$wa->usePreset('com_gatripsys.gatripsyspreset');

$tmpl = Factory::getApplication()->getTemplate(true);
if ($tmpl->params->get('colorName') == 'colors_white') {
    GatripsysHelper::loadTmplStyleModal($wa);
}

//Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR);

$user = GatripsysHelper::getSpecificUser($this->item->user_id);
$today = GatripsysHelper::getTodaysDate();


$this->form->setFieldAttribute('id', 'type', 'hidden');
$this->form->setFieldAttribute('user_id', 'type', 'hidden');
$this->form->setFieldAttribute('user_name', 'type', 'hidden');

?>
<div class="invoice-edit front-end-edit">
<form
	id="form-invoice" action="<?php echo Route::_('index.php?option=com_gatripsys&task=invoiceform.markAsPaid&tmpl=component'); ?>"
	method="post" class="form-validate form-horizontal" enctype="multipart/form-data" target="_parent">

    <h3>Mark Invoice as Paid for <?php echo $user->name; ?></h3>
    <div class="row-fluid">
        <div class="span10 form-horizontal">

            <?php echo $this->form->renderField('id'); ?>
            <?php echo $this->form->renderField('user_id'); ?>
            <?php echo $this->form->renderField('user_name'); ?>

            <fieldset name="payment" class="adminform">
    			<?php echo $this->form->renderFieldset('payment'); ?>
			</fieldset>

        </div>
        <button type="submit" class="validate btn btn-primary"><?php echo Text::_('JSUBMIT'); ?></button>
        <input type="hidden" name="task" value="invoiceform.markAsPaid" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
</div>

