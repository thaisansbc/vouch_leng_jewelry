<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php

$v = '';
/* if($this->input->post('name')){
  $v .= "&product=".$this->input->post('product');
  } */
if ($this->input->post('product')) {
    $v .= '&product=' . $this->input->post('product');
}
if ($this->input->post('category')) {
    $v .= '&category=' . $this->input->post('category');
}
if ($this->input->post('brand')) {
    $v .= '&brand=' . $this->input->post('brand');
}
if ($this->input->post('reference_no')) {
    $v .= '&reference_no=' . $this->input->post('reference_no');
}
if ($this->input->post('project')) {
    $v .= "&project=" . $this->input->post('project');
}
if ($this->input->post('customer')) {
    $v .= '&customer=' . $this->input->post('customer');
}
if ($this->input->post('sale_status')) {
    $v .= '&sale_status=' . $this->input->post('sale_status');
}
if ($this->input->post('phone')) {
    $v .= '&phone=' . $this->input->post('phone');
}
if ($this->input->post('address')) {
    $v .= '&address=' . $this->input->post('address');
}
if ($this->input->post('biller')) {
    $v .= '&biller=' . $this->input->post('biller');
}
if ($this->input->post('warehouse')) {
    $v .= '&warehouse=' . $this->input->post('warehouse');
}
if ($this->input->post('user')) {
    $v .= '&user=' . $this->input->post('user');
}
if ($this->input->post('serial')) {
    $v .= '&serial=' . $this->input->post('serial');
}
if ($this->input->post('start_date')) {
    $v .= '&start_date=' . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= '&end_date=' . $this->input->post('end_date');
}
if ($this->input->post('sale_type')) {
    $v .= '&sale_type=' . $this->input->post('sale_type');
}
if ($this->input->post('payment_status')) {
    $v .= '&payment_status=' . $this->input->post('payment_status');
}
if ($this->input->post('saleman_by')) {
    $v .= "&saleman=" . $this->input->post('saleman_by');
}
if ($this->input->post('zone_id')) {
    $v .= '&zone_id=' . $this->input->post('zone_id');
}
?>
<script>
    $(document).ready(function () {
        oTable = $('#SlRData').dataTable({
            "aaSorting": [[0, "desc"],[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/getSalesReport/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[14];
                nRow.className = (aData[8] > 0) ? "invoice_link2" : "invoice_link2 warning";
                return nRow;
            },
            "aoColumns": [
                {"mRender": fld}, null, null, null, null, null, null, {"mRender": formatQuantity}, 
                {"bSearchable": false, "mRender": pqFormat },
                {"mRender": row_status}, 
                {"mRender": currencyFormat}, 
                {"mRender": currencyFormat}, 
                {"mRender": currencyFormat}, 
                {"mRender": row_status}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var rgtotal = 0, gtotal = 0, paid = 0, balance = 0, customer_total = 0;
                for (var i = 0; i < aaData.length; i++) {
                    gtotal += parseFloat(aaData[aiDisplay[i]][10]);
                    paid += parseFloat(aaData[aiDisplay[i]][11]);
                    balance += parseFloat(aaData[aiDisplay[i]][12]);
                    if(aaData[aiDisplay[i]][7] != null){
                        customer_total += parseFloat(aaData[aiDisplay[i]][7]);
                    }
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[7].innerHTML = currencyFormat(parseFloat(customer_total));
                nCells[10].innerHTML = currencyFormat(parseFloat(gtotal));
                nCells[11].innerHTML = currencyFormat(parseFloat(paid));
                nCells[12].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('project');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('address');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('customer').' (QTY)';?>]", filter_type: "text", data: []},
            {column_number: 9, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
            {column_number: 13, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        <?php if ($this->input->post('customer')) { ?>
        $('#customer').val(<?= $this->input->post('customer') ?>).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "customers/suggestions/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data.results[0]);
                    }
                });
            },
            ajax: {
                url: site.base_url + "customers/suggestions",
                dataType: 'json',
                quietMillis: 15,
                data: function (term, page) {
                    return {
                        term: term,
                        limit: 10
                    };
                },
                results: function (data, page) {
                    if (data.results != null) {
                        return {results: data.results};
                    } else {
                        return {results: [{id: '', text: 'No Match Found'}]};
                    }
                }
            }
        });
        $('#customer').val(<?= $this->input->post('customer') ?>);
        <?php } ?>
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
<style type="text/css">
    body {
        position: static !important;
        overflow-y: auto !important;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('sales_report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
            }
            ?>
        </h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="preview" class="tip" title="<?= lang('print') ?>">
                        <i class="icon fa fa-print"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="xls2" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('customize_report'); ?></p>
                <div id="form">
                    <?php echo admin_form_open('reports/sales'); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('product', 'suggest_product'); ?>
                                <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ''), 'class="form-control" id="suggest_product"'); ?>
                                <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : '' ?>" id="report_product_id"/>
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
                                        $form_category = ($n ? '<select id="category" name="category" class="form-control select" style="width: 100%" placeholder="' . lang('select') . ' ' . lang('category') . '" >
                                        <option value="" selected>' . lang('select') . ' ' . lang('category') . '</option>' : '');
                                        foreach ($data as $key => $categories) {
                                            if (!empty($categories->children)) {
                                                $form_category .= '<option style="color:red;" value="' . $categories->id . '">' . $str . $categories->name . '</option>';
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("brand", "brand"); ?>
                                <?php
                                $br_opt[""] = lang('select') . ' ' . lang('brand');
                                foreach ($brands as $brand) {
                                    $br_opt[$brand->id] = $brand->name;
                                }
                                echo form_dropdown('brand', $br_opt, (isset($_POST['brand']) ? $_POST['brand'] : ''), 'id="poproject" data-placeholder="' . lang("select") . ' ' . lang("brand") . '" class="form-control input-tip select" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("project", "poproject"); ?>
                                <?php
                                $pro[""] = "";
                                foreach ($projects as $project) {
                                    $pro[$project->project_id] = $project->project_name;
                                }
                                echo form_dropdown('project', $pro, (isset($_POST['project']) ? $_POST['project'] : ''), 'id="poproject" data-placeholder="' . lang("select") . ' ' . lang("project") . '" class="form-control input-tip select" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang('reference_no'); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control tip" id="reference_no"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang('created_by'); ?></label>
                                <?php
                                $us[''] = lang('select') . ' ' . lang('user');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->first_name . ' ' . $user->last_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ''), 'class="form-control" id="user" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('user') . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('sale_status', 'slsale_status'); ?>
                                <?php
                                if ($Settings->sale_consignment) {
                                    $sst = [''=>lang('select') . ' ' . lang('status'), 'completed' => lang('completed'), 'consignment' => lang('consignment'), 'pending' => lang('pending')];
                                } else {
                                    $sst = [''=>lang('select') . ' ' . lang('status'), 'completed' => lang('completed'), 'pending' => lang('pending')];
                                }
                                echo form_dropdown('sale_status', $sst,'', 'class="form-control" id="sale_status" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('status') . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="payment_status"><?= lang('payment_status'); ?></label>
                                <?php
                                $ps[''] = ['' => lang('select') . ' ' . lang('status'), 'paid' => lang('paid'), 'unpaid' => lang('unpaid')];
                                echo form_dropdown('payment_status', $ps, (isset($_POST['payment_status']) ? $_POST['payment_status'] : ''), 'class="form-control" id="payment_status" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('status') . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang('customer'); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ''), 'class="form-control" id="customer" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('customer') . '"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="phone"><?= lang('phone'); ?></label>
                                <?php echo form_input('phone', (isset($_POST['phone']) ? $_POST['phone'] : ''), 'class="form-control tip" id="phone"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="address"><?= lang('address'); ?></label>
                                <?php echo form_input('address', (isset($_POST['address']) ? $_POST['address'] : ''), 'class="form-control tip" id="address"'); ?>
                            </div>
                        </div>
                        <?php if ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) { ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang('biller'); ?></label>
                                <?php
                                $bl[''] = lang('select') . ' ' . lang('biller');
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('biller') . '"');
                                ?>
                            </div>
                        </div>
                        <?php } elseif (!empty($billers) && $this->session->userdata('biller_id')) { ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang('biller'); ?></label>
                                <?php
                                $bl['']    = lang('select') . ' ' . lang('biller');
                                $biller_id = explode(',', $this->session->userdata('biller_id'));
                                foreach ($billers as $biller) {
                                    foreach ($biller_id as $key => $value) {
                                        if ($biller->id == $value) {
                                            $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;  
                                        }
                                    }   
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('biller') . '"');
                                ?>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) { ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang('warehouse'); ?></label>
                                <?php
                                $wh[''] = lang('select') . ' ' . lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '"');
                                ?>
                            </div>
                        </div>
                        <?php } elseif (!empty($warehouses) && $this->session->userdata('warehouse_id')) { ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang('warehouse'); ?></label>
                                <?php
                                $wh[''] = lang('select') . ' ' . lang('warehouse');
                                $warehouse_id = explode(',', $this->session->userdata('warehouse_id'));
                                foreach ($warehouses as $warehouse) {
                                    foreach ($warehouse_id as $key => $value) {
                                        if ($warehouse->id == $value) {
                                            $wh[$warehouse->id] = $warehouse->name;        
                                        }
                                    }   
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '"');
                                ?>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($Settings->product_serial) { ?>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang('serial_no', 'serial'); ?>
                                    <?= form_input('serial', '', 'class="form-control tip" id="serial"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('start_date', 'start_date'); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('end_date', 'end_date'); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('sale_type', 'sale_type'); ?>
                                <?php $sales = ['' => 'ALL','1' => 'POS', '0' => 'SALE']; ?>
                                <?= form_dropdown('sale_type', $sales, (isset($_POST['sale_type']) ? $_POST['sale_type'] : ''), 'class="form-control tip" id="sale_type"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="saleman_by"><?= lang('saleman'); ?></label>
                                <?php
                                $sm[''] = lang('select') . ' ' . lang('saleman');
                                if (!empty($salemans)) {
                                    foreach ($salemans as $saleman_) {
                                        $sm[$saleman_->id] = $saleman_->first_name . ' ' . $saleman_->last_name;
                                    }
                                }
                                echo form_dropdown('saleman_by', $sm, (isset($_POST['saleman_by']) ? $_POST['saleman_by'] : ''), 'class="form-control" id="saleman_by" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('saleman') . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="zone_id"><?= lang('zone'); ?></label>
                                <?php
                                $zns[''] = lang('select') . ' ' . lang('zone');
                                if($zones){
                                    foreach ($zones as $zone) {
                                        $zns[$zone->p_id] = $zone->p_name && $zone->p_name != '-' ? $zone->p_name : $zone->p_name;
                                        if($zone->c_id != null){
                                            $child_zones_id = explode("___", $zone->c_id);
                                            $child_zones_name = explode("___", $zone->c_name);

                                            foreach ($child_zones_id as $key => $value) {
                                                $zns[$value] = "&emsp;" . $child_zones_name[$key];
                                            }
                                        }
                                    }
                                }
                                echo form_dropdown('zone_id', $zns, (isset($_POST['zone_id']) ? $_POST['zone_id'] : ''), 'class="form-control" id="zone_id" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('zone') . '"');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table id="SlRData" class="table table-hover table-striped table-condensed reports-table">
                        <thead>
                        <tr>
                            <th style="width: 50px;"><?= lang('date'); ?></th>
                            <th><?= lang('project'); ?></th>
                            <th><?= lang('reference_no'); ?></th>
                            <th style="width: 50px;"><?= lang('biller'); ?></th>
                            <th style="width: 50px;"><?= lang('customer'); ?></th>
                            <th style="width: 40px;"><?= lang('phone'); ?></th>
                            <th style="width: 20px;"><?= lang('address'); ?></th>
                            <th><?= lang('customer').' (Qty)'; ?></th>
                            <th><?= lang('product_qty'); ?></th>
                            <th><?= lang('sale_status'); ?></th>
                            <th><?= lang('grand_total'); ?></th>
                            <th><?= lang('paid'); ?></th>
                            <th><?= lang('balance'); ?></th>
                            <th><?= lang('payment_status'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="width: 50px;"></th>
                            <th></th>
                            <th></th>
                            <th style="width: 50px;"></th>
                            <th style="width: 50px;"></th>
                            <th style="width: 40px;"></th>
                            <th style="width: 20px;"></th>
                            <th><?= lang('customer_total'); ?></th>
                            <th><?= lang('product_qty'); ?></th>
                            <th></th>
                            <th><?= lang('grand_total'); ?></th>
                            <th><?= lang('paid'); ?></th>
                            <th><?= lang('balance'); ?></th>
                            <th></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getSalesReport/pdf/?v=1' . $v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getSalesReport/0/xls/?v=1' . $v)?>";
            return false;
        });
        $('#xls2').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getSalesReport/0/0/0/0/xls2/?v=1' . $v)?>";
            return false;
        });
        $('#preview').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getSalesReport/0/0/preview/?v=1' . $v)?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    openImg(canvas.toDataURL());
                }
            });
            return false;
        });
    });
</script>
