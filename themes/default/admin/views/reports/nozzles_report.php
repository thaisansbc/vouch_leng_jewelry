<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
	$v = "";
	if ($this->input->post('biller')) {
		$v .= "&biller=" . $this->input->post('biller');
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
	if ($this->input->post('tank')) {
		$v .= "&tank=" . $this->input->post('tank');
	}
	if ($this->input->post('product')) {
		$v .= "&product=" . $this->input->post('product');
	}
	if ($this->input->post('month')) {
		$v .= "&month=" . $this->input->post('month');
	}
	if ($this->input->post('year')) {
		$v .= "&year=" . $this->input->post('year');
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
        <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('nozzles_report'); ?><?php
            if ($this->input->post('month')) {
                echo "Date " . $this->input->post('month') . ", " . $this->input->post('year');
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
                    <?php echo admin_form_open("reports/nozzles_report"); ?>
                    <div class="row">
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
                                <label class="control-label" for="user"><?= lang("tank"); ?></label>
                                <?php
                                $opt_tanks[""] = lang('select').' '.lang('tank');
                                foreach ($tanks as $tank) {
                                    $opt_tanks[$tank->id] = $tank->name;
                                }
                                echo form_dropdown('tank', $opt_tanks, (isset($_POST['tank']) ? $_POST['tank'] : ""), 'class="form-control" id="tank" data-placeholder="' . $this->lang->line("tank") . " " . $this->lang->line("tank") . '"');
                                ?>
                            </div>
                        </div>
						
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
						
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
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
                    <table id="FuelDaily" style="margin-bottom:3px;" cellpadding="0" cellspacing="0" border="0" class="table table-condensed table-bordered table-hover table-striped" style="white-space:nowrap;">
                        <thead>
							<tr class="active">
								<th style="width:99px;" rowspan="2"><?= lang("nozzle_no"); ?></th>
								<?php 
									if($tank_items){
										foreach($tank_items as $tank_item){
											echo '<th colspan="6">'.$tank_item->product_name.'</th>';
										}
									}
								?>
								<th style="width:99px;" rowspan="2"><?= lang("total"); ?></th>
							</tr>
							<tr>
								<?php 
									if($tank_items){
										foreach($tank_items as $tank_item){
											echo '<th style="text-align:center; color:#FFF; border: 1px solid #357EBD; background:#428BCA">'.lang("begin").'</th>';
											echo '<th style="text-align:center; color:#FFF; border: 1px solid #357EBD; background:#428BCA">'.lang("ending").'</th>';
											echo '<th style="text-align:center; color:#FFF; border: 1px solid #357EBD; background:#428BCA">'.lang("customer_qty").'</th>';
											echo '<th style="text-align:center; color:#FFF; border: 1px solid #357EBD; background:#428BCA">'.lang("using_qty").'</th>';
											echo '<th style="text-align:center; color:#FFF; border: 1px solid #357EBD; background:#428BCA">'.lang("fuel_qty").'</th>';
											echo '<th style="text-align:center; color:#FFF; border: 1px solid #357EBD; background:#428BCA">'.lang("total_qty").'</th>';
										}
									}
								?>
							</tr>
                        </thead>
                        <tbody>
							<?php 
								if($nozzles){
									$grand_qty = 0;
									foreach($nozzles as $nozzle){
										echo '<tr>
												<td style="text-align:center;">
													<b>'.$nozzle->nozzle_no.'</b><br>
													<small>'.$nozzle->tank.'</small>
												</td>';
												$total_qty = 0;
												if($tank_items){
													foreach($tank_items as $tank_item){
														$row = $this->reports_model->getDailyTankItemsQty($nozzle->tank_id, $nozzle->id, $tank_item->product_id);
														$quantity = $row->quantity + $row->customer_qty + $row->using_qty;
														$total_qty += $quantity;
														echo '<td style="text-align:center;">'.($row->nozzle_start_no>0?$this->bpas->formatDecimal($row->nozzle_start_no,-1):"-").'</td>';
														echo '<td style="text-align:center;">'.($row->nozzle_end_no>0?$this->bpas->formatDecimal($row->nozzle_end_no,-1):"-").'</td>';
														echo '<td style="text-align:center;">'.($row->customer_qty>0?$this->bpas->formatDecimal($row->customer_qty):"-").'</td>';
														echo '<td style="text-align:center;">'.($row->using_qty>0?$this->bpas->formatDecimal($row->using_qty):"-").'</td>';
														echo '<td style="text-align:center;">'.($row->quantity>0?$this->bpas->formatDecimal($row->quantity):"-").'</td>';
														echo '<td style="text-align:center;">'.($quantity>0?$this->bpas->formatDecimal($quantity):"-").'</td>';
													}
												}
												echo '<td style="text-align:center;">'.($total_qty>0?$this->bpas->formatDecimal($total_qty):"-").'</td>';
										echo '</tr>';
										$grand_qty += $total_qty;
									}
									$colspan = (count($tank_items) * 6) + 1;
									echo '<tr>
											<td colspan="'.$colspan.'" style="text-align:right; font-weight:bold;">'.lang("total").' : </td>
											<td style="text-align:center; font-weight:bold;">'.($grand_qty>0?$this->bpas->formatDecimal($grand_qty):"-").'</td>
										</tr>';
								}
							?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<style type="text/css">
	.table .cth{
		background:#428BCA !important;
		border-right:1px solid #357EBD !important;
		border-bottom:1px solid #357EBD !important;
		color:#FFF !important;
	}
</style>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
		$('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?= admin_url('reports/nozzles_export/pdf/?v=1'.$v)?>";
            return false;
        });
		
		$('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/nozzles_export/0/xls/?v=1'.$v)?>";
            return false;
        });
		$("#biller").change(biller); biller();
		function biller(){
			var biller = $("#biller").val();
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

