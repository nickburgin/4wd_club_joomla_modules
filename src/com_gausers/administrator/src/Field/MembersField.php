<?php
/**
 * @version     5.1.6
 * @package     com_gausers
 * @copyright   Copyright (C) 2011-2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gausers\Administrator\Field;

// No direct access to this file
defined('_JEXEC') or die;

// import the list field type
use \Joomla\CMS\Factory;
use \Joomla\CMS\Form\Field\ListField;
use \Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;

class MembersField extends ListField
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'Members';

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

        $options = GausersHelper::getListOptions('#__users', 'Members', 'a.name');

        if (is_null($options)) { $options = array(); }

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
