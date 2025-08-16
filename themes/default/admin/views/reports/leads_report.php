<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php

$v = '';
/* if($this->input->post('name')){
$v .= "&product=".$this->input->post('product');
} */
if ($this->input->post('product')) {
    $v .= '&product=' . $this->input->post('product');
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
if ($this->input->post('zone_id')) {
    $v .= '&zone_id=' . $this->input->post('zone_id');
}
if ($this->input->post('sources')) {
    $v .= '&sources=' . $this->input->post('sources');
}
if ($this->input->post('age')) {
    $v .= '&age=' . $this->input->post('age');
}
if ($this->input->post('gender')) {
    $v .= '&gender=' . $this->input->post('gender');
}
?>
<script>
      var products;
    $.ajax({
        url: '<?= admin_url('products/getProducts_ajax') ?>',
        dataType: "json",
        success: function (data) {
            products = data;
            
        },
        error: function (xhr, error) {
            console.debug(xhr); 
            console.debug(error);
        }
    });
    $(document).ready(function () {
        oTable = $('#CusData').dataTable({
            "aaSorting": [[0, "asc"], [1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/getLeads/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            
            "aoColumns": [null,null,null, 
            { "fnRender": function (o) { 
                        if(o.aData[3] != null && o.aData[3] != ""){
                            var st = [];
                            var products_by_id = o.aData[3].split(",");
                            products_by_id.forEach((element, index, array) => {
                                st[index] = products.find(x => x.id === element).name;
                            }); 
                            var x = "";
                            var c = ["dodgerblue", "seagreen", "darkorange", "mediumslateblue", "darkviolet", "turquoise", "hotpink", "orange", "chocolate", "salmon", "slategray", "mediumpurple", "tomato", "deepskyblue"];
                            st.forEach((element, index) => {
                                var randomColor = Math.floor(Math.random() * 16777215).toString(16);
                                while(randomColor.length < 6) { randomColor = "0" + randomColor; }
                                x += "<span class='label' style='font-size: 12px; padding: 7px; margin: 5px; display: inline-block; color: white; background-color: #" + randomColor + ";'>" + element + "</span>";
                            });
                            return $(this).innerHTML = "<div style='overflow-wrap: anywhere;'>" + x + "</div>";
                        } else {
                            var x = "";
                            x += "<span class='label' style='font-size: 12px; padding: 7px; margin: 5px; display: inline-block; color: white; background-color: #524D42;'>All</span>";
                            return "<div style='overflow-wrap: anywhere;'>" + x + "</div>";
                      }
                    }          
                },
                
                
                null, 
                null, 
                null, 
                null,
                null, 
                null, 
                {"bSortable": false}
            ],
            
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('company');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('project');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('product');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('email_address');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('gender');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('age');?>]", filter_type: "text", data: []},
            {column_number: 8, filter_default_label: "[<?=lang('sources');?>]", filter_type: "text", data: []},
            {column_number: 9, filter_default_label: "[<?=lang('group');?>]", filter_type: "text", data: []},
        ], "footer");

        $('#form').hide();
        $('.toggle_down').click(function() {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function() {
            $("#form").slideUp();
            return false;
        });
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('leads_report'); ?></h2>
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
                <li class="dropdown"><a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a href="#" id="image" class="tip" title="<?= lang('save_image') ?>"><i class="icon fa fa-file-picture-o"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('view_report_lead'); ?></p>
                <div id="form">

                    <?php echo admin_form_open('reports/leads_report'); ?>
                    <div class="row">
                        <div class="col-sm-4 ">
                            <div class="form-group">
                                <?= lang('product', 'suggest_product'); ?>
                                <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ''), 'class="form-control" id="suggest_product"'); ?>
                                <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : '' ?>" id="report_product_id"/>
                            </div>
                        </div>
                        <div class="col-md-4 ">
                            <div class="form-group">
                                <?= lang("project", "poproject"); ?>
                                <?php
                                $pro[""] = " ";
                                foreach ($projects as $project) {
                                    $pro[$project->project_id] = $project->project_name;
                                }
                                echo form_dropdown('project', $pro, (isset($_POST['project']) ? $_POST['project'] : ''), 'id="poproject" data-placeholder="' . lang("select") . ' ' . lang("project") . '" class="form-control input-tip select" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4 hide">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang('reference_no'); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control tip" id="reference_no"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4 hide">
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
                        <div class="col-sm-4 hide">
                            <div class="form-group">
                                <?= lang('sale_status', 'slsale_status'); ?>
                                <?php
                                $sst = [''=>lang('select') . ' ' . lang('status'),'completed' => lang('completed'), 'consignment' => lang('consignment'), 'pending' => lang('pending')];
                                echo form_dropdown('sale_status', $sst,'', 'class="form-control" id="sale_status" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('status') . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4 hide">
                            <div class="form-group">
                                <label class="control-label" for="payment_status"><?= lang('payment_status'); ?></label>
                                <?php
                                $ps[''] = ['' => lang('select') . ' ' . lang('status'), 'paid' => lang('paid'), 'unpaid' => lang('unpaid')];
                                echo form_dropdown('payment_status', $ps, (isset($_POST['payment_status']) ? $_POST['payment_status'] : ''), 'class="form-control" id="saleman_by" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('status') . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4 hide">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang('customer'); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ''), 'class="form-control" id="customer" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('customer') . '"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang('customer'); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ''), 'class="form-control tip" id="customers"'); ?>
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
                                <label class="control-label" for="address"><?= lang('email_address'); ?></label>
                                <?php echo form_input('address', (isset($_POST['address']) ? $_POST['address'] : ''), 'class="form-control tip" id="address"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4 hide">
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
                        <div class="col-sm-4 hide">
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
                        <div class="col-sm-4 hide">
                            <div class="form-group">
                                <?= lang('start_date', 'start_date'); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4 hide">
                            <div class="form-group">
                                <?= lang('end_date', 'end_date'); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4 hide">
                            <div class="form-group">
                                <?= lang('sale_type', 'sale_type'); ?>
                                <?php $sales = ['' => 'ALL','1' => 'POS', '0' => 'SALE']; ?>
                                <?= form_dropdown('sale_type', $sales, (isset($_POST['sale_type']) ? $_POST['sale_type'] : ''), 'class="form-control tip" id="sale_type"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4 hide">
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
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="sources"><?= lang('sources'); ?></label>
                                <?php 
                                $get_fields = $this->site->getcustomfield('sources');
                                $field = [''];
                                if (!empty($get_fields)) {
                                    foreach ($get_fields as $field_id) {
                                        $field[$field_id->id] = $field_id->name;
                                    }
                                }
                                echo form_dropdown('sources',$field,(isset($_POST['sources']) ? $_POST['sources'] : ''), 'class="form-control select" id="sources"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('age', 'age'); ?>
                                <?php 
                                $ages = [
                                    '' => lang('general'),
                                    '10_20' => '10 To 20 '.lang('year'), 
                                    '21_30' => '21 To 30 '.lang('year'),
                                    '31_40' => '31 To 40 '.lang('year'),
                                    '41_50' => '41 To 50 '.lang('year'),
                                    '51_60' => '51 To 60 '.lang('year'),
                                    '61_70' => '61 To 70 '.lang('year'),
                                    '71_80' => '71 To 80 '.lang('year'),
                                    '81_100'=> '81 To 100 '.lang('year'), 
                                ]; ?>

                                <?= form_dropdown('age', $ages, (isset($_POST['age']) ? $_POST['age'] : ''), 'class="form-control tip" id="age"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("gender", "gender"); ?>
                                <?php
                                $gender = array(
                                    '',
                                    'Male' => lang('Male') , 'Female'=> lang('Female'));
                                echo form_dropdown('gender', $gender, (isset($_POST['gender']) ? $_POST['gender'] : ''), 'class="form-control select" id="gender" style="width:100%;"');
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
                <div class="table-responsive">
                    <table id="CusData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-condensed table-hover table-striped reports-table">
                        <thead>
                        <tr class="primary">
                            <th><?= lang('company'); ?></th>
                            <th><?= lang('name'); ?></th>
                            <th><?= lang('project'); ?></th>
                            <th><?= lang('product'); ?></th>
                            <th><?= lang('phone'); ?></th>
                            <th><?= lang('email_address'); ?></th>
                            <th><?= lang('gender'); ?></th>
                            <th><?= lang('age'); ?></th>
                            <th><?= lang('source'); ?></th>
                            <th><?= lang('group'); ?></th>
                            <th style="width:85px;"><?= lang('actions'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="width:85px;"><?= lang('actions'); ?></th>
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
            window.location.href = "<?=admin_url('reports/getLeads/pdf')?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getLeads/0/xls')?>";
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