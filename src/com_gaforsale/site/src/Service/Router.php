<?php

/**
 * @version    4.2.2
 * @package    Com_Gaforsale
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gaforsale\Site\Service;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Component\Router\RouterViewConfiguration;
use \Joomla\CMS\Component\Router\RouterView;
use \Joomla\CMS\Component\Router\Rules\StandardRules;
use \Joomla\CMS\Component\Router\Rules\NomenuRules;
use \Joomla\CMS\Component\Router\Rules\MenuRules;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Categories\Categories;
use \Joomla\CMS\Application\SiteApplication;
use \Joomla\CMS\Categories\CategoryFactoryInterface;
use \Joomla\CMS\Categories\CategoryInterface;
use \Joomla\Database\DatabaseInterface;
use \Joomla\CMS\Menu\AbstractMenu;
use \GlennArkell\Component\Gaforsale\Administrator\Helper\GaforsaleHelper;

/**
 * Class Component Router
 */
class Router extends RouterView
{
	private $noIDs;

	/**
	 * The category factory
	 * @var CategoryFactoryInterface
	 * @since  4.2.2
	 */
	private $categoryFactory;

	/**
	 * The category cache
	 * @var  array
	 * @since  4.2.2
	 */
	private $categoryCache = [];

	public function __construct(SiteApplication $app, AbstractMenu $menu, CategoryFactoryInterface $categoryFactory, DatabaseInterface $db)
	{
		$params = Factory::getApplication()->getParams('com_gaforsale');
		$this->noIDs = (bool) $params->get('sef_ids');
		$this->categoryFactory = $categoryFactory;
		
		/* -------------   Fsitems  -------------------- */
		$fsitems = new RouterViewConfiguration('fsitems');
		$this->registerView($fsitems);

		$fsitem = new RouterViewConfiguration('fsitem');
		$fsitem->setKey('id')->setParent($fsitems);
		$this->registerView($fsitem);

		$fsitemform = new RouterViewConfiguration('fsitemform');
		$fsitemform->setKey('id');
		$this->registerView($fsitemform);

		/* -------------   General stuff  -------------------- */
		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));
		$this->attachRule(new StandardRules($this));
		$this->attachRule(new NomenuRules($this));
	}

	/* -------------   Item Segments  -------------------- */
	/**
	 * Method to get the segment(s) for an transaction
	 * @param   string  $id     ID of the transaction to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 * @return  array|string  The segments of this item
	 */
	public function getFsitemSegment($id, $query)
	{
		return array((int) $id => $id);
	}

	/* -------------   Form Segments  -------------------- */
	/**
	 * Method to get the segment(s) for an transactionform
	 * @param   string  $id     ID of the transactionform to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 * @return  array|string  The segments of this item
	 */
	public function getFsitemformSegment($id, $query)
	{
		return $this->getFsitemSegment($id, $query);
	}


	/* -------------   Item ID  -------------------- */
	/**
	 * Method to get the segment(s) for a transaction
	 * @param   string  $segment  Segment of the transaction to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getFsitemId($segment, $query)
	{
		return (int) $segment;
	}

	/* -------------   Form ID  -------------------- */
	/**
	 * Method to get the segment(s) for a transactionform
	 * @param   string  $segment  Segment of the transactionform to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getFsitemformId($segment, $query)
	{
		return $this->getFsitemId($segment, $query);
	}

	/* -------------   List ID  -------------------- */
	/**
	 * Method to get the id for a category
	 * @param   string  $segment  Segment to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getFsitemsId($segment, $query)
	{
		if (isset($query['id'])) {
			$category = $this->getCategories(["access" => true])->get($query['id']);

			if ($category) {
				foreach ($category->getChildren() as $child) {
					if ($this->noIDs) {
						if ($child->alias == $segment) {
							return $child->id;
						}
					} else {
						if ($child->id == (int) $segment) {
							return $child->id;
						}
					}
				}
			}
		}

		return false;
	}

	/* -------------   General Categories  -------------------- */
	/**
	 * Method to get categories from cache
	 * @param   array  $options   The options for retrieving categories
	 * @return  CategoryInterface  The object containing categories
	 * @since   4.2.2
	 */
	private function getCategories(array $options = []): CategoryInterface
	{
		$key = serialize($options);

		if (!isset($this->categoryCache[$key])) {
			$this->categoryCache[$key] = $this->categoryFactory->createCategory($options);
		}

		return $this->categoryCache[$key];
	}
}
