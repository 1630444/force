<?php
/**
 * @version    $Id$
 * @package    JSN_Sample
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2015 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Never declare the class twice.
if (class_exists('JSNInstallerScript'))
{
	return;
}

/**
 * Class for finalizing JSN extension installation.
 *
 * @package  JSN_Framework
 * @since    1.0.0
 */
abstract class JSNInstallerScript
{
}
