<?php
/**
 * @version    4.2.0
 * @package    pkg_mypackage
 * @subpackage com_gatracklog
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatracklog\Administrator\Extension;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Association\AssociationServiceInterface;
use Joomla\CMS\Association\AssociationServiceTrait;
use Joomla\CMS\Categories\CategoryServiceTrait;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Joomla\CMS\Tag\TagServiceTrait;
use Psr\Container\ContainerInterface;

\defined('_JEXEC') or die;

/**
 * Component class for com_gatracklog
 * @since  4.0.0
 */
class GatracklogComponent extends MVCComponent implements
    BootableExtensionInterface,
    RouterServiceInterface
{
	use AssociationServiceTrait;
	use RouterServiceTrait;
	use HTMLRegistryAwareTrait;
	use CategoryServiceTrait, TagServiceTrait {
		CategoryServiceTrait::getTableNameForSection insteadof TagServiceTrait;
		CategoryServiceTrait::getStateColumnForSection insteadof TagServiceTrait;
	}

    /**
     * Booting the extension. This is the function to set up the environment of the extension like
     * registering new class loaders, etc.
     * If required, some initial set up can be done from services of the container, eg.
     * registering HTML services.
     * @param   ContainerInterface  $container  The container
     * @return  void
     * @since   4.0.0
     */
    public function boot(ContainerInterface $container)
    {

    }

}
