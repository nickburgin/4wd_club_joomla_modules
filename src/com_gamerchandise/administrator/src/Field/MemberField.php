<?php
/**
 * @version     4.1.2
 * @package     com_gamerchandise
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gamerchandise\Administrator\Field;

// No direct access to this file
defined('JPATH_BASE') or die;

use \Joomla\CMS\Form\FormField;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Form\Field\ListField;
use \GlennArkell\Component\Gamerchandise\Administrator\Helper\GamerchandiseHelper;

class MemberField extends ListField
{
	/**
	 * The form field type.
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'Member';

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

        $options = GamerchandiseHelper::getListOptions('#__users', 'Member', 'name');

        $options = array_merge(parent::getOptions(), $options);

        return $options;

	}

}
