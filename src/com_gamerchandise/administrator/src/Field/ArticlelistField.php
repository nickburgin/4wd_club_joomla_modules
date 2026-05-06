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

// import the list field type
use \Joomla\CMS\Form\FormField;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Form\Field\ListField;
use \GlennArkell\Component\Gamerchandise\Administrator\Helper\GamerchandiseHelper;

class ArticlelistField extends ListField
{
	/**
	 * The form field type.
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Articlelist';

	/**
	 * Name of the layout being used to render the field
	 * @var    string
	 * @since  4.1.2
	 */
	protected $layout = 'joomla.form.field.list';

    /**
     * Method to get a list of options for a list input.
     * @return      array           An array of JHtml options.
     */
    protected function getOptions()
    {
        $options = array();

        $options = GamerchandiseHelper::getListOptions('#__content', 'Article', 'title');

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
