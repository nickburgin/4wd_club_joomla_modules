<?php
/**
 * @version     4.1.2
 * @package     com_gamerchandise
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gamerchandise\Administrator\Field;

defined('JPATH_BASE') or die;

use \Joomla\CMS\Form\FormField;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Form\Field\ListField;
use \GlennArkell\Component\Gamerchandise\Administrator\Helper\GamerchandiseHelper;

/**
 * Supports an HTML select list of categories
 */
class ProductField extends ListField
{
	/**
	 * The form field type.
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'Product';

	/**
	 * Name of the layout being used to render the field
	 * @var    string
	 * @since  4.1.2
	 */
	protected $layout = 'joomla.form.field.list';

	/**
	 * Method to get the field options markup.
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getOptions()
	{

        $options = array();

        $options = GamerchandiseHelper::getListOptions('#__gamerchandise_products', 'Product', 'prod_name' );

        $options = array_merge(parent::getOptions(), $options);

        return $options;

	}

}
