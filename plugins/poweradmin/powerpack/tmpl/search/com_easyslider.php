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
?>
		<?php if (empty($items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
		<?php else : ?>
		<table class="table table-striped" id="sliderList">
			<thead>
			<tr>
				<th class="nowrap">
					<?php echo JText::_('JGLOBAL_TITLE'); ?>
				</th>
                <th width="1%" class="nowrap hidden-phone">
					<?php echo JText::_('JPUBLISHED'); ?>
                </th>
				<th width="1%" class="nowrap hidden-phone">
					<?php echo JText::_('JGRID_HEADING_ID'); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="3">
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php foreach ($items as $i => $item) : ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td class="has-context">
                    <div class="pull-left break-word">
                        <a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_easyslider&view=slider&layout=edit&slider_id=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
                            <?php echo $item->title; ?>
                        </a>
                    </div>
                </td>
                <td class="hidden-phone">
                    <?php echo preg_replace('#<a [^>]+>(.+)</a>#', '\\1', JHtml::_('jgrid.published', $item->published, $i)); ?>
                </td>
                <td class="hidden-phone">
                    <?php echo (int) $item->id; ?>
                </td>
            </tr>
			<?php endforeach; ?>
			</tbody>
		</table>
        <?php endif; ?>

        <?php echo (new \JPagination($total, $start, $limit))->getListFooter(); ?>
