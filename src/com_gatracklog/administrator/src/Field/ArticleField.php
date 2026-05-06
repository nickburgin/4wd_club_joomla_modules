<?php
/**
 * @version    4.1.0
 * @package    com_gatracklog
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatracklog\Administrator\Field;

// No direct access to this file
defined('_JEXEC') or die;

// import the list field type
use \Joomla\CMS\Form\FormField;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Form\Field\ListField;
use \GlennArkell\Component\Gatracklog\Administrator\Helper\GatracklogHelper;

class ArticleField extends ListField
{
    /**
     * The field type.
     * @var         string
     */
    protected $type = 'Article';

	/**
	 * Name of the layout being used to render the field
	 * @var    string
	 * @since  4.1.0
	 */
	protected $layout = 'joomla.form.field.list';

    /**
     * Method to get a list of options for a list input.
     * @return      array           An array of Html options.
     */
    protected function getOptions()
    {
        $options = array();

        $options = GatracklogHelper::getListOptions('#__content', 'Article', 'title');

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }

}
