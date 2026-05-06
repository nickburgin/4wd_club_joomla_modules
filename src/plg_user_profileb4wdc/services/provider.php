<?php

/**
 * @version    5.3
 * @package    plg_user_profileb4wdc
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  Copyright (C) 2011 - 2012 Glenn Arkell. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use GlennArkell\Plugin\User\Profileb4wdc\Extension\Profileb4wdc;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     * @param   Container  $container  The DI container.
     * @return  void
     * @since   4.4.0
     */
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $plugin     = new Profileb4wdc(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('user', 'profileb4wdc')
                );
                $plugin->setApplication(Factory::getApplication());
                $plugin->setDatabase($container->get(DatabaseInterface::class));

                return $plugin;
            }
        );
    }
};
