<?php
/**
 * @version     5.3.0
 * @package     com_gatripsys
 * @copyright   Copyright (C) 2011-2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gatripsys\Administrator\Field;

defined('JPATH_PLATFORM') or die;

// import the list field type
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Field\PredefinedlistField;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

/**
 * Form Field to load a list of states
 * @since  3.2
 */
class ExtrastatusField extends PredefinedlistField
{
	/**
	 * The form field type.
	 * @var    string
	 * @since  3.2
	 */
	public $type = 'Extrastatus';

	/**
	 * Name of the layout being used to render the field
	 * @var    string
	 * @since  5.3.0
	 */
	protected $layout = 'joomla.form.field.list';

	/**
	 * Available statuses
	 * @var  array
	 * @since  3.2
	 */
	protected $predefinedOptions = array(
		'1'  =>	'GAUNPAID',
		'3'  =>	'GAREFUNDED',
		'2'  => 'GAPAID',
		'-2' => 'GACANCELLED',
		'*'  => 'JALL',
	);
}
