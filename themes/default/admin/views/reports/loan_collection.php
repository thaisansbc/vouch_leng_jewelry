<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
	$v = "";
	if ($this->input->post('reference_no')) {
		$v .= "&reference_no=" . $this->input->post('reference_no');
	}
	if ($this->input->post('loan_reference_no')) {
		$v .= "&loan_reference_no=" . $this->input->post('loan_reference_no');
	}
	if ($this->input->post('borrower')) {
		$v .= "&borrower=" . $this->input->post('borrower');
	}
	if ($this->input->post('biller')) {
		$v .= "&biller=" . $this->input->post('biller');
	}
	if ($this->input->post('product')) {
		$v .= "&product=" . $this->input->post('product');
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
	if ($this->input->post('start_date_loan')) {
		$v .= "&start_date_loan=" . $this->input->post('start_date_loan');
	}
	if ($this->input->post('end_date_loan')) {
		$v .= "&end_date_loan=" . $this->input->post('end_date_loan');
	}
	if ($this->input->post('currency')) {
		$v .= "&currency=" . $this->input->post('currency');
	}
?>
<script>
    $(document).ready(function () {
		oTable = $('#LoanCollectionTable').dataTable({
            "aaSorting": [[1, "desc"], [3, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?=admin_url('reports/getLoanCollectionReport?v=1&'. $v)?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                return nRow;
            },
            "aoColumns": [
			{"mRender" : fld},
			{"mRender" : fsd},
			null,
			null,
			null,
			null,
			{"mRender" : currencyFormat},
			{"mRender" : currencyFormat},
			{"mRender" : currencyFormat},
			{"mRender" : currencyFormat},
			{"mRender" : currencyFormat},
			{"mRender" : currencyFormat},
			{"mRender" : currencyFormat},
			{"mRender" : currencyFormat},
			{"mRender" : currency_status},
			{"sClass" : "center"},
			{"mRender" : row_status}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
				var pprincipal = 0, pinterest = 0, ptotal = 0, principal =0, interest =0, payment = 0, fee_charge = 0, penalty = 0;
				for (var i = 0; i < aaData.length; i++) {
					pprincipal += parseFloat(aaData[aiDisplay[i]][6]);
					pinterest += parseFloat(aaData[aiDisplay[i]][7]);
					ptotal += parseFloat(aaData[aiDisplay[i]][8]);
					principal += parseFloat(aaData[aiDisplay[i]][9]);
					interest += parseFloat(aaData[aiDisplay[i]][10]);
					payment += parseFloat(aaData[aiDisplay[i]][11]);
					penalty += parseFloat(aaData[aiDisplay[i]][12]);
					fee_charge += parseFloat(aaData[aiDisplay[i]][13]);
				}
				var nCells = nRow.getElementsByTagName('th');
				nCells[6].innerHTML = currencyFormat(parseFloat(pprincipal));
				nCells[7].innerHTML = currencyFormat(parseFloat(pinterest));
				nCells[8].innerHTML = currencyFormat(parseFloat(ptotal));
				nCells[9].innerHTML = currencyFormat(parseFloat(principal));
				nCells[10].innerHTML = currencyFormat(parseFloat(interest));
				nCells[11].innerHTML = currencyFormat(parseFloat(payment));
				nCells[12].innerHTML = currencyFormat(parseFloat(penalty));
				nCells[13].innerHTML = currencyFormat(parseFloat(fee_charge));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('deadline');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('loan_reference_no');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('borrower_code');?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('borrower');?>]", filter_type: "text", data: []},
			{column_number: 14, filter_default_label: "[<?=lang('currency');?>]", filter_type: "text", data: []},
			{column_number: 15, filter_default_label: "[<?=lang('paid_by');?>]", filter_type: "text", data: []},
			{column_number: 16, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<?php echo admin_form_open("reports/loan_collection", ' id="form-submit" '); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('loan_collection_report'); ?></h2>
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
					<div class="row">
						<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("reference_no", "reference_no"); ?>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>
                            </div>
                        </div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("loan_reference_no", "loan_reference_no"); ?>
                                <?php echo form_input('loan_reference_no', (isset($_POST['loan_reference_no']) ? $_POST['loan_reference_no'] : ""), 'class="form-control tip" id="loan_reference_no"'); ?>
                            </div>
                        </div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="borrower"><?= lang("borrower"); ?></label>
                                <?php echo form_input('borrower', (isset($_POST['borrower']) ? $_POST['borrower'] : ""), 'class="form-control" id="borrower" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("borrower") . '"'); ?>
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
                                <label class="control-label" for="user"><?= lang("currency"); ?></label>
                                <?php
                                $cy[""] = lang('select').' '.lang('currency');
                                foreach ($currencies as $currency) {
                                    $cy[$currency->code] = $currency->name;
                                }
                                echo form_dropdown('currency', $cy, (isset($_POST['currency']) ? $_POST['currency'] : ""), 'class="form-control" id="currency" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("currency") . '"');
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
						<div class="col-sm-4">
							<div class="form-group">
								<label for="product"><?= lang('product') ?></label>
								<?php
									$tp[''] = lang('select').' '.lang('product');
									if($products){
										foreach ($products as $product) {
											$tp[$product->id] = $product->name;
										}
									}
									echo form_dropdown('product', $tp, (isset($_POST['product']) ? $_POST['product'] : 0), ' class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("product") . '" style="width:100%;" ');
								?>
							</div>
						</div>
						<!-- $this->bms->hrld(date("Y-m-d H:i")) -->
						<div class="col-sm-4">
								<div class="row">
								<div class="col-sm-6">
		                                <?= lang("start_date", "start_date"); ?>(Deadline)
		                                <?php echo form_input('start_date_loan', (isset($_POST['start_date_loan']) ? $_POST['start_date_loan'] : ''), 'class="form-control datetime" id="start_date_loan"'); ?>
                            	</div>
								<div class="col-sm-6">
	                                <?= lang("end_date", "end_date"); ?>(Deadline)
	                                <?php echo form_input('end_date_loan', (isset($_POST['end_date_loan']) ? $_POST['end_date_loan'] : ''), 'class="form-control datetime" id="end_date_loan"'); ?>
	                            </div>
	                            </div>
                        </div>
                        <div class="col-sm-4">
								<div class="row">
								<div class="col-sm-6">
		                            <div class="form-group">
		                                <?= lang("start_date", "start_date"); ?>(Payment)
		                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
		                            </div>
		                        </div>
		                        <div class="col-sm-6">
		                            <div class="form-group">
		                                <?= lang("end_date", "end_date"); ?>(Payment)
		                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
		                            </div>
		                        </div>
		                	</div>
		                </div>
						
					</div>
					<div class="form-group">
                        <div class="controls"> 
							<?php echo form_submit('submit_report', $this->lang->line("search"), 'class="btn btn-primary"'); ?> 
						</div>
                    </div>
				</div>
				<?php echo form_close(); ?>
                <div class="table-responsive">
                    <table id="LoanCollectionTable" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="width:120px;"><?= lang("payment_date"); ?></th>
                            <th style="width:120px;"><?= lang("deadline"); ?></th>
                            <th style="width:120px;"><?= lang("reference_no"); ?></th>
							<th style="width:120px;"><?= lang("loan_reference_no"); ?></th>
							<th style="width:100px;"><?= lang("borrower_code"); ?></th>
                            <th style="width:100px;"><?= lang("borrower"); ?></th>
                            <th style="width:100px;"><?= lang("principal") ?></th>
							<th style="width:100px;"><?= lang("interest") ?></th>
							<th style="width:100px;"><?= lang("total_amount") ?></th>
							<th style="width:100px;"><?= lang("principal_paid") ?></th>
							<th style="width:100px;"><?= lang("interest_paid") ?></th>
							<th style="width:100px;"><?= lang("payment_paid") ?></th>
							<th style="width:100px;"><?= lang("fee_charge_paid") ?></th>
							<th style="width:100px;"><?= lang("penalty_paid") ?></th>
							<th style="width:100px;"><?= lang("currency") ?></th>
							<th style="width:80px;"><?= lang("paid_by"); ?></th>
                            <th style="width:20px;"><?= lang("type"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
							<tr>
								<td colspan="13" class="dataTables_empty"><?= lang("loading_data"); ?></td>
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
		$('#form').hide();
		$('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
		$('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getLoanCollectionReport/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getLoanCollectionReport/0/xls/?v=1'.$v)?>";
            return false;
        });
		
		$('form[data-toggle="validator"]').bootstrapValidator({ feedbackIcons:{valid: 'fa fa-check',invalid: 'fa fa-times',validating: 'fa fa-refresh'}, excluded: [':disabled'] });
		var borrower = "<?= isset($_POST['borrower'])?$_POST['borrower']:0; ?>";
		$('#borrower').val(borrower).select2({
		   minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url+"loans/getBorrower/" + $(element).val(),
                    dataType: "json",
                    success: function (data) {
                        callback(data[0]);
                    }
                });	
            },ajax: {
				url: site.base_url+"loans/borrower_suggestions",
				dataType: 'json',
				quietMillis: 15,
				data: function (term, page) {
					return {
						term: term,
						limit: 10
					};
				},
				results: function (data, page) {
					if(data.results != null) {
						return { results: data.results };
					} else {
						return { results: [{id: '', text: 'No Match Found'}]};
					}
				}
			}
		});
    });
</script>