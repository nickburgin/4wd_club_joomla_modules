<?php

/**
 * @version    5.3.0
 * @package    com_gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatripsys\Site\Service;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Categories\Categories;

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
		$options['table'] = '#__gatripsys_trips';
		$options['extension'] = 'com_gatripsys.trips';
		parent::__construct($options);
	}
}
