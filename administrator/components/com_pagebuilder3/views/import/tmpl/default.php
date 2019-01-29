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

// Display messages
//if ( JFactory::getApplication()->input->getInt( 'ajax' ) != 1 )
//{
//	echo $this->msgs;
//}

?>

<div class="jsn-product-about jsn-pane jsn-bgpattern pattern-sidebar"
     style="width: 50%; text-align: center; vertical-align: middle; margin: 30px auto; padding: 30px">
    <h2 class="jsn-section-header"> Import PageBuilder 3 Sample Data. </h2>
	<?php

	if ( ! empty( $this->imported ) )
	{
		if ( $this->imported > 0 )
		{
			echo '<h4>' . $this->imported . ' Sample article for Page Builder 3 with niche ' . $this->niche['name'] . ' is successfully imported! Please check the Article with latest ID!</h4>';
		}
		else
		{
			echo '<h4>Failed to import PB3 sample data.</h4>';
		}

	}
    elseif ( isset( $this->niches ) && is_array( $this->niches ) && count( $this->niches ) > 0 )
	{
	    if (!$this->versionCheck) {
	        echo '<label>Importing PB3 sample data mean that you will be lost the old PB3 Articles. Are you sure?</label>';
        }
	    ?>
        <form method="post" action="">
            <div class="checkbox" style="margin: auto; width: 10%; padding: 10px 10px">
                <input type="checkbox" name="confirm-import" value="confirm-import"><label>Confirm</label>
            </div>
            <div class="form-group">
                <label for="sel1">Select Niche:</label>
                <select class="form-control" id="sel1" name="niche">
					<?php foreach ( $this->niches as $niche ): ?>
                        <option value="<?= $niche['id'] ?>"><?= $niche['name'] ?></option>
					<?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-success"> Submit</button>
            </div>
        </form>
	<?php }
	else
	{
		echo '<h4>Please check your default template style you are using!</h4>';

	} ?>
</div>
