<?php
/**
 * @version		$Id: latest.php 1492 2012-02-22 17:40:09Z joomlaworks@gmail.com $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2012 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

?>

<!-- Start K2 Latest Layout -->
<div id="k2Container" class="latestView<?php if($this->params->get('pageclass_sfx')) echo ' '.$this->params->get('pageclass_sfx'); ?>">

	<?php if($this->params->get('show_page_title')): ?>
	<!-- Page title -->
	<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>">
		<?php echo $this->escape($this->params->get('page_title')); ?>
	</div>
	<?php endif; ?>

	<?php foreach($this->blocks as $key=>$block): ?>
	<div class="latestItemsContainer">
	
		<!-- Start Items list -->
		<div class="latestItemList">
		<?php if($this->params->get('latestItemsDisplayEffect')=="first"): ?>
	
			<?php foreach ($block->items as $itemCounter=>$item): K2HelperUtilities::setDefaultImage($item, 'latest', $this->params); ?>
			<?php if($itemCounter==0): ?>
			<?php $this->item=$item; echo $this->loadTemplate('item'); ?>
			<?php else: ?>
		  <h2 class="latestItemTitleList">
		  	<?php if ($item->params->get('latestItemTitleLinked')): ?>
				<a href="<?php echo $item->link; ?>">
		  		<?php echo $item->title; ?>
		  	</a>
		  	<?php else: ?>
		  	<?php echo $item->title; ?>
		  	<?php endif; ?>
		  </h2>
			<?php endif; ?>
			<?php endforeach; ?>
	
		<?php else: ?>
	
			<?php foreach ($block->items as $item): K2HelperUtilities::setDefaultImage($item, 'latest', $this->params); ?>
			<?php $this->item=$item; echo $this->loadTemplate('item'); ?>
			<?php endforeach; ?>
	
		<?php endif; ?>
		</div>
		<!-- End Item list -->

	</div>

	<?php if(($key+1)%($this->params->get('latestItemsCols'))==0): ?>
	<div class="clr"></div>
	<?php endif; ?>

	<?php endforeach; ?>
	<div class="clr"></div>
</div>
<!-- End K2 Latest Layout -->
