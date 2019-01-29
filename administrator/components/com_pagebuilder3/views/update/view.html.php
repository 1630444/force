<?php
/**
 * @version    $Id$
 * @package    JSN_PageBuilder3
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Update view.
 *
 * @package  JSN_PageBuilder3
 * @since    1.0.0
 */
class JSNPageBuilder3ViewUpdate extends JSNUpdateView
{
	/**
	 * Method for display page.
	 *
	 * @param   boolean  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Exception object.
	 */
	function display($tpl = null)
	{
		// Set the toolbar
		JToolBarHelper::title(JText::_('JSN_PAGEBUILDER3_UPDATE_PRODUCT'));

		// Add assets
		JSNPageBuilder3Helper::addAssets();

		// Display the template
		parent::display($tpl);
	}
}
