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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

if (!class_exists('JSNInstallerScript'))
{
	// Get path to JSN Installer class file
	is_readable($base = dirname(__FILE__) . '/administrator/components/com_poweradmin2/jsninstaller.php') ||
		 is_readable($base = dirname(__FILE__) . '/jsninstaller.php') ||
		 is_readable($base = JPATH_ROOT . '/administrator/components/com_poweradmin2/jsninstaller.php') || $base = null;

	if (!empty($base))
	{
		require_once $base;
	}
}

// Import necessary libraries.
jimport('joomla.filesystem.file');

/**
 * Class for finalizing JSN PowerAdmin installation.
 *
 * @package  JSN_PowerAdmin_2
 * @since    1.0.0
 */
class Com_PowerAdmin2InstallerScript extends JSNInstallerScript
{

	/**
	 * XML manifest.
	 *
	 * @var  SimpleXMLElement
	 */
	protected $manifest;

	/**
	 * Implement preflight hook.
	 *
	 * This step will be verify permission for install/update process.
	 *
	 * @param   string  $mode    Install or update?
	 * @param   object  $parent  JInstaller object.
	 *
	 * @return  boolean
	 */
	public function preflight($mode, $parent)
	{
		// Initialize variables
		$this->parentInstaller = $parent->getParent();

		$this->app = JFactory::getApplication();
		$this->manifest = $this->parentInstaller->getManifest();

		// Get installed extension directory and name
		$this->path = $this->parentInstaller->getPath('extension_administrator');
		$this->name = substr(basename($this->path), 4);

		// Check if installed Joomla version is compatible
		$joomlaVersion = new JVersion();
		$canInstall = substr($joomlaVersion->RELEASE, 0, 1) == substr((string) $this->manifest['version'], 0, 1) ? true : false;

		if (!$canInstall)
		{
			$this->app->enqueueMessage(
				sprintf('Cannot install "%s" because the installation package is not compatible with your installed Joomla version',
					(string) $this->manifest->name), 'error');
		}

		// Parse dependency
		$this->parseDependency($this->parentInstaller);

		// Skip checking folder permission if FTP layer is enabled
		$config = JFactory::getConfig();

		if ($config->get('ftp_enable'))
		{
			// Try to backup user edited language file
			return $this->backupLanguage();
		}

		// Check environment
		$canInstallExtension = true;
		$canInstallSiteLanguage = is_writable(JPATH_SITE . '/language');
		$canInstallAdminLanguage = is_writable(JPATH_ADMINISTRATOR . '/language');

		if (!$canInstallSiteLanguage)
		{
			$this->app->enqueueMessage(sprintf('Cannot install language file at "%s"', JPATH_SITE . '/language'), 'error');
		}
		else
		{
			foreach (glob(JPATH_SITE . '/language/*', GLOB_ONLYDIR) as $dir)
			{
				if (!is_writable($dir))
				{
					$canInstallSiteLanguage = false;
					$this->app->enqueueMessage(sprintf('Cannot install language file at "%s"', $dir), 'error');
				}
			}
		}

		if (!$canInstallAdminLanguage)
		{
			$this->app->enqueueMessage(sprintf('Cannot install language file at "%s"', JPATH_ADMINISTRATOR . '/language'), 'error');
		}
		else
		{
			foreach (glob(JPATH_ADMINISTRATOR . '/language/*', GLOB_ONLYDIR) as $dir)
			{
				if (!is_writable($dir))
				{
					$canInstallAdminLanguage = false;
					$this->app->enqueueMessage(sprintf('Cannot install language file at "%s"', $dir), 'error');
				}
			}
		}

		// Checking directory permissions for dependency installation
		foreach ($this->dependencies as & $extension)
		{
			// Simply continue if extension is set to be removed
			if (isset($extension->remove) && (int) $extension->remove > 0)
			{
				continue;
			}

			// Check if dependency can be installed
			switch ($extension->type = strtolower($extension->type))
			{
				case 'component':
					$sitePath = JPATH_SITE . '/components';
					$adminPath = JPATH_ADMINISTRATOR . '/components';

					if (!is_dir($sitePath) || !is_writable($sitePath))
					{
						$canInstallExtension = false;
						$this->app->enqueueMessage(
							sprintf('Cannot install "%s" %s because "%s" is not writable', $extension->name, $extension->type, $sitePath),
							'error');
					}

					if (!is_dir($adminPath) || !is_writable($adminPath))
					{
						$canInstallExtension = false;
						$this->app->enqueueMessage(
							sprintf('Cannot install "%s" %s because "%s" is not writable', $extension->name, $extension->type, $adminPath),
							'error');
					}
					break;

				case 'plugin':
					$path = JPATH_ROOT . '/plugins/' . $extension->folder;

					if (( is_dir($path) && !is_writable($path) ) || ( !is_dir($path) && !is_writable(dirname($path)) ))
					{
						$canInstallExtension = false;
						$this->app->enqueueMessage(
							sprintf('Cannot install "%s" %s because "%s" is not writable', $extension->name, $extension->type, $path),
							'error');
					}
					break;

				case 'module':
				case 'template':
					$path = ( $extension->client == 'site' ? JPATH_SITE : JPATH_ADMINISTRATOR ) . "/{$extension->type}s";

					if (!is_dir($path) || !is_writable($path))
					{
						$canInstallExtension = false;
						$this->app->enqueueMessage(
							sprintf('Cannot install "%s" %s because "%s" is not writable', $extension->name, $extension->type, $path),
							'error');
					}
					break;
			}

			if ($canInstall && $canInstallExtension && $canInstallSiteLanguage && $canInstallAdminLanguage && isset($extension->source))
			{
				// Backup dependency parameters
				$db = JFactory::getDbo();
				$q = $db->getQuery(true);

				$q->select('params');
				$q->from('#__extensions');
				$q->where("element = '{$extension->name}'");
				$q->where("type = '{$extension->type}'");
				$extension->type != 'plugin' || $q->where("folder = '{$extension->folder}'");

				$db->setQuery($q);

				$extension->params = $db->loadResult();
			}
		}

		if ($canInstall && $canInstallExtension && $canInstallSiteLanguage && $canInstallAdminLanguage)
		{
			// Try to backup user edited language file
			return $this->backupLanguage();
		}
		else
		{
			return false;
		}
	}

