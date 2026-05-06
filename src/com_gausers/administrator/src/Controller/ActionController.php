<?php
/**
 * @version     6.0.0
 * @package     com_gausers
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gausers\Administrator\Controller;

// No direct access
\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

/**
 * Action controller class.
 */
class ActionController extends FormController
{

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
        /** @var \Joomla\Component\Content\Administrator\Model\ArticleModel $model */
        $model = $this->getModel('Action', 'Administrator', []);

        // Preset the redirect
        $this->setRedirect(Route::_('index.php?option=com_gausers&view=actions' . $this->getRedirectToListAppend(), false));

        return parent::batch($model);
    }
}
