<?php
/**
 * @version    5.1.0
 * @package    com_gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatripsys\Administrator\Field;

// No direct access to this file
defined('_JEXEC') or die;

// import the list field type
use \Joomla\CMS\Form\FormField;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Form\Field\ListField;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

class ApproversField extends ListField
{
    /**
     * The field type.
     * @var         string
     */
    protected $type = 'Approvers';

	/**
	 * Name of the layout being used to render the field
	 * @var    string
	 * @since  4.0.0
	 */
	protected $layout = 'joomla.form.field.list';

    /**
     * Method to get a list of options for a list input.
     * @return      array           An array of Html options.
     */
    protected function getOptions()
    {
        $options = array();

        $options = GatripsysHelper::getListOptions('#__users', 'Approvers', 'name');

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }

}
