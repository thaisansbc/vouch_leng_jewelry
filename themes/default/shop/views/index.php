<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (!empty($slider)) {
?>
<section class="slider-container hide">
    <div class="container-fluid">
        <div class="row">
            <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
                <ol class="carousel-indicators margin-bottom-sm">
                    <?php
                    $sr = 0;
                    foreach ($slider as $slide) {
                        if (!empty($slide->image)) {
                            echo '<li data-target="#carousel-example-generic" data-slide-to="' . $sr . '" class="' . ($sr == 0 ? 'active' : '') . '"></li> ';
                        }
                        $sr++;
                    } ?>
                </ol>
                <div class="carousel-inner" role="listbox">
                    <?php
                    $sr = 0;
                    foreach ($slider as $slide) {
                        if (!empty($slide->image)) {
                            echo '<div class="item' . ($sr == 0 ? ' active' : '') . '">';
                            if (!empty($slide->link)) {
                                echo '<a href="' . $slide->link . '">';
                            }
                            echo '<img src="' . base_url('assets/uploads/' . $slide->image) . '" alt="">';
                            if (!empty($slide->caption)) {
                                echo '<div class="carousel-caption">' . $slide->caption . '</div>';
                            }
                            if (!empty($slide->link)) {
                                echo '</a>';
                            }
                            echo '</div>';
                        }
                        $sr++;
                    } ?>
                </div>

                <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
                    <span class="fa fa-chevron-left" aria-hidden="true"></span>
                    <span class="sr-only"><?= lang('prev'); ?></span>
                </a>
                <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
                    <span class="fa fa-chevron-right" aria-hidden="true"></span>
                    <span class="sr-only"><?= lang('next'); ?></span>
                </a>
            </div>
        </div>
    </div>
</section>
<?php
} ?>
<!-- Main Header -->
<section class="main-header hide">
    <div class="container">
        <div class="row">
            <center class="search_heading mg_b10">
                <h1>
                    Find your favorite items
                </h1>
                <h3>
                    All what you need, To create our community.
                </h3>
            </center>
            <div class="col-sm-12 col-md-12 margin-top-lg">
                <div class="row">
                    
                    <div class="<?= (!$shop_settings->hide_price) ? 'col-sm-8 col-md-6 col-md-offset-3' : 'col-md-6 col-md-offset-6'; ?> search-box">
                        <?= shop_form_open('products', 'id="product-search-form"'); ?>
                        <div class="input-group">
                            <input name="query" type="text" class="form-control_search" id="product-search_form" aria-label="Search..." placeholder="Search...">
                            <div class="input-group-btn input-group-btn_search">
                                <button type="submit" class="btn btn-default btn-search input-group-btn_search"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>

                    
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End Main Header -->
<section class="page-contents">
    <div class="container hide">
        <div style="width: 100%;">
            <?php foreach ($vendors as $vendor) { ?>
            <div style="width:250px;height: 200px;background: #fff;float: left;margin-right: 10px;">
                <div style="padding:5px 0 0 10px;height:100px;">
                    <div><?= $vendor->name;?></div>
                    <div class="hide"><?= lang('products')?>: 0</div>
                </div>
                <div class="vendors_profile">
                    <div class="cycle_profile_border">
                        <img src="<?= base_url('assets/uploads/logos/' . $vendor->logo) ?>" class="img_profile_cycle">
                    </div>
                </div>
            </div>
            <?php }?>
        </div>
        <div class="clearfix"></div>
        <div class="text-center">
            <button class="btn btn-success" type="button">Browse more vendors</button>
        </div>
    </div>
    <div class="container">
      <h2 class="home-items_title text-center">Check out our products</h2>
        <p class="home-items_subtitle text-center">Find what's products you need!</p>
    </div>

    <?php if ($categories) {?>
    <div class="container" style="padding: 10px;">
        <?php 
        foreach($categories as $bn){
            echo '<a type="button" href="'.base_url('category/').$bn->slug.'" class="btn btn-categories">
                <span>'.$bn->name.'</span>
            </a>';
        }
        ?>
        
    </div>
    <?php }?>
    <div class="container">
        
        <div class="row">
            <div class="col-xs-12">
                <!-- Wrapper for slides -->
                <div class="item row" id="productList">
                    <?php
                    if(!empty($home_products)){ 

                        foreach ($home_products as $product) {
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
