<?php
/*
 * ------------------------------------------------------------------------
 * @version     5.3
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * Author:      Glenn Arkell
 * Website:     https://www.glennarkell.com.au
 * ------------------------------------------------------------------------
*/

namespace GlennArkell\Module\Gausersexecs\Site\Dispatcher;

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
     * Returns the layout data.
     * @return  array
     * @since   4.2.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $data['items'] = $this->getHelperFactory()->getHelper('GausersexecsHelper')->getList($data['params'], $this->getApplication());

        return $data;
    }
}
