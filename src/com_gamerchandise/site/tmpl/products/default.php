<?php
/**
 * @version    4.0.7
 * @package    Com_Gamerchandise
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gamerchandise\Administrator\Helper\GamerchandiseHelper;

// load any assets required
$wa = $this->document->getWebAssetManager();
$wa->usePreset('com_gamerchandise.gamerchandisepreset');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gamerchandise', JPATH_ADMINISTRATOR);

$user = GamerchandiseHelper::getSpecificUser();
$listOrder  = $this->state->get('list.ordering', 'cat_id_name');
$listDirn   = $this->state->get('list.direction', 'asc');
$canCreate  = $user->authorise('core.create', 'com_gamerchandise');
$canEdit    = $user->authorise('core.edit', 'com_gamerchandise');
$canCheckin = $user->authorise('core.manage', 'com_gamerchandise');
$canChange  = $user->authorise('core.edit.state', 'com_gamerchandise');
$canDelete  = $user->authorise('core.delete', 'com_gamerchandise');
$canAdmin = 0;
$cntr = 0;

$prev_cat = 0;

$mbr_disc = $this->params->get('mbr_disc');
$disc_amt = $this->params->get('disc_amt');
$head_txt = $this->params->get('product_txt', '');
$head_txt = str_replace("\r\n", '</p><p>', $head_txt);

$use_soh = $this->params->get('use_soh', 0);
$public_sales = $this->params->get('public_sales', 0);
if (!$canCreate && $public_sales) {$canCreate = true; } 
/*
echo '<pre>Test<br />';
print_r(Factory::getApplication()->getUserState('com_gamerchandise.test.data'));
echo '</pre>';
*/
?>

<h2><?php echo Text::_('COM_GAMERCHANDISE_TITLE_PRODUCTS'); ?></h2>
<p><?php echo $head_txt; ?></p>
<?php if ($mbr_disc) : ?>
	<p><?php echo Text::sprintf('COM_GAMERCHANDISE_DISC_AMT_MESSAGE', $disc_amt); ?></p>
<?php endif; ?>

<form action="<?php echo Route::_('index.php?option=com_gamerchandise&view=products'); ?>" method="post" name="adminForm" id="adminForm">

	<?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>

	<div>

	<?php foreach ($this->items as $i => $item) : $cntr++; ?>
		<?php if ($item->soh == 0 && $use_soh) { continue; } ?>
		<?php if ($prev_cat != $item->cat_id) : ?>
			<div class="clearfix"></div>
			<h2 class="center"><?php echo $item->cat_id_name; $prev_cat = $item->cat_id; $cntr = 1; ?></h2>
			<div class="clearfix"></div>
		<?php endif; ?>
		<div class="row<?php echo $i % 2; ?>">
			<h2 class="center">
				<?php if ($canAdmin) : ?>
					<a href="<?php echo Route::_('index.php?option=com_gamerchandise&view=product&id='.(int) $item->id); ?>">
						<?php echo $this->escape($item->prod_name); ?>
					</a>
				<?php else : ?>
					<?php echo $this->escape($item->prod_name); ?>
				<?php endif; ?>
			</h2>
			<h2 class="center"><?php echo '$'.number_format($item->price,2); ?></h2>
			<p><?php echo $item->prod_desc; ?></p>
			<?php if ($canCreate || $canCheckin || $canDelete): ?>
				<p class="center">
					<?php if ($canCheckin): ?>
						<a href="<?php echo Route::_('index.php?option=com_gamerchandise&task=productform.edit&id=' . $item->id, false, 2); ?>" 
							class="btn btn-warning" type="button" title="<?php echo Text::_('COM_GAMERCHANDISE_EDIT_ITEM'); ?>">
							<i class="icon-edit" ></i>
						</a> &nbsp;
					<?php endif; ?>
					<?php if ($canCreate): ?>
						<a href="<?php echo Route::_('index.php?option=com_gamerchandise&view=saleform&prod_id=' . $item->id, false, 2); ?>"
							class="btn btn-success" type="button" title="<?php echo Text::_('COM_GAMERCHANDISE_ADD_TO_CART'); ?>">
							<i class="icon-cart" ></i>
						</a> &nbsp;
					<?php endif; ?>
					<?php if ($canDelete): ?>
						<a href="<?php echo Route::_('index.php?option=com_gamerchandise&task=productform.remove&id=' . $item->id, false, 2); ?>"
							class="btn btn-danger delete-button" type="button" title="<?php echo Text::_('COM_GAMERCHANDISE_DELETE_ITEM'); ?>">
							<i class="icon-trash" ></i>
						</a>
					<?php endif; ?>
				</p>
			<?php endif; ?>
			<div class="clearfix"></div>
			<div class="center" style="padding-top:5px;">
				<?php if (!empty($item->prod_img)) : ?>
	                <?php
	                    $item->prod_img = HTMLHelper::cleanImageURL($item->prod_img);
	                    $orient = ($item->prod_img->attributes['width'] > $item->prod_img->attributes['height']) ? 'smalltn_l' : 'smalltn_p';
	                    $image_link = '<a href="'.$item->prod_img->url.'" id="#myModal"';
	                    $image_link .= ' class="btn btn-primary xbottom" target="_blank">';
	                    $image_link .= '<img class="'.$orient.' zoom" src="'.$item->prod_img->url.'"';
	                    $image_link .= ' style ="max-height: 80px;" height="100" width="100" title="'.$item->prod_name.'"></a>';
	                ?>
	                <?php echo $image_link; ?>
				<?php else : ?>
					<p>No Image Available</p>
				<?php endif; ?>
			</div>
		</div>
		<?php if ($cntr == 2) { echo '<div class="clearfix"></div>'; $cntr = 0; } ?>

	<?php endforeach; ?>
	</div>
	<div class="list-footer">
		<?php echo $this->pagination->getListFooter(); ?>
	</div>

	<?php if ($canCheckin) : ?>
		<a href="<?php echo Route::_('index.php?option=com_gamerchandise&task=productform.edit&id=0', false, 2); ?>" class="btn btn-success">
		    <i class="icon-plus"></i> <?php echo Text::_('COM_GAMERCHANDISE_ADD_ITEM'); ?>
		</a>
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
			$("#myModal").on("click", function() {
			   $(this).modal('show');
			});
		});

		function deleteItem() {
			if (!confirm("<?php echo Text::_('COM_GAMERCHANDISE_DELETE_MESSAGE'); ?>")) {
				return false;
			}
		}
	</script>
<?php endif; ?>
