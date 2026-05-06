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

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

use Joomla\CMS\Extension\Service\Provider\HelperFactory;
use Joomla\CMS\Extension\Service\Provider\Module;
use Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The popular articles module service provider.
 *
 * @since  4.3.0
 */
return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function register(Container $container)
    {
        $container->registerServiceProvider(new ModuleDispatcherFactory('\\GlennArkell\\Module\\Gatripsys'));
        $container->registerServiceProvider(new HelperFactory('\\GlennArkell\\Module\\Gatripsys\\Site\\Helper'));

        $container->registerServiceProvider(new Module());
    }
};