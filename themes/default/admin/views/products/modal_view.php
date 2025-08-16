<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
            <i class="fa fa-2x">&times;</i>
        </button>
        <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
            <i class="fa fa-print"></i> <?= lang('print'); ?>
        </button>
        <h4 class="modal-title" id="myModalLabel"><?= $product->name . (SHOP && $product->hide != 1 ? ' (' . lang('shop_views') . ': ' . $product->views . ')' : ''); ?></h4>
    </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-xs-5">
                    <img id="pr-image" src="<?= base_url() ?>assets/uploads/<?= $product->image ?>" alt="<?= $product->name ?>" class="img-responsive img-thumbnail"/>
                    <div id="multiimages" class="padding10">
                        <?php if (!empty($images)) {
                            echo '<a class="img-thumbnail change_img" href="' . base_url() . 'assets/uploads/' . $product->image . '" style="margin-right:5px;"><img class="img-responsive" src="' . base_url() . 'assets/uploads/thumbs/' . $product->image . '" alt="' . $product->image . '" style="width:' . $Settings->twidth . 'px; height:' . $Settings->theight . 'px;" /></a>';
                            foreach ($images as $ph) {
                                echo '<div class="gallery-image"><a class="img-thumbnail change_img" href="' . base_url() . 'assets/uploads/' . $ph->photo . '" style="margin-right:5px;"><img class="img-responsive" src="' . base_url() . 'assets/uploads/thumbs/' . $ph->photo . '" alt="' . $ph->photo . '" style="width:' . $Settings->twidth . 'px; height:' . $Settings->theight . 'px;" /></a>';
                                if ($Owner || $Admin || $GP['products-edit']) {
                                    echo '<a href="#" class="delimg" data-item-id="' . $ph->id . '"><i class="fa fa-times"></i></a>';
                                }
                                echo '</div>';
                            }
                        } ?>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="col-xs-7">
                    <div class="table-responsive">
                        <table class="table table-borderless table-striped dfTable">
                            <tbody>
                                <tr>
                                    <td colspan="2" style="background-color:#FFF;"></td>
                                </tr>      
                                <tr>
                                    <td style="width:30%;"><?= lang('barcode_qrcode'); ?></td>
                                    <td style="width:70%;">
                                        <img src="<?= admin_url('misc/barcode/' . $product->code . '/' . $product->barcode_symbology . '/24/0'); ?>" alt="<?= $product->code; ?>" class="bcimg" />
                                        <?= $this->bpas->qrcode('link', urlencode(admin_url('products/view/' . $product->id)), 2); ?>
                                    </td>
                                </tr>
                                <?php if ($product->project_id) {
                                    $project = $this->site->getAllProjectByID($product->project_id);
                                    echo '
                                    <tr>
                                        <td>'.lang('project').'</td>
                                        <td>'.$project->project_name.'</td>
                                    </tr>
                                    ';
                                } ?>
                                <tr>
                                    <td><?= lang('type'); ?></td>
                                    <td><?= lang($product->type); ?></td>
                                </tr>
                                <tr>
                                    <td><?= lang('name'); ?></td>
                                    <td><?= $product->name; ?></td>
                                </tr>
                                <tr>
                                    <td><?= lang('code'); ?></td>
                                    <td><?= $product->code; ?></td>
                                </tr>
                                <tr>
                                    <td><?= lang('brand'); ?></td>
                                    <td><?= $brand ? $brand->name : ''; ?></td>
                                </tr>
                                <tr>
                                    <td><?= lang('serial_no'); ?></td>
                                    <td><?= $product->serial_no; ?></td>
                                </tr>
                                 <tr>
                                    <td><?= lang('weight'); ?></td>
                                    <td><?= $product->weight; ?></td>
                                </tr>
                                <tr>
                                    <td><?= lang('max_serial'); ?></td>
                                    <td><?= $product->max_serial; ?></td>
                                </tr>
                                <tr>
                                    <td><?= lang('category'); ?></td>
                                    <?php if ($category) { ?>
                                    <td><?= $category->name; ?></td>
                                    <?php } else { ?>
                                    <th></th>
                                    <?php } ?>
                                </tr>
                                <?php if ($product->subcategory_id) { ?>
                                    <tr>
                                        <td><?= lang('subcategory'); ?></td>
                                        <td><?= $subcategory->name; ?></td>
                                    </tr>
                                    <?php } ?>
                                    <tr>
                                        <td><?= lang('unit'); ?></td>
                                        <td><?= $unit ? $unit->name . ' (' . $unit->code . ')' : ''; ?></td>
                                    </tr>
                                    <?php 
                                    echo '<tr><td>' . lang('default_currency') . '</td><td>' . $product->currency. '</td></tr>';
                                    if ($Owner || $Admin) {
                                        echo '<tr><td>' . lang('cost') . '</td><td>' . $this->bpas->formatMoney($product->cost) . '</td></tr>';
                                        echo '<tr><td>' . lang('price') . '</td><td>' . $this->bpas->formatMoney($product->price) . '</td></tr>';
                                        if($product->currency !='USD'){
                                            echo '<tr><td>' . lang('other_cost') . '</td><td>' . $this->bpas->formatMoney($product->other_cost) . '</td></tr>';
                                            echo '<tr><td>' . lang('other_price') . '</td><td>' . $this->bpas->formatMoney($product->other_price) . '</td></tr>';
                                        }
                                        if ($product->promotion) {
                                            echo '<tr><td>' . lang('promotion') . '</td><td>' . $this->bpas->formatMoney($product->promo_price) . ' (' . $this->bpas->hrsd($product->start_date) . ' - ' . $this->bpas->hrsd($product->end_date) . ')</td></tr>';
                                        }
                                    } else {
                                        if ($GP['products-cost']) {
                                            echo '<tr><td>' . lang('cost') . '</td><td>' . $this->bpas->formatMoney($product->cost) . '</td></tr>';
                                            if($product->currency !='USD'){
                                                echo '<tr><td>' . lang('other_cost') . '</td><td>' . $this->bpas->formatMoney($product->other_cost) . '</td></tr>';
                                            }
                                        }
                                        if ($GP['products-price']) {
                                            echo '<tr><td>' . lang('price') . '</td><td>' . $this->bpas->formatMoney($product->price) . '</td></tr>';
                                            if($product->currency !='USD'){
                                                echo '<tr><td>' . lang('other_price') . '</td><td>' . $this->bpas->formatMoney($product->other_price) . '</td></tr>';
                                            }
                                            if ($product->promotion) {
                                                echo '<tr><td>' . lang('promotion') . '</td><td>' . $this->bpas->formatMoney($product->promo_price) . ' (' . $this->bpas->hrsd($product->start_date) . ' - ' . $this->bpas->hrsd($product->end_date) . ')</td></tr>';
                                            }
                                        }
                                    } ?>
                                    <?php if ($product->tax_rate) { ?>
                                    <tr>
                                        <td><?= lang('tax_rate'); ?></td>
                                        <td><?= $tax_rate->name; ?></td>
                                    </tr>
                                    <tr>
                                        <td><?= lang('tax_method'); ?></td>
                                        <td><?= $product->tax_method == 0 ? lang('inclusive') : lang('exclusive'); ?></td>
                                    </tr>
                                    <?php } ?>
                                    <?php if ($product->alert_quantity != 0) { ?>
                                    <tr>
                                        <td><?= lang('alert_quantity'); ?></td>
                                        <td><?= $this->bpas->formatQuantity($product->alert_quantity); ?></td>
                                    </tr>
                                    <?php } ?>
                                    <?php if ($variants) { ?>
                                    <tr>
                                        <td><?= lang('product_variants'); ?></td>
                                        <td><?php foreach ($variants as $variant) {
                                            echo '<span class="label label-primary">' . $variant->name . '</span> ';
                                        } ?></td>
                                    </tr>
                                    <?php } if ($product_options) { ?>
                                    <tr>
                                        <td><?= lang('product_options'); ?></td>
                                        <td><?php foreach ($product_options as $opt) {
                                            echo '<span class="label label-primary">' . $opt->name . '</span> ';
                                        } ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-xs-12">
                        <div class="row">
                            <div class="col-xs-5">
                                <?php if ($product->cf1 || $product->cf2 || $product->cf3 || $product->cf4 || $product->cf5 || $product->cf6) { ?>
                                <h3 class="bold"><?= lang('custom_fields') ?></h3>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-condensed dfTable two-columns">
                                        <thead>
                                            <tr>
                                                <th><?= lang('custom_field') ?></th>
                                                <th><?= lang('value') ?></th>
                                            </tr>
                                        </thead>
                                    <tbody>
                                        <?php if ($product->cf1) {
                                            echo '<tr><td>' . lang('pcf1') . '</td><td>' . $product->cf1 . '</td></tr>';
                                        }
                                        if ($product->cf2) {
                                            echo '<tr><td>' . lang('pcf2') . '</td><td>' . $product->cf2 . '</td></tr>';
                                        }
                                        if ($product->cf3) {
                                            echo '<tr><td>' . lang('pcf3') . '</td><td>' . $product->cf3 . '</td></tr>';
                                        }
                                        if ($product->cf4) {
                                            echo '<tr><td>' . lang('pcf4') . '</td><td>' . $product->cf4 . '</td></tr>';
                                        }
                                        if ($product->cf5) {
                                            echo '<tr><td>' . lang('pcf5') . '</td><td>' . $product->cf5 . '</td></tr>';
                                        }
                                        if ($product->cf6) {
                                            echo '<tr><td>' . lang('pcf6') . '</td><td>' . $product->cf6 . '</td></tr>';
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php } ?>
                            <?php if ((!$Supplier || !$Customer) && !empty($warehouses) && $product->type == 'standard') { ?>
                            <h3 class="bold"><?= lang('warehouse_quantity') ?></h3>
                            <div class="table-responsive">
                                <table class="table table-bordered table-condensed">
                                    <thead>
                                        <tr>
                                            <th><?= lang('warehouse_name') ?></th>
                                            <th><?= lang('quantity'); ?></th>
                                            <?php if ($this->Settings->using_weight) {
                                                echo '<th>'.lang('weight').'</th>';
                                            }?>
                                            <th><?= lang('rack');?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (($Admin || $Owner) || !$this->session->userdata('warehouse_id')) {
                                            foreach ($warehouses as $warehouse) {
                                                echo '<tr>
                                                        <td>' . $warehouse->name . ' (' . $warehouse->code . ')</td>
                                                        <td><strong>' . $this->bpas->formatQuantity($warehouse->quantity) . '</strong></td>';
                                                        if ($this->Settings->using_weight) {
                                                                echo '<td>'.$this->bpas->formatQuantity($warehouse->weight).'</td>';
                                                            }
                                                 echo ' <td>'.($warehouse->rack ? ' (' . $warehouse->rack . ')' : '') .'</td>
                                                </tr>';
                                            }
                                        } else {
                                            $wh_id = explode(',', $this->session->userdata('warehouse_id'));
                                            foreach ($warehouses as $warehouse) {
                                                foreach ($wh_id as $key => $value) {
                                                    if ($warehouse->id == $value) {
                                                        echo '<tr>
                                                            <td>' . $warehouse->name . ' (' . $warehouse->code . ')</td>
                                                            <td><strong>' . $this->bpas->formatQuantity($warehouse->quantity).'</td>';
                                                            if ($this->Settings->using_weight) {
                                                                echo '<td>'.$this->bpas->formatQuantity($warehouse->weight).'</td>';
                                                            }
                                                        echo '    <td><strong>' .($warehouse->rack ? ' (' . $warehouse->rack . ')' : '') . '</td>
                                                        </tr>';
                                                    }
                                                }
                                            }
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="col-xs-7">
                            <?php if ($product->type == 'combo') { ?>
                            <h3 class="bold"><?= lang('combo_items') ?></h3>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-condensed dfTable two-columns">
                                    <thead>
                                        <tr>
                                            <th><?= lang('product_name') ?></th>
                                            <th><?= lang('quantity') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if($combo_items) {
                                            foreach ($combo_items as $combo_item) {
                                                echo '<tr><td>' . $combo_item->name . ' (' . $combo_item->code . ') </td><td>' . $this->bpas->formatQuantity($combo_item->qty) . '</td></tr>';
                                            } 
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php } ?>
                            <?php if (!empty($options) && $product->type == 'standard') { ?>
                            <h3 class="bold"><?= lang('product_variants_quantity'); ?></h3>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-condensed dfTable">
                                    <thead>
                                        <tr>
                                            <th><?= lang('warehouse_name') ?></th>
                                            <th><?= lang('product_variant'); ?></th>
                                            <th><?= lang('quantity') . ' (' . lang('rack') . ')'; ?></th>
                                            <?php if ($Owner || $Admin) {
                                                echo '<th>' . lang('price_addition') . '</th>';
                                            } ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($options as $option) {
                                            if ($option->wh_qty != 0) {
                                                echo '<tr><td>' . $option->wh_name . '</td><td>' . $option->name . '</td><td class="text-center">' . $this->bpas->formatQuantity($option->wh_qty) . '</td>';
                                                if ($Owner || $Admin && (!$Customer || $GP['products-cost'])) {
                                                    echo '<td class="text-right">' . $this->bpas->formatMoney($option->price) . '</td>';
                                                }
                                                echo '</tr>';
                                            }
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <?php if ($addon_items) { ?>
                            <h3 class="bold"><?= lang('addOn_items') ?></h3>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-condensed dfTable two-columns">
                                    <thead>
                                        <tr>
                                            <th style="width: 33%"><?= lang('product_name') ?></th>
                                             <th><?= lang('price') ?></th>
                                            <th><?= lang('description') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($addon_items as $addon_item) {
                                            echo '<tr>
                                                    <td style="width: 33%">' . $addon_item->name . ' (' . $addon_item->code . ')</td>
                                                     <td>' . $addon_item->price . '</td>
                                                    <td>' . $addon_item->description . '</td>
                                                </tr>';
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <?php if($multi_units && sizeof($multi_units) > 1){ ?>
                            <h3 class="bold"><?= lang('multi_units') ?></h3>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-condensed dfTable hour-columns">
                                    <thead>
                                        <tr>
                                            <th style="width: 40% !important;"><?= lang('description') ?></th>
                                            <th style="width: 30% !important;"><?= lang('operation_math') ?></th>
                                            <?php if (($Owner || $Admin) || $GP['products-cost']) { ?>
                                                <th><?= lang('cost') ?></th>
                                            <?php } ?>
                                            <?php if (($Owner || $Admin) || $GP['products-price']) { ?>
                                                <th><?= lang('price') ?></th>
                                            <?php } ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($multi_units as $multi_unit) {
                                            if($multi_unit->id == $product->sale_unit || $multi_unit->id == $product->purchase_unit){
                                                if ($multi_unit->id == $product->sale_unit && $multi_unit->id == $product->purchase_unit) {
                                                    echo '<tr>
                                                            <td>' . $multi_unit->name . ' (' . $multi_unit->code . ')</td>
                                                            <td>' . $multi_unit->operator . ' ' . $multi_unit->operation_value . '</td>';
                                                    if (($Owner || $Admin) || $GP['products-cost']) {
                                                        echo '<td>' . $this->bpas->formatMoney($multi_unit->cost) . ' <span class="badge badge-info">def</span></td>';
                                                    }
                                                    if (($Owner || $Admin) || $GP['products-price']) {
                                                        echo '<td>' . $this->bpas->formatMoney($multi_unit->price) . ' <span class="badge badge-info">def</span></td>';
                                                    }
                                                    echo '</tr>';
                                                } elseif ($multi_unit->id == $product->purchase_unit){
                                                    echo '<tr>
                                                            <td>' . $multi_unit->name . ' (' . $multi_unit->code . ')</td>
                                                            <td>' . $multi_unit->operator . ' ' . $multi_unit->operation_value . '</td>';
                                                    if (($Owner || $Admin) || $GP['products-cost']) {
                                                        echo '<td>' . $this->bpas->formatMoney($multi_unit->cost) . ' <span class="badge badge-info">def</span></td>';
                                                    }
                                                    if (($Owner || $Admin) || $GP['products-price']) {
                                                        echo '<td>' . $this->bpas->formatMoney($multi_unit->price) . '</td>';
                                                    }
                                                    echo '</tr>';
                                                } else {
                                                    echo '<tr>
                                                            <td>' . $multi_unit->name . ' (' . $multi_unit->code . ')</td>
                                                            <td>' . $multi_unit->operator . ' ' . $multi_unit->operation_value . '</td>';
                                                    if (($Owner || $Admin) || $GP['products-cost']) {
                                                        echo '<td>' . $this->bpas->formatMoney($multi_unit->cost) . '</td>';
                                                    }
                                                    if (($Owner || $Admin) || $GP['products-price']) {
                                                        echo '<td>' . $this->bpas->formatMoney($multi_unit->price) . ' <span class="badge badge-info">def</span></td>';
                                                    }
                                                    echo '</tr>'; 
                                                }
                                            } else {
                                                echo '<tr>
                                                        <td>' . $multi_unit->name . ' (' . $multi_unit->code . ')</td>
                                                        <td>' . $multi_unit->operator . ' ' . $multi_unit->operation_value . '</td>';
                                                if (($Owner || $Admin) || $GP['products-cost']) {
                                                    echo '<td>' . $this->bpas->formatMoney($multi_unit->cost) . '</td>';
                                                }
                                                if (($Owner || $Admin) || $GP['products-price']) {
                                                    echo '<td>' . $this->bpas->formatMoney($multi_unit->price) . '</td>';
                                                }
                                                echo '</tr>'; 
                                            }
                                            // if ($multi_unit->id == $product->purchase_unit) {
                                            //     echo '<tr>
                                            //             <td style="width: 33%">' . $multi_unit->name . ' (' . $multi_unit->code . ')</td>
                                            //             <td>' . $multi_unit->operator . ' ' . $multi_unit->operation_value . '</td>
                                            //             <td>' . $this->bpas->formatMoney($multi_unit->cost) . '</td>
                                            //             <td>' . $this->bpas->formatMoney($multi_unit->price) . ' <span class="badge badge-primary">def</span></td>
                                            //         </tr>';
                                            // }
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php if ($this->Settings->product_expiry && $product->type == 'standard') { ?>
                <div class="row">
                    <div class="col-sm-12">
                       <?php if ($product_expiry) { ?>
                            <h3 class="bold"><?= lang('product_expiry') ?></h3>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-condensed dfTable three-columns">
                                    <thead>
                                        <tr>
                                            <th style="width: 33%"><?= lang('warehouses') ?></th>
                                            <th><?= lang('expiry_date') ?></th>
                                            <th><?= lang('quantity') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($product_expiry as $data) {
                                            if ($data->quantity != 0)
                                            echo '<tr>
                                                <td>'.$data->warehouse_name.'</td>
                                                <td style="width: 33%">' . ($data->expiry == null ? "N/A" : $data->expiry ) .'</td>
                                                <td>' . $this->bpas->formatQuantity($data->quantity) . '</td>
                                            </tr>';
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
            <div class="col-xs-12">
                <?= $product->details ? '<div class="panel panel-success"><div class="panel-heading">' . lang('product_details_for_invoice') . '</div><div class="panel-body">' . $product->details . '</div></div>' : ''; ?>
                <?= $product->product_details ? '<div class="panel panel-primary"><div class="panel-heading">' . lang('product_details') . '</div><div class="panel-body">' . $product->product_details . '</div></div>' : ''; ?>
            </div>
        </div>
        <?php if (!$Supplier || !$Customer) { ?>
            <div class="buttons">
                <div class="btn-group btn-group-justified">
                    <div class="btn-group">
                        <a href="<?= admin_url('products/print_barcodes/' . $product->id) ?>" class="tip btn btn-primary" title="<?= lang('print_barcode_label') ?>">
                            <i class="fa fa-print"></i>
                            <span class="hidden-sm hidden-xs"><?= lang('print_barcode_label') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= admin_url('products/pdf/' . $product->id) ?>" class="tip btn btn-primary" title="<?= lang('pdf') ?>">
                            <i class="fa fa-download"></i>
                            <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= admin_url('products/edit/' . $product->id) ?>" class="tip btn btn-warning tip" title="<?= lang('edit_product') ?>">
                            <i class="fa fa-edit"></i>
                            <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="#" class="tip btn btn-danger bpo" title="<b><?= lang('delete_product') ?></b>" data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('products/delete/' . $product->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>" data-html="true" data-placement="top">
                            <i class="fa fa-trash-o"></i>
                            <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                        </a>
                    </div>
                </div>
            </div>
            <script type="text/javascript">
            $(document).ready(function () {
                $('.tip').tooltip();
            });
            </script>
        <?php } ?>
        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
    $('.change_img').click(function(event) {
        event.preventDefault();
        var img_src = $(this).attr('href');
        $('#pr-image').attr('src', img_src);
        return false;
    });
});
</script>
