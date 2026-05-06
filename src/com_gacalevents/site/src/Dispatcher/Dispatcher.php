<?php
/**
 * @version    3.0.0
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gacalevents\Site\Dispatcher;

defined('JPATH_PLATFORM') or die;

use \Joomla\CMS\Dispatcher\ComponentDispatcher;
use \Joomla\CMS\Language\Text;

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
