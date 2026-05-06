<?php
/**
 * @version    4.1.0
 * @package    com_gatracklog
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatracklog\Administrator\Field;

defined('JPATH_BASE') or die;

use \Joomla\CMS\Form\FormField;
use \Joomla\CMS\Factory;
use \GlennArkell\Component\Gatracklog\Administrator\Helper\GatracklogHelper;

/**
 * Supports an HTML form field
 * @since  1.6
 */
class ModifiedbyField extends \Joomla\CMS\Form\FormField
{
	/**
	 * The form field type.
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'Modifiedby';

	/**
	 * Method to get the field input markup.
	 * @return    string    The field input markup.
	 * @since    1.6
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html   = array();
		$user   = GatracklogHelper::getSpecificUser();
		$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $user->id . '" />';
		if (!$this->hidden) {
			$html[] = "<div>" . $user->name . " (" . $user->username . ")</div>";
		}

		return implode($html);
	}
}
