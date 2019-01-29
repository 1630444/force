<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
// Function checks if the given version is greater or equal the installed version, then return true, false otherwise
function checkVMVersion($comparedVersion = '3.0.0')
{
	if (!class_exists( 'VmConfig' ))
	{
		$vmConfigPath = JPATH_ROOT . '/administrator/components/com_virtuemart/helpers/config.php';	
		if (file_exists($vmConfigPath))
		{
			require_once ($vmConfigPath);
		}
		else
		{
			return false;
		}	
	}	
	
			
	$installedVersion = VmConfig::getInstalledVersion();
	if (version_compare($installedVersion, $comparedVersion, '>='))
	{
		return true;
	}
	
	return false;
}