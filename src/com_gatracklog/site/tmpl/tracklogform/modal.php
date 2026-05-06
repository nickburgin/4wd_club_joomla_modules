<?php
/**
 * @version    4.1.0
 * @package    com_gatracklog
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gatracklog\Administrator\Helper\GatracklogHelper;

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gatracklog.gatracklogpreset');

//Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gatracklog', JPATH_ADMINISTRATOR);

$user = GatracklogHelper::getSpecificUser();

?>

<form
	id="form-comment" action="<?php echo Route::_('index.php?option=com_gatracklog&task=tracklogform.saveComment'); ?>"
	method="post" class="form-validate form-horizontal" enctype="multipart/form-data" target="_parent">

	<p>&nbsp;</p>
    <div class="row-fluid">
        <div class="span12 form-horizontal">
        
			<input type="hidden" name="jform[track_id]" value="<?php echo $this->item->id; ?>" />
			<input type="hidden" name="jform[user_id]" value="<?php echo $user->id; ?>" />
			<input type="hidden" name="jform[created_by]" value="<?php echo $user->id; ?>" />

			<?php echo $this->form->renderField('tcomment'); ?>

        </div>
        <p class="center">
            <button type="submit" class="validate btn btn-primary"><?php echo Text::_('JSUBMIT'); ?></button>
        </p>
    </div>
    <div>
		<input type="hidden" name="option" value="com_gatracklog"/>
		<input type="hidden" name="task" value="tracklogform.saveComment"/>
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>

