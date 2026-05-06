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
use \Joomla\CMS\Form\FormField;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Form\Field\ListField;
use \Joomla\CMS\Component\ComponentHelper;
use \GlennArkell\Component\Gabroadcast\Administrator\Helper\GabroadcastHelper;

class CustomvaluelistField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Customvaluelist';

	/**
	 * Name of the layout being used to render the field
	 * @var    string
	 * @since  4.0.4
	 */
	protected $layout = 'joomla.form.field.list';

    /**
     * Method to get a list of options for a list input.
     * @return      array           An array of JHtml options.
     */
    protected function getOptions()
    {
        // clean up custom fields if Y/N radio button
		$params = ComponentHelper::getParams('com_gabroadcast');
		$fieldID  = $params->get( 'cust_field', 1);
        $fieldRecord = GabroadcastHelper::getField($fieldID);

        $options = array();

        $cust_options = GabroadcastHelper::getListvalueOptions('#__fields_values', $fieldRecord->title, 'a.value');

        if (!empty($fieldRecord->id) && $fieldRecord->type == 'radio') {
            foreach ($cust_options AS $c) {
                if ($c->text == 1) {
                    $c->text = 'Yes';
                } elseif ($c->text == 0) {
                    $c->text = 'No';
                }
                $options[] = $c;
            }
        } else {
            $options[] = $cust_options;
        }

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
