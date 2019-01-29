<?php
/**
 * @version    $Id$
 * @package    JSN_Framework
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Import necessary Joomla libraries
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * System plugin for initializing JSN Framework.
 *
 * @package  JSN_Framework
 * @since    1.0.0
 */
class PlgSystemJSNFramework extends JPlugin
{
	/**
	 * @var JApplication
	 */
	private static $_app = null;

	/**
	 * Register JSN Framework initialization.
	 *
	 * @return  void
	 */
	public function onAfterInitialise()
	{
		// Initialize JSN Framework
		require_once dirname(__FILE__) . '/libraries/loader.php';
		require_once dirname(__FILE__) . '/jsnframework.defines.php';

		// Get application object
		self::$_app = JFactory::getApplication();

		// Get requested component, view and task
		$this->option	= self::$_app->input->getCmd('option');
		$this->view		= self::$_app->input->getCmd('view');
		$this->task		= self::$_app->input->getCmd('task');

		// Skip the rest if a screen of the Centrora Security component is requested.
		if ($this->option === 'com_ose_firewall')
		{
			return;
		}

		// Get active language
		$lang = JFactory::getLanguage();

		// Check if language file exists for active language
		if ( ! file_exists(JPATH_ROOT . '/administrator/language/' . $lang->get('tag') . '/' . $lang->get('tag') . '.plg_system_jsnframework.ini'))
		{
			// If requested component has the language file, install then load it
			if (file_exists(JPATH_ROOT . '/administrator/components/' . $this->option . '/language/admin/' . $lang->get('tag') . '/' . $lang->get('tag') . '.plg_system_jsnframework.ini'))
			{
				JSNLanguageHelper::install((array) $lang->get('tag'), false, true);
				$lang->load('plg_system_jsnframework', JPATH_ADMINISTRATOR, null, true);
			}
			// Otherwise, try to load language file from plugin directory
			else
			{
				$lang->load('plg_system_jsnframework', JSN_PATH_FRAMEWORK, null, true);
			}
		}
		else
		{
			$lang->load('plg_system_jsnframework', JPATH_ADMINISTRATOR, null, true);
		}

		// Disable notice and warning by default for our products.
		// The reason for doing this is if any notice or warning appeared then handling JSON string will fail in our code.
		if (function_exists('error_reporting') AND in_array($this->option, JSNVersion::$products))
		{
			error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
		}

		// Set event handlers to detect and update dependency installation / removal
		self::$_app->registerEvent('onExtensionAfterInstall',		'jsnExtFrameworkUpdateDependencyAfterInstallExtension');
		self::$_app->registerEvent('onExtensionBeforeUninstall',	'jsnExtFrameworkUpdateDependencyBeforeUninstallExtension');

	}

	/**
	 * Event handler to re-parse request URI.
	 *
	 * @return  void
	 */
	public function onAfterRoute()
	{
		// Get installed Joomla version
		$JVersion 	= new JVersion;
		$JVersion 	= $JVersion->getShortVersion();
		$option 	= trim((string) $this->option);

		if (self::$_app->isAdmin() && version_compare($JVersion, '3.0', '>=') && in_array($option, JSNVersion::$products))
		{
			$manifestFile = JPATH_ADMINISTRATOR . '/components/' . $option . '/' . str_replace('com_', '', $option) . '.xml';
			if (file_exists($manifestFile))
			{
				$xml 	= JSNUtilsXml::load($manifestFile);
				$attr 	= $xml->attributes();

				if (count($attr))
				{
					if (isset($attr['version']) && (string) $attr['version'] != '')
					{
						$version = (string) $attr['version'];

						if ($option == 'com_imageshow')
						{
							$version = str_replace('.x', '.0', $version);
						}

						if (version_compare($version, '3.0', '<'))
						{
							// Check if all JSN Extensions are compatible with Joomla 3.x, if not, redirect to index.php and show a warning message
							self::$_app->enqueueMessage(JText::sprintf('You are running a Joomla 2.5 version of %1$s on Joomla 3.x. Please download %1$s for Joomla 3.x and reinstall via Joomla! Installer to fix the problem.', 'JSN ' . ucfirst(str_replace('com_', '', $option))), 'warning');
							self::$_app->redirect('index.php');
							return false;
						}
					}
				}
			}
		}

		// Make sure our onAfterRender event handler is the last one executed
		self::$_app->registerEvent('onAfterRender', 'jsnExtFrameworkFinalize');
	}

