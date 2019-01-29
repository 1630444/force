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


$list = $this->convert->getOldContentListID();

foreach ($list as $k => $v)
{
	foreach ($v as $id)
	{
		echo '<textarea data-pageid="' . $id . '" data-pagetype="' . $k . '" id="pb_' . $k . '_' . $id . '" class="pb-convert hidden"></textarea>';
	}
}


?>
<div id="convert-view">
    <div class="btn btn-group" style="text-align: center; vertical-align: middle; width: 100%">
        <button id="convert-confirm" class="btn btn-info" style="margin: 10px 10px 10px 10px"> Start Convert</button>
        <button id="convert-backup" class="btn btn-warning hidden" style="margin: 10px 10px 10px 10px"> Revert Back Up
        </button>
    </div>
    <div id="convert-progress" class="hidden" style="text-align: center; vertical-align: middle; width: 100%">
        <label id="convert-view-label"> </label>
        <div class="progress progress-striped active">
            <div id="convert-view-progress-bar" class="bar">%</div>
        </div>
    </div>
</div>