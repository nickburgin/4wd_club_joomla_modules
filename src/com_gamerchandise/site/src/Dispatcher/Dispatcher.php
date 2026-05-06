<?php
/**
 * @version    4.0.7
 * @package    com_gamerchandise
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gamerchandise\Site\Dispatcher;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Language\Text;

/**
 * ComponentDispatcher class
 * @since  4.0.7
 */
class Dispatcher extends ComponentDispatcher
{
	/**
	 * Dispatch a controller task. Redirecting the user if appropriate.
	 * @return  void
	 * @since   4.0.7
	 */
	public function dispatch()
	{
		parent::dispatch();
	}
}
