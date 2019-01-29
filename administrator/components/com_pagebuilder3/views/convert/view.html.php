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
 * About view
 *
 * @package  JSN_PageBuilder3
 * @since    1.0.0
 */
class JSNPageBuilder3ViewConvert extends JSNBaseView
{
	protected $convert;

	/**
	 * Display method
	 *
	 * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @since 1.4.0
	 * @return    void
	 */
	function display($tpl = null)
	{
		// Get config parameters
//		$config = JSNConfigHelper::get();

		$doc = JFactory::getDocument();
		// Set the toolbar
		JToolbarHelper::title(JText::_('JSN_PAGEBUILDER3_CONVERT_TITLE'));

		// Add toolbar menu
//		JSNPageBuilder3Helper::addToolbarMenu();

		// Set the submenu
//		JSNPageBuilder3Helper::addSubmenu('convert');
		if (!class_exists('JSNPageBuilder3ConvertHelper'))
		{
			require_once JPATH_ROOT . '/administrator/components/com_pagebuilder3/helpers/convert.php';
		}
		$this->convert = new JSNPageBuilder3ConvertHelper();


		// Add assets
		JSNPageBuilder3Helper::addAssets();

// Load required stylesheets.
		JSNHtmlAsset::addStyle('//cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.10/components/icon.min.css');
		JSNHtmlAsset::addStyle('//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');

		JSNHtmlAsset::addScriptLibrary('pagefly', JUri::root(true) . '/plugins/editors/pagebuilder3/assets/app/main');

		JSNHtmlAsset::loadScript(JURI::root(true) . '/administrator/components/com_pagebuilder3/assets/js/convert.js');


		// Display the template
		parent::display($tpl);
	}

}
