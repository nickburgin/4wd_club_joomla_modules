<?php
# ------------------------------------------------------------------------
# @copyright   Copyright (C) 2014. All rights reserved.
# @license     GNU General Public License version 2 or later
# Author:      Glenn Arkell
# Websites:    https://www.glennarkell.com.au
# ------------------------------------------------------------------------

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Folder;

FormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of categories
 */
class JFormFieldListfolders extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'listfolders';

	/**
	 * Method to get the field options markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getOptions()
	{
        $options = array();

		$path = JPATH_ROOT . '/images/';

		// Get a list of folders in the search path with the given filter.
		$folders = Folder::folders($path, $filter = '.', $recursive = false, $fullpath = false);
		// Build the options list from the list of folders.
		if (is_array($folders))
		{
			foreach ($folders as $folder)
			{
				// Remove the root part and the leading /
				//$folder = trim(str_replace($path, '', $folder), '/');
				$options[] = JHtml::_('select.option', $folder, $folder);
			}
		}
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

}
