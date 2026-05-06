<?php
/**
 * @version     6.0.0
 * @package     com_gausers
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Session\SessionInterface;
use Joomla\CMS\Application\SiteApplication;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;

//Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gausers', JPATH_ADMINISTRATOR);

// load any assets required
$wa = $this->document->getWebAssetManager()
    ->usePreset('com_gausers.gauserspreset');
GausersHelper::loadTmplStyleModal($wa);

$user = GausersHelper::getSpecificUser($this->item->user_id);

// over-ride the pay_type with the default
$this->item->pay_type = $this->params->get('def_pay_type',0);

// get the current date-time based on timezone
$date  = Factory::getDate();
$today = date_format($date,'Y-m-d');
$this->form->setFieldAttribute('id', 'type', 'hidden');
$this->form->setFieldAttribute('user_id', 'type', 'hidden');
$this->form->setFieldAttribute('user_name', 'default', $user->name);
$this->form->setFieldAttribute('user_name', 'type', 'hidden');
$this->form->setFieldAttribute('paid_date', 'default', $today);

?>
<div class="edit item-page">
<form
	id="form-invoice" action="<?php echo Route::_('index.php?option=com_gausers&task=invoiceform.markAsPaid'); ?>"
	method="post" class="form-validate form-horizontal" enctype="multipart/form-data" target="_parent">

<h3>Mark Invoice as Paid for <?php echo $user->name; ?></h3>

    <div class="row-fluid">
        <div class="span12 form-horizontal">
        
            <?php echo $this->form->renderField('id'); ?>
            <?php echo $this->form->renderField('user_id'); ?>
            <?php echo $this->form->renderField('user_name'); ?>
            <?php echo $this->form->renderField('paid_date'); ?>
            <?php echo $this->form->renderField('pay_type'); ?>
            <?php echo $this->form->renderField('invoice_amt'); ?>

        </div>

        <p class="center">
            <button type="submit" class="validate btn btn-primary"><?php echo Text::_('JSUBMIT'); ?></button>
        </p>
    </div>
    <div>
        <input type="hidden" name="task" value="invoiceform.markAsPaid" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
</div>