	/**
	 * Proceed positions rendering
	 *
	 * Remove default tp=1 layout, replace by jsn style to
	 * show page positions
	 *
	 * @return  void
	 */
	public function onAfterDispatch()
	{
		if ( ! JSNVersion::isJoomlaCompatible(JSN_FRAMEWORK_REQUIRED_JOOMLA_VER))
		{
			return;
		}

		// Keep this for joomla 2.5. Will be deprecated.
		if (JSNVersion::isJoomlaCompatible('2.5') AND ! JSNVersion::isJoomlaCompatible('3.0'))
		{
			if (self::$_app->isAdmin()
				AND self::$_app->input->getVar('format', '') != 'raw'
				AND self::$_app->input->getVar('option', '') == 'com_poweradmin'
				AND self::$_app->input->getVar('view') != 'update'
				AND self::$_app->input->getVar('view') != 'changeposition')
			{
				$version = PoweradminHelper::getVersion();

				if (version_compare($version, '1.1.3', '>='))
				{
					$JSNMedia = JSNFactory::getMedia();
					$JSNMedia->addMedia();
				}
			}
		}

		if (self::$_app->input->getCmd('poweradmin', 0) == 1)
		{
			$jsnHelper = JSNPositionsModel::_getInstance();
			$jsnHelper->renderEmptyComponent();
			$jsnHelper->renderEmptyModule();
		}
	}

	/**
	 * Before render needs using this function to make format of HTML of modules
	 *
	 * @return  Changed HTML format
	 */
	public function onBeforeRender()
	{
		if ( ! JSNVersion::isJoomlaCompatible(JSN_FRAMEWORK_REQUIRED_JOOMLA_VER))
		{
			return;
		}

		if (self::$_app->isAdmin())
		{
			// Ask user to review JoomlaShine product on JED
			$this->askForReview();

			/* Initialize edition manager.
			JSNHtmlHelper::loadEditionManager($this->option, 'jsn/test-edition');*/
		}
		elseif (JSNVersion::isJoomlaCompatible('3.0') AND self::$_app->input->getCmd('poweradmin', 0) == 1)
		{
			$jsnHelper = JSNPositionsModel::_getInstance();
			$jsnHelper->renderEmptyModule();
		}
	}

