/**
 * Created by minhpt on 5/5/17.
 */
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

define(
	[
		'jquery',
		'jquery.ui'
	],
	function ($) {
		var JSNPageBuilder3Import = function (params) {
			this.params = $.extend({}, params);
			this.initialize();
		}

		JSNPageBuilder3Import.prototype = {
			initialize: function () {
			},
		};

		return JSNPageBuilder3Import;
	}
);