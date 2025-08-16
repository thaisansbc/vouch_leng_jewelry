<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
    $v = "";
    if ($this->input->post('product')) {
    $v .= "&product=" . $this->input->post('product');
    }
    if ($this->input->post('category')) {
        $v .= "&category=" . $this->input->post('category');
    }
    if ($this->input->post('product_type')) {
        $v .= "&product_type=" . $this->input->post('product_type');
    }
    if ($this->input->post('start_date')) {
        $v .= "&start_date=" . $this->input->post('start_date');
    }
    if ($this->input->post('end_date')) {
        $v .= "&end_date=" . $this->input->post('end_date');
    }
    if ($this->input->post('cf1')) {
        $v .= "&cf1=" . $this->input->post('cf1');
    }
    if ($this->input->post('cf2')) {
        $v .= "&cf2=" . $this->input->post('cf2');
    }
    if ($this->input->post('cf3')) {
        $v .= "&cf3=" . $this->input->post('cf3');
    }
    if ($this->input->post('cf4')) {
        $v .= "&cf4=" . $this->input->post('cf4');
    }
    if ($this->input->post('cf5')) {
        $v .= "&cf5=" . $this->input->post('cf5');
    }
    if ($this->input->post('cf6')) {
        $v .= "&cf6=" . $this->input->post('cf6');
    }
?>
<style type="text/css" media="screen">
    <?php if($this->Settings->show_warehouse_qty) { ?>
        #PRData td:nth-child(12) {
            text-align: right;
        }
        #PRData td:nth-child(11) {
            text-align: right;
        }
        #PRData td:nth-child(10) {
            text-align: right;
        }   
    <?php } else { ?>
        #PRData td:nth-child(10) {
            text-align: right;
        }
    <?php } ?>
</style>
<?php

    $warehouse_header = '';
    $warehouse_footer = '';
    $warehouse_value = '';
    if ($this->Settings->show_warehouse_qty) {
        $warehouses = $this->site->getAllWarehouses();
        if($warehouses){
            foreach($warehouses as $warehoused){
                $warehouse_value  .='{"mRender": JSConvertQty, "sClass": "text-center"},';
                $warehouse_header .= '<th>'.$warehoused->name.'</th>';
                $warehouse_footer .= '<th></th>';
            }
        }
    }
