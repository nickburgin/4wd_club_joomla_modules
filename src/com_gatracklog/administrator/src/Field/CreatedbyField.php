<?php
/**
 * @version     4.2.0
 * @package     pkg_mypackage
 * @subpackage  com_gatracklog
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatracklog\Administrator\Field;

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use \GlennArkell\Component\Gatracklog\Administrator\Helper\GatracklogHelper;

/**
 * Supports an HTML select list of categories
 *
 * @since  1.6
 */
class CreatedbyField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'Createdby';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 *
	 * @since    1.6
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();

		// Load user
		$user_id = $this->value;

		if ($user_id)
		{
			$user = GatracklogHelper::getSpecificUser($user_id);
		}
		else
		{
			$user   = Factory::getApplication()->getIdentity();
			$html[] = '<input type="hidden" name="' . $user->name . '" value="' . $user->id . '" />';
		}

		if (!$this->hidden)
		{
			$html[] = "<div>" . $user->name . " (" . $user->username . ")</div>";
		}

		return implode($html);
	}
}
