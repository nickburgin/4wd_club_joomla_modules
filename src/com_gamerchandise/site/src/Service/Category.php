<?php

/**
 * @version    4.0.7
 * @package    com_gamerchandise
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gamerchandise\Site\Service;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Categories\Categories;

/**
 * Content Component Category Tree
 * @since  1.6
 */
class Category extends Categories
{
	/**
	 * Class constructor
	 * @param   array  $options  Array of options
	 * @since   11.1
	 */
	public function __construct($options = array())
	{
		$options['table'] = '#__gamerchandise_products';
		$options['extension'] = 'com_gamerchandise.products';
		parent::__construct($options);
	}
}
