<?php
/*
 * ------------------------------------------------------------------------
 * @version     5.2
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * Author:      Glenn Arkell
 * Website:     https://www.glennarkell.com.au
 * ------------------------------------------------------------------------
*/

namespace GlennArkell\Module\Gatripsys\Site\Dispatcher;

use \Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use \Joomla\CMS\Helper\HelperFactoryAwareInterface;
use \Joomla\CMS\Helper\HelperFactoryAwareTrait;

\defined('_JEXEC') or die;

/**
 * Dispatcher class for mod_articles_news
 * @since  4.2.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Runs the dispatcher.
     * @return  void
     * @since   4.3.0
     */
    public function dispatch()
    {
        // The module will not show if no user is logged in.
        //$user = $this->getApplication()->getIdentity();
        //if ($user === null || $user->id === 0) {
        //    return;
        //}

        parent::dispatch();
    }

    /**
     * Returns the layout data.
     * @return  array
     * @since   4.2.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $data['items'] = $this->getHelperFactory()->getHelper('GatripsysHelper')->getList($data['params'], $this->getApplication());

        return $data;
    }
}
