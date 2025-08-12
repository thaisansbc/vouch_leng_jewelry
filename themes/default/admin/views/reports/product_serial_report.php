<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php

$v = "";

if ($this->input->post('serial_number')) {
    $v .= "&serial_number=" . $this->input->post('serial_number');
}
if ($this->input->post('warehouse')) {
    $v .= "&warehouse=" . $this->input->post('warehouse');
}
if ($this->input->post('status')) {
    $v .= "&status=" . $this->input->post('status');
}
if ($this->input->post('product')) {
    $v .= "&product=" . $this->input->post('product');
}


?>
<script>
    $(document).ready(function () {
        oTable = $('#dmpData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/getProductSerialReport/?v=1' . $v); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
			'fnRowCallback': function (nRow, aData, iDisplayIndex) {
				nRow.id = aData[9]; 
				nRow.className = "product_link";
				return nRow;
			},
            "aoColumns": [null, null, null, null,{"mRender": currencyFormat}, {"mRender": currencyFormat}, null, null, {"mRender": row_status}],
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('product_code');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('product_name')?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('serial_number');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('warehouse')?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('cost')?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('price')?>]", filter_type: "text", data: []},
			{column_number: 6, filter_default_label: "[<?=lang('color')?>]", filter_type: "text", data: []},
			{column_number: 7, filter_default_label: "[<?=lang('description')?>]", filter_type: "text", data: []},
			{column_number: 8, filter_default_label: "[<?=lang('status')?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
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

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-filter"></i><?= lang('product_serial_report'); ?>
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
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
                    <?php echo admin_form_open("reports/product_serial_report"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("product", "suggest_product"); ?>
                                <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ""), 'class="form-control" id="suggest_product"'); ?>
                                <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : "" ?>" id="report_product_id"/>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="serial_number"><?= lang("serial_number"); ?></label>
                                <?php echo form_input('serial_number', (isset($_POST['serial_number']) ? $_POST['serial_number'] : ""), 'class="form-control tip" id="serial_number"'); ?>

                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse")  ?></label>
                                <?php
                                $wh[""] = lang('select').' '.lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>
						<div class="clearfix"></div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="status"><?= lang("status")  ?></label>
                                <?php
                                $st[""] = lang('select').' '.lang('status');
								$st["active"] = lang('active');
								$st["inactive"] = lang('inactive');
                                echo form_dropdown('status', $st, (isset($_POST['status']) ? $_POST['status'] : ""), 'class="form-control" id="status" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("status") . '"');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table id="dmpData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr>
                            <th class="col-xs-1"><?= lang("product_code"); ?></th>
                            <th class="col-xs-2"><?= lang("product_name"); ?></th>
                            <th class="col-xs-2"><?= lang("serial_number"); ?></th>
							<th class="col-xs-1"><?= lang("warehouse") ?></th>
                            <th class="col-xs-1"><?= lang("cost") ?></th>
                            <th class="col-xs-1"><?= lang("price"); ?></th>
							<th class="col-xs-1"><?= lang("color"); ?></th>
							<th class="col-xs-2"><?= lang("description"); ?></th>
							<th class="col-xs-2"><?= lang("status"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="9" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
            window.location.href = "<?=admin_url('reports/getProductSerialReport/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getProductSerialReport/0/xls/?v=1'.$v)?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    openImg(img);
                }
            });
            return false;
        });
    });
</script>
<style type="text/css" media="screen">
	<?php if(!$Owner && !$Admin && !$this->session->userdata('show_cost')){ ?>
		#dmpData td:nth-child(5),#dmpData th:nth-child(5) {
		   display: none !important;
		}
	<?php } ?>
</style>
