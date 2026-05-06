<?php
/**
 * @version    4.0.7
 * @package    com_gamerchandise
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
use GlennArkell\Component\Gamerchandise\Administrator\Extension\GamerchandiseComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;


/**
 * The service provider.
 * @since  4.0.7
 */
return new class implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 * @param   Container  $container  The DI container.
	 * @return  void
	 * @since   4.0.7
	 */
	public function register(Container $container)
	{

		$container->registerServiceProvider(new CategoryFactory('\\GlennArkell\\Component\\Gamerchandise'));
		$container->registerServiceProvider(new MVCFactory('\\GlennArkell\\Component\\Gamerchandise'));
		$container->registerServiceProvider(new ComponentDispatcherFactory('\\GlennArkell\\Component\\Gamerchandise'));
		$container->registerServiceProvider(new RouterFactory('\\GlennArkell\\Component\\Gamerchandise'));

		$container->set(
			ComponentInterface::class,
			function (Container $container)
			{
				$component = new GamerchandiseComponent($container->get(ComponentDispatcherFactoryInterface::class));

				$component->setRegistry($container->get(Registry::class));
				$component->setMVCFactory($container->get(MVCFactoryInterface::class));
				$component->setCategoryFactory($container->get(CategoryFactoryInterface::class));
				$component->setRouterFactory($container->get(RouterFactoryInterface::class));

				return $component;
			}
		);
	}
};
