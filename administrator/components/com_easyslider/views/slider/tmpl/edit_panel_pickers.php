<?php
/**
 * @version    $Id$
 * @package    JSN_EasySlider
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<div class="panel panel-default es-panel" id="font-awesome-picker">
	<div class="picker-wrapper">
		<div class="picker-groups">
			<div class="picker-group">
				<h3 class="picker-group-name" data-bind="text:name"><?php echo JText::_('JSN_EASYSLIDER_SLIDER_GROUP_NAME');?></h3>
				<div class="picker-icons">
					<li class="picker-icon" data-bind="attr:{data-name:name}">
						<span data-bind="attr:{ class:class }" class="fa fa-cog"></span>
					</li>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="panel panel-default es-panel" id="fonts-picker">
	<div class="panel-body">
		<ul class="font-groups">
			<li class="font-group">
				<h4 class="font-group-name" data-bind="text:name">San Serif</h4>
				<ul class="font-items">
					<li class="font-item" data-bind="attr:{data-name:name}">
						<img>
					</li>
				</ul>
			</li>
		</ul>
	</div>
</div>

<div class="panel panel-default es-panel" id="color-picker">
	<div class="panel-body">
		<input type="text" class="input-color-value" />
	</div>
</div>

<div class="panel panel-default es-panel" id="view-mode-selector">
	<div class="panel-body">
		<div class="btn-group btn-group-vertical btn-group-lg">
			<a class="btn btn-default switch-to-mode" data-mode="desktop">
				<span class="fa fa-television"></span>
			</a>
			<a class="btn btn-default switch-to-mode" data-mode="laptop">
				<span class="fa fa-laptop"></span>
			</a>
			<a class="btn btn-default switch-to-mode" data-mode="tablet">
				<span class="fa fa-tablet"></span>
			</a>
			<a class="btn btn-default switch-to-mode" data-mode="mobile">
				<span class="fa fa-mobile"></span>
			</a>
		</div>
	</div>
</div>