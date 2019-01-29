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

// Get Joomla input object.
$input = JFactory::getApplication()->input;

// Get search coverage.
if (!empty($coverage = $input->getCmd('coverage'))) :

// Get keyword to search for results.
if (empty($search = $input->getString('search', $input->getString('keyword'))))
{
	$search = $input->getArray(array('filter' => 'array'));
	$search = $input->getArray(array('search' => 'string'), $search['filter']);
	$search = $search['search'];
}

// Prepare form action.
$link = "index.php?option=com_poweradmin2&view=search&layout=results&coverage={$coverage}";

if ($limit = $input->getInt('limit'))
{
    $link .= "&limit={$limit}";
}

// Load necessary assets.
JHtml::_('behavior.core');
?>
<form action="<?php echo JRoute::_($link); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
	<?php else : ?>
	<div id="j-main-container">
	<?php endif; ?>
		<?php
		// Trigger an event to display search results.
		JFactory::getApplication()->triggerEvent('onPowerAdminShowSearchResultsForCoverage', array($coverage, $search));
		?>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
    </div>
</form>
<?php
endif;
