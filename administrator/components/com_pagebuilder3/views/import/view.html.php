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
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * About view
 *
 * @package  JSN_PageBuilder3
 * @since    1.0.0
 */
class JSNPageBuilder3ViewImport extends JSNBaseView {
	protected $niches;
	protected $status;
	protected $niche;
	protected $imported;
	protected $versionCheck = false;

	/**
	 * Display method
	 *
	 * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return    void
	 */
	function display( $tpl = null ) {
		// Get config parameters
		$config = JSNConfigHelper::get();

		// Set the toolbar
		JToolbarHelper::title( JText::_( 'JSN_PAGEBUILDER3_IMPORT_TITLE' ) );

		// Add toolbar menu
		JSNPageBuilder3Helper::addToolbarMenu();

		// Set the submenu
		JSNPageBuilder3Helper::addSubmenu( 'import' );

		// Get messages
		$msgs = '';

		if ( ! $config->get( 'disable_all_messages' ) )
		{
			$msgs = JSNUtilsMessage::getList( 'IMPORT' );
			$msgs = count( $msgs ) ? JSNUtilsMessage::showMessages( $msgs ) : '';
		}

		// Assign variables for rendering
		$this->assignRef( 'msgs', $msgs );

		if ( ! class_exists( 'JSNPageBuilder3ContentHelper' ) )
		{
			require_once JPATH_ROOT . '/administrator/components/com_pagebuilder3/helpers/content.php';
		}

		$this->hanldeTemplateInfo();
		if ( ! empty( $_POST['confirm-import'] && ! empty( $_POST['niche'] ) ) )
		{
			foreach ( $this->niches as $niche )
			{
				if ( $niche['id'] === $_POST['niche'] )
				{
					$this->niche = $niche;
				}
			}
			if ( isset( $this->niche ) )
			{
				$this->imported = $this->downloadAndInsert( $this->niche );
			}
		}


		// Add assets
		JSNPageBuilder3Helper::addAssets();


		// Display the template
		parent::display( $tpl );
	}


	public function hanldeTemplateInfo() {
		$this->status  = [ true, '' ];
		$db            = JFactory::getDbo();
		$queryTemplate = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home = 1";
		$db->setQuery( $queryTemplate );
		$defaultemplate = $db->loadResult();
		$tpl            = explode( '_', $defaultemplate );
		try
		{
			$templateData = json_decode( self::curlGet( "https://www.joomlashine.com/sunfwdata/sampledata/templates/tpl_{$tpl[1]}.json" ), true );
		}
		catch ( Exception $e )
		{
			$templateData = null;
		}

		if ( $templateData !== null && is_array( $templateData ) )
		{
			if ( isset( $templateData['individual'] ) )
			{
				$this->niches = $templateData['individual'];
			}
			elseif ( isset( $templateData['pro'] ) )
			{
				$this->niches = $templateData['pro'];
			}
			elseif ( isset( $templateData['developer'] ) )
			{
				$this->niches = $templateData['developer'];
			}
		}
	}

	private static function curlGet( $Url ) {
		$ch = curl_init( $Url );
		curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 1000 );
		$result = curl_exec( $ch );
		curl_close( $ch );

		return $result;
	}

	function downloadAndInsert( $niche ) {
		$result = false;
		$url    = $niche['download'];
		$http   = new JHttp();
		// Detect a writable folder to store demo assets.
		foreach ( array( 'media', 'cache', 'tmp' ) as $folder )
		{
			$folder = JPATH_ROOT . "/{$folder}";

			if (
				is_dir( $folder )
				&&
				is_writable( $folder )
				&&
				JFolder::create( "{$folder}/joomlashine/pagebuilder3" )
			)
			{
				$mediaFolder = "{$folder}/joomlashine/pagebuilder3";

				break;
			}
		}

		if ( isset( $mediaFolder ) )
		{

			try
			{
				$data = $http->get( $url );

				if ( is_file( $mediaFolder . '/data.zip' ) )
				{
					unlink( $mediaFolder . '/data.zip' );
				}
				// Write downloaded data to local file.
				if ( ! JFile::write( $mediaFolder . '/data.zip', $data->body ) )
				{
					throw new Exception( 'Failed to store sample data style!' );
				}
			}
			catch ( Exception $e )
			{
				throw $e;
			}

			if ( $this->extract( $mediaFolder . '/data.zip', $mediaFolder . '/data' ) )
			{
				try
				{
					if ($this->versionCheck !== true) {
						$db = JFactory::getDbo();
						$q = $db->getQuery(true);
						$q->delete($db->quoteName('#__content'))
						  ->where($db->quoteName('introtext') . ' LIKE \'%<!-- Start PageBuilder Data|%\'');
						$db->setQuery($q);
						$db->execute();
					}
					$result = JSNPageBuilder3ContentHelper::insertArticles( $mediaFolder . '/data' );
					if (is_dir($mediaFolder.'/data/images/joomlashine')) {
						self::recurse_copy($mediaFolder.'/data/images/joomlashine', JPATH_ROOT."/images/joomlashine");
					}
					self::delTree($mediaFolder);
				}
				catch ( Exception $e )
				{
					throw $e;
				}

			}
		}

		return $result;
	}

	public static function delTree($dir) {
		$files = array_diff(scandir($dir), array('.','..'));
		foreach ($files as $file) {
			(is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
		}
		return rmdir($dir);
	}

	private function extract( $filename, $dir ) {
		$zip = new ZipArchive;
		if ( $zip->open( $filename ) === true )
		{
			$zip->extractTo( $dir );
			$zip->close();

			return true;
		}
		else
		{
			return false;
		}
	}

	private static function recurse_copy($src,$dst) {
		$dir = opendir($src);
		@mkdir($dst);
		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) ) {
					self::recurse_copy($src . '/' . $file,$dst . '/' . $file);
				}
				else {
					copy($src . '/' . $file,$dst . '/' . $file);
				}
			}
		}
		closedir($dir);
	}
}