	/**
	 * Do some output manipulation.
	 *
	 * Auto-inject <b>jsn-master tmpl-nameOfDefaultTemplate</b> into the class
	 * attribute of <b>&lt;body&gt;</b> tag if not already exists. This
	 * automation only affects backend page.
	 *
	 * @return  void
	 */
	public static function onAfterRender()
	{
		// Make sure the remaining process is executed in last order
		if ( ! defined('JSN_EXTFW_LAST_EXECUTION'))
		{
			return;
		}

		// Get active component
		$option = self::$_app->input->getCmd('option');

		// Get the rendered HTML code
		$html = JResponse::getBody();

		if (self::$_app->input->getVar('poweradmin'))
		{
			if (preg_match_all('#<a[^\>]*href\s*=\s*[\'"]([^"]*[^"]+)[\'"]#i', $html, $ms, PREG_SET_ORDER))
			{
				foreach ($ms as $m)
				{
					$html = str_replace($m[0], str_replace($m[1], 'javascript:void(0)', $m[0]), $html);
				}
			}
		}

		// Do some fixes if this is admin page
		if (self::$_app->isAdmin())
		{
			// Fix asset links for Joomla 2.5
			if (JSNVersion::isJoomlaCompatible('2.5') AND ! JSNVersion::isJoomlaCompatible('3.0') AND strpos($html, JSN_URL_ASSETS) !== false)
			{
				// Get asset link
				if (preg_match_all('#<(link|script)([^\>]*)(href|src)="([^"]*' . JSN_URL_ASSETS . '[^"]+)"#i', $html, $matches, PREG_SET_ORDER))
				{
					foreach ($matches AS $match)
					{
						// Do replace
						$html = str_replace(
							$match[0],
							'<' . $match[1] . $match[2] . $match[3] . '="' . dirname(dirname($match[4])) . '/' . str_replace('.', '-', basename(dirname($match[4]))) . '/' . basename($match[4]) . '"',
							$html
						);
					}
				}
			}

			// Remove our extensions from the Joomla 3.0's global config page
			if ($option == 'com_config' AND JSNVersion::isJoomlaCompatible('3.0'))
			{
				$html = preg_replace(
					'#<li>[\r\n]+\t+<a href="index.php\?option=com_config&view=component&component=(' . implode('|', JSNVersion::$products) . ')">[^<]+</a>[\r\n]+\t+</li>#',
					'',
					$html
				);
			}

			// Alter body tag
			if (preg_match('/<body[^>]*>/i', $html, $match) AND strpos($match[0], 'jsn-master tmpl-' . self::$_app->getTemplate()) === false)
			{
				if (strpos($match[0], 'class=') === false)
				{
					$match[1] = substr($match[0], 0, -1) . ' class=" jsn-master tmpl-' . self::$_app->getTemplate() . ' ">';
				}
				else
				{
					$match[1] = str_replace('class="', 'class=" jsn-master tmpl-' . self::$_app->getTemplate() . ' ', $match[0]);
				}

				$html = str_replace($match[0], $match[1], $html);
			}

			if (JSNVersion::isJoomlaCompatible('3.2'))
			{
				// Clean-up HTML5 fall-back script if running on Joomla 3.2
				if (in_array($option, JSNVersion::$products)
					AND preg_match('#[\r\n][\s\t]+<script src="[^"]*/media/system/js/html5fallback(-uncompressed)?\.js"[^>]+></script>#', $html, $match))
				{
					$html = str_replace($match[0], '', $html);
				}

				// Temporary fix jQuery version conflict on Joomla 3.2
				$pos = strpos($html, JSN_URL_ASSETS . '/3rd-party/jquery/jquery.min.js');

				if ($pos !== false AND preg_match('#<script[^>]+src="' . JSN_URL_ASSETS . '/3rd-party/jquery/jquery.min.js"[^>]*></script>#', $html, $match))
				{
					$html = explode($match[0], $html);

					// Do some tricks on multiple jQuery instances
					$script = '<script type="text/javascript">'
						. "\n\t\t" . '(JoomlaShine = window.JoomlaShine || {});'
						. "\n\t\t" . '(!window.jQuery || (JoomlaShine.jQueryBackup = jQuery));'
						. "\n\t" . '</script>'
						. "\n\t" . $match[0];

					// Update document header
					$html[0] .= $script;

					// Truncate content
					$html[2] = substr($html[1], strpos($html[1], '</head>'));
					$html[1] = substr($html[1], 0, strpos($html[1], '</head>'));

					if (preg_match('#<script[^>]+src="[^"]*/media/jui/js/jquery(\.min)?\.js(\?(.*))?"[^>]*></script>#', $html[1], $match))
					{
						$script = '<script type="text/javascript">'
							. "\n\t\t" . '(JoomlaShine = window.JoomlaShine || {});'
							. "\n\t\t" . '(!window.jQuery || (JoomlaShine.jQuery = jQuery));'
							. "\n\t" . '</script>'
							. "\n\t" . $match[0];

						// Update document header
						$html[1] = str_replace($match[0], $script, $html[1]);
					}
					elseif (preg_match('#<script[^>]+src="[^"]*/js/template\.js[^"]*"[^>]*></script>#', $html[1], $match))
					{
						$script = '<script type="text/javascript">'
							. "\n\t\t" . '(JoomlaShine = window.JoomlaShine || {});'
							. "\n\t\t" . '(!window.jQuery || (JoomlaShine.jQuery = jQuery));'
							. "\n\t\t" . '(!JoomlaShine.jQueryBackup || (jQuery = JoomlaShine.jQueryBackup));'
							. "\n\t" . '</script>'
							. "\n\t" . $match[0];

						// Update document header
						$html[1] = str_replace($match[0], $script, $html[1]);
					}
					elseif (preg_match('#<script type="text/javascript">#', $html[1], $match))
					{
						$script = '<script type="text/javascript">'
							. "\n\t\t" . '(JoomlaShine = window.JoomlaShine || {});'
							. "\n\t\t" . '(!window.jQuery || (JoomlaShine.jQuery = jQuery));'
							. "\n\t\t" . '(!JoomlaShine.jQueryBackup || (jQuery = JoomlaShine.jQueryBackup));';

						// Update document header
						$html[1] = str_replace($match[0], $script, $html[1]);
					}

					$html = implode($html);

					// Fix for (function($) { ... })(jQuery);
					$tmp = preg_split('/\}[\s\t\r\n]*\)*\([^\r\n]*jQuery[^\r\n]*\)\s*;?/', $html);
					$html = '';
					$i = 0;

					foreach ($tmp AS $part)
					{
						$i++;

						if ($i == count($tmp))
						{
							$html .= $part;
						}
						else
						{
							$parts = preg_split('/\(\s*function\s*\(\s*\$\s*\)\s*\{/', $part, 2);

							if (count($parts) < 2)
							{
								$html .= $part;
							}
							elseif (stripos($parts[1], 'jsn') !== false)
							{
								$html .= "{$parts[0]}(function($) {{$parts[1]}})((window.JoomlaShine && JoomlaShine.jQuery) ? JoomlaShine.jQuery : jQuery);";
							}
							else
							{
								$html .= "{$parts[0]}(function($) {{$parts[1]}})(jQuery);";
							}
						}
					}

					// Remove JSN ImageShow's buggy fix for jQuery conflict
					if (strpos($html, 'administrator/components/com_imageshow/assets/js/joomlashine/jquery.safe.element.js') !== false)
					{
						$html = preg_replace('#[\r\n][\s\t]*<script[^>]+src="[^"]*/joomlashine/jquery.safe.element.js[^"]*"[^>]*></script>#', '', $html);
					}
				}
			}
		}

		// Attach JS declaration
		$html = str_replace('</head>', JSNHtmlAsset::buildHeader() . '</head>', $html);

		// Fix compatibility problem between require.js and RokPad editor
		if (strpos($html, '/plugins/editors/rokpad/'))
		{
			if (preg_match_all('#[\r\n][\s\t]*<script[^>]+src="[^"]*/plugins/editors/rokpad/[^"]+"[^>]*></script>#', $html, $matches, PREG_SET_ORDER))
			{
				foreach ($matches AS $match)
				{
					// Clean the script tag from its original position
					$html = str_replace($match[0], '', $html);

					// Inject the removed script tag into the end of head section
					$html = str_replace('</head>', $match[0] . '</head>', $html);
				}
			}
		}

		// Set new response body
		JResponse::setBody($html);
	}

