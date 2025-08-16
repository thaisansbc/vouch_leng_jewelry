<?php defined('BASEPATH') or exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="text/javascript">if (parent.frames.length !== 0) { top.location = '<?= site_url(); ?>'; }</script>
    <title><?= $page_title; ?></title>
    <meta name="description" content="<?= $page_desc; ?>">
    <link rel="shortcut icon" href="<?= base_url(); ?>/sbc_favicon.ico">
    <link href="<?= $assets; ?>css/libs.min.css" rel="stylesheet">
    <link href="<?= $assets; ?>css/styles.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/custom/shop.css') ?>" rel="stylesheet"/>
    <meta property="og:url" content="<?= isset($product) && !empty($product) ? site_url('product/' . $product->slug) : site_url(); ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?= $page_title; ?>" />
    <meta property="og:description" content="<?= $page_desc; ?>" />
    <meta property="og:image" content="<?= isset($product) && !empty($product) ? base_url('assets/uploads/' . $product->image) : base_url('assets/uploads/logos/' . $shop_settings->logo); ?>" />
</head>
<body>
    <section id="wrapper" class="">
        <header>
            <!-- Top Header -->
            <section class="top-header">
                <div class="container">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="pull-left logo">
                                <a href="<?= site_url(); ?>">
                                    <img alt="<?= $shop_settings->shop_name; ?>" src="<?= base_url('assets/uploads/logos/' . $shop_settings->logo); ?>"  height="40" />
                                </a>
                            </div>
                   <!--      <?php
                        //if (!empty($pages)) {
                            echo '<ul class="list-inline nav pull-left hidden-xs">';
                            foreach ($pages as $page) {
                                echo '<li><a href="' . site_url('page/' . $page->slug) . '">' . $page->name . '</a></li>';
                            }
                            echo '</ul>';
                        //}
                        ?> -->

                            <ul class="list-inline nav pull-right ">
                                <?= $loggedIn && $Staff ? '<li class="hidden-xs"><a href="' . admin_url() . '"><i class="fa fa-dashboard"></i> ' . lang('admin_area') . '</a></li>' : ''; ?>
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                    <img src="<?= base_url('assets/images/' . $Settings->user_language . '.png'); ?>" alt="">
                                    <span class="hidden-xs">&nbsp;&nbsp;<?= ucwords($Settings->user_language); ?></span>
                                 </a>
                                 <ul class="dropdown-menu dropdown-menu-right">
                                    <?php $scanned_lang_dir = array_map(function ($path) {
                                    return basename($path);
                                }, glob(APPPATH . 'language/*', GLOB_ONLYDIR));
                                    foreach ($scanned_lang_dir as $entry) {
                                        if (file_exists(APPPATH . 'language' . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . 'shop' . DIRECTORY_SEPARATOR . 'shop_lang.php')) {
                                            ?>
                                    <li>
                                        <a href="<?= site_url('main/language/' . $entry); ?>">
                                            <img src="<?= base_url('assets/images/' . $entry . '.png'); ?>" class="language-img">
                                            &nbsp;&nbsp;<?= ucwords($entry); ?>
                                        </a>
                                    </li>
                                    <?php
                                        }
                                    } ?>
                                </ul>
                            </li>
                            <?php if (!$shop_settings->hide_price && !empty($currencies)) {
                                        ?>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                    <?= $selected_currency->symbol . ' ' . $selected_currency->code; ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <?php
                                    foreach ($currencies as $currency) {
                                        echo '<li><a href="' . site_url('main/currency/' . $currency->code) . '">' . $currency->symbol . ' ' . $currency->code . '</a></li>';
                                    } ?>
                                </ul>
                            </li>
                            <?php
                                    } ?>
                                <?php
                                if ($loggedIn) {
                                    ?>
                                    <?php if (!$shop_settings->hide_price) {
                                        ?>
                                    <li class="hidden-xs"><a href="<?= shop_url('wishlist'); ?>"><i class="fa fa-heart"></i> <?= lang('wishlist'); ?> (<span id="total-wishlist"><?= $wishlist; ?></span>)</a></li>
                                    <?php
                                    } ?>
                                    <li class="dropdown">
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                            <?= lang('hi') . ' ' . $loggedInUser->first_name; ?> <span class="caret"></span>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <li class=""><a href="<?= site_url('profile'); ?>"><i class="mi fa fa-user"></i> <?= lang('profile'); ?></a></li>
                                            <li class=""><a href="<?= shop_url('orders'); ?>"><i class="mi fa fa-heart"></i> <?= lang('orders'); ?></a></li>
                                            <li class=""><a href="<?= shop_url('quotes'); ?>"><i class="mi fa fa-heart-o"></i> <?= lang('quotes'); ?></a></li>
                                            <li class=""><a href="<?= shop_url('downloads'); ?>"><i class="mi fa fa-download"></i> <?= lang('downloads'); ?></a></li>
                                            <li class=""><a href="<?= shop_url('addresses'); ?>"><i class="mi fa fa-building"></i> <?= lang('addresses'); ?></a></li>
                                            <li class="divider"></li>
                                            <li class=""><a href="<?= site_url('logout'); ?>"><i class="mi fa fa-sign-out"></i> <?= lang('logout'); ?></a></li>
                                        </ul>
                                    </li>
                                    <?php
                                } else {
                                    ?>
                                    <li>
                                        <div class="dropdown">
                                            <a class="btn dropdown-toggle" href="<?= admin_url();?>">
                                                <i class="fa fa-sign-in"></i> <?= lang('login'); ?> <span class="caret"></span>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-login" aria-labelledby="dropdownLogin" data-dropdown-in="zoomIn" data-dropdown-out="fadeOut">
                                                <?php  include FCPATH . 'themes' . DIRECTORY_SEPARATOR . $Settings->theme . DIRECTORY_SEPARATOR . 'shop' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'login_form.php'; ?>
                                            </div>
                                        </div>
                                    </li>
                                    <?php
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
            <!-- End Top Header -->
            <!-- Nav Bar -->
            <nav class="navbar navbar-default menu_bar" role="navigation">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="navbar-header">
                                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-ex1-collapse">
                                    <?= lang('navigation'); ?>
                                </button>
                                <a href="<?= site_url('cart'); ?>" class="btn btn-default btn-cart-xs visible-xs pull-right shopping-cart">
                                    <i class="fa fa-shopping-cart"></i> <span class="cart-total-items"></span>
                                </a>
                            </div>
                            <div class="collapse navbar-collapse" id="navbar-ex1-collapse">
                                <ul class="nav navbar-nav">
                                    <li class="<?= $m == 'main' && $v == 'index' ? 'active' : ''; ?>"><a href="<?= base_url(); ?>"><?= lang('home'); ?></a></li>
                                    <?php if ($isPromo) {
                                            ?>
                                    <li class="<?= $m == 'shop' && $v == 'products' && $this->input->get('promo') == 'yes' ? 'active' : ''; ?>"><a href="<?= shop_url('products?promo=yes'); ?>"><?= lang('promotions'); ?></a></li>
                                    <?php
                                        } ?>
                                    <li class="hide <?= $m == 'shop' && $v == 'products' && $this->input->get('promo') != 'yes' ? 'active' : ''; ?>"><a href="<?= shop_url('products'); ?>"><?= lang('products'); ?></a></li>
                            
                                    <li class="dropdown">
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                            <?= lang('categories'); ?> <span class="caret"></span>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <?php
                                            foreach ($categories as $pc) {
                                                echo '<li class="' . ($pc->subcategories ? 'dropdown dropdown-submenu' : '') . '">';
                                                echo '<a ' . ($pc->subcategories ? 'class="dropdown-toggle" data-toggle="dropdown"' : '') . ' href="' . site_url('category/' . $pc->slug) . '">' . $pc->name . '</a>';
                                                if ($pc->subcategories) {
                                                    echo '<ul class="dropdown-menu">';
                                                    foreach ($pc->subcategories as $sc) {
                                                        echo '<li><a href="' . site_url('category/' . $pc->slug . '/' . $sc->slug) . '">' . $sc->name . '</a></li>';
                                                    }
                                                    echo '<li class="divider"></li>';
                                                    echo '<li><a href="' . site_url('category/' . $pc->slug) . '">' . lang('all_products') . '</a></li>';
                                                    echo '</ul>';
                                                }
                                                echo '</li>';
                                            }
                                            ?>
                                        </ul>
                                    </li>
                                    <li><a href="<?= base_url('main/vendors');?>"><?= lang('vendors'); ?></a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-4 search-box">
                            <?php //if($v != 'index'){?>
                            <?= shop_form_open('products', 'id="product-search-form"'); ?>
                            <div class="input-group">
                                <input name="query" type="text" class="form-control" id="product-search" aria-label="Search..." placeholder="<?= lang('search'); ?>">
                                <div class="input-group-btn">
                                    <button type="submit" class="btn btn-default btn-search"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                            <?= form_close(); ?>
                            <?php //} ?>
                            &nbsp;
                        </div>
                        <div class="col-md-2">
                            <?php if (!$shop_settings->hide_price) {
                                    ?>
                                <div class="cart-btn hidden-xs">
                                    <button type="button" class="btn btn-theme btn-block dropdown-toggle shopping-cart" id="dropdown-cart" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                        <i class="fa fa-shopping-cart margin-right-md"></i>
                                        <span class="cart-total-items"></span>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-cart">
                                        <div id="cart-contents">
                                            <table class="table table-condensed table-striped table-cart" id="cart-items"></table>
                                            <div id="cart-links" class="text-center margin-bottom-md">
                                                <div class="btn-group btn-group-justified" role="group" aria-label="View Cart and Checkout Button">
                                                    <div class="btn-group">
                                                        <a class="btn btn-default btn-sm" href="<?= site_url('cart'); ?>"><i class="fa fa-shopping-cart"></i> <?= lang('view_cart'); ?></a>
                                                    </div>
                                                    <div class="btn-group">
                                                        <a class="btn btn-default btn-sm" href="<?= site_url('cart/checkout'); ?>"><i class="fa fa-check"></i> <?= lang('checkout'); ?></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="cart-empty"><?= lang('please_add_item_to_cart'); ?></div>
                                    </div>
                                </div>
                                <?php
                                } ?>
                        </div>
                    </div>
                    

                </div>
            </nav>
            <!-- End Nav Bar -->            
        </header>
