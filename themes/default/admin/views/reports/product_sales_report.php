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
<?php echo admin_form_open("reports/product_sales_report", ' id="form-submit" '); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('product_sales_report') ?></h2>
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
                <p class="introtext"><?= lang("product_sales_report") ?></p>
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
                                if ($billers) {
									foreach ($billers as $biller) {
										$bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;
									}
								}
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
						<?php if ($Settings->project == 1) { ?>
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
                                <label class="control-label" for="saleman"><?= lang("saleman"); ?></label>
                                <?php
                                $sm[""] = lang('select').' '.lang('saleman');
                                foreach ($salemans as $saleman) {
                                    $sm[$saleman->id] = $saleman->first_name . " " . $saleman->last_name;
                                }
                                echo form_dropdown('saleman', $sm, (isset($_POST['saleman']) ? $_POST['saleman'] : ""), 'class="form-control" id="saleman" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("saleman") . '"');
                                ?>
                            </div>
                        </div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer_id" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                            </div>
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
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date" autocomplete="off"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date" autocomplete="off"'); ?>
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
								<th width=120><?= lang("product_type") ?></th>
								<th width=120><?= lang("unit_quantity") ?></th>	
								<?php if (isset($Settings->foc) && $Settings->foc == 1) { $foc_colspan = 1; ?>
									<th width=120><?= lang("foc") ?></th>	
								<?php } else { $foc_colspan = 0 ;} ?>
								<th width=120><?= lang("unit_price") ?></th>
								<th width=120><?= lang("total_discount") ?></th>
								<?php if($Admin || $Owner || $GP['products-cost']){ ?>
									<th width=120><?= lang("total_cost") ?></th>
								<?php } ?>
								<th width=120><?= lang("total_price") ?></th>
								<?php if($Admin || $Owner || $GP['products-cost']){ ?>
									<th width=120><?= lang("gross_profit") ?></th>
								<?php } ?>
							</tr>
                        </thead>
                        <tbody>
							<?php
							$date               = date("Y-m-d");								
							$product            = $this->input->post("product");
							$category           = $this->input->post("category");
							$warehouse_id       = $this->input->post("warehouse");
							$start_date         = $this->input->post("start_date");
							$end_date           = $this->input->post("end_date");
							$saleman            = $this->input->post("saleman");
							$biller             = $this->input->post("biller");
							$customer           = $this->input->post("customer");
							$project            = $this->input->post("project");
							$grand_total        = 0; 
							$total_gross_profit = 0; 
							$total_cost 		= 0; 
							$total_discount 	= 0; 
							$total_sold 		= 0;
							$total_unit_price 	= 0;
							foreach ($result_categories as $result_category) {
								$product_sales = $this->reports_model->getProductBySales($result_category->id, $start_date, $end_date, $product, $warehouse_id, $saleman, $biller, $project, $customer);
								$subtotal = 0; 
								$gross_profit = 0; 
								$cost = 0;
								$discount = 0; 
								if ($product_sales) { ?>
									<tr>
										<td colspan="12" class="bold left">
											<i class="	fa fa-chevron-circle-right"></i>
											<?= $result_category->name ?>
										</td>
									</tr>
									<?php 
									foreach ($product_sales as $i => $product_sale) {
										if ($product_sale->item_discount > 0) {
											$product_sale->unit_price = $product_sale->unit_price + ($product_sale->item_discount / $product_sale->unit_quantity) ;
										}
										$subtotal 	  += $product_sale->subtotal;
										$gross_profit += ($product_sale->subtotal - $product_sale->cost);
										$cost 		  += $product_sale->cost;
										$discount 	  += $product_sale->item_discount;
										$row_body      = '';
										if ($product_sale->product_type == 'bom' || $product_sale->product_type == 'combo') {
											$raw_datas = array();
											$raw_split = explode('#',$product_sale->raw_materials);
											if ($raw_split) {
												foreach ($raw_split as $raw_row) {
													$raw_meterial = json_decode($raw_row);
													if ($raw_meterial) {
														foreach ($raw_meterial as $row) {
															if (isset($raw_datas[$row->product_id])) { 
																$raw_datas[$row->product_id] = $raw_datas[$row->product_id] + $row->quantity;
															} else {
																$raw_datas[$row->product_id] =  $row->quantity;
															}
														}
													}
												}
											}
											if ($raw_datas) {
												foreach ($raw_datas as $key => $raw_data) { 
													$raw_info = $this->reports_model->getProductById($key);
													$row_body .= '<tr>
																	<td></td>
																	<td class="right">'.$raw_info->name.' - '.$raw_info->code.'</td>
																	<td>'.lang($raw_info->type).'</td>
																	<td colspan="'.(1 + $foc_colspan).'"></td>
																	<td class="right">'.$this->bpas->convertQty($key,$raw_data).'</td>
																	<td colspan="5"></td>
																</tr>';
												}
											}
										}
										$cstyle = ""; 
										if ($product_sale->unit_price == 0) {
											$cstyle = " style='color:red; font-weight:bold; text-decoration:underline; ' ";
										} ?>
										<tr <?=$cstyle?> >
											<td><?= ($i+1) ?></td>
											<td class="left"><?= ucfirst($product_sale->product_name); ?> - <?= $product_sale->product_code; ?></td>
											<td><?= ucfirst($product_sale->product_type); ?></td>
											<td class="right"><?= $product_sale->unit_quantity ." ".$product_sale->unit_name ?></td>	
											<?php if (isset($Settings->foc) && $Settings->foc == 1) { ?>
												<td class="right"><?= $this->bpas->convertQty($product_sale->product_id, $product_sale->foc); ?></td>
											<?php } ?>
											<td class="right"><?= $this->bpas->formatMoney($product_sale->unit_price); ?></td>											
											<td class="right"><?= $this->bpas->formatMoney($product_sale->item_discount) ?></td>
											<?php if($Admin || $Owner || $GP['products-cost']){ ?>
												<td class="right"><?= $this->bpas->formatMoney($product_sale->cost); ?></td>
											<?php } ?>
											<td class="right"><?= $this->bpas->formatMoney($product_sale->subtotal); ?></td>
											<?php if($Admin || $Owner || $GP['products-cost']){ ?>
												<td class="right"><?= $this->bpas->formatMoney($product_sale->subtotal - $product_sale->cost); ?></td>
											<?php } ?>
										</tr>	
										<?= $row_body ?>
									<?php }
									$grand_total 		+= $subtotal;
									$total_gross_profit += $gross_profit;
									$total_cost 	    += $cost;
									$total_discount     += $discount; ?>
									<tr class="bold" style="color:#357EBD">
										<td colspan="<?= (5 + $foc_colspan) ?>"></td>
										<td class="right"><?= $this->bpas->formatMoney($discount); ?></td>
										<?php if ($Admin || $Owner || $GP['products-cost']) { ?>
											<td class="right"><?= $this->bpas->formatMoney($cost); ?></td>
										<?php } ?>
										<td class="right"><?= $this->bpas->formatMoney($subtotal); ?></td>
										<?php if ($Admin || $Owner || $GP['products-cost']) { ?>
											<td class="right"><?= $this->bpas->formatMoney($gross_profit); ?></td>
										<?php } ?>
									</tr>
								<?php 	
								} 						
							} ?>
							<tr class="bold">
								<td colspan="<?= (5 + $foc_colspan) ?>" class="right" style="vertical-align: top !important;"><?= lang("total") ?></td>
								<td class="right"><?= $this->bpas->formatMoney($total_discount); ?></td>
								<?php if($Admin || $Owner || $GP['products-cost']){ ?>
									<td class="right"><?= $this->bpas->formatMoney($total_cost); ?></td>
								<?php } ?>
								<td class="right"><?= $this->bpas->formatMoney($grand_total); ?></td>
								<?php if($Admin || $Owner || $GP['products-cost']){ ?>
									<td class="right"><?= $this->bpas->formatMoney($total_gross_profit); ?></td>
								<?php } ?>
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
		} else {
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
					if (data) {
						$(".no-project").html(data.result);
						$("#project").select2();
					} else {
						
					}
				}
			})
		}
    });
</script>