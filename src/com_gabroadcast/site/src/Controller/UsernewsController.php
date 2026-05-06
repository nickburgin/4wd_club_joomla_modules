<?php
/**
 * @version    4.3.3
 * @package    Com_Gabroadcast
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gabroadcast\Site\Controller;

// No direct access.
\defined('_JEXEC') or die;

/**
 * Multi list controller class.
 * @since  1.6
 */
class UsernewsController extends GabroadcastController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional
	 * @param   array   $config  Configuration array for model. Optional
	 *
	 * @return object	The model
	 *
	 * @since	1.6
	 */
	public function getModel($name = 'Usernews', $prefix = 'Site', $config = array('ignore_request' => true))
	{

		return parent::getModel($name, $prefix, $config);
	}
}