	/**
	 * Handle onExtensionBeforeInstall event.
	 *
	 * @param   string            $method     Installation method.
	 * @param   string            $type       Extension type.
	 * @param   SimpleXMLElement  $manifest   The extension's manifest object.
	 * @param   integer           $extension  Installed extension ID.
	 *
	 * @return  void
	 */
	public function onExtensionBeforeInstall($method, $type, $manifest, $extension)
	{
		// Simply return if the current screen is the default screen of Joomla for installing extension.
		if ($this->option === 'com_installer' && $this->view === 'install')
		{
			return;
		}

		$this->onExtensionBeforeUpdate($type, $manifest, 'install');
	}

	/**
	 * Handle onExtensionBeforeUpdate event.
	 *
	 * @param   string            $type      Extension type.
	 * @param   SimpleXMLElement  $manifest  The extension's manifest object.
	 * @param   string            $method    The current installation method.
	 *
	 * @return  void
	 */
	public function onExtensionBeforeUpdate($type, $manifest, $method = 'update')
	{
		// Simply return if the extension type is not 'component'.
		if ((string) $type !== 'component')
		{
			return;
		}

		// Generate path to the installed component's manifest file.
		$ext = strtolower(\JFilterInput::getInstance()->clean((string) $manifest->name, 'string'));

		if (strpos($ext, 'com_') === 0)
		{
			$ext = substr($ext, 4);
		}

		$ext = str_replace('jsn', '', $ext);
		$ext = trim($ext);

		$xml = JPATH_ADMINISTRATOR . "/components/com_{$ext}/{$ext}.xml";

		// Simply return if the manifest file does not exists.
		if (!is_file($xml) || !($xml = simplexml_load_file($xml)))
		{
			return;
		}

		// Simply return if the component is updating itself.
		if ($this->option === "com_{$ext}" && $this->view === 'update' && $this->task === 'update.install')
		{
			return;
		}

		// Simply return if the component is upgrading itself.
		if ($this->option === "com_{$ext}" && $this->view === 'upgrade' && $this->task === 'upgrade.install')
		{
			return;
		}

		// Simply return if the component being updated does not depend on JSN Ext. Framework gen. 1.
		if (stripos((string) $xml->author, 'JoomlaShine') === false && stripos((string) $xml->copyright, 'JoomlaShine') === false)
		{
			return;
		}
		elseif (isset($xml->group) && (string) $xml->group === 'jsnextfw')
		{
			return;
		}

		// Get Joomla application object.
		$app = JFactory::getApplication();

		try
		{
			// Get the product info.
			$info       = JSNUtilsXml::loadManifestCache("com_{$ext}");
			$edition    = JSNUtilsText::getConstant('EDITION', "com_{$ext}");
			$identified = ($identified = JSNUtilsText::getConstant('IDENTIFIED_NAME', "com_{$ext}")) ? $identified : strtolower($info->name);

			// Get parameters of the component being updated.
			$params = JSNUtilsExtension::getExtensionParams('component', "com_{$ext}");

			// Check if a token for updating products is set.
			if (empty($params['token_key']))
			{
				// Get JSN Extension Framework's parameters.
				$params = JSNUtilsExtension::getExtensionParams('plugin', 'jsnframework', 'system');

				// Check if a token for updating products is set.
				if (empty($params['token_key']))
				{
					throw new Exception(JText::sprintf('JSN_EXTFW_MISSING_TOKEN_KEY', JText::_($info->name), "index.php?option=com_{$ext}&view=configuration"));
				}
			}

			// Get Joomla version.
			$version = new JVersion;

			// Get Joomla config.
			$config = JFactory::getConfig();

			// Get update server.
			$server = JSN_EXT_DOWNLOAD_UPDATE_URL;

			// Build query string to download the product update.
			$query[] = 'token=' . $params['token_key'];
			$query[] = 'edition=' . strtolower(urlencode($edition));
			$query[] = 'joomla_version=' . $version->RELEASE;
			$query[] = 'identified_name=' . $identified;

			if (strtolower($edition) != 'pro unlimited' && strtolower($edition) != 'pro standard')
			{
				$domain = JUri::root();

				if (preg_match('@^(?:http://www\.|http://|www\.|http:|https://www\.|https://|www\.|https:)?([^/]+)@i', $domain, $domainFilter))
				{
					$domain = $domainFilter[1];
				}

				$query[] = 'domain=' . urlencode($domain);

				$server = JSN_EXT_DOWNLOAD_UPDATE_URL_V2;
			}

			// Build the final URL to download the product update.
			$url = $server . '&' . implode('&', $query);

			// Generate file name for the update package.
			$name[] = 'jsn';
			$name[] = $identified;

			if ($edition)
			{
				$name[] = strtolower(str_replace(' ', '_', $edition));
			}

			$name[] = 'j' . $version->RELEASE;
			$name[] = 'install.zip';

			$name = implode('_', $name);

			// Disable max. execution time.
			ini_set('max_execution_time', 0);

			// Try to download the update package.
			try
			{
				$path = $config->get('tmp_path') . '/' . $name;
				JSNUtilsHttp::get($url, $path, true);
			}
			catch (Exception $e)
			{
				throw new Exception(JText::_('JSN_EXTFW_UPDATE_DOWNLOAD_PACKAGE_FAIL') . ': ' . $e->getMessage());
			}

			// Validate the downloaded update package.
			if (filesize($path) < 10)
			{
				// Get LightCart error code
				$error = file_get_contents($path);

				throw new Exception(str_replace(
					'JSN JSN',
					'JSN',
					JText::sprintf(
						'JSN_EXTFW_LIGHTCART_ERROR_' . $error,
						JText::_($info->name) . ' ' . strtoupper($edition)
					)
				));
			}

			// Skip redirection after updating if this is a multi-update session.
			if ($this->option == 'com_installer' && $this->view == 'update' && $this->task == 'update.update' && count($cid = (array) $app->input->getVar('cid', array())) > 1)
			{
				$app->input->set('tool_redirect', 0);
			}

			// Get the current Joomla's installer object.
			$installer = JInstaller::getInstance();

			// Extract the update package to the target installer folder.
			JArchive::extract($path, $installer->getPath('source'));

			// Then, remove it immediately.
			JFile::delete($path);

			// Re-setup the installation process.
			$installer->setupInstall($method);

			/* Load the default JSNInstallerScript class.
			require_once JSN_PATH_LIBRARIES . '/jsninstaller.php';

			// Prevent JSNInstallerScript class from being redeclared.
			if (class_exists('JSNInstallerScript'))
			{
				// Find the installer script in the source directory.
				if (count($files = JFolder::files($installer->getPath('source'), 'jsninstaller\.php$', true, true)))
				{
					foreach ($files as $file)
					{
						$content = JFile::read($file);
						$content = explode('<?php', $content, 2);
						$content = "{$content[0]}<?php if (class_exists('JSNInstallerScript')) return;{$content[1]}";

						JFile::write($file, $content);
					}
				}
			}*/

			// Copy checksum files to the installed component directory.
			if (count($files = JFolder::files($installer->getPath('source'), '\.checksum$', true, true)))
			{
				// Generate path to installed component directory.
				$path = JPATH_ADMINISTRATOR . "/components/com_{$ext}";

				// Copy all checksum files over.
				foreach ($files as $file)
				{
					JFile::copy($file, $path . '/' . basename($file));
				}
			}
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}
	}

