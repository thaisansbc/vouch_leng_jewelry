
<style type="text/css" media="all">
	#PRData{ 
		white-space:nowrap; 
		width:100%; 
	}
    #PRData td:nth-child(6), #PRData td:nth-child(7) {
        text-align: right;
    }
 
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-barcode"></i><?= lang('supplier_products') ; ?>
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
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
				<?php echo admin_form_open('reports/supplier_details', 'id="action-form"'); ?>
					<div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>

                            </div>
                        </div>

                       
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="cat"><?= lang("products"); ?></label>
                                <?php
								$pro[""] = "ALL";
                                foreach ($products as $product) {
                                    $pro[$product->id] = $product->code.' / '.$product->name;
                                }
                                echo form_dropdown('product', $pro, (isset($_POST['product']) ? $_POST['product'] : ""), 'class="form-control" id="product" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("producte") . '"');
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
                                echo form_dropdown('warehouse', $waee, (isset($_GET['warehouse']) ? $_GET['warehouse'] : $warehouse), 'class="form-control select" id="warehouse" placeholder="' . lang("select") . " " . lang("warehouse") . '" style="width:100%"')
                                ?>

                            </div>
                        </div>
						
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("supplier"); ?></label>
                                <?php
								$su = array(""=>"ALL");
								$supp = $this->db->select("supplier_id,supplier")->group_by("supplier_id")->get("bpas_purchases")->result();
                                foreach ($supp as $sup) {
                                    $su[$sup->supplier_id] = $sup->supplier;
                                }
                                echo form_dropdown('supplier', $su, (isset($_POST['supplier']) ? $_POST['supplier'] : ""), 'class="form-control" id="supplier" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("supplier") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("from_date", "from_date"); ?>
                                <?php echo form_input('from_date', (isset($_POST['from_date']) ? $_POST['from_date'] :$this->bpas->hrsd($from_date)), 'class="form-control date" id="from_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("to_date", "to_date"); ?>
                                <?php echo form_input('to_date', (isset($_POST['to_date']) ? $_POST['to_date'] :$this->bpas->hrsd($to_date)), 'class="form-control date" id="to_date"'); ?>
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
                    <table id="PRData" class="table table-hover table-striped table-condensed">
                        <thead>
                        <tr class="primary">
							<th class=""><?= lang("type") ?></th>
							<th class=""><?= lang("date") ?></th>
							<th class=""><?= lang("reference") ?></th>
							<th class=""><?= lang("name") ?></th>
							<th class=""><?= lang("warehouse") ?></th>
							<th class=""><?= lang("qty") ?></th>
							<th class=""><?= lang("unit") ?></th>
							<th class=""><?= lang("unit_cost") ?></th>
							<th class=""><?= lang("amount") ?></th>
							
                        </tr>
                        </thead>
                        <tbody>
							<?php
							$grand = 0 ;
							$gqty = 0;
							$wid = $this->reports_model->getWareByUserID();
							$this->db->select("bpas_purchases.supplier_id,supplier,SUM(bpas_purchase_items.quantity) as qty");
							$this->db->join("bpas_purchase_items","bpas_purchase_items.purchase_id=bpas_purchases.id","LEFT");
							if($supplier){
								$this->db->where("bpas_purchases.supplier_id",$supplier);
							}
							if($reference){
								$this->db->where("reference_no",$reference);
							}
							
							if($product_id){
								$this->db->where("product_id",$product_id);
							}
							if($from_date && $to_date){
								$this->db->where('bpas_purchases.date >="'.$from_date.' 00.00" AND bpas_purchase_items.date<="'.$to_date.' 23.59"');
							}
							if($warehouse){
								$this->db->where("bpas_purchases.warehouse_id",$warehouse);
							}else{
								if($wid){
									$this->db->where("bpas_purchases.warehouse_id IN ($wid)");
								}
							}
							$this->db->group_by("bpas_purchases.supplier_id");
							$suppliers = $this->db->get("bpas_purchases")->result();
							if(is_array($suppliers)){
								foreach($suppliers as $row){
									if($row->supplier_id){
										if($row->qty){
											?>
											<tr>
												<td colspan="9" style="background:#F0F8FF;"><b><?=$row->supplier?></b></td>
											</tr>
											<?php
												$this->db->select("product_id,product_name,bpas_purchase_items.quantity,real_unit_cost,unit_cost,bpas_purchases.supplier_id,reference_no,bpas_purchase_items.date,transaction_type,option_id,unit,net_shipping,warehouses.name as warehouse_name")->join("bpas_purchases","bpas_purchases.id = bpas_purchase_items.purchase_id","LEFT")->join("bpas_products","bpas_products.id = bpas_purchase_items.product_id","LEFT")->where("bpas_purchase_items.transaction_type = 'PURCHASE'");
												$this->db->join('warehouses','warehouses.id = purchases.warehouse_id');
												if($reference){
													$this->db->where("reference_no",$reference);
												}
												if($supplier){
													$this->db->where("bpas_purchases.supplier_id",$supplier);
												}
												if($product_id){
													$this->db->where("product_id",$product_id);
												}
												if($from_date && $to_date){
													$this->db->where('bpas_purchase_items.date >="'.$from_date.'" AND bpas_purchase_items.date<="'.$to_date.'"');
												}
												if($warehouse){
													$this->db->where("bpas_purchases.warehouse_id",$warehouse);
												}else{
													if($wid){
														$this->db->where("bpas_purchases.warehouse_id IN ($wid)");
													}
												}
												$pur_items = $this->db->get("bpas_purchase_items")->result();
												$tqty = 0 ; 
												$amount = 0 ;
												$totalshipping = 0 ;
												$vqty = 0;
												$unit_name = "";
												$product_cost = 0;
												
											if(is_array($pur_items)){
												foreach($pur_items as $row1){
													if($row->supplier_id == $row1->supplier_id){
														if($row1->option_id){
															$unit_n = $this->db->get_where('bpas_product_variants',array('id'=> $row1->option_id),1)->row();
															$unit_q = $unit_n->qty_unit;
															//$unit_name = ' ( '.$this->bpas->formatQuantity(( abs($row1->quantity)*$unit_q)/$unit_q).' '.$unit_n->name.' )';
															$vqty = abs($row1->quantity)*$unit_q;
															$unit_name = $this->bpas->convert_unit_2_string($row1->product_id,$vqty);			
														}else{
															$unit = $this->reports_model->getUn($row1->unit);
															if($unit){
															$unit_name = $unit->name;
															}
															$vqty =  abs($row1->quantity);
														}
														$product_cost = $row1->unit_cost;
														?>
														<tr>
															<td class="text-center"><?=$row1->transaction_type?></td>
															<td><?=$this->bpas->hrsd($row1->date)?></td>
															<td><?=$row1->reference_no?></td>
															<td><?=$row1->product_name?></td>
															<td><?=$row1->warehouse_name?></td>
															<td class="text-right"><?=$this->bpas->formatQuantity($vqty)?></td>
															<td ><?=$unit_name?></td>
															<td class="text-right"><?=$this->bpas->formatMoney($row1->unit_cost)?></td>
															<td class="text-right"><b><?=$this->bpas->formatMoney(abs($row1->quantity)*$product_cost)?></b></td>
														</tr>
														<?php
														$tqty+=$vqty;
														$amount+=(abs($row1->quantity)*$product_cost);
													}
												}
											}
										?>
									<tr style="background:#F0F8FF;">
										<td ><b>Total >> <?=$row->supplier?></b></td>
										<td ></td>
										<td ></td>
										<td ></td>
										<td ></td>
										<td class="text-right"><b><?=$this->bpas->formatQuantity($tqty)?></b></td>
										<td ></td>
										<td ></td>
										<td class="text-right"><b><?=$this->bpas->formatMoney($amount)?></b></td>
										
									</tr>
									<?php
									$grand +=$amount;
									$gqty+=$tqty;
										}
									}
								}
							}
							?>
							<tr >
								<td style="background:#4682B4;color:white;"><b>Grand Total</b></td>
								<td style="background:#4682B4;color:white;"></td>
								<td style="background:#4682B4;color:white;"></td>
								<td style="background:#4682B4;color:white;"></td>
								<td style="background:#4682B4;color:white;"></td>
								<td style="background:#4682B4;color:white;" class="text-right"><b><?=$this->bpas->formatQuantity($gqty)?></b></td>
								<td style="background:#4682B4;color:white;"></td>
								<td style="background:#4682B4;color:white;"></td>
								<td style="background:#4682B4;color:white;" class="text-right"><b><?=$this->bpas->formatMoney($grand)?></b></td>
								
							</tr>
                        </tbody>
                       
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    
	$(document).ready(function(){
		$('#form').hide();
		$('.toggle_down').click(function () {
			$("#form").slideDown();
			return false;
		});
		$('.toggle_up').click(function () {
			$("#form").slideUp();
			return false;
		});
		/*
		$("#excel").click(function(e){
			e.preventDefault();
			window.location.href = "<?=admin_url('products/getProductAll/0/xls/')?>";
			return false;
		});
		$('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('products/getProductAll/pdf/?v=1'.$v)?>";
            return false;
        });
		*/
		$('.date').datetimepicker({
			format: site.dateFormats.js_sdate, 
			fontAwesome: true, 
			language: 'bpas', 
			todayBtn: 1, 
			autoclose: 1, 
			minView: 2 
		});
		
		$(document).on('focus','.date', function(t) {
			$(this).datetimepicker({format: site.dateFormats.js_sdate, fontAwesome: true, todayBtn: 1, autoclose: 1, minView: 2 });
		});
	
		$('body').on('click', '#multi_adjust', function() {
			 if($('.checkbox').is(":checked") === false){
				alert('Please select at least one.');
				return false;
			}
			var arrItems = [];
			$('.checkbox').each(function(i){
				if($(this).is(":checked")){
					if(this.value != ""){
						arrItems[i] = $(this).val();   
					}
				}
			});
			$('#myModal').modal({remote: '<?=base_url('products/multi_adjustment');?>?data=' + arrItems + ''});
			$('#myModal').modal('show');
        });
		$('#excel').on('click', function(e){
			e.preventDefault();
				window.location.href = "<?=admin_url('reports/ProductsSuppliersReport/0/xls/'.$warehouse1.'/'.$supplier1.'/'.$reference1.'/'.$product_id1.'/'.$from_date1.'/'.$to_date1)?>";
				return false;
			
		});
		$('#pdf').on('click', function(e){
			e.preventDefault();
				window.location.href = "<?=admin_url('reports/ProductsSuppliersReport/pdf/0'.$warehouse1.'/'.$supplier1.'/'.$reference1.'/'.$product_id1.'/'.$from_date1.'/'.$to_date1)?>";
				return false;
		});
	});
</script>

