<?php
/**
 * @version    5.2.3
 * @package    Com_Gafinance
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gafinance\Administrator\Field;

// No direct access to this file
defined('_JEXEC') or die;

// import the list field type
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Field\ListField;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GafinanceHelper;

class ClientField extends ListField
{
    /**
     * The field type.
     * @var         string
     */
    protected $type = 'Client';

	/**
	 * Name of the layout being used to render the field
	 * @var    string
	 * @since  5.2.3
	 */
	protected $layout = 'joomla.form.field.list';

    /**
     * Method to get a list of options for a list input.
     * @return      array           An array of JHtml options.
     */
    protected function getOptions()
    {
        $options = array();

        $options = GafinanceHelper::getListOptions('#__gafinance_patrons', 'Client', 'name');

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }

}
