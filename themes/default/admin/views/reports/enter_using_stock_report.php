<?php
	$v = "";	
	if ($this->input->get('reference_no')) {
		$v .= "&reference_no=" . $this->input->get('reference_no');
	}
	if ($this->input->get('customer')) {
		$v .= "&customer=" . $this->input->get('customer');
	}
	if ($this->input->get('driver')) {
		$v .= "&driver=" . $this->input->get('driver');
	}
	if ($this->input->get('warehouse')) {
		$v .= "&warehouse=" . $this->input->get('warehouse');
	}
	if ($this->input->get('user')) {
		$v .= "&user=" . $this->input->get('user');
	}
	if ($this->input->get('serial')) {
		$v .= "&serial=" . $this->input->get('serial');
	}
	if ($this->input->get('start_date')) {
		$v .= "&start_date=" . $this->input->get('start_date');
	}
	if ($this->input->get('end_date')) {
		$v .= "&end_date=" . $this->input->get('end_date');
	}
	if (isset($biller_id)) {
		$v .= "&biller_id=" . $biller_id;
	}
?>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        <?php if ($this->input->post('customer')) { ?>
        $('#customer').val(<?= $this->input->post('customer') ?>).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: "get", async: false,
                    url: site.base_url + "customers/suggestions/" + $(element).val(),
                    dataType: "json",
                    success: function (data){
                        callback(data.results[0]);
                    }
                });
            },
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
            },
			$('#customer').val(<?= $this->input->post('customer') ?>);
        });

        <?php } ?>
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
		$('.reset').click(function(){
			window.location.reload(true);
		});
    });
</script>
<?php
    echo admin_form_open('reports/usingStockReport_action', 'id="action-form"');
