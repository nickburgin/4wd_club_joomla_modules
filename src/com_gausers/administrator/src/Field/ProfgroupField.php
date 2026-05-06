<?php
/**
 * @version     5.1.6
 * @package     com_gausers
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gausers\Administrator\Field;

defined('JPATH_BASE') or die;

// import the list field type
use \Joomla\CMS\Factory;
use \Joomla\CMS\Form\Field\ListField;
use \Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;

/**
 * Supports an HTML select list
 */
class ProfgroupField extends ListField
{
	/**
	 * The form field type.
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Profgroup';

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
        $options = GausersHelper::getProfileGroupOptions();
        
        if (is_null($options) || !$options) { $options = array(); }

        $options = array_merge(parent::getOptions(), $options);

        return $options;

    }
}