	/**
	 * Handle Ajax requests.
	 *
	 * @return  void
	 */
	public function onAjaxJSNFramework()
	{
		// Set necessary headers.
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		// Execute the requested Ajax action.
		JSNAjax::execute();

		// Exit immediately to prevent Joomla from processing further.
		exit;
	}

	/**
	 * Ask user to review JoomlaShine product on JED.
	 *
	 * @return  void
	 */
	private function askForReview()
	{
		/* Continue only if this is admin page of JoomlaShine product
		if (self::$_app->isAdmin() AND in_array($this->option, JSNVersion::$products))
		{
			// Get product options
			$config = JSNConfigHelper::get($this->option);

			if ( (int) $config->get('review_popup', 1) )
			{
				// Get time difference
				$time = time();
				$last = $config->get('last_ask_for_review', 0);

				if ($last == 0)
				{
					$last = filemtime(JPATH_ROOT . "/administrator/components/{$this->option}/" . substr($this->option, 4) . '.xml');
				}

				// Check if it's time to ask for review
				if ($time - $last >= REVIEW_POPUP_PERIOD)
				{
					// Load script to popup a modal ask user for review
					JSNHtmlAsset::loadScript(
						'jsn/askforreview',
						array(
							'url'		=> JUri::root() . "plugins/system/jsnframework/libraries/joomlashine/choosers/review/index.php?component={$this->option}",
							'language'	=> JSNUtilsLanguage::getTranslated(array('JSN_EXTFW_CHOOSERS_REVIEW_ON_JED'))
						)
					);

					// Get config model
					$model = new JSNConfigModel;

					// Store time of last popup
					$form = $model->getForm(array(), true, JPATH_ROOT . '/administrator/components/' . $this->option . '/config.xml');
					$data = array('last_ask_for_review' => $time);

					try
					{
						// Save new configuration
						$model->save($form, $data);
					}
					catch (Exception $e)
					{
						// Do nothing as this is a background process
					}
				}
			}
		}*/
	}
}

