
<?php 
if(!empty($more_products)){ 
    foreach ($more_products as $product) {
        ?>
        <div class="col-sm-6 col-md-3">
            <div class="product" style="z-index: 1;">
                <div class="details text-center" style="transition: all 100ms ease-out 0s;">
                    <?php
                    if ($product['promotion']) {
                        ?>
                        <span class="badge badge-right theme"><?= lang('promo'); ?></span>
                        <?php
                    } ?>
                    <img width="200" height="200" src="<?= base_url('assets/uploads/' . $product['image']); ?>" alt="">
                    <?php if (!$this->shop_settings->hide_price) {
                        ?>
                    <div class="image_overlay"></div>
                    <div class="btn add-to-cart" data-id="<?= $product['id']; ?>"><i class="fa fa-shopping-cart"></i> <?= lang('add_to_cart'); ?></div>
                    <?php
                    } ?>
                    <div class="stats-container">
                        <?php if (!$this->shop_settings->hide_price) {
                        ?>
                        <span class="product_price">
                            <?php
                            if ($product['promotion']) {
                                echo '<del class="text-red">' . $this->bpas->convertMoney(isset($product['special_price']) && !empty(isset($product['special_price'])) ? $product['special_price'] : $product['price']) . '</del><br>';
                                echo $this->bpas->convertMoney($product['promo_price']);
                            } else {
                                echo $this->bpas->convertMoney(isset($product['special_price']) && !empty(isset($product['special_price'])) ? $product['special_price'] : $product['price']);
                            } ?>
                        </span>
                        <?php
                    } ?>
                        <span class="product_name">
                            <a href="<?= site_url('product/' . $product['slug']); ?>"><?= $product['name']; ?></a>
                        </span>
                        <a href="<?= site_url('category/' . $product['category_slug']); ?>" class="link"><?= $product['category_name']; ?></a>
                        <?php
                        if ($product['brand_name']) {
                            ?>
                            <span class="link">-</span>
                            <a href="<?= site_url('brand/' . $product['brand_slug']); ?>" class="link"><?= $product['brand_name']; ?></a>
                            <?php
                        } ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
        <?php
    } 
    if($postNum > $postLimit){ ?>
    <div class="load-more" lastID="<?php echo $product['id']; ?>" style="display: none;">
        <img src="<?php echo base_url('assets/images/loading.gif'); ?>"/> Loading more posts...
    </div>
<?php }else{ ?>
    <div class="load-more" lastID="0" style="display: none;">
        That's All!
    </div>
<?php } ?>    
<?php 
}?>