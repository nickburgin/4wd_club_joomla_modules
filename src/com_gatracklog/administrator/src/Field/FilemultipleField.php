<?php
/**
 * @version    0.0.2
 * @package    com_gatracklog
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatracklog\Administrator\Field;

defined('JPATH_BASE') or die;

use \Joomla\CMS\Form\FormField;

/**
 * Supports an HTML form field
 * @since  1.6
 */
class FileMultipleField extends FormField
{
	/**
	 * The form field type.
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'File';

	/**
	 * Method to get the field input markup.
	 * @return    string    The field input markup.
	 * @since    1.6
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = '<input type="file" name="' . $this->name . '[]" multiple>';

		return $html;
	}
}
