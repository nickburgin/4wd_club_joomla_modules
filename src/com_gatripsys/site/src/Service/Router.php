<?php

/**
 * @version    5.3.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatripsys\Site\Service;

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
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

/**
 * Class Component Router
 */
class Router extends RouterView
{
	private $noIDs;

	/**
	 * The category factory
	 * @var CategoryFactoryInterface
	 * @since  4.0.2
	 */
	private $categoryFactory;

	/**
	 * The category cache
	 * @var  array
	 * @since  4.0.2
	 */
	private $categoryCache = [];

	public function __construct(SiteApplication $app, AbstractMenu $menu, CategoryFactoryInterface $categoryFactory, DatabaseInterface $db)
	{
		$params = Factory::getApplication()->getParams('com_gatripsys');
		$this->noIDs = (bool) $params->get('sef_ids');
		$this->categoryFactory = $categoryFactory;
		
		/* -------------   Trips  -------------------- */
		$trips = new RouterViewConfiguration('trips');
		$this->registerView($trips);

		$trip = new RouterViewConfiguration('trip');
		$trip->setKey('id')->setParent($trips);
		$this->registerView($trip);

		$tripform = new RouterViewConfiguration('tripform');
		$tripform->setKey('id');
		$this->registerView($tripform);

		/* -------   invoices Config  --------------- */
		$invoices = new RouterViewConfiguration('invoices');
		$this->registerView($invoices);

		$invoice = new RouterViewConfiguration('invoice');
		$invoice->setKey('id')->setParent($invoices);
		$this->registerView($invoice);

		$invoiceform = new RouterViewConfiguration('invoiceform');
		$invoiceform->setKey('id');
		$this->registerView($invoiceform);

		/* -------   incidents Config  --------------- */
		$incidents = new RouterViewConfiguration('incidents');
		$this->registerView($incidents);

		$incident = new RouterViewConfiguration('incident');
		$incident->setKey('id')->setParent($incidents);
		$this->registerView($incident);

		$incidentform = new RouterViewConfiguration('incidentform');
		$incidentform->setKey('id');
		$this->registerView($incidentform);

		/* -------   attendees Config  --------------- */
		$attendees = new RouterViewConfiguration('attendees');
		$this->registerView($attendees);

		$attendee = new RouterViewConfiguration('attendee');
		$attendee->setKey('id')->setParent($attendees);
		$this->registerView($attendee);

		$attendeeform = new RouterViewConfiguration('attendeeform');
		$attendeeform->setKey('id');
		$this->registerView($attendeeform);

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
	public function getTripSegment($id, $query)
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
	public function getTripformSegment($id, $query)
	{
		return $this->getTripSegment($id, $query);
	}

	/* -------------   Item ID  -------------------- */
	/**
	 * Method to get the segment(s) for a transaction
	 * @param   string  $segment  Segment of the transaction to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getTripId($segment, $query)
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
	public function getTripformId($segment, $query)
	{
		return $this->getTripId($segment, $query);
	}

	/* -------------   List ID  -------------------- */
	/**
	 * Method to get the id for a category
	 * @param   string  $segment  Segment to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getTripsId($segment, $query)
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
	 * @since   4.0.2
	 */
	private function getCategories(array $options = []): CategoryInterface
	{
		$key = serialize($options);

		if (!isset($this->categoryCache[$key])) {
			$this->categoryCache[$key] = $this->categoryFactory->createCategory($options);
		}

		return $this->categoryCache[$key];
	}

	/* -------------   Item Segments  -------------------- */
	/**
	 * Method to get the segment(s) for an transaction
	 * @param   string  $id     ID of the transaction to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 * @return  array|string  The segments of this item
	 */
	public function getInvoiceSegment($id, $query)
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
	public function getInvoiceformSegment($id, $query)
	{
		return $this->getInvoiceSegment($id, $query);
	}

	/* -------------   Item ID  -------------------- */
	/**
	 * Method to get the segment(s) for a transaction
	 * @param   string  $segment  Segment of the transaction to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getInvoiceId($segment, $query)
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
	public function getInvoiceformId($segment, $query)
	{
		return $this->getInvoiceId($segment, $query);
	}

	/* -------------   List ID  -------------------- */
	/**
	 * Method to get the id for a category
	 * @param   string  $segment  Segment to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getInvoicesId($segment, $query)
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

	/* -------------   Item Segments  -------------------- */
	/**
	 * Method to get the segment(s) for an transaction
	 * @param   string  $id     ID of the transaction to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 * @return  array|string  The segments of this item
	 */
	public function getAttendeeSegment($id, $query)
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
	public function getAttendeeformSegment($id, $query)
	{
		return $this->getAttendeeSegment($id, $query);
	}

	/* -------------   Item ID  -------------------- */
	/**
	 * Method to get the segment(s) for a transaction
	 * @param   string  $segment  Segment of the transaction to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getAttendeeId($segment, $query)
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
	public function getAttendeeformId($segment, $query)
	{
		return $this->getAttendeeId($segment, $query);
	}

	/* -------------   List ID  -------------------- */
	/**
	 * Method to get the id for a category
	 * @param   string  $segment  Segment to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
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

	/* -------------   Item Segments  -------------------- */
	/**
	 * Method to get the segment(s) for an transaction
	 * @param   string  $id     ID of the transaction to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 * @return  array|string  The segments of this item
	 */
	public function getIncidentSegment($id, $query)
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
	public function getIncidentformSegment($id, $query)
	{
		return $this->getIncidentSegment($id, $query);
	}

	/* -------------   Item ID  -------------------- */
	/**
	 * Method to get the segment(s) for a transaction
	 * @param   string  $segment  Segment of the transaction to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getIncidentId($segment, $query)
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
	public function getIncidentformId($segment, $query)
	{
		return $this->getIncidentId($segment, $query);
	}

	/* -------------   List ID  -------------------- */
	/**
	 * Method to get the id for a category
	 * @param   string  $segment  Segment to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getIncidentsId($segment, $query)
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
}
