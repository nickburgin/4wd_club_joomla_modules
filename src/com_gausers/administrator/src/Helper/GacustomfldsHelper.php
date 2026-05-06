<?php
/**
 * @version    6.0.0
 * @package    Com_Gausers
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gausers\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * Custom Fields helper.
 */
class GacustomfldsHelper
{
	/**
	 * Get all the custom fields as an array for an object
	 * @param   object  $user object or item object for a component.
	 * @param   string  context for a component, ie if linked to user then com_users.user
	 * @return  array	fields and values
	 *
	 * if the third parameter in the getFields is NOT set to true, then the values are 
	 * returned as reference numbers and if true, then text value of the reference is returned.
	 */
    public static function getCustomFields($obj, $context = 'com_users.user')
	{
		$app = Factory::getApplication();
		$connection = array();

		$fields = FieldsHelper::getFields($context, $obj, true);
		//Factory::getApplication()->setUserState('com_gausers.test.data', $fields);

		foreach ($fields as $fld) {
            $connection[$fld->id] = $fld->value;
        }

		return $connection;

	}

    /**
    *   Method to get values for a member based on field id
    *   @param int $id field_id reference
    *   @param int $userId user id reference
    *   @return object field name and value
    */
	public static function getCustomFieldValue($id = 0, $userId = 0)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.field_id AS id, b.title AS fieldName, a.value AS fieldValue ');
		$query->from(' #__fields_values AS a ');
		$query->join('LEFT', ' #__fields AS b ON b.id = a.field_id');
		$query->where(' a.field_id = '.(int) $id );
		$query->where(' a.item_id = '.(int) $userId );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Field Values');
	        return false;
	    }

	}

	/**
	* Update value of Custom Field
	* @param int field_id value
	* @param string item_id value
	* @param string value submitted
	* @return boolean
	*/
	public static function updateCustomFieldValue($field_id = 0, $item_id = 0, $value = 0) {
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$db->setQuery('UPDATE #__fields_values SET value = '.$db->Quote($value).' WHERE field_id = '.(int) $field_id.' AND item_id = '.$db->Quote($item_id) );
		try {
			$db->execute();
			return true;
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage(Text::_('COM_GAUSERS_CUSTFLD_UPD_FAILED'), 'warning');
			return false;
		}
	}

	/**
	* Create value of Custom Field
	* @param int field_id value
	* @param string item_id value
	* @param string value submitted
	* @return boolean
	*/
	public static function createCustomFieldValue($field_id = 0, $item_id = 0, $value = 0) {
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$db->setQuery('INSERT into #__fields_values (field_id, item_id, value) VALUES ('.(int) $field_id.','.$db->Quote($item_id).','.$db->Quote($value).')' );
		try {
			$db->execute();
			return true;
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage(Text::_('COM_GAUSERS_CUSTFLD_INS_FAILED'), 'warning');
			return false;
		}
	}

}

