<?php
/**
 * @version     5.3.0
 * @package     com_gatripsys
 * @copyright   Copyright (C) 2011-2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gatripsys\Administrator\Field;

// No direct access to this file
defined('_JEXEC') or die;

// import the list field type
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Field\ListField;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

class TripratingField extends ListField
{
    /**
     * The field type.
     * @var         string
     */
    protected $type = 'Triprating';

	/**
	 * Name of the layout being used to render the field
	 * @var    string
	 * @since  5.3.0
	 */
	protected $layout = 'joomla.form.field.list';

    /**
     * Method to get a list of options for a list input.
     * @return      array           An array of JHtml options.
     */
    protected function getOptions()
    {
        //$options = array("0"=>" - Select Trip Rating - ");
        $options = array();

        $options = GatripsysHelper::getCategoryOptions('com_gatripsys', 'Trip Rating', 'title');
        //$options = array_merge($options, $xoptions);

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
