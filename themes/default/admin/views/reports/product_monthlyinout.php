<script type="text/javascript">
$(document).ready(function(){
	
	$('body').on('click', '#excel', function(e) {
	   e.preventDefault();
	   var k = false;
	   $.each($("input[name='val[]']:checked"), function(){
	    k = true;

	   });
	   $('#form_action').val($('#excel').attr('data-action'));
	   $('#action-form-submit').trigger('click');
  	});
  	$('body').on('click', '#pdf', function(e) {
	   e.preventDefault();
	   var k = false;
	   $.each($("input[name='val[]']:checked"), function(){
	    
	    k = true;
	   });
	   $('#form_action').val($('#pdf').attr('data-action'));
	   $('#action-form-submit').trigger('click');
  	});
});
</script>
<?php 
// if ($Owner) {
   // echo form_open('reports/productionReports' ,'id="action-form"');
// } 
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('monthly_products') ; ?>
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
					<a href="#" id="pdf" data-action="export_pdf"  class="tip" title="<?= lang('download_pdf') ?>">
						<i class="icon fa fa-file-pdf-o"></i>
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
	<?php if ($Owner) { ?>
	    <div style="display: none;">
	        <input type="hidden" name="form_action" value="" id="form_action"/>
	        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
	    </div>
	    <?= form_close() ?>
	<?php } ?>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
				
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
				<?php echo admin_form_open('reports/product_monthlyinout', 'id="action-form" method="GET"'); ?>
					<div class="row">
                       <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="cat"><?= lang("products"); ?></label>
                                <?php
								$cat[""] = "ALL";
                                foreach ($products as $product) {
                                    $cat[$product->id] = $product->code.' / '.$product->name;
                                }
                                echo form_dropdown('product', $cat, (isset($_POST['product']) ? $_POST['product'] : $product2), 'class="form-control" id="product" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("producte") . '"');
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
                                echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : $category2), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                                ?>

                            </div>
                        </div>
						<?php if(isset($biller_idd)){?>
						<div class="col-sm-4">
						 <div class="form-group">
                                    <?= lang("biller", "biller"); ?>
                                    <?php 
									$str = "";
									$q = $this->db->get_where("companies",array("id"=>$biller_idd),1);
									 if ($q->num_rows() > 0) {
										 $str = $q->row()->name.' / '.$q->row()->company;
										echo form_input('biller',$str , 'class="form-control" id="biller"');
									 }
									?>
                                </div>
						 </div>
						<?php } ?>
                         <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("warehouse", "warehouse") ?>
                                <?php
                                $waee[''] = "ALL";
                                foreach ($warefull as $wa) {
                                    $waee[$wa->id] = $wa->code.' / '.$wa->name;
                                }
                                echo form_dropdown('warehouse', $waee, (isset($_GET['warehouse']) ? $_GET['warehouse'] : ''), 'class="form-control select" id="warehouse" placeholder="' . lang("select") . " " . lang("warehouse") . '" style="width:100%"')
                                ?>

                            </div>
                        </div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                <?php
								$bill[""] = "ALL";
                                foreach ($billers as $biller) {
                                    $bill[$biller->id] =  $biller->code.' / '.$biller->name;
                                }
                                echo form_dropdown('biller', $bill, (isset($_GET['biller']) ? $_GET['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("in_out", "in_out") ?>
                                <?php
                                $in_out = array(
                                    'all' => lang('all'),
                                    'in' => lang('in'),
                                    'out' => lang('out')
                                );
                                echo form_dropdown('in_out', $in_out, (isset($_POST['in_out']) ? $_POST['in_out'] :$in_out2), 'class="form-control select" id="in_out" placeholder="' . lang("select") . " " . lang("in_out") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>
						
					<div class="col-sm-4">
							<div class="form-group">
								<?= lang("year", "year"); ?>
								<?php echo form_input('year', (isset($_POST['year']) ? $_POST['year'] : $year2), 'class="form-control date-year" id="year"'); ?>
							</div>
						</div>
						
						</div>
					<div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary sub"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
					
                </div>
                <div class="clearfix"></div>
				<?php 
					$months = array(
								'01' => 'January',
								'02' => 'February',
								'03' => 'March',
								'04' => 'April',
								'05' => 'May',
								'06' => 'June',
								'07' => 'July',
								'08' => 'August',
								'09' => 'September',
								'10' => 'October',
								'11' => 'November',
								'12' => 'December',
					);
					?>
                <div class="table-responsive" style="width:100%;overflow:auto;">
                    <table id="tbstock" class="table table-condensed table-bordered table-hover table-striped" >
                        <thead>
							<tr>
														
								<th><?= lang("product_code") ?></th>
								<th><?= lang("product_name") ?></th>
								
								<?php
									foreach ($months as $k => $v) {
											echo "<th style='padding-right: 30px;padding-left: 30px;'>$v</th>";
									}
								?>								
							</tr>                        
						</thead>
                        <tbody>
							<?php
							$total = array();
								foreach($stocks as $row){
							?>		
							<tr>
								<td><?=$row->code?$row->code:'ID:'.$row->product_id?></td>
								<td><?=$row->name?><?=" (".$row->name_unit.")"?></td>
								<?php
								$am = 0;
									foreach ($months as $k => $v) {
										
										$this->db->select("SUM(COALESCE((-1)*quantity_balance,0)) AS outt")
										->join("products","products.id=purchase_items.product_id","LEFT")	
										->join("bpas_purchases","bpas_purchases.id=purchase_items.purchase_id","LEFT")
										->where("purchase_items.product_id",$row->product_id)
										->where("quantity_balance<",0)
										->where("DATE_FORMAT(bpas_purchase_items.date, '%m')=",$k);
										if($warehouse2){
											$this->db->where("bpas_purchase_items.warehouse_id",$warehouse2);
										}else{		
											if($wid2){
												$this->db->where("bpas_purchase_items.warehouse_id IN ($wid2)");
											}
										}
										if($biller2){
											$this->db->where("bpas_purchases.biller_id",$biller2);
										}
										$q = $this->db->get("purchase_items")->row();
										
										$this->db->select("SUM(COALESCE(quantity_balance,0)) AS inn")
										->join("products","products.id=purchase_items.product_id","LEFT")
										->join("bpas_purchases","bpas_purchases.id=purchase_items.purchase_id","LEFT")
										->where("purchase_items.product_id",$row->product_id)
										->where("quantity_balance>",0)
										->where("DATE_FORMAT(bpas_purchase_items.date, '%m')=",$k);
										if($warehouse2){
											$this->db->where("bpas_purchase_items.warehouse_id",$warehouse2);
										}else{		
											if($wid2){
												$this->db->where("bpas_purchase_items.warehouse_id IN ($wid2)");
											}
										}
										if($biller2){
											$this->db->where("bpas_purchases.biller_id",$biller2);
										}
										$q2 = $this->db->get("purchase_items")->row();
										////////////////////
										$this->db->select("SUM(COALESCE(quantity_balance,0)) AS inn")
										->join("products","products.id=purchase_items.product_id","LEFT")
										->join("bpas_purchases","bpas_purchases.id=purchase_items.purchase_id","LEFT")
										->where("purchase_items.product_id",$row->product_id)
										->where("quantity_balance>",0)
										->where("DATE_FORMAT(bpas_purchase_items.date, '%m')=",$k);
										if($warehouse2){
											$this->db->where("bpas_purchase_items.warehouse_id",$warehouse2);
										}else{		
											if($wid2){
												$this->db->where("bpas_purchase_items.warehouse_id IN ($wid2)");
											}
										}
										if($biller2){
											$this->db->where("bpas_purchases.biller_id",$biller2);
										}
										$q_unit_in = $this->db->get("purchase_items")->row();
										/////////////////////////
										$this->db->select("SUM(COALESCE((-1)*quantity_balance,0)) AS outt")
										->join("products","products.id=purchase_items.product_id","LEFT")
										->join("bpas_purchases","bpas_purchases.id=purchase_items.purchase_id","LEFT")
										->where("purchase_items.product_id",$row->product_id)
										->where("quantity_balance<",0)
										->where("DATE_FORMAT(bpas_purchase_items.date, '%m')=",$k);
										if($warehouse2){
											$this->db->where("bpas_purchase_items.warehouse_id",$warehouse2);
										}else{		
											if($wid2){
												$this->db->where("bpas_purchase_items.warehouse_id IN ($wid2)");
											}
										}
										if($biller2){
											$this->db->where("bpas_purchases.biller_id",$biller2);
										}
										$q_unit_out = $this->db->get("purchase_items")->row();
										///////////////////////
										$this->db->select("SUM(COALESCE(quantity_balance,0)) AS inou,option_id")
										->join("products","products.id=purchase_items.product_id","LEFT")
										->join("bpas_purchases","bpas_purchases.id=purchase_items.purchase_id","LEFT")
										->where("purchase_items.product_id",$row->product_id)
										->where("DATE_FORMAT(bpas_purchase_items.date, '%m')=",$k);
										if($warehouse2){
											$this->db->where("bpas_purchase_items.warehouse_id",$warehouse2);
										}else{		
											if($wid2){
												$this->db->where("bpas_purchase_items.warehouse_id IN ($wid2)");
											}
										}
										if($biller2){
											$this->db->where("bpas_purchases.biller_id",$biller2);
										}
										$q_unit = $this->db->get("purchase_items")->row();
										
										$am = $q2->inn - $q->outt;
								?>
									<?php 
									if($in_out2 == "all"){	
										if($q2->inn || $q->outt){?>
											<td style='text-align:right ;'>
											<?php  if($am){?>
											<span style="color:blue;"><?=$this->bpas->formatDecimal($am)?></span><br>
											<?php
													
														if($q_unit->inou){
															echo   $this->bpas->convert_unit_2_string($row->product_id,$q_unit->inou);
														}
														$total[$v] +=$am;
														
													}
													?>
											</td>
										<?php
											
										}else{
											echo "<td></td>";
										}
									}else if($in_out2 == "in"){
										if($q2->inn){?>
											<td style='text-align:right; '>
											
											<span style="color:blue;"><?=$this->bpas->formatDecimal($q2->inn)?></span><br>
											<?php
													 
														if($q_unit_in->inn){
															echo   $this->bpas->convert_unit_2_string($row->product_id,$q_unit_in->inn);
														}
														$total[$v] +=$q2->inn;
													
													?>
											</td>
										<?php
										
											
										}else{
											echo "<td></td>";
										}
									}else{
										if($q->outt){?>
											<td style='text-align:right; '>
											
											<span style="color:blue;"><?=$this->bpas->formatDecimal($q->outt)?></span><br>
											<?php
													 
														if($q_unit_out->outt){
															echo   $this->bpas->convert_unit_2_string($row->product_id,$q_unit_out->outt);
														}
														
													$total[$v] +=$q->outt;
													?>
											
											</td>
										<?php
											
										
										}else{
											echo "<td></td>";
										}
									}

									?>
								<?php } ?>
							</tr>
							<?php

								}
							
							?>
							<tr>
								<td colspan="2" style="text-align:right; background-color: #428BCA;color:white;border-color: #357EBD;"><b>Total:</b></td>
								<?php 
								foreach ($months as $k => $v) {?>
									<td style='text-align:right; background-color: #428BCA;color:white;border-color: #357EBD;'>
									<?php if(isset($total[$v])){?>
									<b><?=$this->bpas->formatDecimal($total[$v])?></b>
									<?php } ?>
									</td>
								<?php
								}
								?>
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
			window.location.href = "<?= admin_url('reports/productMonthlyInOutReport/0/xls/' . $product1 . '/' . $year2 . '/' . $category1 . '/' . $warehouse1 . '/' . $in_out2.'/'.$wid1.'/'.$biller1) ?>";
			return false;
		}
	});
	$('#pdf').on('click', function (e) {
		e.preventDefault();
		if ($('.checkbox:checked').length <= 0) {
			window.location.href = "<?= admin_url('reports/productMonthlyInOutReport/pdf/0/' . $product1 . '/' . $year2 . '/' . $category1 . '/' . $warehouse1 . '/' . $in_out2.'/'.$wid1.'/'.$biller1) ?>";
			return false;
		}
	});
});
</script>