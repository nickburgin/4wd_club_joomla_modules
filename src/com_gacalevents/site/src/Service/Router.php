<?php

/**
 * @version    3.3.1
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gacalevents\Site\Service;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Factory;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Categories\CategoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Menu\AbstractMenu;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;

/**
 * Class Component Router
 */
class Router extends RouterView
{
	private $noIDs;

	/**
	 * The category factory
	 * @var CategoryFactoryInterface
	 * @since  4.0.0
	 */
	private $categoryFactory;

	/**
	 * The category cache
	 * @var  array
	 * @since  4.0.0
	 */
	private $categoryCache = [];

	public function __construct(SiteApplication $app, AbstractMenu $menu, CategoryFactoryInterface $categoryFactory, DatabaseInterface $db)
	{
		$params = Factory::getApplication()->getParams('com_gacalevents');
		$this->noIDs = (bool) $params->get('sef_ids');
		$this->categoryFactory = $categoryFactory;
		
		$events = new RouterViewConfiguration('events');
		$this->registerView($events);

		$event = new RouterViewConfiguration('event');
		$event->setKey('id')->setParent($events, 'cat_id');
		$this->registerView($event);

		$eventform = new RouterViewConfiguration('eventform');
		$eventform->setKey('id');
		$this->registerView($eventform);

		$attendees = new RouterViewConfiguration('attendees');
		$this->registerView($attendees);

		$attendee = new RouterViewConfiguration('attendee');
		$attendee->setKey('id');
		$this->registerView($attendee);

		$attendeeform = new RouterViewConfiguration('attendeeform');
		$attendeeform->setKey('id');
		$this->registerView($attendeeform);

		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));
		$this->attachRule(new StandardRules($this));
		$this->attachRule(new NomenuRules($this));
	}

	/**
	 * Method to get the segment(s) for a category
	 * @param   string  $id     ID of the category to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 * @return  array|string  The segments of this item
	 */
	public function getEventsSegment($id, $query)
	{
		$category = $this->getCategories(["access" => true])->get($id);

		if ($category) {
			$path = array_reverse($category->getPath(), true);
			$path[0] = '1:root';

			if ($this->noIDs) {
				foreach ($path as &$segment) {
					list($id, $segment) = explode(':', $segment, 2);
				}
			}

			return $path;
		}

		return array();
	}
	public function getAttendeesSegment($id, $query)
	{
		$category = $this->getCategories(["access" => true])->get($id);

		if ($category) {
			$path = array_reverse($category->getPath(), true);
			$path[0] = '1:root';

			if ($this->noIDs) {
				foreach ($path as &$segment) {
					list($id, $segment) = explode(':', $segment, 2);
				}
			}

			return $path;
		}

		return array();
	}

	/**
	 * Method to get the segment(s) for an event
	 * @param   string  $id     ID of the event to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 * @return  array|string  The segments of this item
	 */
	public function getEventSegment($id, $query)
	{
		return array((int) $id => $id);
	}
	public function getAttendeeSegment($id, $query)
	{
		return array((int) $id => $id);
	}

	/**
	 * Method to get the segment(s) for an eventform
	 * @param   string  $id     ID of the eventform to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 * @return  array|string  The segments of this item
	 */
	public function getEventformSegment($id, $query)
	{
		return $this->getEventSegment($id, $query);
	}
	public function getAttendeeformSegment($id, $query)
	{
		return $this->getAttendeeSegment($id, $query);
	}

	/**
	 * Method to get the segment(s) for an event
	 * @param   string  $segment  Segment of the event to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getEventId($segment, $query)
	{
		return (int) $segment;
	}
	public function getAttendeeId($segment, $query)
	{
		return (int) $segment;
	}

	/**
	 * Method to get the segment(s) for an eventform
	 * @param   string  $segment  Segment of the eventform to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getEventformId($segment, $query)
	{
		return $this->getEventId($segment, $query);
	}
	public function getAttendeeformId($segment, $query)
	{
		return $this->getAttendeeId($segment, $query);
	}

	/**
	 * Method to get the id for a category
	 * @param   string  $segment  Segment to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getEventsId($segment, $query)
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
	public function getAttendeesId($segment, $query)
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

	/**
	 * Method to get categories from cache
	 * @param   array  $options   The options for retrieving categories
	 * @return  CategoryInterface  The object containing categories
	 * @since   4.0.0
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
