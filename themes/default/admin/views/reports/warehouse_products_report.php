<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$v = '';
if ($this->input->get('product')) {
    $v .= '&product=' . $this->input->get('product');
}
if ($this->input->get('category')) {
    $v .= '&category=' . $this->input->get('category');
}
if ($this->input->get('biller')) {
    $v .= '&biller=' . $this->input->get('biller');
}
if ($this->input->get('start_date')) {
    $v .= '&start_date=' . $this->input->get('start_date');
}
if ($this->input->get('end_date')) {
    $v .= '&end_date=' . $this->input->get('end_date');
}
?>
<style>
	#tbstock .shead th{
		background-color: #428BCA;border-color: #357EBD;color:white;text-align:center;
	}
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('warehouse_products') ; ?>
        	<?php if ($start_date1) { ?>
				&nbsp;From Expiry Date: <u><?= $this->bpas->hrsd($start_date1); ?></u>&nbsp;&nbsp;To Expiry Date: <u><?= $this->bpas->hrsd($end_date1) ?></u>
        	<?php } ?>
        </h2>
		<div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="javascript:void(0);" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="javascript:void(0);" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
                <li class="dropdown">
					<a href="#" id="excel" data-action="export_excel"  class="tip" title="<?= lang('download_xls') ?>">
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
				<?php echo admin_form_open('reports/warehouse_products', 'id="action-form" method="GET"'); ?>
					<div class="row">
                       <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="cat"><?= lang("products"); ?></label>
                                <?php
								$pro[""] = "ALL";
                                foreach ($products as $product) {
                                    $pro[$product->id] = $product->code.' / '.$product->name;
                                }
                                echo form_dropdown('product', $pro, (isset($_GET['product']) ? $_GET['product'] : ''), 'class="form-control" id="product" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("producte") . '"');
                                ?>
								
                            </div>
                        </div>
                        
						<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("category", "category") ?>
                                <?php
                                $cat[''] = "ALL";
                                foreach ($categories as $category) {
                                    $cat[$category->id] = $category->name;
                                }
                                echo form_dropdown('category', $cat, (isset($_GET['category']) ? $_GET['category'] : ''), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                                ?>

                            </div>
                        </div>
						<div class="col-sm-4 hide">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("biller"); ?></label>
                                <?php
								$bill[""] = "ALL";
                                foreach ($billers as $biller) {
                                    $bill[$biller->id] =  $biller->name;
                                }
                                echo form_dropdown('biller', $bill, (isset($_GET['biller']) ? $_GET['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>

						<?php if ($this->Settings->product_expiry == 1) { ?>
                        <div class="col-sm-4 hide">
                            <div class="form-group">
                                <?= lang("from_edate", "from_edate"); ?>
                                <?php echo form_input('start_date', (isset($_GET['start_date']) ? $_GET['start_date'] : ""), 'class="form-control date" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4 hide">
                            <div class="form-group">
                                <?= lang("to_edate", "to_edate"); ?>
                                <?php echo form_input('end_date', (isset($_GET['end_date']) ? $_GET['end_date'] : ""), 'class="form-control date" id="end_date"'); ?>
                            </div>
                        </div>
						<?php } ?>

					</div>
					<div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary sub"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
					
                </div>
                <div class="clearfix"></div>
				
                <div class="table-responsive" style="width:100%;overflow:auto;">
                    <table id="tbstock" class="table table-condensed table-bordered table-hover table-striped" >
                        <thead>
							<tr>							
								<th><?= lang("product_code") ?></th>
								<th><?= lang("product_name") ?></th>
								<?php if ($Settings->product_expiry == 1) { ?>
								<th><?= lang("expiry_date") ?></th>
								<?php } ?>
								<?php
								if(is_array($warefull)){
									foreach($warefull as $w){
										echo "<th>".$w->name."</th>";
									}
								}
								?>
								<th><?= lang("total") ?></th>
							</tr>
							
						</thead>
                        <tbody>
						<?php
							$total_q = 0;
							$str = "";
							$tt_qty=0;
							$arr = array();

						if(is_array($products_details)){
							if(is_array($warefull)){
								foreach($warefull as $w){
									$arr[$w->id] = 0;
								}
							}
							foreach($products_details as $pro){
								if($pro->uname){
									$str= "(".$pro->uname.")";
								}else{
									$str  = "";
								}
						?>
							<tr>
								<td><?=$pro->code?></td>
								<td><?=$pro->name." ".$str?></td>
								<?php if ($Settings->product_expiry == 1) { ?>
								<td><?= $pro->expiry != null && $pro->expiry != '' ? $this->bpas->hrsd($pro->expiry) : 'N/A'; ?></td>
								<?php } ?>
								<?php
								$tt = 0;
								if(is_array($warefull)){
									foreach($warefull as $w){
										$qty = $this->reports_model->getQtyByWare($pro->id,$w->id,$product2,$category2,$biller2, $pro->expiry, $wid1, $start_date1, $end_date1);
										if(isset($qty->wqty)){
											echo "<td  class='text-right'>".$this->bpas->formatQuantity($qty->wqty)."<br>".$this->bpas->convert_unit_2_string($pro->id,$qty->wqty)."</td>";
											$tt+=$qty->wqty;
										}else{
											echo "<td  class='text-right'>0.00</td>";
											$tt+=0;
										}
										$arr[$w->id] += (isset($qty->wqty) ? $qty->wqty : 0);
									}
								}
								?>
								<?php 
									echo "<td class='text-right'><b>".$this->bpas->formatQuantity($tt)."</b><br>".$this->bpas->convert_unit_2_string($pro->id,$tt)."</td>";
								?>
							</tr>
						<?php
							$tt_qty +=$tt;
							}
						}
						$col = 3;
						if ($this->Settings->product_expiry == 0) {
							$col = 2;
						}
						?>
							<tr>
								<td colspan="<?= $col; ?>" style='background-color: #428BCA;color:white;text-align:right;'><b><?= lang("total") ?></b></td>
								<?php

								if(is_array($warefull)){
									foreach($warefull as $w){	
										echo "<td style='background-color: #428BCA;color:white;text-align:right;'>" . (isset($arr[$w->id]) && count($arr) > 0 ? $arr[$w->id] : '0.00') . "</td>";
									}
								}
								?>
								<td style='background-color: #428BCA;color:white;text-align:right;'><b><?=$this->bpas->formatDecimal($tt_qty)?></b></td>
							</tr>
                        </tbody>                       
                    </table>
                </div>
				<div class=" text-right">
					<div class="dataTables_paginate paging_bootstrap">
						<?= $pagination; ?>
					</div>
				</div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		$(document).on('focus','.date-year', function(t) {
				$(this).datetimepicker({
					format: "yyyy",
					startView: 'decade',
					minView: 'decade',
					viewSelect: 'decade',
					autoclose: true,
				});
		});
	    $('#form').hide();
	    $('.toggle_down').click(function () {
	        $("#form").slideDown();
	        return false;
	    });
	    $('.toggle_up').click(function () {
	        $("#form").slideUp();
	        return false;
	    });
	    $('#excel').on('click', function (e) {
			e.preventDefault();
			if ($('.checkbox:checked').length <= 0) {
				window.location.href = "<?= admin_url('reports/warehouseProductReport/0/xls/?v=1' . $v) ?>";
				return false;
			}
		});
		$('#pdf').on('click', function (e) {
			e.preventDefault();
			if ($('.checkbox:checked').length <= 0) {
				window.location.href = "<?= admin_url('reports/warehouseProductReport/pdf/0/'.$product1.'/'.$category1.'/'.$end_date1.'/'.$end_date1) ?>";
				return false;
			}
		});	
	});
</script>