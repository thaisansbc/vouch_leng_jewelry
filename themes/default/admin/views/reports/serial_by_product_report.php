<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php echo admin_form_open("reports/serial_by_product_report", ' id="form-submit" '); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('serial_by_product_report').' ('.($sel_warehouse ? $sel_warehouse->name : lang('all_warehouses')).')'; ?></h2>
		
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
                <p class="introtext"><?= lang("serial_by_product_report") ?></p>
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
                                <?= lang("category", "category") ?>
                                <?php
                                $cat[''] = lang('select').' '.lang('category');
                                foreach ($categories as $category) {
                                    $cat[$category->id] = $category->name;
                                }
                                echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ''), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
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
                                <?= lang("status", "status") ?>
                                <?php
                                $status = array( null =>  lang("select") . " " . lang("status") ,'0' => lang('active'),'1' => lang('inactive'));
								echo form_dropdown('status', $status, (isset($_POST['status']) ? $_POST['status'] : ''), 'class="form-control select" id="status" style="width:100%"');?>
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
                    <table class="table table-bordered table-striped dfTable reports-table">
                        <thead>
							<tr>
								<th>
									<?= lang("product") ?> (<?= lang("code") ?>)
								</th>
								<th><?= lang("serial") ?></th>
								<th><?= lang("warehouse") ?></th>	
								<th><?= lang("color") ?></th>
								<th><?= lang("description") ?></th>
								<th><?= lang("quantity") ?></th>
								<?php if($Admin || $Owner || $GP['products-cost']){ ?>
									<th><?= lang("cost") ?></th>
								<?php } if($Admin || $Owner || $GP['products-price']){ ?>
									<th><?= lang("price") ?></th>
								<?php } ?>
								
							</tr>
                        </thead>
						<tbody>
							<?php
								$td_serail = '';
								if($product_serials){
									$product_code = '';
									
									$grand_quantity = 0;
									$grand_cost = 0;
									$grand_price = 0;
									
									$total_quantity = 0;
									$total_cost = 0;
									$total_price = 0;
									foreach($product_serials as $product_serial){
										if($product_code == $product_serial->product_code){
											$product = '';
										}else{
											if($product_code != ''){
												$td_serail .= '<tr style="font-weight:bold !important">
																	<td class="right" colspan="5">'.lang('total').'</td>
																	<td class="right">'.$total_quantity.'</td>
																	'.(($Admin || $Owner || $GP['products-cost']) ? '<td class="right">'.$this->bpas->formatMoney($total_cost).'</td>' : '').'
																	'.(($Admin || $Owner || $GP['products-price']) ? '<td class="right">'.$this->bpas->formatMoney($total_price).'</td>' : '').'
																</tr>';
											}
											$product = $product_serial->product_name.' ('.$product_serial->product_code.')';
											$product_code = $product_serial->product_code;
											$total_quantity = 0;
											$total_cost = 0;
											$total_price = 0;
										}
										
										
										
										$quantity = 1;
										$td_price = '';
										$td_cost = '';
										if($Admin || $Owner || $GP['products-cost']){
											$td_cost = '<td class="right">'.$this->bpas->formatMoney($product_serial->cost).'</td>';
										}
										if($Admin || $Owner || $GP['products-price']){
											$td_price = '<td class="right">'.$this->bpas->formatMoney($product_serial->price).'</td>';
										}
										if($product_serial->inactive == 1){
											$quantity = 0;
										}
										
										$total_cost += $product_serial->cost * $quantity;
										$total_price += $product_serial->price * $quantity;
										$total_quantity += $quantity;
										
										$grand_cost += $product_serial->cost * $quantity;
										$grand_price += $product_serial->price * $quantity;
										$grand_quantity += $quantity;
										
										$td_serail .= '<tr>
															<td>'.$product.'</td>
															<td>'.$product_serial->serial.'</td>
															<td>'.$product_serial->warehouse_name.'</td>
															<td>'.$product_serial->color.'</td>
															<td>'.$product_serial->description.'</td>
															<td class="right">'.$quantity.'</td>
															'.$td_cost.'
															'.$td_price.'
														</tr>';
									}
									$td_serail .= '<tr style="font-weight:bold !important">
														<td class="right" colspan="5">'.lang('total').'</td>
														<td class="right">'.$total_quantity.'</td>
														'.(($Admin || $Owner || $GP['products-cost']) ? '<td class="right">'.$this->bpas->formatMoney($total_cost).'</td>' : '').'
														'.(($Admin || $Owner || $GP['products-price']) ? '<td class="right">'.$this->bpas->formatMoney($total_price).'</td>' : '').'
													</tr>
													<tr style="font-weight:bold !important">
														<td class="right" colspan="5">'.lang('grand_total').'</td>
														<td class="right">'.$grand_quantity.'</td>
														'.(($Admin || $Owner || $GP['products-cost']) ? '<td class="right">'.$this->bpas->formatMoney($grand_cost).'</td>' : '').'
														'.(($Admin || $Owner || $GP['products-price']) ? '<td class="right">'.$this->bpas->formatMoney($grand_price).'</td>' : '').'
													</tr>';
								}else{
									$td_serail = '<tr>
														<td colspan="8">'.lang('sEmptyTable').'</td>
													</tr>';
								}
								echo $td_serail; 
							?>
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
				url : "<?= admin_url("reports/get_project") ?>",
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
