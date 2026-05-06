<?php
/**
 * @version     5.1.6
 * @package     com_gausers
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gausers\Administrator\Controller;

// No direct access.
\defined('_JEXEC') or die;

use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\MVC\Controller\AdminController;
use \Joomla\CMS\Session;
use \Joomla\CMS\Language\Text;

/**
 * Members list controller class.
 */
class ClubexecsController extends AdminController
{
	/**
	 * Method to clone existing records
	 * @return void
	 */
	public function duplicate()
	{
		// Check for request forgeries
		$this->checkToken();

		// Get id(s)
		$pks = $this->input->post->get('cid', array(), 'array');

		try
		{
			if (empty($pks))
			{
				throw new \Exception(Text::_('COM_GAUSERS_NO_ELEMENT_SELECTED'));
			}

			ArrayHelper::toInteger($pks);
			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(Text::_('COM_GAUSERS_ITEMS_SUCCESS_DUPLICATED'));
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		$this->setRedirect('index.php?option=com_gausers&view=clubexecs');
	}
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'Clubexec', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}


	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 * @return  void
	 * @since   3.0
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$input = Factory::getApplication()->input;
		$pks = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		ArrayHelper::toInteger($pks);
		ArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		Factory::getApplication()->close();
	}
    
    
    
}
