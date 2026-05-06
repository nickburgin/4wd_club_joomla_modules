<?php
/**
 * @version    6.0.0
 * @package    Com_Gausers
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Date\Date;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\HTML\HTMLHelper;
use GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use GlennArkell\Component\Gausers\Administrator\Helper\GainvoiceHelper;
use GlennArkell\Component\Gausers\Administrator\Helper\GanamesHelper;
use GlennArkell\Component\Gausers\Administrator\Helper\GamodalHelper;

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gausers.gauserspreset');

// Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gausers', JPATH_ADMINISTRATOR);

$user       = Factory::getApplication()->getIdentity();
$listOrder  = $this->state->get('list.ordering', 'user_id_name');
$listDirn   = $this->state->get('list.direction', 'asc');
$canCreate  = $user->authorise('core.create', 'com_gausers');
$canEdit    = $user->authorise('core.edit', 'com_gausers');
$canCheckin = $user->authorise('core.manage', 'com_gausers');
$canChange  = $user->authorise('core.edit.state', 'com_gausers');
$canDelete  = $user->authorise('core.delete', 'com_gausers');

$profSuf = $this->params->get('profile_suffix', 'b4wdc');
$profPartner = 'profile'.$profSuf.'.partner';
$default_mship = $this->params->get('default_mship', 1);
$mship = GausersHelper::getMshiptypeID($default_mship);

$jdate       = Factory::getDate();
$jdate = $jdate->format('Y-m-d');
$mshipPeriod = $this->params->get('mship_period', 1);
$lastInvDate = $this->params->get('invoice_date');
$newInvDate = \date('Y-m-d', strtotime($lastInvDate." +".($mship->mship_term-1)." ".$mship->term_type));
$new_invDate = \date('Y-m-d', strtotime($newInvDate." +9 months"));
//$new_invDate = $new_invDate->modify('+'.($mship->mship_term-1).' '.$mship->term_type);
//$new_invDate = $new_invDate->modify('+10 months');
$inv_ready = ($jdate > $new_invDate) && $mshipPeriod != 3 ? true : false;

$paramInvDate = new Date(strtotime($this->params->get('invoice_date') ?? ''));
$oldStartDate = date_format($paramInvDate->modify('-'.$mship->mship_term.' '.$mship->term_type),'Y-m-d');
$eDate = $paramInvDate->modify('+'.$mship->mship_term.' '.$mship->term_type);
$newEndDate = $eDate->modify('-1 DAY');

//$invNeeded = GausersHelper::checkNewInvoicesDue($mship, $new_invDate);

$emailPre = $this->params->get('exclude_email_pref');
$preLen = strlen($emailPre);

$genLink = GausersHelper::getHTTPQuery(null, 'task', 'invoice.generateInv', null, null);
$genLink = GausersHelper::getHTTPQuery($genLink, null, null, Session::getFormToken(), '1');
$genURL = 'index.php?'.http_build_query($genLink, '', '&amp;');

$updLink = GausersHelper::getHTTPQuery(null, 'task', 'invoices.disableuser', null, null);
$updURL = 'index.php?'.http_build_query($updLink, '', '&amp;');
$rinLink = GausersHelper::getHTTPQuery(null, 'task', 'invoices.reinstateuser', null, null);
$rinURL = 'index.php?'.http_build_query($rinLink, '', '&amp;');

$remLink = GausersHelper::getHTTPQuery(null, 'task', 'invoices.reminderInv', null, null);
$remLink = GausersHelper::getHTTPQuery($remLink, null, null, Session::getFormToken(), '1');
$remURL = 'index.php?'.http_build_query($remLink, '', '&amp;');

$filterState = $this->getState('filter.state');

//GausersHelper::gaPrint(Factory::getApplication()->getUserState('com_gausers.test.data'));
//Factory::getApplication()->setUserState('com_gausers.test.data', null);

?>
<h2><?php echo $this->params->get('page_heading'); ?></h2>
<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm">

    <?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>

	<div class="clearfix" style="margin-bottom:20px;"> </div>
	
	<table class="table table-striped" id="invoiceList">
		<thead>
		<tr>
			<th class='mbr-name'>
				<?php echo Text::_('COM_GAUSERS_INVOICES_MEMBER_NAME'); ?>
			</th>
			<th class='inv-amt hidden-phone'>
				<?php echo Text::_('COM_GAUSERS_INVOICES_AMT'); ?>
			</th>
			<th class='inv-date'>
				<?php echo Text::_('COM_GAUSERS_INVOICES_END_DATE'); ?>
			</th>
			<th class='inv-date hidden-phone'>
				<?php echo Text::_('COM_GAUSERS_INVOICES_PAID_DATE'); ?>
			</th>

			<?php if ($canEdit || $canDelete): ?>
				<th class="inv-acts center">
					<?php echo Text::_('COM_GAUSERS_INVOICES_ACTIONS'); ?>
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
                $canEdit = $user->authorise('core.edit', 'com_gausers');

                $mship = GausersHelper::getMshiptypeID($item->mship_id);

                // setup the modal links etc
                $payBtn = GamodalHelper::setupModalButton('view', 'invoiceform', 'id', $item->id, 'modalpaid', 'myModal', 'secondary', '', '', 'fa-regular fa-credit-card', $user->name);

        		// set up the delete link of a invoice record
        		$removeLink = GausersHelper::getHTTPQuery(null, 'task', 'invoice.remove', 'id', $item->id);
                $removeURL = 'index.php?'.http_build_query($removeLink, '', '&amp;');

        		// set up the resend link of a invoice record
        		$resendLink = GausersHelper::getHTTPQuery(null, 'task', 'invoice.resendInv', 'id', $item->id);
        		$resendLink = GausersHelper::getHTTPQuery($resendLink, null, null, Session::getFormToken(), '1');
                $resendURL = 'index.php?'.http_build_query($resendLink, '', '&amp;');

                $resetLink = GausersHelper::getHTTPQuery(null, 'task', 'invoice.publish', 'id', $item->id);
        		$resetLink = GausersHelper::getHTTPQuery($resetLink, null, null, 'state', 1);
                $resetURL = 'index.php?'.http_build_query($resetLink, '', '&amp;');

                $regenLink = GausersHelper::getHTTPQuery(null, 'task', 'invoice.regenInvoice', 'id', $item->id);
        		$regenLink = GausersHelper::getHTTPQuery($regenLink, null, null, Session::getFormToken(), '1');
                $regenURL = 'index.php?'.http_build_query($regenLink, '', '&amp;');

    			if (!$canEdit && $user->authorise('core.edit.own', 'com_gausers')) {
					$canEdit = $user->id == $item->created_by;
    			}

				if($item->block == 1) {
					$txt_style = '';
				} else {
					if ($item->snailMail) {
						$txt_style = ' style="color:red;"';
					} else {
						$txt_style = ' style="color:green;"';
					}
				}
				
				// gather membership name
                $member = GanamesHelper::breakdownNamesFromUserID($item->user_id, $profPartner);

                if ($member) {
					$item->fullname = GanamesHelper::combineNames($member);
				} else {
					if (empty($item->user_id_name)) {
                        $item->fullname = ' - * - * - Member Deleted? - * - * - ';
                    } else {
                        $item->fullname = $item->user_id_name;
                    }
				}
				
				if ($item->registerDate > $oldStartDate) {
    				$joinDate = '<span class="smallTxt"> (Reg: '. HtmlHelper::date($item->registerDate, Text::_('COM_GAUSERS_STD_DATE')).')</span>';
				} else {
                    $joinDate = '';
                }
                $filename = 'images/members/invoices/Invoice'.$this->escape(str_pad($item->id, 6, '0', STR_PAD_LEFT)).'.pdf';
                if (\is_file($filename)) {
                    $pdfLink = '<a href="'.$filename.'" target="_blank" title="'.Text::_("COM_GAUSERS_INVOICE_PREVIEW").'">';
                    $pdfLink .= str_pad($item->id, 6, "0", STR_PAD_LEFT).' - '.$this->escape($item->fullname);
                    $pdfLink .= '</a> &nbsp;'.$joinDate;
                    $noInvoice = false;
                } else {
                    $pdfLink = 'No Invoice on File for '.$this->escape($item->fullname).' - '.$joinDate;
                    $noInvoice = true;
                }

			?>
			<tr class="row<?php echo $i % 2; ?>">

				<td class='mbr-name'>
					<?php if (isset($item->checked_out) && $item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'invoices.', $canCheckin); ?>
					<?php endif; ?>

                    <?php echo $pdfLink; ?>
				</td>
                <td class='inv-amt hidden-phone' <?php echo $txt_style; ?>><?php echo $item->invoice_amt; ?></td>
                <td class='inv-date' <?php echo $txt_style; ?>><?php echo $edate = !empty($item->end_date) ? HtmlHelper::date($item->end_date, Text::_('COM_GAUSERS_DISPLAY_DATE')) : ''; ?></td>
                <td class='inv-date hidden-phone' <?php echo $txt_style; ?>><?php echo $pdate = !empty($item->paid_date) ? HtmlHelper::date($item->paid_date, Text::_('COM_GAUSERS_DISPLAY_DATE')) : ''; ?></td>
                <td class='inv-acts'>
                    <?php if($user->authorise('core.members', 'com_gausers')): ?>

                        <?php if ($filterState != 2) : ?>
    						<a href="<?php echo Route::_($resendURL); ?>" class="btn btn-secondary"
    							title="<?php echo Text::_('COM_GAUSERS_INVOICE_RESEND'); ?>" ><i class="icon-mail"></i>
    						</a>
                        <?php endif; ?>

                        <?php if ($filterState == 1 || $filterState == '') : ?>
                            <?php echo $payBtn; ?>
    						<?php if ($canDelete): ?>
    							<a href="<?php echo Route::_($removeURL); ?>" class="btn btn-secondary delete-button" type="button" >
                                    <i class="icon-trash"></i>
    							</a>
    						<?php endif; ?>
                        <?php endif; ?>
                        <?php if ($filterState == -2) : ?>
    							<a href="<?php echo Route::_($resetURL); ?>" class="btn btn-secondary"
                                    title="<?php echo Text::_('COM_GAUSERS_INVOICE_RESET'); ?>" ><i class="icon-redo"></i>
    							</a>
                        <?php endif; ?>
                        <?php if ($noInvoice) : ?>
    							<a href="<?php echo Route::_($regenURL); ?>" class="btn btn-success"
                                    title="<?php echo Text::_('COM_GAUSERS_INVOICE_REGEN'); ?>" ><i class="icon-redo"></i>
    							</a>
                        <?php endif; ?>

                    <?php endif; ?>
                </td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<div class="span2"></div>
	<div class="span9">
		<p><strong><em>Note:</em></strong> <?php echo Text::_('COM_GAUSERS_INVOICE_LIST_NOTE'); ?></p>
	</div>

	<?php if ($canCreate) : ?>
    	<?php if ($filterState == '') : ?>
    		<a href="<?php echo Route::_($updURL); ?>" class="btn btn-danger pull-right"
    			title="<?php echo Text::_('COM_GAUSERS_DISABLEUSERS_DESC'); ?>">
    		   <i class="icon-cross"></i> <?php echo Text::_('COM_GAUSERS_DISABLEUSERS'); ?>
    		</a>
    		<a href="<?php echo Route::_($rinURL); ?>" class="btn btn-warning pull-right"
    			title="<?php echo Text::_('COM_GAUSERS_REINSTATEUSERS_DESC'); ?>">
    		   <i class="icon-tick"></i> <?php echo Text::_('COM_GAUSERS_REINSTATEUSERS'); ?>
    		</a> &nbsp;
    		<a href="<?php echo Route::_($remURL); ?>" class="btn btn-info pull-right"
    			title="<?php echo Text::_('COM_GAUSERS_REMINDERINV_DESC'); ?>">
    		   <i class="icon-envelope"></i> <?php echo Text::_('COM_GAUSERS_REMINDERINV'); ?>
    		</a>
    	<?php endif; ?>
    	<?php if ($inv_ready) : ?>
    		<a href="<?php echo Route::_($genURL); ?>" class="gen-invoices btn btn-success pull-right"
    			title="<?php echo Text::_('COM_GAUSERS_CREATE_NEW_INVOICES_DESC'); ?>">
    		   <i class="icon-plus"></i> <?php echo Text::_('COM_GAUSERS_CREATE_NEW_INVOICES'); ?>
    		</a> &nbsp;
    	<?php endif; ?>
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
	
			if (!confirm("<?php echo Text::_('COM_GAUSERS_DELETE_MESSAGE'); ?>")) {
				return false;
			}
		}
		function invoiceItem() {
	
			if (!confirm("<?php echo Text::_('COM_GAUSERS_INVOICE_MESSAGE'); ?>")) {
				return false;
			}
		}
	</script>
<?php endif; ?>
