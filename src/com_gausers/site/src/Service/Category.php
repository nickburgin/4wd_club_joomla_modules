<?php
/**
 * @version     5.1.6
 * @package     com_gausers
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

namespace GlennArkell\Component\Gausers\Site\Service;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Categories\Categories;

/**
 * Gausers Component Category Tree
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
		$options['table'] = '#__gausers_invoices';
		$options['extension'] = 'com_gausers.invoices';
		parent::__construct($options);
	}
}
