<?php
/**
 * @version     4.2.1
 * @package     com_gabroadcast
 * @copyright   Copyright (C) 2011-2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gabroadcast\Administrator\Field;

// No direct access to this file
defined('_JEXEC') or die;

// import the list field type
use \Joomla\CMS\Form\FormField;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Form\Field\ListField;
use \GlennArkell\Component\Gabroadcast\Administrator\Helper\GabroadcastHelper;

class MembersField extends ListField
{
    /**
     * The field type.
     * @var         string
     */
    protected $type = 'Members';

	/**
	 * Name of the layout being used to render the field
	 * @var    string
	 * @since  4.2.1
	 */
	protected $layout = 'joomla.form.field.list';

    /**
     * Method to get a list of options for a list input.
     * @return      array           An array of JHtml options.
     */
    protected function getOptions()
    {
        $options = array();

        $options = GabroadcastHelper::getListOptions('#__users', 'Members', 'a.name');

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
