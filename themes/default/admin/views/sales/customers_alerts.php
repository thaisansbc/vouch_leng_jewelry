<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
	$v = "";
	if ($this->input->post('customer')) {
		$v .= "&customer=" . $this->input->post('customer');
	}
	// if ($this->input->post('start_date')) {
	// 	$v .= "&start_date=" . $this->input->post('start_date');
    // }
    // if ($this->input->post('end_date')) {
	// 	$v .= "&end_date=" . $this->input->post('end_date');
	// }
?>

<script type="text/javascript">
    $(document).ready(function () {
        function fd(x){
            return x.split('-').reverse().join('/');
        }

        var oTable = $('#SLData').dataTable({
            "aaSorting": [[2, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('sales/getCustomersAlerts/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox}, 
                null, null, null, null, null, 
                {"mRender": currencyFormat},
                {"mRender": fd},
            ],
            "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
                var balance = 0;
                for (var i = 0; i < aaData.length; i++) {
                    if (aaData[aiDisplay[i]][6] != null && aaData[aiDisplay[i]][6] != '') {
                        balance += parseFloat(aaData[aiDisplay[i]][6]);
                    }
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[6].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('gender');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('email');?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('address');?>]", filter_type: "text", data: []},
			{column_number: 7, filter_default_label: "[dd/mm/yyyy]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<style>
    .icheckbox_square-blue {
        float: left;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
            class="fa-fw fa fa-heart"></i><?=lang('list_customers_alerts');?>
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
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results');?></p>
                <div id="form">
                    <?php echo admin_form_open('sales/customers_alerts'); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang('customer'); ?></label>
                                <?php
                                $cus[''] = lang('select') . ' ' . lang('customer');
                                foreach ($companies as $customer) {
                                    $cus[$customer->id] = $customer->name;
                                }
                                echo form_dropdown('customer', $cus, (isset($_POST['customer']) ? $_POST['customer'] : ''), 'class="form-control" id="customer" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('customer') . '"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"><?php echo form_submit('submit_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?></div>
                    </div>
                </div>
                <?php echo form_close(); ?>

                <div class="table-responsive">
                    <table id="SLData" class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="min-width: 5px; width: 5px; text-align: left;">
                                    <input class="checkbox checkft" type="checkbox" name="check"/>
                                </th>
                                <th style="width: 10%;"><?php echo $this->lang->line("customer"); ?></th>
                                <th style="width: 10%;"><?php echo $this->lang->line("gender"); ?></th>
                                <th style="width: 10%;"><?php echo $this->lang->line("phone"); ?></th>
                                <th style="width: 10%;"><?php echo $this->lang->line("email"); ?></th>
                                <th style="width: 35%;"><?php echo $this->lang->line("address"); ?></th>
                                <th style="width: 35%; text-align: right !important;"><?php echo $this->lang->line("balance"); ?></th>
                                <th style="width: 30%;"><?php echo $this->lang->line("end_date"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7"
                                    class="dataTables_empty"><?php echo $this->lang->line("loading_data"); ?>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th style="min-width: 5%; width: 5%; text-align: left;">
                                    <input class="checkbox checkft" type="checkbox" name="check"/>
                                </th>
                                <th style="width: 10%;"></th>
                                <th style="width: 10%;"></th>
                                <th style="width: 10%;"></th>
                                <th style="width: 10%;"></th>
                                <th style="width: 35%;"></th>
                                <th style="width: 35%; text-align: right;"><?php echo $this->lang->line("balance"); ?></th>
                                <th style="width: 30%;"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner) {?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?= form_close()?>
<?php } ?>
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