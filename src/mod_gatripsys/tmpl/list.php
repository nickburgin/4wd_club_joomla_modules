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

$elements = GatripsysHelper::getList($params);

$tableField = explode(':', $params->get('field'));
$table_name = !empty($tableField[0]) ? $tableField[0] : '';
$field_name = !empty($tableField[1]) ? $tableField[1] : '';
?>

<?php if (!empty($elements)) : ?>
	<table class="table">
		<?php foreach ($elements as $element) : ?>
			<tr>
				<th><?php echo GatripsysHelper::renderTranslatableHeader($table_name, $field_name); ?></th>
				<td><?php echo GatripsysHelper::renderElement(
						$table_name, $params->get('field'), $element->{$field_name}
					); ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
<?php endif;
