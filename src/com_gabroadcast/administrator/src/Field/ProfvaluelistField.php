<?php
/**
 * @version     4.0.4
 * @package     com_gabroadcast
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gabroadcast\Administrator\Field;

defined('JPATH_BASE') or die;

// import the list field type
use \Joomla\CMS\Factory;
use \Joomla\CMS\Form\Field\ListField;
use \Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gabroadcast\Administrator\Helper\GabroadcastHelper;

/**
 * Supports an HTML select list
 */
class ProfvaluelistField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Profvaluelist';

	/**
	 * Name of the layout being used to render the field
	 * @var    string
	 * @since  4.0.0
	 */
	protected $layout = 'joomla.form.field.list';

    /**
     * Method to get a list of options for a list input.
     *
     * @return      array           An array of JHtml options.
     */
    protected function getOptions()
    {
		$options = array();

        $prof_options = GabroadcastHelper::getLocalProfileOptions('v');
        // cycle through each returned option
        foreach ($prof_options AS $po) {
            // clean up profile values with quotes
            $po->value = \str_replace('"', '', $po->value);
            $po->text = \str_replace('"', '', $po->text);

            // check if value set
            if (!empty($po->value) && $po->value > '') {
                $options[] = $po;
            }
        }
        $options = \array_unique($options, SORT_REGULAR);
        $options = \array_merge(parent::getOptions(), $options);

        return $options;
    }
}
