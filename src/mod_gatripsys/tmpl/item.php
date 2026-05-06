<?php
/**
 * @package     com_gatripsys
 * @subpackage  mod_gatripsys
 * @version     4.1.0
 * @author      Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright   2021 Glenn Arkell
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use \GlennArkell\Module\Gatripsys\Site\Helper\GatripsysHelper;

$element = GatripsysHelper::getItem($params);
?>

<?php if (!empty($element)) : ?>
	<div>
		<?php $fields = get_object_vars($element); ?>
		<?php foreach ($fields as $field_name => $field_value) : ?>
			<?php if (GatripsysHelper::shouldAppear($field_name)): ?>
				<div class="row">
					<div class="span4">
						<strong><?php echo GatripsysHelper::renderTranslatableHeader($params->get('item_table'), $field_name); ?></strong>
					</div>
					<div
						class="span8"><?php echo GatripsysHelper::renderElement($params->get('item_table'), $field_name, $field_value); ?></div>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
<?php endif;
