<?php
/**
 * @version    4.0.2
 * @package    Com_Gaforsale
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  Copyright (C) 2013. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Access\Access;
use \Joomla\CMS\Layout\LayoutHelper;
use \GlennArkell\Component\Gaforsale\Administrator\Helper\GaforsaleHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gaforsale', JPATH_ADMINISTRATOR);

$user = GaforsaleHelper::getSpecificUser();
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_gaforsale');
$canEdit    = $user->authorise('core.edit', 'com_gaforsale');
$canCheckin = $user->authorise('core.manage', 'com_gaforsale');
$canChange  = $user->authorise('core.edit.state', 'com_gaforsale');
$canDelete  = $user->authorise('core.delete', 'com_gaforsale');
$this->show_offerdate = $this->params->get('show_offerdate', 0);
$adminUser = $this->params->get('email_user', 0);

?>
<form action="<?php echo Route::_('index.php?option=com_gaforsale&view=fsitems'); ?>" method="post"
      name="adminForm" id="adminForm">

	<h2><?php echo Text::_($this->params->get('header_title')); ?></h2>
	<p><?php echo Text::_($this->params->get('header_text')); ?></p>
	<p>&nbsp;</p>

	<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

	<?php if ($this->items): ?>
		<table width="98%" class="table table-striped" id="fsitemList" style="border-top: 1px solid #000;">
			<thead>
			    <tr>
					<td class="salehead"><?php echo Text::_('COM_GAFORSALE_FSITEM_DESC'); ?></td>
					<td class="salehead"><?php echo Text::_('COM_GAFORSALE_FSITEM_PRICE'); ?></td>
				</tr>
			</thead>
	
			<tbody>
			    <?php foreach ($this->items as $item): ?>
					<?php /* Set up if item should be displayed */ ?>
					<?php $this->dispAdmin = ($canCheckin || ($user->id > 0 && $user->id == $adminUser)) ? true : false; ?>
					<?php $this->dispOwner = ($user->id == $item->user_id) ? true : false; ?>
                    <?php $this->record = $item; ?>

					<?php if ($item->state == 0 && $this->dispAdmin): ?>
						<tr><td colspan="2" style="border: 3px solid #f00;">
							    <h3><?php echo Text::_('COM_GAFORSALE_FSITEM_NEEDSTOBEPUBLISHED'); ?> &nbsp;
								<a class="btn btn-success" title="<?php echo Text::_('COM_GAFORSALE_FSITEM_MAKEVISIBLE'); ?>"
									href="index.php?option=com_gaforsale&task=fsitem.publish&id=<?php echo $item->id; ?>"
									data-original-title="<?php echo Text::_('COM_GAFORSALE_FSITEM_MAKEVISIBLE'); ?>">
							   	    <i class="icon-publish"></i>
					 			</a> &nbsp;
								<a class="btn btn-danger" title="<?php echo Text::_('COM_GAFORSALE_FSITEM_REMOVE'); ?>"
									href="index.php?option=com_gaforsale&task=fsitem.remove&id=<?php echo $item->id; ?>"
									data-original-title="<?php echo Text::_('COM_GAFORSALE_FSITEM_REMOVE'); ?>">
							   	    <i class="icon-trash"></i>
					 			</a>
					 			</h3>
				            </td>
				        </tr>
						<?php echo LayoutHelper::render('default_item', array('view' => $this), dirname(__FILE__)); ?>

					<?php elseif ($item->state == 0 && $this->dispOwner): ?>
						<tr><td colspan="2" style="border: 3px solid #f00;">
							    <h3><?php echo Text::_('COM_GAFORSALE_FSITEM_NEEDSTOBEPUBLISHED'); ?></h3>
				            </td>
				        </tr>
						<?php echo LayoutHelper::render('default_item', array('view' => $this), dirname(__FILE__)); ?>

					<?php elseif ($item->state == 1): ?>

						<?php echo LayoutHelper::render('default_item', array('view' => $this), dirname(__FILE__)); ?>

					<?php endif; ?>

			     <?php endforeach; ?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
		</table>
	<?php else : ?>
		<p><?php echo Text::_('COM_GAFORSALE_NO_ITEMS'); ?></p>
	<?php endif; ?>

	<?php if ($canCreate) : ?>
		<a href="<?php echo Route::_('index.php?option=com_gaforsale&task=fsitemform.edit&id=0', false, 2); ?>" class="btn btn-success btn-small">
			<i class="icon-plus"></i> <?php echo Text::_('COM_GAFORSALE_ADD_ITEM'); ?>
		</a>
	<?php endif; ?>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
