<?php
/**
 * @version    4.2.0
 * @package    pkg_mypackage
 * @subpackage com_gatracklog
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatracklog\Site\Dispatcher;

use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Language\Text;

\defined('_JEXEC') or die;

/**
 * ComponentDispatcher class
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
	/**
	 * Dispatch a controller task. Redirecting the user if appropriate.
	 * @return  void
	 * @since   4.0.0
	 */
	public function dispatch()
	{
		parent::dispatch();
	}
}
