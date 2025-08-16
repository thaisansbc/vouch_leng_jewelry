<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";
if ($this->input->post('reference_no')) {
    $v .= "&reference_no=" . $this->input->post('reference_no');
}
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}
if ($this->input->post('project')) {
    $v .= "&project=" . $this->input->post('project');
}
if ($this->input->post('warehouse')) {
    $v .= "&warehouse=" . $this->input->post('warehouse');
}
if ($this->input->post('saleman')) {
    $v .= "&saleman=" . $this->input->post('saleman');
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
?>
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
        <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('fuel_sale_details_report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
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
                    <?php echo admin_form_open("reports/fuel_sale_details"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>

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
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $wh[""] = lang('select').' '.lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
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
                                        if(isset($projects) && $projects){
                                            foreach ($projects as $project) {
                                                $pj[$project->id] = $project->name;
                                            }
                                        }
                                        
                                        echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("saleman"); ?></label>
                                <?php
                                $opsaleman[""] = lang('select').' '.lang('saleman');
                                foreach ($salemans as $saleman) {
                                    $opsaleman[$saleman->id] = $saleman->last_name . " " . $saleman->first_name;
                                }
                                echo form_dropdown('saleman', $opsaleman, (isset($_POST['saleman']) ? $_POST['saleman'] : ""), 'class="form-control" id="saleman" data-placeholder="' . $this->lang->line("saleman") . " " . $this->lang->line("saleman") . '"');
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
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <div class="clearfix"></div>
                <div class="table-responsive">
                    <table id="FuelSaleDetailData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped dataTable">
                        <thead>
                        <tr class="active">
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
							<th><?= lang("biller"); ?></th>
							<th><?= lang("saleman"); ?></th>
							<th><?= lang("time"); ?></th>
							<th><?= lang("using_qty"); ?></th>
							<th><?= lang("customer_qty"); ?></th>
                            <th><?= lang("customer_amount"); ?></th>
							<th><?= lang("fuel_qty"); ?></th>
                            <th><?= lang("fuel_amount"); ?></th>
                            <th><?= lang("cash_change"); ?></th>
							<th><?= lang("cash_submit"); ?></th>
							<th><?= lang("credit_amount"); ?></th>
							<th><?= lang("bank_amount"); ?></th>
							<th><?= lang("different"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
							<?php 
								if($fuel_sales){
                                    $html = '';
                                    $total = 0;
                                    $total_cash = 0;
                                    $total_cash_open = 0;
									$total_different = 0;
									$total_credit = 0;
									$total_bank = 0;
									foreach($fuel_sales as $fuel_sale){
                                        $total += $fuel_sale->total;
                                        $total_cash_open += $fuel_sale->total_cash_open;
										$total_cash += $fuel_sale->total_cash;
										$total_credit += $fuel_sale->credit_amount;
                                        $total_bank += $fuel_sale->bank_amount;
										$different = ($fuel_sale->bank_amount + $fuel_sale->credit_amount + $fuel_sale->total_cash - $fuel_sale->total_cash_open - $fuel_sale->total);
                                        $total_different += $different;
										$html .= '<tr>
												<td style="text-align:center;">'.$this->bpas->hrld($fuel_sale->date).'</td>
												<td style="text-align:center;">'.$fuel_sale->reference_no.'</td>
												<td style="text-align:left;">'.$fuel_sale->biller.'</td>
												<td style="text-align:center;">'.$fuel_sale->saleman.'</td>
												<td style="text-align:center;">'.$fuel_sale->time.'</td>
												<td style="text-align:center;">'.$this->bpas->formatQuantity($fuel_sale->using_qty).'</td>
												<td style="text-align:center;">'.$this->bpas->formatQuantity($fuel_sale->customer_qty).'</td>
												<td style="text-align:right;">'.$this->bpas->formatMoney($fuel_sale->customer_amount).'</td>
												<td style="text-align:center;">'.$this->bpas->formatQuantity($fuel_sale->quantity).'</td>
												<td style="text-align:right;">'.$this->bpas->formatMoney($fuel_sale->total).'</td>
                                                <td style="text-align:right;">'.$this->bpas->formatMoney($fuel_sale->total_cash_open).'</td>
                                                <td style="text-align:right;">'.$this->bpas->formatMoney($fuel_sale->total_cash).'</td>
												<td style="text-align:right;">'.$this->bpas->formatMoney($fuel_sale->credit_amount).'</td>
												<td style="text-align:right;">'.$this->bpas->formatMoney($fuel_sale->bank_amount).'</td>
												<td style="text-align:right;">'.$this->bpas->formatMoney($different).'</td>
											</tr>';
										
										$sale_fuel_items = $this->reports_model->getSaleFuelItemsDetails($fuel_sale->id);
										if($sale_fuel_items){
												$html .='<tr>
														<td colspan="6"></td>
														<td style="text-align:center; font-weight:bold; text-decoration:underline;">'.lang('item').'</td>
														<td style="text-align:center; font-weight:bold; text-decoration:underline;">'.lang('tank').'</td>
														<td style="text-align:center; font-weight:bold; text-decoration:underline;">'.lang('nozzle_no').'</td>
														<td style="text-align:center; font-weight:bold; text-decoration:underline;">'.lang('nozzle_start_no').'</td>
														<td style="text-align:center; font-weight:bold; text-decoration:underline;">'.lang('nozzle_end_no').'</td>
														<td style="text-align:center; font-weight:bold; text-decoration:underline;">'.lang('using_qty').'</td>
														<td style="text-align:center; font-weight:bold; text-decoration:underline;">'.lang('customer_qty').'</td>
														<td style="text-align:center; font-weight:bold; text-decoration:underline;">'.lang('fuel_qty').'</td>
														<td style="text-align:center; font-weight:bold; text-decoration:underline;">'.lang('total_qty').'</td>
													<tr>';
											foreach($sale_fuel_items as $fuel_item){
												$html .='<tr>
															<td colspan="6"></td>
															<td style="text-align:left;">'.$fuel_item->item.'</td>
															<td style="text-align:left;">'.$fuel_item->tank.'</td>
															<td style="text-align:center;">'.$fuel_item->nozzle_no.'</td>
															<td style="text-align:center;">'.$this->bpas->formatQuantity($fuel_item->nozzle_start_no).'</td>
															<td style="text-align:center;">'.$this->bpas->formatQuantity($fuel_item->nozzle_end_no).'</td>
															<td style="text-align:center;">'.$this->bpas->formatQuantity($fuel_item->using_qty).'</td>
															<td style="text-align:center;">'.$this->bpas->formatQuantity($fuel_item->customer_qty).'</td>
															<td style="text-align:center;">'.$this->bpas->formatQuantity($fuel_item->quantity).'</td>
															<td style="text-align:center;">'.$this->bpas->formatQuantity($fuel_item->quantity + $fuel_item->customer_qty + $fuel_item->using_qty).'</td>
														<tr>';
											}
										}
									}
									$html .= '<tr>
												<td style="font-weight:bold; text-align:right; text-decoration:underline;" colspan="9">'.lang("total").'</td>
												<td style="font-weight:bold; text-align:right; text-decoration:underline;">'.$this->bpas->formatMoney($total).'</td>
                                                <td style="font-weight:bold; text-align:right; text-decoration:underline;">'.$this->bpas->formatMoney($total_cash_open).'</td>
                                                <td style="font-weight:bold; text-align:right; text-decoration:underline;">'.$this->bpas->formatMoney($total_cash).'</td>
												<td style="font-weight:bold; text-align:right; text-decoration:underline;">'.$this->bpas->formatMoney($total_credit).'</td>
												<td style="font-weight:bold; text-align:right; text-decoration:underline;">'.$this->bpas->formatMoney($total_bank).'</td>
												<td style="font-weight:bold; text-align:right; text-decoration:underline;">'.$this->bpas->formatMoney($total_different).'</td>
											</tr>';
									echo $html;
								}
							?>
                        </tbody>
                    </table>
					<div class="dataTables_paginate" style="float:right;">
						<?=$pagination?>
					</div>
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
            window.location.href = "<?=site_url('reports/fuel_sale_details_export/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/fuel_sale_details_export/0/xls/?v=1'.$v)?>";
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
		$("#biller").change(biller); biller();
		function biller(){
			var biller = $("#biller").val();
			var project = "<?= (isset($_POST['project']) ? trim($_POST['project']) : ''); ?>";
			$.ajax({
				url : "<?= site_url("reports/get_project") ?>",
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



