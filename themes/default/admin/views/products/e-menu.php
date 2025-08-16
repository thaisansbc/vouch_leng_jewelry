<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style type="text/css" media="screen">

</style>

<div class="breadcrumb-header no-print">
    <?php $wh_title = ($warehouse_id ? $warehouse->name : ((isset($user_warehouse) && !empty($user_warehouse)) ? $user_warehouse->name : lang('all_warehouses'))); ?>           
    <h2 class="blue"><i class="fa-regular fa-fw fa fa-barcode"></i>
        <?= lang('e_menu'); ?></h2>
    <div class="box-icon">
        <ul class="btn-tasks">
            <li class="dropdown">
                <a href="#" onclick="window.print();return false;" id="print-icon" class="tip" title="<?= lang('print') ?>">
                    <i class="icon fa fa-print"></i>
                </a>
            </li>
        </ul>
    </div>
</div>
<div class="box">

    <div class="box-content">
        <div class="row">
            <div style="text-align:center;">
                <h1>Menu</h1>
            </div>
            <div style="width:100%;">
              <?php 
              foreach($categories as $category){
                ?>
                <div style="width:50%;float: left;">
                    <?= $category->name;?>
                    <?php 
                    $products = $this->site->getProductByCategoryID($category->id);
                    foreach($products as $product){
                    ?>
                    <table width="100%" style="margin-left: 20px;">
                        <tr>
                            <td width="80%"><?= $product->name;?></td>
                            <td><?= $this->bpas->formatMoney($product->price);?></td>
                        </tr>
                    </table>
                    <?php
                    }
                    ?>
                </div>
                    
                <?php
              }
              ?>
           
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>