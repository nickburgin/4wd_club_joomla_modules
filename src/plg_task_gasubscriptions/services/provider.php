<?php

/**
 * @package     pkg_gausers
 * @subpackage  plg.task.gasubscriptions
 * @version 	5.3
 * @copyright   (C) 2024 Glenn Arkell <https://www.glennarkell.com.au>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use GlennArkell\Plugin\Task\Gasubscriptions\Extension\Gasubscriptions;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     * @param   Container  $container  The DI container.
     * @return  void
     * @since   5.0.0
     */
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $plugin = new Gasubscriptions(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('task', 'gasubscriptions')
                );
                $plugin->setApplication(Factory::getApplication());
                $plugin->setDatabase($container->get(DatabaseInterface::class));

                return $plugin;
            }
        );
    }
};
