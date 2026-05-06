<?php
/**
 * @version    5.1.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Session\Session;
use \Joomla\CMS\Date\Date;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GainvoiceHelper;

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gatripsys.gatripsyspreset');

// Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR);

$user       = GatripsysHelper::getSpecificUser();
$listOrder  = $this->state->get('list.ordering', 'a.user_id');
$listDirn   = $this->state->get('list.direction', 'asc');
$canCreate  = $user->authorise('core.create', 'com_gatripsys');
$canEdit    = $user->authorise('core.edit', 'com_gatripsys');
$canCheckin = $user->authorise('core.manage', 'com_gatripsys');
$canChange  = $user->authorise('core.edit.state', 'com_gatripsys');
$canDelete  = $user->authorise('core.invoice', 'com_gatripsys');

$jdate = new Date();
$jdate = $jdate->format('Y-m-d');
$invDate = $this->params->get('invoice_date', 'now');
$new_invDate = new Date($invDate);
$new_invDate = $new_invDate->modify('+10 months');
$inv_ready = ($jdate > $new_invDate) ? true : false;
$emailPre = $this->params->get('exclude_email_pref');
$invpref = $this->params->get('inv_pref', 'TRIP');
$preLen = \strlen($emailPre);

?>
<h2><?php echo $this->params->get('page_heading'); ?></h2>
<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm" class="com-content-category__articles">

	<?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>

    <div class="table-responsive com-contact-categories categories-list">
	<table class="table table-striped" id="invoiceList">
		<thead>
		<tr>
			<th class='mbr-name'>
				<?php echo Text::_('COM_GATRIPSYS_INVOICES_MEMBER_NAME'); ?>
			</th>
			<th class='trip-name'>
				<?php echo Text::_('COM_GATRIPSYS_INVOICES_TRIP_TITLE'); ?>
			</th>
			<th class='inv-amt'>
				<?php echo Text::_('COM_GATRIPSYS_INVOICES_AMT'); ?>
			</th>
			<th class='inv-date hidden-phone'>
				<?php echo Text::_('COM_GATRIPSYS_INVOICES_INV_DATE'); ?>
			</th>
			<?php if ($canEdit || $canDelete): ?>
				<th class="inv-acts center">
					<?php echo Text::_('COM_GATRIPSYS_INVOICES_ACTIONS'); ?>
				</th>
			<?php endif; ?>

		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) : ?>
            <?php
                $canEdit = $user->authorise('core.edit', 'com_gatripsys');

                // setup the modal links etc
                $payLink = GatripsysHelper::getHTTPQuery(null, 'view', 'invoiceform', 'id', $item->id);
				$payLink = GatripsysHelper::getHTTPQuery($payLink, null, null, 'tmpl', 'component');
				$payLink = GatripsysHelper::getHTTPQuery($payLink, null, null, 'layout', 'modalpaid');
				$modalParams = array(
				        'url'        => 'index.php?'.http_build_query($payLink, '', '&amp;'),
				        'title'      => Text::_("COM_GATRIPSYS_MARKPAID"),
				        'closeButton'=> true,
				        'modalWidth' => 60,
				        'bodyHeight' => 75,
				        'backdrop'   => 'static'
				        );
				$modalname = 'modal-myModal'.$item->id;
				$html = '<a class="btn btn-secondary" href="#'.$modalname.'" data-bs-toggle="modal">';
				$html .= '<i class="icon-credit" title="'.Text::_('COM_GATRIPSYS_MARKPAID').'"></i></a>';
				
				if (!$canEdit && $user->authorise('core.edit.own', 'com_gatripsys')) {
                    $canEdit = GatripsysHelper::getSpecificUser()->id == $item->created_by;
                }

				if($item->block) {
					$txt_style = '';
				} else {
                    $txt_style = ' style="color:green;"';
					if (substr($item->email,0,$preLen) == $emailPre) {
						$txt_style = ' style="color:red;"';
					}
				}
				$member = GainvoiceHelper::breakdownNamesFromUserID($item->user_id);
				$mshipname = GainvoiceHelper::combineNames($member);

			?>
			<tr class="row<?php echo $i % 2; ?>">

				<td class='mbr-name'>
					<?php if (isset($item->checked_out) && $item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'invoices.', $canCheckin); ?>
					<?php endif; ?>
                    <a href="images/trips/invoices/<?php echo $invpref.$this->escape(str_pad($item->id, 6, '0', STR_PAD_LEFT)).'.pdf'; ?>"
						target="_blank" title="Preview Invoice">
						<?php echo str_pad($item->id, 4, '0', STR_PAD_LEFT).' - '.$this->escape($mshipname); ?>
					</a>
				</td>
                <td class='trip-name' <?php echo $txt_style; ?>><?php echo substr($item->trip_title,0,60) ; ?></td>
                <td class='inv-amt' <?php echo $txt_style; ?>><?php echo $item->invoice_amt ; ?></td>
                <td class='inv-date hidden-phone' <?php echo $txt_style; ?>>
                    <?php echo $item->created_date > 0 ? HTMLHelper::date($item->created_date, Text::_('COM_GATRIPSYS_DISPLAY_DATETXT')) : '-'; ?>
                </td>
                <td class='inv-acts'>
                    <?php if ($item->state == 1) : ?>
						<?php if($user->authorise('core.invoice', 'com_gatripsys')): ?>
							<a href="<?php echo Route::_('index.php?option=com_gatripsys&task=invoice.resendInv&id='.$item->id.'&'. Session::getFormToken() .'=1'); ?>"
                                title="Resend Invoice" class="btn btn-warning"><i class="icon-mail-2"></i>
							</a>&nbsp;
							<?php if ($canDelete): ?>
								<a href="<?php echo Route::_('index.php?option=com_gatripsys&task=invoice.remove&id=' . $item->id, false, 2); ?>"
								class="btn btn-danger delete-button" type="button" title="Cancel Invoice"><i class="icon-trash"></i>
								</a>&nbsp;
							<?php endif; ?>
	                        <?php echo $html .= HTMLHelper::_('bootstrap.renderModal', $modalname, $modalParams); ?>
	                    <?php endif; ?>
                    <?php endif; ?>
                </td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	</div>

	<div style="float: left;">
		<p><strong><em>Note:</em></strong> <?php echo Text::_('COM_GATRIPSYS_INVOICE_LIST_NOTE'); ?></p>
	</div>
	<?php if (\count($this->items) > 0) : ?>
		<div style="padding-left:10px; float:right;">
			<a href="<?php echo Route::_('index.php?option=com_gatripsys&task=invoices.sendReminder', false, 2); ?>"
				class="btn btn-info" type="button"><i class="icon-email"></i> <?php echo Text::_("COM_GATRIPSYS_SEND_REMINDER"); ?>
			</a>
		</div>
	<?php endif; ?>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php if($canDelete) : ?>
	<script type="text/javascript">
	
		jQuery(document).ready(function () {
			jQuery('.delete-button').click(deleteItem);
			jQuery('.gen-invoices').click(invoiceItem);
		});

		function deleteItem() {
			if (!confirm("<?php echo Text::_('COM_GATRIPSYS_DELETE_MESSAGE'); ?>")) {
				return false;
			}
		}
		function invoiceItem() {
			if (!confirm("<?php echo Text::_('COM_GATRIPSYS_INVOICE_MESSAGE'); ?>")) {
				return false;
			}
		}
	</script>
<?php endif; ?>