/**
 * Finalize response body.
 *
 * @return  void
 */
function jsnExtFrameworkFinalize()
{
	define('JSN_EXTFW_LAST_EXECUTION', 1);
	PlgSystemJSNFramework::onAfterRender();
}

/**
 * Update dependency after an extension is installed.
 *
 * @param   object  $installer   Joomla installer object.
 * @param   mixed   $identifier  Extension ID on installation success, boolean FALSE otherwise.
 *
 * @return  void
 */
function jsnExtFrameworkUpdateDependencyAfterInstallExtension($installer, $identifier)
{
	if (is_integer($identifier))
	{
		// Get installed extension
		$ext = basename($installer->getPath('extension_administrator'));

		// Check if our product is installed
		if (in_array($ext, JSNVersion::$products))
		{
			// Build query to get dependency installation status
			$db	= JFactory::getDbo();
			$q	= $db->getQuery(true);

			$q->select('manifest_cache, custom_data, params');
			$q->from('#__extensions');
			$q->where("element = 'jsnframework'");
			$q->where("type = 'plugin'", 'AND');
			$q->where("folder = 'system'", 'AND');

			$db->setQuery($q);

			// Load dependency installation status
			$status = $db->loadObject();

			// old params
			$oldParams	 = array();

			if ($status->params != '')
			{
				$oldParams = json_decode($status->params, true);
			}

			$ext = substr($ext, 4);
			$dep = ! empty($status->custom_data) ? (array) json_decode($status->custom_data) : array();

			// Update dependency list
			in_array($ext, $dep) OR $dep[] = $ext;
			$status->custom_data = array_unique($dep);

			// Build query to update dependency data
			$q = $db->getQuery(true);

			$q->update('#__extensions');
			$q->set("custom_data = '" . json_encode($status->custom_data) . "'");

			// Backward compatible: keep data in this column for older product to recognize
			$manifestCache = json_decode($status->manifest_cache);
			$manifestCache->dependency = $status->custom_data;

			$q->set("manifest_cache = '" . json_encode($manifestCache) . "'");

			// Backward compatible: keep data in this column also for another old product to recognize
			$params = array_combine($status->custom_data, $status->custom_data);
			if (isset($oldParams['token_key']))
			{
				$params ['token_key'] = $oldParams['token_key'];
			}

			$params = json_encode((object) $params);

			$q->set("params = '" . $params . "'");

			$q->where("element = 'jsnframework'");
			$q->where("type = 'plugin'", 'AND');
			$q->where("folder = 'system'", 'AND');

			$db->setQuery($q);
			$db->execute();
		}
	}
}

