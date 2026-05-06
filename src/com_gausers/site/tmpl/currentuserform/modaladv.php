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
use Joomla\CMS\Date\Date;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Session\SessionInterface;
use Joomla\CMS\Application\SiteApplication;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GainvoiceHelper;

//Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gausers', JPATH_ADMINISTRATOR);

$wa = $this->document->getWebAssetManager()
    ->usePreset('com_gausers.gauserspreset');
GausersHelper::loadTmplStyleModal($wa);

if (!isset($this->item->id)) {
    $id = $this->state->get('currentuser.id');
} else {
    $id = $this->item->id;
}
$user = GausersHelper::getSpecificUser($id);
$inv = GainvoiceHelper::getLastInvoiceMship($id);

// set up the next date
$lastEndDate = new Date($inv->end_date);
$nxtEndDate = $lastEndDate->modify('+'.$inv->mship_term.' '.$inv->term_type);
$newEndDate = date_format($nxtEndDate,'Y-m-d');

$this->form->setFieldAttribute('user_id', 'default', $id);
$this->form->setFieldAttribute('user_id', 'type', 'hidden');
$this->form->setFieldAttribute('new_end_date', 'default', $newEndDate);

?>

<div class="front-end-edit">
<form
	id="form-gauser" action="<?php echo Route::_('index.php?option=com_gausers&task=currentuserform.advPayment&tmpl=component'); ?>"
	method="post" class="form-validate form-horizontal" enctype="multipart/form-data" target="_parent">

    <?php echo '<p class="center">'.Text::sprintf('COM_GAUSERS_LAST_INV_PAID', $inv->title, $inv->end_date).'</p>'; ?>
    <div class="row-fluid">
        <div class="span12 form-horizontal">

            <?php echo $this->form->renderField('user_id'); ?>
            <?php echo $this->form->renderField('mship_id'); ?>
            <?php echo $this->form->renderField('new_end_date'); ?>

        </div>
        <p class="center">
            <button type="submit" class="validate btn btn-primary"><?php echo Text::_('JSUBMIT'); ?></button>
        </p>
    </div>
    <div>
        <input type="hidden" name="task" value="currentuserform.advPayment" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
</div>
