<?php

/**
 * @version     $Id$
 * @package     JSN_Mobilize
 * @subpackage  AdminComponent
 * @author      JoomlaShine Team <support@joomlashine.com>
 * @copyright   Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$app = JFactory::getApplication();
// Display messages
echo $app->input->getInt('ajax', 0) ? '' : $this->msgs;

// Get HTML code for config form
ob_start();
JSNConfigHelper::render($this->config);
$configForm = ob_get_clean();

// Mark required parameter
if ($required = $app->input->getVar('required'))
{
	foreach ($required AS $param)
	{
		$configForm = str_replace(
		'id="jsnconfig-' . str_replace('_', '-', $param) . '-field" class="control-group"', 'id="jsnconfig-' . str_replace('_', '-', $param) . '-field" class="control-group error"', $configForm
		);
	}
}

// Display config form
echo $configForm;
$products	=	JSNMobilizeHelper::getDependentExtensions();
// Display footer
JSNHtmlGenerate::footer($products);
