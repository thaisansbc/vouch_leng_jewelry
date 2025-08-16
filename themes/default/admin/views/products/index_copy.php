<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
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
    <?php if ($Owner || $Admin || $GP['products-cost']) { ?>
    #dtFilter-filter--PRData-7, #PRData td:nth-child(8) {
        text-align: right !important;
    }
    <?php } if ($Owner || $Admin || $GP['products-price']) { ?>
    #dtFilter-filter--PRData-8, #PRData td:nth-child(9) {
        text-align: right !important;
    }
    #dtFilter-filter--PRData-9 {
        text-align: center !important;
    }
    <?php } ?>
</style>
<script>
    var oTable;
    $(document).ready(function () {
        oTable = $('#PRData').dataTable({
            "aaSorting": [[2, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100,200,300,400,500, -1], [10, 25, 50, 100,200,300,400,500, "<?= lang('all') ?>"]],
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
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox}, {"bSortable": false,"mRender": img_hl}, null, null, null, null, null,
                <?php if ($Owner || $Admin) {
                    echo '{"mRender": currencyFormat}, {"mRender": currencyFormat},';
                } else {
                    if ($GP['products-cost']) {
                        echo '{"mRender": currencyFormat},';
                    }
                    if ($GP['products-price']) {
                        echo '{"mRender": currencyFormat},';
                    }
                } ?> { "mRender": formatQuantity}, null, 
                <?php if (!$warehouse_id || !$Settings->racks) {
                    echo '{"bVisible": false},';
                } else {
                    echo '{"bSortable": true},';
                } ?> {"mRender": formatQuantity}, {"bSortable": false}
            ]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 2, filter_default_label: "[<?=lang('code');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('brand');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('category');?>]", filter_type: "text", data: []},
            <?php $col = 6;
            if ($Owner || $Admin) {
                echo '{column_number : 7, filter_default_label: "[' . lang('cost') . ']", filter_type: "text", data: [] },';
                echo '{column_number : 8, filter_default_label: "[' . lang('price') . ']", filter_type: "text", data: [] },';
                $col += 2;
            } else {
                if ($GP['products-cost']) {
                    $col++;
                    echo '{column_number : ' . $col . ', filter_default_label: "[' . lang('cost') . ']", filter_type: "text", data: [] },';
                }
                if ($GP['products-price']) {
                    $col++;
                    echo '{column_number : ' . $col . ', filter_default_label: "[' . lang('price') . ']", filter_type: "text, data: []" },';
                }
            } ?>
            {column_number: <?php $col++; echo $col; ?>, filter_default_label: "[<?=lang('quantity');?>]", filter_type: "text", data: []},
            {column_number: <?php $col++; echo $col; ?>, filter_default_label: "[<?=lang('unit');?>]", filter_type: "text", data: []},
            <?php $col++; if ($warehouse_id && $Settings->racks) {
                echo '{column_number : ' . $col . ', filter_default_label: "[' . lang('rack') . ']", filter_type: "text", data: [] },';
            } ?>
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
</script>
<div class="breadcrumb-header">
    <?php $wh_title = ($warehouse_id ? $warehouse->name : ((isset($user_warehouse) && !empty($user_warehouse)) ? $user_warehouse->name : lang('all_warehouses'))); ?>           
    <h2 class="blue"><i class="fa-regular fa-fw fa fa-barcode"></i>
        <?php $this->bpas->title($bc); ?>
        <?= ' (' . $wh_title . ')' . ($supplier ? ' (' . lang('supplier') . ': ' . ($supplier->company && $supplier->company != '-' ? $supplier->company : $supplier->name) . ')' : ''); ?></h2>
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
                    <i class="fa-regular icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"> <span class="fa fa-angle-down"></span></i>
                </a>
                <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                    <li>
                        <a href="<?= admin_url('products/add') ?>">
                            <i class="fa fa-plus-circle"></i> <?= lang('add_product') ?>
                        </a>
                    </li>
                    <?php if (!$warehouse_id) { ?>
                    <li>
                        <a href="<?= admin_url('products/update_price') ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                            <i class="fa fa-file-excel-o"></i> <?= lang('update_price') ?>
                        </a>
                    </li>
                    <?php } ?>
                    <li>
                        <a href="#" id="labelProducts" data-action="labels">
                            <i class="fa fa-print"></i> <?= lang('print_barcode_label') ?>
                        </a>
                    </li>
                    <li>
                        <a href="#" id="excel" data-action="export_excel">
                            <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                        </a>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <a href="#" class="bpo" title="<b><?= $this->lang->line('delete_products') ?></b>"
                            data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                            data-html="true" data-placement="left">
                        <i class="fa fa-trash-o"></i> <?= lang('delete_products') ?>
                         </a>
                     </li>
                </ul>
            </li>
            <?php if (($this->Owner || $this->Admin) || !$this->session->userdata('warehouse_id')) { ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="fa-regular icon fa fa-building-o tip" data-placement="left" title="<?= lang('warehouses') ?>"> <span class="fa fa-angle-down"></span> </i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('products') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        foreach ($warehouses as $warehouse) {
                            echo '<li><a href="' . admin_url('products/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                        } ?>
                    </ul>
                </li>
            <?php } elseif (!empty($warehouses)) { ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="fa-regular icon fa fa-building-o tip" data-placement="left" title="<?= lang('warehouses') ?>"> <?= lang('warehouses') ?></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('products') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        $wh_id = '';
                        if($this->session->userdata('warehouse_id') != null) {
                            $wh_id = explode(',', $this->session->userdata('warehouse_id'));
                            foreach ($warehouses as $warehouse) {
                                foreach ($wh_id as $key => $value) {
                                    if ($warehouse->id==$value) {
                                        echo '<li><a href="' . admin_url('products/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                    }
                                }
                            } 
                        } ?>
                    </ul>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>
<div class="box">
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
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
                    <table id="PRData" class="table table-condensed table-hover table-striped">
                        <thead>
                            <tr class="primary">
                                <th style="min-width:30px; width: 30px; text-align: center !important;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <th style="min-width:40px; width: 40px; text-align: center !important;"><?php echo $this->lang->line('image'); ?></th>
                                <th><?= lang('code') ?></th>
                                <th><?= lang('name') ?></th>
                                <th><?= lang('type') ?></th>
                                <th><?= lang('brand') ?></th>
                                <th><?= lang('category') ?></th>
                                <?php if ($Owner || $Admin) {
                                    echo '<th style="text-align:right !important;">' . lang('cost') . '</th>';
                                    echo '<th style="text-align:right !important;">' . lang('price') . '</th>';
                                } else {
                                    if ($GP['products-cost']) {
                                        echo '<th style="text-align:right !important;">' . lang('cost') . '</th>';
                                    }
                                    if ($GP['products-price']) {
                                        echo '<th style="text-align:right !important;">' . lang('price') . '</th>';
                                    }
                                } ?>
                                <th style="text-align:center !important;"><?= lang('quantity') ?></th>
                                <th><?= lang('unit') ?></th>
                                <th><?= lang('rack') ?></th>
                                <th><?= lang('alert_quantity') ?></th>
                                <th style="min-width: 65px; text-align: center !important;"><?= lang('actions') ?></th>
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
                                <th style="min-width :40px; width: 40px; text-align: center;"><?php echo $this->lang->line('image'); ?></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <?php if ($Owner || $Admin) {
                                    echo '<th></th>';
                                    echo '<th></th>';
                                } else {
                                    if ($GP['products-cost']) {
                                        echo '<th></th>';
                                    }
                                    if ($GP['products-price']) {
                                        echo '<th></th>';
                                    }
                                } ?>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th style="width:65px; text-align:center;"><?= lang('actions') ?></th>
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