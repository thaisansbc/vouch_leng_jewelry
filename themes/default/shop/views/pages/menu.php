<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="page-contents">
    <?php if ($categories) {?>
                <div class="container" style="padding: 10px;">
                    <?php 
                    foreach($categories as $bn){
                        echo '<a type="button" href="#" class="btn btn-categories">
                            <span>'.$bn->name.'</span>
                        </a>';
                    }
                    ?>
                    
                </div>
                <?php }?>
    <div class="container">
        <div class="row">
            <div class="col-xs-12">

                <div class="item row" id="productList">
                    <?php
                    if(!empty($home_products)){ 

                        foreach ($home_products as $product) {
                            ?>
                            <div class="col-sm-6 col-md-3">
                                <div class="product" style="z-index: 1;">
                                    <div class="details" style="transition: all 100ms ease-out 0s;">
                                        <?php
                                        if ($product['promotion']) {
                                            ?>
                                            <span class="badge badge-right theme"><?= lang('promo'); ?></span>
                                            <?php
                                        } ?>
                                        <img width="100%" height="200" src="<?= base_url('assets/uploads/' . $product['image']); ?>" alt="">
                                        <?php if (!$shop_settings->hide_price) {
                                            ?>
                                        <div class="image_overlay"></div>
                                        <div class="btn add-to-cart" data-id="<?= $product['id']; ?>"><i class="fa fa-shopping-cart"></i> <?= lang('add_to_cart'); ?></div>
                                        <?php
                                        } ?>
                                        <div class="stats-container">
                                            <div class="product_name">
                                                <a href="<?= site_url('product/' . $product['slug']); ?>"><?= $product['name']; ?></a>
                                            </div>
                                            <?php if (!$shop_settings->hide_price) { ?>
                                           
                                            <div class="product_price text-right">
                                                <?php
                                                if ($product['promotion']) {
                                                    echo '<del class="text-red">' . $this->bpas->convertMoney(isset($product['special_price']) && !empty(isset($product['special_price'])) ? $product['special_price'] : $product['price']) . '</del><br>';
                                                    echo $this->bpas->convertMoney($product['promo_price']);
                                                } else {
                                                    echo $this->bpas->convertMoney(isset($product['special_price']) && !empty(isset($product['special_price'])) ? $product['special_price'] : $product['price']);
                                                } ?>
                                            </div>
                                            <?php } ?>
                                            
                                            
                                            
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        } ?>
                        <div class="load-more" lastID="<?php echo $product['id']; ?>" style="display: none;">
                            <img src="<?= base_url('assets/images/loading.gif'); ?>"/> Loading more posts...
                        </div>
                    <?php }else { ?>
                        <p>Post(s) not available.</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</section>
