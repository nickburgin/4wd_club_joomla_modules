<?php
/**
 * @version    6.0.0
 * @package    Com_Gausers
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gausers\Administrator\Field;

// No direct access to this file
\defined('_JEXEC') or die;

// import the list field type
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;

class CustfldField extends ListField
{
    /**
     * The field type.
     * @var         string
     */
    protected $type = 'Custfld';

	/**
	 * Name of the layout being used to render the field
	 * @var    string
	 * @since  4.0.0
	 */
	protected $layout = 'joomla.form.field.list';

    /**
     * Method to get a list of options for a list input.
     * @return      array           An array of JHtml options.
     */
    protected function getOptions()
    {

		$options = array();

        $options = GausersHelper::getListOptions('#__fields', 'Custom Field', 'a.title' );

        if (is_null($options)) { $options = array(); }

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
