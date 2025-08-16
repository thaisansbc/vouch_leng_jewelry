<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css">
    .dfTable th, .dfTable td {
        text-align: center;
        vertical-align: middle;
    }
    .dfTable td {
        padding: 2px;
    }

    .data tr:nth-child(odd) td {
        color: #2FA4E7;
    }

    .data tr:nth-child(even) td {
        text-align: right;
    }
</style>

 <?php echo admin_form_open("reports/product_purchases_report", ' id="form-submit" '); ?>
 
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('product_purchases_report') ?></h2>
		
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
                <p class="introtext"><?= lang("product_purchases_report") ?></p>
				
				<div id="form">
				
                   
					
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
                                <label class="control-label" for="user"><?= lang("biller"); ?></label>
                                <?php
                                $bl[""] = lang('select').' '.lang('biller');
                                if($billers){
									foreach ($billers as $biller) {
										$bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
									}
								}
								
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
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
                                <?= lang("category", "category") ?>
                                <?php
                                $cat[''] = lang('select').' '.lang('category');
                                foreach ($categories as $category) {
                                    $cat[$category->id] = $category->name.' ('.$category->code.')';
                                }
                                echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ''), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("supplier", "supplier"); ?>
                                <?php echo form_input('supplier', (isset($_POST['supplier']) ? $_POST['supplier'] : ""), 'class="form-control" id="supplier_id"'); ?> </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $wh[""] = lang('select').' '.lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name.' ('.$warehouse->code.')';
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
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
                        <div class="controls"> 
							<?php echo form_submit('submit_report', $this->lang->line("Search"), 'class="btn btn-primary"'); ?> 
						</div>
                    </div>
					
					<?php echo form_close(); ?>
					
				</div>
				
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-condensed dfTable reports-table">
                        <thead>
							<tr>
								<th width="3%" rowspan="2">
									<i class="fa fa-chevron-circle-down" aria-hidden="true"></i>
								</th>
								<th>
									<?= lang("warehouse") ?>
									<i class="fa fa-angle-double-right" aria-hidden="true"></i>
									<?= lang("category") ?>
									<i class="fa fa-angle-double-right" aria-hidden="true"></i>
									<?= lang("product") ?>
								</th>
								<th><?= lang("product_type") ?></th>
								<th><?= lang("quantity") ?></th>	
								<th><?= lang("unit_cost") ?></th>
								<th><?= lang("discount") ?></th>
								<th><?= lang("subtotal") ?></th>
							</tr>
                        </thead>
                        <tbody>
							<?php
							$date = date("Y-m-d");								
							$product = $this->input->post("product");
							$category = $this->input->post("category");
							$warehouse_id = $this->input->post("warehouse");
							$start_date = $this->input->post("start_date");
							$end_date = $this->input->post("end_date");
							$saleman = $this->input->post("saleman");
							$biller = $this->input->post("biller");
							$supplier = $this->input->post("supplier");
							$project = $this->input->post("project");
							
							$grand_total 		= 0; 
							$total_discount 	= 0; 

							foreach($result_categories as $result_category){
								$product_purchases = $this->reports_model->getProductByPurchases($result_category->id, $start_date, $end_date, $product, $warehouse_id, $biller, $project, $supplier);
								$subtotal = 0; 
								$discount = 0; 
								$unit_cost = 0;
								if($product_purchases){ ?>
									<tr>
										<td colspan="12" class="bold left">
											<i class="	fa fa-chevron-circle-right"></i>
											<?= $result_category->name ?>
										</td>
									</tr>
									<?php 
									foreach($product_purchases as $i => $product_purchase){
										$subtotal 	+= $product_purchase->subtotal;
										$discount 	+= $product_purchase->item_discount;
									?>
										<tr>
											<td><?= ($i+1) ?></td>
											<td class="left"><?= ucfirst($product_purchase->product_name); ?> - <?= $product_purchase->product_code; ?></td>
											<td><?= ucfirst($product_purchase->product_type); ?></td>
											<td class="right"><?= $this->bpas->convertQty($product_purchase->product_id, $product_purchase->quantity); ?></td>											
											<td class="right"><?= $this->bpas->formatMoney($product_purchase->unit_cost); ?></td>
											<td class="right"><?= $this->bpas->formatMoney($product_purchase->item_discount) ?></td>
											<td class="right"><?= $this->bpas->formatMoney($product_purchase->subtotal); ?></td>
										</tr>	
									<?php 
									}
									$grand_total += $subtotal;
									$total_discount += $discount; ?>
									
									<tr class="bold" style="color:#357EBD">
										<td colspan="5"></td>
										<td class="right"><?= $this->bpas->formatMoney($discount); ?></td>
										<td class="right"><?= $this->bpas->formatMoney($subtotal); ?></td>
									</tr>
								<?php } ?>	
						<?php } ?>
							<tr class="bold">
								<td colspan="5" class="right" style="vertical-align: top !important;"><?= lang("total") ?></td>
								<td class="right"><?= $this->bpas->formatMoney($total_discount); ?></td>
								<td class="right"><?= $this->bpas->formatMoney($grand_total); ?></td>
							</tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
		var supplier_id = "<?= isset($_POST['supplier'])?$_POST['supplier']:0 ?>";
		if (supplier_id > 0) {
		  $('#supplier_id').val(supplier_id).select2({
			minimumInputLength: 1,
			data: [],
			initSelection: function (element, callback) {
			  $.ajax({
				type: "get", async: false,
				url: site.base_url+"suppliers/getSupplier/" + $(element).val(),
				dataType: "json",
				success: function (data) {
				  callback(data[0]);
				}
			  });
			},
			ajax: {
			  url: site.base_url + "suppliers/suggestions",
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
		  $('#supplier_id').select2({
			minimumInputLength: 1,
			ajax: {
			  url: site.base_url + "suppliers/suggestions",
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
		
        $('#pdf').click(function (event) {
            event.preventDefault();
			$("#form-submit").append("<input type='hidden' name='pdf' value=1 />")
			$("#form-submit").submit();
            return false;
        });

		$("#xls").click(function(e) {
			event.preventDefault();
			$("#form-submit").append("<input type='hidden' name='xls' value=1 />")
			$("#form-submit").submit();
			return true;			
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
					}else{
						
					}
				}
			})
		}
		
    });
</script>