/**
 * Update dependency before an extension is being removed.
 *
 * @param   integer  $identifier  Extension ID.
 *
 * @return  boolean
 */
function jsnExtFrameworkUpdateDependencyBeforeUninstallExtension($identifier)
{
	// Get extension being removed
	$ext = JTable::getInstance('Extension');
	$ext->load($identifier);
	$ext = $ext->element;

	// Check if our product is being removed
	if (in_array($ext, JSNVersion::$products))
	{
		// Build query to get dependency installation status
		$db	= JFactory::getDbo();
		$q	= $db->getQuery(true);

		$q->select('manifest_cache, custom_data, params');
		$q->from('#__extensions');
		$q->where("element = 'jsnframework'");
		$q->where("type = 'plugin'", 'AND');
		$q->where("folder = 'system'", 'AND');

		$db->setQuery($q);

		// Load dependency installation status
		$status = $db->loadObject();

		// old params
		$oldParams	 = array();

		if ($status->params != '')
		{
			$oldParams = json_decode($status->params, true);
		}

		$ext	= substr($ext, 4);
		$deps	= ! empty($status->custom_data) ? (array) json_decode($status->custom_data) : array();

		// Update dependency tracking
		$status->custom_data = array();

		foreach ($deps AS $dep)
		{
			// Backward compatible: ensure that product is not removed
			// if ($dep != $ext)
			if ($dep != $ext AND is_dir(JPATH_BASE . '/components/com_' . $dep))
			{
				$status->custom_data[] = $dep;
			}
		}

		// Build query to update dependency data
		$q = $db->getQuery(true);

		$q->update('#__extensions');
		$q->set("custom_data = '" . json_encode($status->custom_data) . "'");

		// Backward compatible: keep data in this column for older product to recognize
		$manifestCache = json_decode($status->manifest_cache);
		$manifestCache->dependency = $status->custom_data;

		$q->set("manifest_cache = '" . json_encode($manifestCache) . "'");

		// Backward compatible: keep data in this column also for another old product to recognize
		if (count($status->custom_data))
		{
			$params = array_combine($status->custom_data, $status->custom_data);

			if (isset($oldParams['token_key']))
			{
				$params ['token_key'] = $oldParams['token_key'];
			}

			$params = json_encode((object) $params);
		}
		else
		{
			$params = '';
		}

		$q->set("params = '" . $params . "'");

		$q->where("element = 'jsnframework'");
		$q->where("type = 'plugin'", 'AND');
		$q->where("folder = 'system'", 'AND');

		$db->setQuery($q);
		$db->execute();
	}

	// Always return TRUE so the extension can be removed
	return true;
}