?>
<script>
    function JSConvertQty(product_qty){
        product_qty = product_qty.split("|");
        var product_id = product_qty[1];
        var quantity = formatDecimalRaw(product_qty[0]);
        var product_units = <?= $product_units ?>;
        if(product_units[product_id]){
            var unit_string = '';
            var i = 1;
            var operation = '';
            if(quantity < 0){
                quantity = quantity * (-1);
                operation = '-';
            }
            if(quantity < 1){
                return quantity;
            }
            $.each(product_units[product_id], function () {
                if(quantity >= this.unit_qty){
                    if(i > 1){
                        unit_string += ', ';
                    }
                    if(this.unit_qty == 1){
                        var quantity_unit = quantity / this.unit_qty;
                    }else{
                        var quantity_unit = parseInt(quantity / this.unit_qty);
                    }

                    unit_string += formatQuantity2(quantity_unit)+' <span style="color:#357EBD;">'+this.unit_name+'</span>';
                    quantity = quantity - (quantity_unit * this.unit_qty);
                    i++;
                }
            });
            return operation+''+unit_string;
        }else{
            return quantity;
        }
    }


    var oTable;
    $(document).ready(function () {
        oTable = $('#PRData').dataTable({
            "aaSorting": [[2, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('products/getProducts' . ($warehouse_id ? '/' . $warehouse_id : '') . ($supplier ? '?supplier=' . $supplier->id : '') . '?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "product_link";
                //if(aData[7] > aData[9]){ nRow.className = "product_link warning"; } else { nRow.className = "product_link"; }
                return nRow;
            },
            'bStateSave': true,
            'fnStateSave': function (oSettings, oData) {
                localStorage.setItem('DataTables_' + window.location.pathname, JSON.stringify(oData));
            },
            'fnStateLoad': function (oSettings) {
                var data = localStorage.getItem('DataTables_' + window.location.pathname);
                return JSON.parse(data);
            },
            "search": {
                "caseInsensitive": false
            },
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox}, {"bSortable": false,"mRender": img_hl}, null, null, null, null, null, <?php if($Owner || $Admin) { echo '{"mRender": currencyFormat}, {"mRender": currencyFormat},'; } else { if($this->session->userdata('show_cost')) { echo '{"mRender": currencyFormat},';  } if($this->session->userdata('show_price')) { echo '{"mRender": currencyFormat},';  } } ?> <?= $warehouse_value ?> {"mRender": JSConvertQty},  <?php if(!$warehouse_id || !$Settings->racks) { echo '{"bVisible": false},'; } else { echo '{"bSortable": true},'; } ?> {"mRender": formatQuantity}, {"bSortable": false}
            ]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 2, filter_default_label: "[<?=lang('code');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('category');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('unit');?>]", filter_type: "text", data: []},
            
            <?php $col = 6;
            if($Owner || $Admin) {
                echo '{column_number : 7, filter_default_label: "['.lang('cost').']", filter_type: "text", data: [] },';
                echo '{column_number : 8, filter_default_label: "['.lang('price').']", filter_type: "text", data: [] },';
                $col += 2;
            } else {
                if($this->session->userdata('show_cost')) { $col++; echo '{column_number : '.$col.', filter_default_label: "['.lang('cost').']", filter_type: "text", data: [] },'; }
                if($this->session->userdata('show_price')) { $col++; echo '{column_number : '.$col.', filter_default_label: "['.lang('price').']", filter_type: "text", data: [] },'; }
            }
            ?>
            <?php
            if ($this->Settings->show_warehouse_qty) {
                if($warehouses){
                    foreach($warehouses as $warehoused){
                        $col++;
                        echo '{column_number: '.$col.', filter_default_label: "['.$warehoused->name.']", filter_type: "text", data: []},';
                    }
                }
            }
            ?>
            {column_number: <?php $col++; echo $col; ?>, filter_default_label: "[<?=lang('quantity');?>]", filter_type: "text", data: []},
            <?php $col++; if($warehouse_id && $Settings->racks) { echo '{column_number : '. $col.', filter_default_label: "['.lang('rack').']", filter_type: "text", data: [] },'; } ?>
            {column_number: <?php $col++; echo $col; ?>, filter_default_label: "[<?=lang('alert_quantity');?>]", filter_type: "text", data: []},
        ], "footer");


        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
    });
    
    
    $('#price_list').live('click',function(){
        var product_id = '';
        var intRegex = /^\d+$/;
        var i = 0;
        $('.input-xs').each(function(){
            if ($(this).is(':checked') && intRegex.test($(this).val())) {
                if(i==0){
                    product_id += $(this).val();
                    i=1;
                }else{
                    product_id += "ProductID"+$(this).val();
                }
                
            }
        });
        if(product_id==''){
            alert("<?= lang('no_sale_selected') ?>")
            return false;
        }else{
            var link = '<?= anchor('products/price_list/#######', '<i class="fa fa-money"></i> ' . lang('price_list'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal" class="price_list"')?>';
            var price_list_link = link.replace("#######", product_id);
            $("#price_box").html(price_list_link);
            $('.price_list').click();
            $("#price_box").html('<a href="javascript:void(0)" id="price_list" data-action="price_list"><i class="fa fa-money"></i> <?=lang('price_list')?></a>');      
            return false;
        }
    });
 
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-barcode"></i><?= lang('products') . ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')'.($supplier ? ' ('.lang('supplier').': '.($supplier->company && $supplier->company != '-' ? $supplier->company : $supplier->name).')' : ''); ?>
        </h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="fa-regular icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="fa-regular icon fa fa-toggle-down"> </i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= admin_url('products/add') ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('add_product') ?>
                            </a>
                        </li>
                        <?php if(!$warehouse_id) { ?>
                        <li>
                            <a href="<?= admin_url('products/update_price') ?>" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal">
                                <i class="fa fa-file-excel-o"></i> <?= lang('update_price') ?>
                            </a>
                        </li>
                        <?php } ?>
                        <li>
                            <a href="#" id="labelProducts" data-action="labels">
                                <i class="fa fa-print"></i> <?= lang('print_barcode_label') ?>
                            </a>
                        </li>
                        <?php if ($Owner || $Admin || $GP['products-price_list']) { ?>
                            <li id="price_box">
                                <a href="javascript:void(0)" id="price_list" data-action="price_list">
                                    <i class="fa fa-money"></i> <?=lang('price_list')?>
                                </a>
                            </li>
                        <?php } ?>

                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_products") ?></b>"
                                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                                data-html="true" data-placement="left">
                            <i class="fa fa-trash-o"></i> <?= lang('delete_products') ?>
                             </a>
                         </li>
                    </ul>
                </li>
                <?php if (!empty($warehouses)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= site_url('products') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($warehouses as $warehouse) {
                                echo '<li><a href="' . site_url('products/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
                    <?php echo admin_form_open("products"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="product_id"><?= lang("product"); ?></label>
                                <?php
                                $pr[0] = $this->lang->line("all");;
                                foreach ($products as $product) {
                                    $pr[$product->id] = $product->name . " | " . $product->code ;
                                }
                                echo form_dropdown('product', $pr, (isset($_POST['product']) ? $_POST['product'] : ""), 'class="form-control" id="product" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("product") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group all">
                                <?= lang('category', 'category') ?>
                                <div class="input-group" style="width: 100%">
                                    <?php 
                                    $form_category = null;
                                    function formMultiLevelCategory($data, $n, $str = '')
                                    {
                                        $form_category = ($n ? '<select id="category" name="category" class="form-control select" style="width: 100%" placeholder="' . lang('select') . ' ' . lang('category') . '" ><option value="" selected>' . lang('select') . ' ' . lang('category') . '</option>' : '');
                                        foreach ($data as $key => $categories) {
                                            if (!empty($categories->children)) {
                                                $form_category .= '<option disabled>' . $str . $categories->name . '</option>';
                                                $form_category .= formMultiLevelCategory($categories->children, 0, ($str.'&emsp;&emsp;'));
                                            } else {
                                                $form_category .= ('<option value="' . $categories->id . '">' . $str . $categories->name . '</option>');
                                            }
                                        }
                                        $form_category .= ($n ? '</select>' : '');
                                        return $form_category;
                                    }
                                    echo formMultiLevelCategory($nest_categories, 1); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("product_type", "product_type"); ?>
                                <?php
                                $pst = array('0' => lang('active'), '1' => lang('inactive'));
                                echo form_dropdown('product_type', $pst, (isset($_POST['product_type']) ? $_POST['product_type'] : ''), 'class="form-control input-tip" id="product_type"');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit_product', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <?php if ($Owner || $Admin || $GP['bulk_actions']) {
                    echo admin_form_open('products/product_actions' . ($warehouse_id ? '/' . $warehouse_id : ''), 'id="action-form"');
                } ?>
                <div class="table-responsive">
                    <table id="PRData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="primary">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line("image"); ?></th>
                            <th><?= lang("code") ?></th>
                            <th><?= lang("name") ?></th>
                            <th><?= lang("type") ?></th>
                            <th><?= lang("category") ?></th>
                            <th><?= lang("unit") ?></th>
                            
                            <?php
                            if ($Owner || $Admin) {
                                echo '<th>' . lang("cost") . '</th>';
                                echo '<th>' . lang("price") . '</th>';
                            } else {
                                if ($this->session->userdata('show_cost')) {
                                    echo '<th>' . lang("cost") . '</th>';
                                }
                                if ($this->session->userdata('show_price')) {
                                    echo '<th>' . lang("price") . '</th>';
                                }
                            }
                            ?>
                            <?= $warehouse_header ?>
                            <th><?= lang("quantity") ?></th>
                            <th><?= lang("rack") ?></th>
                            <th><?= lang("alert_quantity") ?></th>
                            <th style="min-width:65px; text-align:center;"><?= lang("actions") ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="12" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                        </tr>
                        </tbody>

                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line("image"); ?></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <?php
                            if ($Owner || $Admin) {
                                echo '<th></th>';
                                echo '<th></th>';
                            } else {
                                if ($this->session->userdata('show_cost')) {
                                    echo '<th></th>';
                                }
                                if ($this->session->userdata('show_price')) {
                                    echo '<th></th>';
                                }
                            }
                            ?>
                            <?= $warehouse_footer ?>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="width:65px; text-align:center;"><?= lang("actions") ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $Admin || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>
