<?php
/**
 * @version    4.0.7
 * @package    com_gatripsys
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
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR);

$user    = GatripsysHelper::getSpecificUser();
$canEdit = GatripsysHelper::canUserEdit($user, $this->item);
if (!$canEdit) {
	$canEdit = $user->id == $this->item->leader;
}

$charge_trip = $this->params->get('charge_trip', 0);

$this->form->removeField('insurance_cat');
$this->form->removeField('version_note');
$submitLink = GatripsysHelper::getHTTPQuery(null, 'task', 'tripform.saveComment', null, null);
?>

<div class="trip-edit front-end-edit">
	<?php if (!$canEdit) : ?>
		<h3>
			<?php throw new \Exception(Text::_('COM_GATRIPSYS_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
		</h3>
	<?php else : ?>
		<h1><?php echo Text::sprintf('COM_GATRIPSYS_ADD_COMMENT_TRIP', $this->item->title); ?></h1>

		<form id="form-attendee" action="<?php echo Route::_('index.php?'.http_build_query($submitLink, '', '&amp;')); ?>"
			method="post" class="form-validate form-horizontal" enctype="multipart/form-data" target="_parent">

			<div class="row-fluid">
				<div class="span10 form-horizontal">
					<fieldset name="extrainfo" class="adminform" style="padding: 0 10px !important;">
						<?php echo $this->form->renderFieldset('extrainfo'); ?>
						<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>"/>
						<input type="hidden" name="jform[title]" value="<?php echo $this->item->title; ?>" />
					</fieldset>
				</div>
			</div>

			<div class="control-group">
				<div class="controls">

					<?php if ($this->canSave): ?>
						<button type="submit" class="validate btn btn-primary">
							<span class="fas fa-check" aria-hidden="true"></span>
							<?php echo Text::_('JSUBMIT'); ?>
						</button>
					<?php endif; ?>
				</div>
			</div>

			<input type="hidden" name="option" value="com_gatripsys"/>
			<input type="hidden" name="task" value="tripform.saveComment"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	<?php endif; ?>
</div>
