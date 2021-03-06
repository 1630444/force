<?php
/**
 * @version    $Id$
 * @package    JSN_PowerAdmin_2
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access');

if (class_exists('JsnExtFwAssets'))
{
	// Load required libraries.
	JsnExtFwAssets::loadJsnElements();

	// Generate base URL to assets folder.
	$base_url = JUri::root(true) . '/plugins/system/poweradmin2/assets';

	// Load assets of JSN PowerAdmin.
	JsnExtFwAssets::loadStylesheet("{$base_url}/css/style.css");
	JsnExtFwAssets::loadStylesheet("{$base_url}/css/custom.css");
	JsnExtFwAssets::loadScript("{$base_url}/js/poweradmin.js");

	// Load edition manager if necessary.
	if (JFactory::getApplication()->input->getCmd('option') !== 'com_poweradmin2')
	{
		JsnExtFwAssets::loadEditionManager(null,'com_poweradmin2', array(
			'skipVerifyingUser' => true
		));
	}
}

// Initialize Google Analytics.
JLoader::register('JSNPowerAdmin2Helper', JPATH_ADMINISTRATOR . '/components/com_poweradmin2/helpers/poweradmin2.php');

JSNPowerAdmin2Helper::initEventTracking();

// Render the module layout.
require JModuleHelper::getLayoutPath('mod_poweradminbar', $params->get('layout', 'default'));
