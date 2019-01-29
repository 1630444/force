<?php // no direct access
defined ('_JEXEC') or die('Restricted access');
// add javascript for price and cart, need even for quantity buttons, so we need it almost anywhere
vmJsApi::jPrice();


$col = 1;
$pwidth = ' width' . floor (100 / $products_per_row);
if ($products_per_row > 1) {
	$float = "floatleft";
} else {
	$float = "center";
}
?>
<div id="feauture-product" class="featured-view vmgroup<?php echo $params->get ('moduleclass_sfx') ?> vm-group">

	<?php if ($headerText) { ?>
	<div class="vmheader"><a href="<?php echo $footerText ?>"><?php echo $headerText ?></a></div>
	<?php
}
	if ($display_style == "div") {
		
		// Calculating Products Per Row
		$cellwidth = ' width'.floor ( 100 / $products_per_row );

		$BrowseTotalProducts = count($products);

		$col = 1;
		$nb = 1;
		$row = 1;

		foreach ( $products as $product ) {

			// Show the horizontal seperator
			if ($col == 1 && $nb > $products_per_row) { ?>
			<?php }

			// this is an indicator wether a row needs to be opened or not
			if ($col == 1) { ?>
		<div class="row">
			<?php }
	    // Show Products ?>
		<div class="product vm-col<?php echo ' vm-col-' . $products_per_row ?>">
			<div class="spacer">
				<div class="vm-product-media-container">
					<div class="product-thumb">
						<?php
						echo $product->images[0]->displayMediaThumb('class="img-responsive"', false);
						?>
					</div>
					<div class="mark">
						<div class="mark-inner">
							<div class="vm-details-button">
								<?php // Product Details Button
								$link = empty($product->link)? $product->canonical:$product->link;
								echo JHtml::link($link,vmText::_ ( 'COM_VIRTUEMART_PRODUCT_DETAILS' ), array ('title' => $product->product_name, 'class' => 'product-details' ) );
								//echo JHtml::link ( JRoute::_ ( 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id , FALSE), vmText::_ ( 'COM_VIRTUEMART_PRODUCT_DETAILS' ), array ('title' => $product->product_name, 'class' => 'product-details' ) );
								?>
							</div>
							<?php //echo $rowsHeight[$row]['customs'] ?>
							<div class="vm3pr-<?php echo $rowsHeight[$row]['customfields'] ?>"> <?php
							echo shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$product,'rowHeights'=>$rowsHeight[$row])); ?>
							</div>
						</div>	
					</div>
				</div>
				<div class="product-info">
					<div class="align-left">
						<h2 class="product-name"><?php echo JHtml::link ($product->link, $product->product_name); ?></h2>
					</div>
					<div class="align-right">
						<div class="vm3pr-<?php echo $rowsHeight[$row]['price'] ?>"> 
							<?php
							if ($show_price and  isset($product->prices)) {
								echo '<div class="product-price">'.$currency->createPriceDiv ('salesPrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
								if ($product->prices['salesPriceWithDiscount'] > 0) {
									echo $currency->createPriceDiv ('salesPriceWithDiscount', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
								}
								echo '</div>';
							}
							?>
						</div>
					</div>
					<div class="clearbreak"></div>
				</div>
			</div>
		</div>

		<?php
	    $nb ++;

	      // Do we need to close the current row now?
	      if ($col == $products_per_row || $nb>$BrowseTotalProducts) { ?>
	  </div>
	      <?php
	      	$col = 1;
			$row++;
	    } else {
	      $col ++;
	    }
	  }?>
	    <div class="clear"></div>

		<?php
	} else {
		$last = count ($products) - 1;
		?>

		<ul class="vmproduct<?php echo $params->get ('moduleclass_sfx'); ?> productdetails vm-product grid-layout3 clearafter">
			<?php foreach ($products as $product) : 
				$addclass = '';
				if(isset($product->customfieldsSorted['normal'])){
					foreach ($product->customfieldsSorted['normal'] as $prdcs){
						if($prdcs->custom_title == "product-type"){
							$addclass = ' '.strtolower(str_replace(" ","-",trim($prdcs->customfield_value)));
						}
					}
				}			
			?>	

			<li class="<?php echo $float ?> grid-col">
				
				<div class="vm-inner-product">
					<?php
					echo '<div class="product-img">';
					?>
					<?php if($addclass){ ?>
						<span class="label <?php echo $addclass;?>"><?php echo $addclass;?></span>
					<?php }?>
					<?php
					if (!empty($product->images[0])) {
						$image = $product->images[0]->displayMediaThumb ('class="featuredProductImage" border="0"', FALSE);
					} else {
						$image = '';
					}				
						echo JHTML::_ ('link', JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id), $image, array('title' => $product->product_name));
					echo '</div>';
					echo '<div class="clear"></div>';
					$url = JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' .
						$product->virtuemart_category_id); ?>
					<div class="product-info clearafter">
						<h3 class="product-name"><a href="<?php echo $url ?>"><?php echo $product->product_name ?></a> </h3>    <?php
						// $product->prices is not set when show_prices in config is unchecked
						if ($show_price and  isset($product->prices)) {
							echo '<div class="product-price">'.$currency->createPriceDiv ('salesPrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
							if ($product->prices['salesPriceWithDiscount'] > 0) {
								echo $currency->createPriceDiv ('salesPriceWithDiscount', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
							}
							echo '</div>';
						}
						if ($show_addtocart) {
							echo shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$product));
						}
						?>
					</div>
				</div>
			</li>
			<?php
			if ($col == $products_per_row && $products_per_row && $last) {
				echo '
		</ul><div class="clear"></div>
		<ul  class="vmproduct' . $params->get ('moduleclass_sfx') . ' productdetails grid-layout3 vm-product clearafter">';
				$col = 1;
			} else {
				$col++;
			}
			$last--;
		endforeach; ?>
		</ul>
		<div class="clear"></div>

		<?php
	}
	if ($footerText) : ?>
		<div class="vmfooter<?php echo $params->get ('moduleclass_sfx') ?>">
			<?php echo $footerText ?>
		</div>
		<?php endif; ?>
</div>