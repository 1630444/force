<?php // no direct access
defined ('_JEXEC') or die('Restricted access');
// add javascript for price and cart, need even for quantity buttons, so we need it almost anywhere
vmJsApi::jPrice();

$doc = JFactory::getDocument();

$col = 1;
$class_col = floor (12 / $products_per_row);
$pwidth = ' width' . floor (100 / $products_per_row);
if ($products_per_row > 1) {
	$float = "floatleft";
} else {
	$float = "center";
}
?>
<div id="promo-products" class="vmgroup<?php echo $params->get ('moduleclass_sfx') ?> productdetails vm-product">

	<?php if ($headerText) { ?>
	<div class="vmheader"><a href="<?php echo $footerText ?>"><?php echo $headerText ?></a></div>
	<?php
}
	if ($display_style == "div") {
		?>
		<div class="">
			<div id="VMCarousel" class="carousel slide row" data-ride="carousel" interval="5000">
			    <!-- Wrapper for slides -->
			    <div class="carousel-inner">
				    <?php foreach ($products as $key=>$product) { ?>
				  	  <?php if($key == 0) {
				  	  	echo '<div class="item active">';
				       } elseif ( $key % $products_per_row == 0 ){
				       		echo '<div class="item">';
				       } ?>
						<div class="col-lg-<?php echo $class_col;?> col-md-<?php echo $class_col;?> col-sm-<?php echo $class_col;?> col-xs-12">
							<div class="vm-inner-product">
								<?php
									echo '<div class="">';
								?>
								<?php if($addclass){ ?>
									<span class="label <?php echo $addclass;?>"><?php echo $addclass;?></span>
								<?php }?>
								<?php
								if (!empty($product->images[0])) {
									$image = $product->images[0]->displayMediaThumb ('class="featuredProductImage img-responsive" border="0"', FALSE);
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
						</div>
						<?php
						if ( (($key+1) % $products_per_row == 0) || ($key+1 == $totalProd) ) {
							echo "	</div>";
							$col = 1;
						}
					} ?>
			    </div>
			    <!-- Indicators -->
			    <div class="clearfix"></div>
			    <ol class="carousel-indicators">
			        <?php
			      	$i = 0;
			      	$j = 0;
			      	foreach ($products as $key=>$product) { 
			      		if ( ($key+1) % $products_per_row == 0 ) {
			      			if($i == 0) {
			      				echo '<li data-target="#VMCarousel" data-slide-to="'. $i .'" class="active"></li>';
			      			} else {
			      				echo '<li data-target="#VMCarousel" data-slide-to="'. $i .'" class=""></li>';
			      			}
			      			$i++;
			      		}
			      	?>
			      	<?php } ?>
			    </ol>
			</div>


		</div>
		<?php
	} else {
		$last = count ($products) - 1;
		?>

		<ul id="demo-promo-products-slider" class="vmproduct<?php echo $params->get ('moduleclass_sfx'); ?> productdetails vm-product clearafter demo-bx-slider">
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

			<li class="<?php echo $pwidth ?> <?php echo $float ?>">
				
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
		<ul  class="vmproduct' . $params->get ('moduleclass_sfx') . ' productdetails vm-product clearafter">';
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