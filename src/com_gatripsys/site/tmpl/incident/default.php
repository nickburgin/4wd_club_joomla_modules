<?php
/**
 * @version    5.3.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

//Load admin language file
//$lang = Factory::getApplication()->getLanguage();
//$lang->load('com_gatripsys', JPATH_ADMINISTRATOR);
$lang = Factory::getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR, 'en-GB', true);

$canEdit = GatripsysHelper::getSpecificUser()->authorise('core.edit', 'com_gatripsys');
if (!$canEdit && GatripsysHelper::getSpecificUser()->authorise('core.edit.own', 'com_gatripsys')) {
	$canEdit = GatripsysHelper::getSpecificUser()->id == $this->item->created_by;
}

// find the file type for displaying
$dispFile = '';
$imgs = array('jpg', 'jpeg', 'png', 'gif');
if (isset($this->item->inc_img) && $this->item->inc_img > '') {
    $file = \pathinfo($this->item->inc_img);
    $src =  $file['dirname'] . '/' . $file['basename'];
    if (in_array($file['extension'], $imgs)) {
        // show image
        $dispFile = '<img src="'.$src.'" title="'.$file['filename'].'" />';
    } elseif ($file['extension'] == 'pdf') {
        // show pdf
        $dispFile = '<iframe width="100%" height="300" style="border: 1px solid black;" src="'.$src.'" allowfullscreen></iframe>';
    }
}

/*
echo  GatripsysHelper::gaPrint($file);
*/
?>
<?php if ($this->item) : ?>
	<h2>Incident Details</h2>
	<div class="item_fields">
		<table class="table">
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_STATE'); ?></th>
				<td>
					<i class="icon-<?php echo ($this->item->state == 1) ? 'publish' : 'unpublish'; ?>"></i>
				</td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_CREATED_BY'); ?></th>
				<td><?php echo $this->item->created_by_name; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_CREATED_DATE'); ?></th>
				<td><?php echo $this->item->created_date; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_MODIFIED_BY'); ?></th>
				<td><?php echo $this->item->modified_by_name; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_UPDATE_DATE'); ?></th>
				<td><?php echo $this->item->modified_date; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_INCIDENT_USER_ID'); ?></th>
				<td><?php echo $this->item->user_id_name; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_INCIDENT_TRIP_ID'); ?></th>
				<td><?php echo '('.$this->item->trip->id.') '.$this->item->trip->title.' - Depart: '.substr($this->item->trip->dept_date,0,10); ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_INCIDENT_INCID_DATE'); ?></th>
				<td><?php echo $this->item->incid_date; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_INCIDENT_PERS_INVOLVED'); ?></th>
				<td><?php echo $this->item->pers_involved; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_INCIDENT_LOCATION'); ?></th>
				<td><?php echo $this->item->location; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_INCIDENT_MAP_REF'); ?></th>
				<td><?php echo $this->item->map_ref; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_INCIDENT_GPS_REF'); ?></th>
				<td>
					<a href="https://www.google.com.au/maps?t=h&q=loc:<?php echo $this->item->gps_ref; ?>&z=17" target="_blank"><?php echo $this->item->gps_ref; ?></a>
				</td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_INCIDENT_WITNESSES'); ?></th>
				<td><?php echo $this->item->witnesses; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_COMMENT'); ?></th>
				<td><?php echo $this->item->comment; ?></td>
			</tr>
			<tr>
				<th style="vertical-align:top;"><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_INCIDENT_INC_IMG'); ?></th>
				<td><?php echo '<p class="center">'.$dispFile.'</p><p class="center small">'.$src.'</p>'; ?></td>
			</tr>

		</table>
	</div>
	<div style="margin: 15px 0;">
		<a href="<?php echo Route::_('index.php?option=com_gatripsys&view=trip&id='.$this->item->trip_id); ?>" class="btn btn-warning" type="button" title="Trip Details" >
			<i class="icon-undo-2"></i> <?php echo Text::_('Return'); ?>
		</a>
		<?php if($canEdit && $this->item->checked_out == 0): ?>
			<a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_gatripsys&task=incidentform.edit&id='.$this->item->id.'&trip_id='.$this->item->trip_id); ?>">
				<i class="icon-edit"></i> <?php echo Text::_("COM_GATRIPSYS_EDIT_ITEM"); ?>
			</a>
		<?php endif; ?>
		<?php if(GatripsysHelper::getSpecificUser()->authorise('core.admin','com_gatripsys')):?>
			<a class="btn btn-danger" href="<?php echo Route::_('index.php?option=com_gatripsys&task=incidentform.remove&id=' . $this->item->id, false); ?>">
				<i class="icon-trash"></i> <?php echo Text::_("COM_GATRIPSYS_DELETE_ITEM"); ?>
			</a>
		<?php endif; ?>
	</div>
<?php else: ?>
	<?php echo Text::_('COM_GATRIPSYS_ITEM_NOT_LOADED'); ?>
<?php endif; ?>