	/**
	 * Implement postflight hook.
	 *
	 * @param   string  $type    Extension type.
	 * @param   object  $parent  JInstaller object.
	 *
	 * @return  void
	 */
	public function postflight($type, $parent)
	{
		// Get original installer
		isset($this->parentInstaller) || $this->parentInstaller = $parent->getParent();

		// Update language file
		$this->updateLanguage();

		// Process dependency installation
		foreach ($this->dependencies as $extension)
		{
			// Remove installed extension if requested
			if (isset($extension->remove) && (int) $extension->remove > 0)
			{
				$this->removeExtension($extension);
				continue;
			}

			// Install only dependency that has local installation package
			if (isset($extension->source))
			{
				// Install and update dependency status
				$this->installExtension($extension);
			}
			elseif (!isset($this->missingDependency))
			{
				$this->missingDependency = true;
			}
		}

		// Create dependency declaration constant
		if (!defined('JSN_' . strtoupper($this->name) . '_DEPENDENCY'))
		{
			// Get Joomla config
			$config = JFactory::getConfig();

			// Unset some unnecessary properties
			foreach ($this->dependencies as & $dependency)
			{
				unset($dependency->source);
				unset($dependency->upToDate);
			}

			$this->dependencies = json_encode($this->dependencies);

			// Store dependency declaration
			file_exists($defines = $this->path . '/defines.php') || file_exists($defines = $this->path . "/{$this->name}.defines.php") ||
			$defines = $this->path . "/{$this->name}.php";

			if ($config->get('ftp_enable') || is_writable($defines))
			{
				$buffer = preg_replace('/(defined\s*\(\s*._JEXEC.\s*\)[^\n]+\n)/',
					'\1' . "\ndefine('JSN_" . strtoupper($this->name) . "_DEPENDENCY', '" . $this->dependencies . "');\n",
					JFile::read($defines));

				JFile::write($defines, $buffer);
			}
		}

		// Clean latest product version cache file
		$cache = JFactory::getConfig()->get('tmp_path') . '/JoomlaShineUpdates.json';

		if (file_exists($cache))
		{
			jimport('joomla.filesystem.file');
			JFile::delete($cache);
		}

		// Register check update link for Joomla
		$JVersion = new JVersion();

		// Check if current joomla version is 2.5.x
		$isJoomla25 = ( version_compare($JVersion->getShortVersion(), '2.5', '>=') &&
			version_compare($JVersion->getShortVersion(), '3.0', '<') );

		if (version_compare($JVersion->RELEASE, '3.1', '>='))
		{
			// Get id for the extension just installed
			$ext = JTable::getInstance('Extension');

			$ext->load(array(
				'name' => $this->name,
				'element' => basename($this->path),
				'type' => 'component'
			));

			if ($ext->extension_id)
			{
				// Get current check update data
				$db = JFactory::getDbo();
				$q = $db->getQuery(true);

				$q->select('update_site_id');
				$q->from('#__update_sites');
				$q->where('location LIKE ' . $q->quote('%index.php?option=' . $ext->element . '&task=checkUpdate%'));

				$db->setQuery($q);

				if ($uid = $db->loadResult())
				{
					// Clean-up current check update data
					$q = $db->getQuery(true);

					$q->delete('#__update_sites');
					$q->where('update_site_id = ' . (int) $uid);

					$db->setQuery($q);

					if ($isJoomla25)
					{
						$db->query();
					}
					else
					{
						$db->execute();
					}

					$q = $db->getQuery(true);

					$q->delete('#__update_sites_extensions');
					$q->where('update_site_id = ' . (int) $uid);

					$db->setQuery($q);

					if ($isJoomla25)
					{
						$db->query();
					}
					else
					{
						$db->execute();
					}
				}

				// Register check update data
				$ln = 'http://www.joomlashine.com/versioning/extensions/' . $ext->element . '.xml';
				$q = $db->getQuery(true);

				$q->insert('#__update_sites');
				$q->columns('`name`, `type`, `location`, `enabled`');
				$q->values($q->quote($this->name) . ', ' . $q->quote('collection') . ', ' . $q->quote($ln) . ', 1');

				$db->setQuery($q);

				if ($isJoomla25)
				{
					$db->query();
				}
				else
				{
					$db->execute();
				}

				if ($uid = $db->insertid())
				{
					$q = $db->getQuery(true);

					$q->insert('#__update_sites_extensions');
					$q->columns('`update_site_id`, `extension_id`');
					$q->values((int) $uid . ', ' . (int) $ext->extension_id);

					$db->setQuery($q);

					if ($isJoomla25)
					{
						$db->query();
					}
					else
					{
						$db->execute();
					}
				}
			}
		}

		// Get a database connector object.
		$db = JFactory::getDbo();

		// Make the execution order of the system plugin of JSN PowerAdmin as early as possible.
		$qr = $db->getQuery(true)
			->update('#__extensions')
			->set('ordering = -99')
			->where("element = 'poweradmin2'")
			->where("type = 'plugin'")
			->where("folder = 'system'");

		$db->setQuery($qr)->execute();

		// If admin bar is enabled, unpublish Joomla built-in Admin Menu module.
		$qr = $db->getQuery(true)
			->select('value')
			->from('#__jsn_poweradmin2_config')
			->where('name = "enable_adminbar"');

		$adminBarEnabled = $db->setQuery($qr)->loadResult();
		$adminBarEnabled = empty($adminBarEnabled) ? 1 : (int) $adminBarEnabled;

		$qr = $db->getQuery(true)
			->update('#__modules')
			->set('published = ' . ( $adminBarEnabled ? 0 : 1 ))
			->where('client_id = 1')
			->where("module = 'mod_menu'")
			->where("position = 'menu'");

		$db->setQuery($qr)->execute();

		// Then, publish the admin bar module of JSN PowerAdmin.
		$qr = $db->getQuery(true)
			->update('#__modules')
			->set('published = ' . ( $adminBarEnabled ? 1 : 0 ))
			->where('client_id = 1')
			->where("module = 'mod_poweradminbar'")
			->where("position = 'menu'");

		$db->setQuery($qr)->execute();

		// Check if JSN PowerAdmin gen. 1 is installed?
		if (is_file(JPATH_ADMINISTRATOR . '/components/com_poweradmin/poweradmin.xml'))
		{
			// Make sure the configuration table of JSN PowerAdmin gen. 1 exists in database.
			$tables = $db->getTableList();

			foreach ($tables as $table)
			{
				if (strpos($table, 'jsn_poweradmin_config') !== false)
				{
					// Disable the admin bar of JSN PowerAdmin gen. 1.
					$db->setQuery("REPLACE INTO {$table} (`name`, `value`) VALUES ('enable_adminbar', '0')")
						->execute();

					// Disable the position chooser feature of JSN PowerAdmin gen. 1.
					$db->setQuery("REPLACE INTO {$table} (`name`, `value`) VALUES ('position_chooser_enhance', '0')")
						->execute();
				}
			}
		}

		// Override some files if needed.
		$fails = array();

		// Check if the Yjsg Framework plugin is installed?
		if (JPluginHelper::isEnabled('system', 'yjsg'))
		{
			// Override some files of the Yjsg Framework plugin.
			foreach (array(
				/*'plugins/system/yjsg/includes/yjsgcore/classes/extend/38/component/view.php',
				'plugins/system/yjsg/includes/yjsgcore/classes/extend/38/layout/file.php',*/
				'plugins/system/yjsg/includes/yjsgcore/classes/extend/38/module/helper.php'
			) as $file)
			{
				// Always backup the existing file first.
				JFile::copy(JPATH_ROOT . "/{$file}", JPATH_ROOT . "/{$file}.backed-up-by-poweradmin2-at-" . date('YmdHis'));

				// Then, copy our file over.
				if (!JFile::copy(JPATH_ROOT . "/plugins/system/poweradmin2/overrides/{$file}", JPATH_ROOT . "/{$file}"))
				{
					$fails[] = $file;
				}
			}
		}

		// Check if the JSN Template Framework plugin is installed?
		if (JPluginHelper::isEnabled('system', 'jsntplframework'))
		{
			// Override some files of the JSN Template Framework plugin.
			foreach (array(
				'plugins/system/jsntplframework/includes/core/j3x/jsntplmodulehelper.php'
			) as $file)
			{
				// Always backup the existing file first.
				JFile::copy(JPATH_ROOT . "/{$file}", JPATH_ROOT . "/{$file}.backed-up-by-poweradmin2-at-" . date('YmdHis'));

				// Then, copy our file over.
				if (!JFile::copy(JPATH_ROOT . "/plugins/system/poweradmin2/overrides/{$file}", JPATH_ROOT . "/{$file}"))
				{
					$fails[] = $file;
				}
			}
		}

		// Check if the JSN Sun Framework plugin is installed?
		if (JPluginHelper::isEnabled('system', 'sunfw'))
		{
			// Override some files of the JSN Template Framework plugin.
			foreach (array(
				'plugins/system/sunfw/includes/overwrite/j3x/libraries/cms/module/helper.php'
			) as $file)
			{
				// Always backup the existing file first.
				JFile::copy(JPATH_ROOT . "/{$file}", JPATH_ROOT . "/{$file}.backed-up-by-poweradmin2-at-" . date('YmdHis'));

				// Then, copy our file over.
				if (!JFile::copy(JPATH_ROOT . "/plugins/system/poweradmin2/overrides/{$file}", JPATH_ROOT . "/{$file}"))
				{
					$fails[] = $file;
				}
			}
		}

		if (count($fails))
		{
			throw new Exception(
				'Failed to override some existing files.
				This might cause JSN PowerAdmin 2 not to work properly.
				Please make sure the following files and/or their parent folders are writable:
				' . implode(', ', $fails));
		}

		// Check if redirect should be disabled
		if ($this->app->input->getBool('tool_redirect', true))
		{
			// Do we have any missing dependency
			if ($this->missingDependency)
			{
				if (strpos($_SERVER['HTTP_REFERER'], '/administrator/index.php?option=com_installer') !== false)
				{
					// Set redirect to finalize dependency installation
					$this->parentInstaller->setRedirectURL('index.php?option=com_' . $this->name . '&view=installer');
				}
				else
				{
					// Let Ajax client redirect
					echo '
<script type="text/javascript">
	if (window.parent)
		window.parent.location.href ="' . JUri::root() .
						'administrator/index.php?option=com_' . $this->name . '&view=installer";
	else
		location.href ="' . JUri::root() . 'administrator/index.php?option=com_' . $this->name . '&view=installer";
</script>';
					exit();
				}
			}
		}
	}

	/**
	 * Implement uninstall hook.
	 *
	 * @param   object  $parent  JInstaller object.
	 *
	 * @return  void
	 */
	public function uninstall($parent)
	{
		// Initialize variables
		isset($this->parentInstaller) || $this->parentInstaller = $parent->getParent();

		$this->app = JFactory::getApplication();
		$this->manifest = $this->parentInstaller->getManifest();

		// Get installed extension directory and name
		$this->path = $this->parentInstaller->getPath('extension_administrator');
		$this->name = substr(basename($this->path), 4);

		// Parse dependency
		$this->parseDependency($this->parentInstaller);

		// Remove all dependency
		foreach ($this->dependencies as $extension)
		{
			if ($extension->type == 'plugin' && $extension->folder == 'system' && $extension->name == 'jsnextfw')
			{
				continue;
			}

			$this->removeExtension($extension);
		}
	}

	/**
	 * Retrieve dependency from manifest file.
	 *
	 * @param   object  $installer  JInstaller object.
	 *
	 * @return  object  Return itself for method chaining.
	 */
	protected function parseDependency($installer)
	{
		// Continue only if dependency not checked before
		if (!isset($this->dependencies) || !is_array($this->dependencies))
		{
			// Preset dependency list
			$this->dependencies = array();

			if (isset($this->manifest->subinstall) && $this->manifest->subinstall instanceof SimpleXMLElement)
			{
				// Loop on each node to retrieve dependency information
				foreach ($this->manifest->subinstall->children() as $node)
				{
					// Verify tag name
					if (strcasecmp($node->getName(), 'extension') != 0)
					{
						continue;
					}

					// Re-create serializable dependency object
					$extension = (array) $node;
					$extension = (object) $extension['@attributes'];

					$extension->title = trim(
						(string) $node != '' ? (string) $node : ( $node['title'] ? (string) $node['title'] : (string) $node['name'] ));

					// Validate dependency
					if (!isset($extension->name) || !isset($extension->type) || !in_array($extension->type,
							array(
								'template',
								'plugin',
								'module',
								'component'
							)) || ( $extension->type == 'plugin' && !isset($extension->folder) ))
					{
						continue;
					}

					// Check if dependency has local installation package
					if (isset($extension->dir) && is_dir($source = $installer->getPath('source') . '/' . $extension->dir))
					{
						$extension->source = $source;
					}

					$this->dependencies[] = $extension;
				}
			}
		}

		return $this;
	}

	/**
	 * Install a dependency.
	 *
	 * @param   object  $extension  Object containing extension details.
	 *
	 * @return  void
	 */
	public function installExtension($extension)
	{
		// Get application object
		isset($this->app) || $this->app = JFactory::getApplication();

		// Get database object
		$db = JFactory::getDbo();
		$q = $db->getQuery(true);

		// Build query to get dependency installation status
		$q->select('manifest_cache, custom_data');
		$q->from('#__extensions');
		$q->where("element = '{$extension->name}'");
		$q->where("type = '{$extension->type}'");
		$extension->type != 'plugin' || $q->where("folder = '{$extension->folder}'");

		// Execute query
		$db->setQuery($q);

		if ($status = $db->loadObject())
		{
			// Initialize variables
			$JVersion = new JVersion();
			$manifest = json_decode($status->manifest_cache);

			// Get information about the dependency to be installed
			$xml = JPATH::clean($extension->source . '/' . $extension->name . '.xml');

			if (is_file($xml) && ( $xml = simplexml_load_file($xml) ))
			{
				if ($JVersion->RELEASE == (string) $xml['version'] && version_compare($manifest->version, (string) $xml->version, '<'))
				{
					// The dependency to be installed is newer than the existing one, mark for update
					$doInstall = true;
				}

				if ($JVersion->RELEASE != (string) $xml['version'] && version_compare($manifest->version, (string) $xml->version, '<='))
				{
					// The dependency to be installed might not newer than the existing one but Joomla version is difference, mark for update
					$doInstall = true;
				}
			}
		}
		elseif (isset($extension->source))
		{
			// The dependency to be installed not exist, mark for install
			$doInstall = true;
		}

		if (isset($doInstall) && $doInstall)
		{
			// Install dependency
			$installer = new JInstaller();

			if (!$installer->install($extension->source))
			{
				$this->app->enqueueMessage(sprintf('Error installing "%s" %s', $extension->name, $extension->type), 'error');
			}
			else
			{
				$this->app->enqueueMessage(sprintf('Install "%s" %s was successfull', $extension->name, $extension->type));

				// Update dependency status
				$this->updateExtension($extension);

				// Build query to get dependency installation status
				$q = $db->getQuery(true);

				$q->select('manifest_cache, custom_data');
				$q->from('#__extensions');
				$q->where("element = '{$extension->name}'");
				$q->where("type = '{$extension->type}'");
				$extension->type != 'plugin' || $q->where("folder = '{$extension->folder}'");

				$db->setQuery($q);

				// Load dependency installation status
				$status = $db->loadObject();
			}
		}

		// Update dependency tracking
		if (isset($status))
		{
			$ext = isset($this->name) ? $this->name : substr($this->app->input->getCmd('option'), 4);
			$dep = !empty($status->custom_data) ? (array) json_decode($status->custom_data) : array();

			// Backward compatible: move all dependency data from params to custom_data column
			if (is_array(
				$params = ( isset($extension->params) && $extension->params != '{}' ) ? (array) json_decode($extension->params) : null))
			{
				foreach (array(
					         'imageshow',
					         'poweradmin'
				         ) as $com)
				{
					if ($com != $ext && isset($params[$com]))
					{
						$dep[] = $com;
					}
				}
			}

			// Update dependency list
			in_array($ext, $dep) || $dep[] = $ext;

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
			$params = is_array($params) ? array_merge($params, array_combine($status->custom_data, $status->custom_data)) : array_combine(
				$status->custom_data, $status->custom_data);

			$q->set("params = '" . json_encode($params) . "'");

			$q->where("element = '{$extension->name}'");
			$q->where("type = '{$extension->type}'");

			$extension->type != 'plugin' || $q->where("folder = '{$extension->folder}'");

			$db->setQuery($q);
			$db->execute();
		}
	}

	/**
	 * Update dependency status.
	 *
	 * @param   object  $extension  Extension to update.
	 *
	 * @return  object  Return itself for method chaining.
	 */
	protected function updateExtension($extension)
	{
		// Get object to working with extensions table
		$table = JTable::getInstance('Extension');

		// Load extension record
		$condition = array(
			'type' => $extension->type,
			'element' => $extension->name
		);

		if ($extension->type == 'plugin')
		{
			$condition['folder'] = $extension->folder;
		}

		$table->load($condition);

		// Update extension record
		$table->enabled = ( isset($extension->publish) && (int) $extension->publish > 0 ) ? 1 : 0;
		$table->protected = ( isset($extension->lock) && (int) $extension->lock > 0 ) ? 1 : 0;
		$table->client_id = ( isset($extension->client) && $extension->client == 'site' ) ? 0 : 1;

		// Store updated extension record
		$table->store();

		// Update module instance
		if ($extension->type == 'module')
		{
			// Get object to working with modules table
			$module = JTable::getInstance('module');

			// Load module instance
			$module->load(array(
				'module' => $extension->name
			));

			// Update module instance
			$module->title = $extension->title;
			$module->ordering = isset($extension->ordering) ? $extension->ordering : 0;
			$module->published = ( isset($extension->publish) && (int) $extension->publish > 0 ) ? 1 : 0;

			if ($hasPosition = ( isset($extension->position) && (string) $extension->position != '' ))
			{
				$module->position = (string) $extension->position;
			}

			// Store updated module instance
			$module->store();

			// Set module instance to show in all page
			if ($hasPosition && (int) $module->id > 0)
			{
				// Get database object
				$db = JFactory::getDbo();
				$q = $db->getQuery(true);

				try
				{
					// Remove all menu assignment records associated with this module instance
					$q->delete('#__modules_menu');
					$q->where("moduleid = {$module->id}");

					// Execute query
					$db->setQuery($q);
					$db->execute();

					// Build query to show this module instance in all page
					$q->insert('#__modules_menu');
					$q->columns('moduleid, menuid');
					$q->values("{$module->id}, 0");

					// Execute query
					$db->setQuery($q);
					$db->execute();
				}
				catch (Exception $e)
				{
					throw $e;
				}
			}
		}

		return $this;
	}

	/**
	 * Disable a dependency.
	 *
	 * @param   object  $extension  Extension to update.
	 *
	 * @return  object  Return itself for method chaining.
	 */
	protected function disableExtension($extension)
	{
		// Get database object
		$db = JFactory::getDbo();
		$q = $db->getQuery(true);

		// Build query
		$q->update('#__extensions');
		$q->set('enabled = 0');
		$q->where("element = '{$extension->name}'");
		$q->where("type = '{$extension->type}'");

		$extension->type != 'plugin' || $q->where("folder = '{$extension->folder}'");

		// Execute query
		$db->setQuery($q);
		$db->execute();

		return $this;
	}

	/**
	 * Unlock a dependency.
	 *
	 * @param   object  $extension  Extension to update.
	 *
	 * @return  object  Return itself for method chaining.
	 */
	protected function unlockExtension($extension)
	{
		// Get database object
		$db = JFactory::getDbo();
		$q = $db->getQuery(true);

		// Build query
		$q->update('#__extensions');
		$q->set('protected = 0');
		$q->where("element = '{$extension->name}'");
		$q->where("type = '{$extension->type}'");

		$extension->type != 'plugin' || $q->where("folder = '{$extension->folder}'");

		// Execute query
		$db->setQuery($q);
		$db->execute();

		return $this;
	}

	/**
	 * Remove a dependency.
	 *
	 * @param   object  $extension  Extension to update.
	 *
	 * @return  object  Return itself for method chaining.
	 */
	protected function removeExtension($extension)
	{
		// Get application object
		isset($this->app) || $this->app = JFactory::getApplication();

		// Preset dependency status
		$extension->removable = true;

		// Get database object
		$db = JFactory::getDbo();
		$q = $db->getQuery(true);

		// Build query to get dependency installation status
		$q->select('extension_id, manifest_cache, custom_data, params');
		$q->from('#__extensions');
		$q->where("element = '{$extension->name}'");
		$q->where("type = '{$extension->type}'");

		$extension->type != 'plugin' || $q->where("folder = '{$extension->folder}'");

		// Execute query
		$db->setQuery($q);

		if ($status = $db->loadObject())
		{
			// Initialize variables
			$id = $status->extension_id;
			$ext = isset($this->name) ? $this->name : substr($this->app->input->getCmd('option'), 4);
			$deps = !empty($status->custom_data) ? (array) json_decode($status->custom_data) : array();

			// Update dependency tracking
			$status->custom_data = array();

			foreach ($deps as $dep)
			{
				// Backward compatible: ensure that product is not removed
				// if ($dep != $ext)
				if ($dep != $ext && is_dir(JPATH_BASE . '/components/com_' . $dep))
				{
					$status->custom_data[] = $dep;
				}
			}

			if (count($status->custom_data))
			{
				// Build query to update dependency data
				$q = $db->getQuery(true);

				$q->update('#__extensions');
				$q->set("custom_data = '" . json_encode($status->custom_data) . "'");

				// Backward compatible: keep data in this column for older product to recognize
				$manifestCache = json_decode($status->manifest_cache);
				$manifestCache->dependency = $status->custom_data;

				$q->set("manifest_cache = '" . json_encode($manifestCache) . "'");

				// Backward compatible: keep data in this column also for another old product to recognize
				$params = is_array(
					$params = ( isset($status->params) && $status->params != '{}' ) ? (array) json_decode($status->params) : null) ? array_merge(
					$params, array_combine($status->custom_data, $status->custom_data)) : array_combine($status->custom_data,
					$status->custom_data);

				$q->set("params = '" . json_encode($params) . "'");

				$q->where("element = '{$extension->name}'");
				$q->where("type = '{$extension->type}'");
				$extension->type != 'plugin' || $q->where("folder = '{$extension->folder}'");

				$db->setQuery($q);
				$db->execute();

				// Indicate that extension is not removable
				$extension->removable = false;
			}
		}
		else
		{
			// Extension was already removed
			$extension->removable = false;
		}

		if ($extension->removable)
		{
			// Disable and unlock dependency
			$this->disableExtension($extension);
			$this->unlockExtension($extension);

			// Remove dependency
			$installer = new JInstaller();

			if ($installer->uninstall($extension->type, $id))
			{
				$this->app->enqueueMessage(sprintf('"%s" %s has been uninstalled', $extension->name, $extension->type));
			}
			else
			{
				$this->app->enqueueMessage(sprintf('Cannot uninstall "%s" %s', $extension->name, $extension->type));
			}
		}

		return $this;
	}

	/**
	 * Attemp to backup user edited language file before re-install/update.
	 *
	 * @return  boolean  FALSE if backup fail for any reason, TRUE otherwise.
	 */
	protected function backupLanguage()
	{
		// Load language utility class
		$langUtil = ( class_exists('JSNUtilsLanguage') && method_exists('JSNUtilsLanguage', 'edited') ) ? 'JSNUtilsLanguage' : 'JSNUtilsLanguageTmp';

		if ($langUtil != 'JSNUtilsLanguage')
		{
			file_exists($this->parentInstaller->getPath('source') . '/admin/libraries/joomlashine/utils/language.php') ? require_once $this->parentInstaller->getPath(
					'source') . '/admin/libraries/joomlashine/utils/language.php' : ( $langUtil = null );
		}

		if ($langUtil)
		{
			// Get list of component's supported language
			$admin = is_dir($this->path . '/language/admin') ? JFolder::folders($this->path . '/language/admin') : null;
			$site = is_dir($this->path . '/language/site') ? JFolder::folders($this->path . '/language/site') : null;

			if ($admin && $site)
			{
				$langs = array_merge($admin, $site);
			}
			elseif ($admin || $site)
			{
				$langs = $admin ? $admin : $site;
			}

			$langs = isset($langs) ? array_unique($langs) : array();

			// Loop thru supported language list and get all language files installed in Joomla's language folder
			foreach ($langs as $lang)
			{
				// Check if language is installed in Joomla's language folder and manually edited by user
				$isEdited = array(
					'admin' => call_user_func(array(
						$langUtil,
						'edited'
					), $lang, false, "com_{$this->name}"),
					'site' => call_user_func(array(
						$langUtil,
						'edited'
					), $lang, true, "com_{$this->name}")
				);

				foreach ($isEdited as $client => $edited)
				{
					if ($edited)
					{
						// Get list of language file
						$files = glob($this->path . "/language/{$client}/{$lang}/{$lang}.*.ini");

						// Backup all language file installed in Joomla's language folder
						foreach ($files as $file)
						{
							// Generate path to user edited language file in Joomla's language folder
							$f = ( $client == 'admin' ? JPATH_ADMINISTRATOR : JPATH_SITE ) . "/language/{$lang}/" . basename($file);

							// Backup user edited language file to temporary directory
							if (is_readable($f))
							{
								if (!JFile::copy($f, "{$f}.jsn-installer-backup"))
								{
									$backupFails[] = str_replace(JPATH_ROOT, 'JOOMLA_ROOT', $f);
								}
							}
						}
					}
				}
			}

			if (isset($backupFails))
			{
				$this->app->enqueueMessage(
					'Cannot backup following user edited language file(s): <ul><li>' . implode('</li><li>', $backupFails) . '</li></ul>',
					'warning');

				return false;
			}
		}

		return true;
	}

	/**
	 * Update all language file installed in Joomla's language folder.
	 *
	 * @return  boolean  FALSE if update fail for any reason, TRUE otherwise.
	 */
	protected function updateLanguage()
	{
		// Load language utility class
		$langUtil = ( class_exists('JSNUtilsLanguage') && method_exists('JSNUtilsLanguage', 'edited') ) ? 'JSNUtilsLanguage' : 'JSNUtilsLanguageTmp';

		if ($langUtil != 'JSNUtilsLanguage')
		{
			file_exists($this->parentInstaller->getPath('source') . '/admin/libraries/joomlashine/utils/language.php') ? require_once $this->parentInstaller->getPath(
					'source') . '/admin/libraries/joomlashine/utils/language.php' : ( $langUtil = null );
		}

		if ($langUtil)
		{
			// Get list of component's supported language
			$admin = is_dir($this->path . '/language/admin') ? JFolder::folders($this->path . '/language/admin') : null;
			$site = is_dir($this->path . '/language/site') ? JFolder::folders($this->path . '/language/site') : null;

			if ($admin && $site)
			{
				$langs = array_merge($admin, $site);
			}
			elseif ($admin || $site)
			{
				$langs = $admin ? $admin : $site;
			}
			$langs = isset($langs) ? array_unique($langs) : array();

			// Loop thru supported language list and get all language files installed in Joomla's language folder
			foreach ($langs as $lang)
			{
				// Check if language is installed in Joomla's language folder
				$isInstalled = array(
					'admin' => call_user_func(array(
						$langUtil,
						'installed'
					), $lang, false, "com_{$this->name}"),
					'site' => call_user_func(array(
						$langUtil,
						'installed'
					), $lang, true, "com_{$this->name}")
				);

				foreach ($isInstalled as $client => $installed)
				{
					if ($installed)
					{
						// Install new language file
						call_user_func(array(
							$langUtil,
							'install'
						), (array) $lang, $client != 'admin' ? true : false, true, "com_{$this->name}");

						// Get list of language file
						$files = glob($this->path . "/language/{$client}/{$lang}/{$lang}.*.ini");

						// Check if any installed language file has backup
						foreach ($files as $file)
						{
							// Clean all possible new-line character left by 'glob' function
							$file = preg_replace('/(\r|\n)/', '', $file);

							// Generate path to installed language file in Joomla's language folder
							$f = ( $client == 'admin' ? JPATH_ADMINISTRATOR : JPATH_SITE ) . "/language/{$lang}/" . basename($file);

							// If language file has backup, merge all user's translation into new language file
							if (is_readable("{$f}.jsn-installer-backup"))
							{
								// Read content of new language file to array
								$new = file($file);

								// Read content of backup file to associative array
								foreach (file("{$f}.jsn-installer-backup") as $line)
								{
									if (!empty($line) && !preg_match('/^\s*;/', $line) &&
										preg_match('/^\s*([^=]+)="([^\r\n]+)"\s*$/', $line, $match))
									{
										$bak[$match[1]] = $match[2];
									}
								}

								// Merge user's translation into new language file
								foreach ($new as & $line)
								{
									if (!empty($line) && !preg_match('/^\s*;/', $line) &&
										preg_match('/^\s*([^=]+)="([^\r\n]+)"\s*$/', $line, $match))
									{
										if (isset($bak[$match[1]]))
										{
											$line = str_replace($match[2], $bak[$match[1]], $line);

											// Mark as merged
											isset($merged) || $merged = true;
										}
									}
								}

								// Update installed language file with merged content
								if (isset($merged) && $merged)
								{
									$new = implode($new);

									if (!JFile::write($f, $new))
									{
										$mergeFails[] = str_replace(JPATH_ROOT, 'JOOMLA_ROOT', $f);
									}
								}

								// Remove backup file
								JFile::delete("{$f}.jsn-installer-backup");
							}
						}
					}
				}
			}

			if (isset($mergeFails))
			{
				$this->app->enqueueMessage(
					'Cannot merge user edited translation back to following language file(s): <ul><li>' . implode('</li><li>', $mergeFails) .
					'</li></ul>', 'warning');

				return false;
			}
		}

		return true;
	}
}
