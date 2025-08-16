<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
	$v = "";
	if ($this->input->post('biller')) {
		$v .= "&biller=" . $this->input->post('biller');
	}
	if ($this->input->post('user')) {
		$v .= "&user=" . $this->input->post('user');
	}
	if ($this->input->post('year')) {
		$v .= "&year=" . $this->input->post('year');
	}
?>

<script>
    $(document).ready(function () {
        function attachment(x) {
            if (x != null) {
                return '<a href="' + site.base_url + 'assets/uploads/' + x + '" target="_blank"><i class="fa fa-chain"></i></a>';
            }
            return x;
        }

        oTable = $('#RSL').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('payrolls/getSalaries13Report/?v=1' . $v); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[13];
                nRow.className = "salary_13_link";
                return nRow;
            },
            "aoColumns": [{"mRender": fld},null,null, null,{"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat},{"mRender": currencyFormat},{"mRender": currencyFormat},{"mRender": decode_html},{"mRender": row_status},{"mRender": pay_status},{"bSortable": false,"mRender": attachment}],
			"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
				var salary_13 = 0; annual_amount = 0, net_amount = 0, paid = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
					salary_13 += parseFloat(aaData[aiDisplay[i]][4]);
                    annual_amount += parseFloat(aaData[aiDisplay[i]][5]);
					net_amount += parseFloat(aaData[aiDisplay[i]][6]);
					paid += parseFloat(aaData[aiDisplay[i]][7]);
					balance += parseFloat(aaData[aiDisplay[i]][8]);
                }
                var nCells = nRow.getElementsByTagName('th');
				nCells[4].innerHTML = currencyFormat(salary_13);
                nCells[5].innerHTML = currencyFormat(annual_amount);
				nCells[6].innerHTML = currencyFormat(net_amount);
				nCells[7].innerHTML = currencyFormat(paid);
				nCells[8].innerHTML = currencyFormat(balance);
            }
        }).fnSetFilteringDelay().dtFilter([
			{column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
			{column_number: 1, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('month');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
			{column_number: 10, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
			{column_number: 11, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
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
        <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('salaries_13_report'); ?></h2>
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

                    <?php echo admin_form_open("payrolls/salaries_13_report"); ?>
                    <div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label" for="year"><?= lang("year"); ?></label>
								<?php echo form_input('year', (isset($_POST['year']) ? $_POST['year'] : ""), 'class="form-control year" id="year"'); ?>
							</div>
						</div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("biller"); ?></label>
                                <?php
                                $bl[""] = lang('select').' '.lang('biller');
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                <?php
                                $us[""] = lang('select').' '.lang('user');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->last_name . " " . $user->first_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>
                <div class="table-responsive">
                    <table id="RSL" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped">
                        <thead>
							<tr class="active">
								<th><?= lang("date"); ?></th>	
								<th><?= lang("biller"); ?></th>	
								<th><?= lang("year"); ?></th>
								<th><?= lang("created_by"); ?></th>
								<th><?= lang("salary_13"); ?></th>
								<th><?= lang("annual_amount"); ?></th>
								<th><?= lang("net_amount"); ?></th>
								<th><?= lang("paid"); ?></th>
								<th><?= lang("balance"); ?></th>
								<th><?= lang("note"); ?></th>
								<th><?= lang("status"); ?></th>
								<th><?= lang("payment_status"); ?></th>
								<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
							</tr>
                        </thead>
                        <tbody>
							<tr>
								<td colspan="13" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
							</tr>
                        </tbody>
                        <tfoot class="dtFilter">
							<tr class="active">
								<th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
								<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
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
            window.location.href = "<?=admin_url('payrolls/getSalaries13Report/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('payrolls/getSalaries13Report/0/xls/?v=1'.$v)?>";
            return false;
        });
    });
</script>



