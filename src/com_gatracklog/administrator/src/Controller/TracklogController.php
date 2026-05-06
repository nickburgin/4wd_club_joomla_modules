<?php
/**
 * @version    4.2.0
 * @package    pkg_mypackage
 * @subpackage com_gatracklog
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatracklog\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
//use Joomla\CMS\Log\Log;


/**
 * Single record controller class.
 * @since  1.6.0
 */
class TracklogController extends FormController
{
	//Log::add('an error to display', Log::ALL, 'msg-error-cat');

    /**
     * Method to run batch operations.
     * @param   object  $model  The model.
     * @return  boolean   True if successful, false otherwise and internal error is set.
     * @since   1.6
     */
    public function batch($model = null)
    {
        $this->checkToken();

        // Set the model
        $model = $this->getModel('Tracklog', 'Administrator', []);

        // Preset the redirect
        $this->setRedirect(Route::_('index.php?option=com_gatracklog&view=tracklogs' . $this->getRedirectToListAppend(), false));

        return parent::batch($model);
    }
}
