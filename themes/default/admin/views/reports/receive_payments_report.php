<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
$v = "";
if ($this->input->post('paid_by')) {
    $v .= "&paid_by=" . $this->input->post('paid_by');
}
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}
if ($this->input->post('project')) {
    $v .= "&project=" . $this->input->post('project');
}
if ($this->input->post('user')) {
    $v .= "&user=" . $this->input->post('user');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
if ($this->input->post('type')) {
    $v .= "&type=" . $this->input->post('type');
}
?>
<script>
    $(document).ready(function () {
        oTable = $('#ReceivePaymentsData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/getReceivePaymentsReport/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [null,{"mRender": fld}, null, null,null, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": currencyFormat}, {"mRender": row_status}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var amount = 0, pending_amount = 0, checked_amount = 0, verified_amount = 0, approve_amount = 0, balance = 0;
                for (var i = 0; i < aaData.length; i++) {
					amount += parseFloat(aaData[aiDisplay[i]][5]);
					pending_amount += parseFloat(aaData[aiDisplay[i]][6]);
					checked_amount += parseFloat(aaData[aiDisplay[i]][7]);
					approve_amount += parseFloat(aaData[aiDisplay[i]][8]);
					verified_amount += parseFloat(aaData[aiDisplay[i]][9]);
					balance += parseFloat(aaData[aiDisplay[i]][10]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[5].innerHTML = currencyFormat(parseFloat(amount));
				nCells[6].innerHTML = currencyFormat(parseFloat(pending_amount));
				nCells[7].innerHTML = currencyFormat(parseFloat(checked_amount));
				nCells[8].innerHTML = currencyFormat(parseFloat(approve_amount));
				nCells[9].innerHTML = currencyFormat(parseFloat(verified_amount));
				nCells[10].innerHTML = currencyFormat(parseFloat(balance));
            }
        }).fnSetFilteringDelay().dtFilter([
			{column_number: 0, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('received_by');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('paid_by');?>]", filter_type: "text", data: []},
        ], "footer");

    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-money"></i><?= lang('receive_payments_report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            } ?>
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
                <p class="introtext"><?= lang('customize_report'); ?></p>
                <div id="form">
                    <?php echo admin_form_open("reports/receive_payments_report"); ?>
                    <div class="row">
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="rbiller"><?= lang("biller"); ?></label>
                                <?php
                                $bl[''] = lang("select")." ".lang("biller");
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="rbiller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
						<?php if($Settings->project == 1){ ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("project", "project"); ?>
									<div class="no-project">
										<?php
										$pj[''] = '';
										if (isset($projects) && $projects != false) {
                                            foreach ($projects as $project) {
                                                $pj[$project->id] = $project->name;
                                            }
                                        }
										echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : isset($Settings->project_id)? $Settings->project_id: ''), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
										?>
									</div>
								</div>
							</div>
						<?php } ?>
						<div class="col-sm-4">
                            <div class="form-group">
                            <?=lang("paid_by", "paid_by");?>
                                <select name="paid_by" id="paid_by" class="form-control paid_by">
                                    <?= $this->bpas->cash_opts($this->input->post('paid_by') ? $this->input->post('paid_by') : 0, false, true); ?>
                                </select>
                            </div>
                        </div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("received_by"); ?></label>
                                <?php
                                $us[""] = lang('select').' '.lang('received_by');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->last_name . " " . $user->first_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("type", "type"); ?>
								<?php
									$opt_type[""] = lang("select")." ".lang("type");
									$opt_type["sale"] = lang("sale");
									$opt_type["pos"] = lang("pos");
									echo form_dropdown('type', $opt_type, (isset($_POST['type']) ? $_POST['type'] : ''), 'id="type" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("type") . '"  class="form-control input-tip select"');
								?>
							</div>
						</div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control datetime" id="end_date"'); ?>
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
                    <table id="ReceivePaymentsData"class="table table-bordered table-hover table-striped table-condensed reports-table">
                        <thead>
							<tr>
								<th><?= lang("biller"); ?></th>
								<th><?= lang("date"); ?></th>
								<th><?= lang("reference_no"); ?></th>
								<th><?= lang("received_by"); ?></th>
								<th><?= lang("paid_by"); ?></th>
								<th><?= lang("amount"); ?></th>
								<th><?= lang("pending"); ?></th>
								<th><?= lang("checked"); ?></th>
								<th><?= lang("approved"); ?></th>
								<th><?= lang("verified"); ?></th>
								<th><?= lang("balance"); ?></th>
								<th><?= lang("status"); ?></th>
							</tr>
                        </thead>
                        <tbody>
							<tr>
								<td colspan="12" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
							</tr>
                        </tbody>
                        <tfoot class="dtFilter">
							<tr class="active">
								<th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
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
		$('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
		
        var customer_id = "<?= isset($_POST['customer'])?$_POST['customer']:0 ?>";
		if (customer_id > 0) {
		  $('#customer_id').val(customer_id).select2({
			minimumInputLength: 1,
			data: [],
			initSelection: function (element, callback) {
			  $.ajax({
				type: "get", async: false,
				url: site.base_url+"customers/getCustomer/" + $(element).val(),
				dataType: "json",
				success: function (data) {
				  callback(data[0]);
				}
			  });
			},
			ajax: {
			  url: site.base_url + "customers/suggestions",
			  dataType: 'json',
			  deietMillis: 15,
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
		}else{
		  $('#customer_id').select2({
			minimumInputLength: 1,
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
		}
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getReceivePaymentsReport/0/xls/?v=1'.$v)?>";
            return false;
        });
		$("#rbiller").change(biller); biller();
		function biller(){
			var biller = $("#rbiller").val();
			var project = "<?= (isset($_POST['project']) ? trim($_POST['project']) : ''); ?>";
			$.ajax({
				url : "<?= admin_url("reports/get_project") ?>",
				type : "GET",
				dataType : "JSON",
				data : { biller : biller, project : project },
				success : function(data){
					if(data){
						$(".no-project").html(data.result);
						$("#project").select2();
					}
				}
			})
		}
    });
</script>