?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('report_list_using_stock'); ?><?php
            if ($this->input->post('start_date')) {
                echo " From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>"><i
                            class="icon fa fa-toggle-up"></i></a></li>
                <li class="dropdown"><a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>"><i
                            class="icon fa fa-toggle-down"></i></a></li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <!-- <li class="dropdown">
                    <a href="#" id="pdf" data-action="export_pdf" class="tip" title="<?= lang('download_pdf') ?>"><i class="icon fa fa-file-pdf-o"></i></a>
                </li> -->
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a>
                </li>
                <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>"><i class="icon fa fa-file-picture-o"></i></a>
                </li>
				<!--<li class="dropdown">
					<a data-toggle="dropdown" class="dropdown-toggle" href="#">
						<i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("billers") ?>"></i>
					</a>
					<ul class="dropdown-menu pull-right" class="tasks-menus" role="menu"
						aria-labelledby="dLabel">
						<li><a href="<?= admin_url('reports/sales') ?>"><i class="fa fa-building-o"></i> <?= lang('billers') ?></a></li>
						<li class="divider"></li>
						<?php
						foreach ($billers as $biller){
							echo '<li ' . ($biller_id && $biller_id == $biller->id ? 'class="active"' : '') . '><a href="' . admin_url('reports/sales/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company . '</a></li>';
						}
						?>
					</ul>
				</li>-->
            </ul>
        </div>
    </div>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('customize_report'); ?></p>
                <div id="form">
                    <?php echo admin_form_open("reports/list_using_stock_report",'method="GET"'); ?>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_GET['reference_no']) ? $_GET['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>
                            </div>
                        </div>
                        <?php if($Admin || $Owner){ ?>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="employee"><?= lang("employee"); ?></label>
                                <?php
                                $b[""] = "Select Biller";
                                foreach ($billers as $biller){
                                    $b[$biller->id] = $biller->name.' / '.$biller->company;
                                }
                                echo form_dropdown('biller', $b, (isset($_GET['biller']) ? $_GET['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
                        <?php } elseif(isset($biller_idd)){?>
						<div class="col-sm-3">
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
						<div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="employee"><?= lang("employee"); ?></label>
                                <?php
                                $E[""] = "ALL";
                                foreach ($employee as $emp){
                                    $E[$emp->id] = $emp->username;
                                }
                                echo form_dropdown('employee', $E, (isset($_GET['employee']) ? $_GET['employee'] : ""), 'class="form-control" id="employee" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("employee") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $wh[""] = "ALL";
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->code.' / '.$warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_GET['warehouse']) ? $_GET['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>
						<?php if($this->Settings->product_serial) { ?>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <?= lang('serial_no', 'serial'); ?>
                                    <?= form_input('serial', '', 'class="form-control tip" id="serial"'); ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_GET['start_date']) ? $_GET['start_date'] : $this->bpas->hrsd($start_date)), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_GET['end_date']) ? $_GET['end_date'] : $this->bpas->hrsd($end_date)), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-lg-1"style="padding-left:0px;">
                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <div class="clearfix"></div>
                <div class="table-responsive">
                    <table class="table table-condensed table-striped">
						<thead>
							<tr class="info-head">
								<th style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkth" type="checkbox" name="val" />
								</th>
								<th style="width:200px;" class="center"><?= lang("item"); ?></th> 
								<th style="width:150px;"><?= lang("category_expense"); ?></th> 
								<th style="width:150px;"><?= lang("description"); ?></th> 
								<th style="width:150px;"><?= lang("quantity"); ?></th>
								<th style="width:150px;"><?= lang("unit"); ?></th>
								<th style="width:150px;display:none"><?= lang("cost"); ?></th>
								<th style="width:150px;"><?= lang("total"); ?></th>							
							</tr>
						</thead>
						<?php  
						if(is_array($using_stock)){
						    foreach($using_stock as $stock){
						          $query=$this->db->query("
							         SELECT
										bpas_enter_using_stock_items.*, bpas_products.NAME AS product_name,
										bpas_expense_categories.NAME AS exp_cate_name,
										bpas_enter_using_stock_items.unit AS unit_name,
										bpas_products.cost,
										bpas_position.NAME AS pname,
										bpas_reasons.description AS rdescription,
										bpas_product_variants.qty_unit AS variant_qty,
										bpas_product_variants.name as var_name,
										bpas_enter_using_stock.type
									FROM
										bpas_enter_using_stock_items
									LEFT JOIN bpas_enter_using_stock ON bpas_enter_using_stock.reference_no = bpas_enter_using_stock_items.reference_no
									LEFT JOIN bpas_products ON bpas_products.CODE = bpas_enter_using_stock_items.CODE
									LEFT JOIN bpas_position ON bpas_enter_using_stock_items.description = bpas_position.id 
									LEFT JOIN bpas_reasons ON bpas_enter_using_stock_items.reason = bpas_reasons.id
									LEFT JOIN bpas_product_variants ON bpas_enter_using_stock_items.option_id = bpas_product_variants.id
									LEFT JOIN bpas_expense_categories ON bpas_enter_using_stock_items.exp_cate_id = bpas_expense_categories.id 
                                    WHERE bpas_enter_using_stock_items.reference_no='{$stock->refno}' 
									 ")->result();
						?>
                        <tbody>
						       <tr class="bold">
							      <td style="min-width:30px; width: 30px; text-align: center;background-color:#E9EBEC">
									<input type="checkbox" name="val[]" class="checkbox multi-select input-xs" value="<?= $stock->id; ?>" />
								  </td>
								  <td colspan="7" style="font-size:14px;background-color:#E9EBEC;color:#265F7B  "><?= $stock->refno ." >> ".$this->bpas->hrld($stock->date) ." >> ".$stock->company ." >> ".$stock->warehouse_name ." >> ".$stock->username ?></td> 
							   </tr>
							   <?php foreach($query as $q){ ?>
							    <tr>
							      <td style="min-width:30px; width: 30px; text-align: center;"></td>
								  <td><?=$q->product_name ."(".$q->code .")" ?></td> 
							      <td><?=$q->exp_cate_name ?></td> 
								  <td><?=$q->rdescription ?></td> 
							      <td class="text-center"><?= $q->type == 'use' ? $this->bpas->formatQuantity($q->qty_use) : $this->bpas->formatQuantity(-1*$q->qty_use)?></td> 
							      <td class="text-center"><?=!empty($q->var_name)?$q->var_name :$q->unit_name ?></td> 
								  <td class="text-right"style="display:none;"><?=$this->bpas->formatMoney($q->cost)?></td>
								  <td class="text-right"><?=($q->type == 'use' ? $this->bpas->formatMoney($q->cost*$q->qty_use) : $this->bpas->formatMoney($q->cost*(-1*$q->qty_use))) ?></td> 
							   </tr>
							   <?php }?>
					   </tbody> 
						<?php } } ?>					
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
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getSalesReport/pdf/0/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/export_using_stock_report/0/xls/?v=1'.$v)?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    openImg(canvas.toDataURL());
                }
            });
            return false;
        });
    });
</script>