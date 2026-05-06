<?php

/**
 * @version    4.1.0
 * @package    com_gatracklog
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatracklog\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/**
 * Component Controller
 * @since  1.5
 */
class DisplayController extends BaseController
{
	/**
	 * The default view.
	 * @var    string
	 * @since  1.6
	 */
	protected $default_view = 'tracklogs';

	/**
	 * Method to display a view.
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 * @return  BaseController|boolean  This object to support chaining.
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = array())
	{
		return parent::display();
	}
}
