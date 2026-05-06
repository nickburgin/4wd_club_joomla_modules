<?php
/**
 * @version    4.0.2
 * @package    Com_Gaforsale
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use GlennArkell\Component\Gaforsale\Administrator\Extension\GaforsaleComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;


/**
 * Service provider.
 * @since  4.0.2
 */
return new class implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 * @param   Container  $container  The DI container.
	 * @return  void
	 * @since   4.0.2
	 */
	public function register(Container $container)
	{

		$container->registerServiceProvider(new CategoryFactory('\\GlennArkell\\Component\\Gaforsale'));
		$container->registerServiceProvider(new MVCFactory('\\GlennArkell\\Component\\Gaforsale'));
		$container->registerServiceProvider(new ComponentDispatcherFactory('\\GlennArkell\\Component\\Gaforsale'));
		$container->registerServiceProvider(new RouterFactory('\\GlennArkell\\Component\\Gaforsale'));

		$container->set(
			ComponentInterface::class,
			function (Container $container)
			{
				$component = new GaforsaleComponent($container->get(ComponentDispatcherFactoryInterface::class));

				$component->setRegistry($container->get(Registry::class));
				$component->setMVCFactory($container->get(MVCFactoryInterface::class));
				$component->setCategoryFactory($container->get(CategoryFactoryInterface::class));
				$component->setRouterFactory($container->get(RouterFactoryInterface::class));

				return $component;
			}
		);
	}
};
