<?php defined('BASEPATH') OR exit('No direct script access allowed');
$v = "";

if ($this->input->post('product')) {
    $v .= "&product=" . $this->input->post('product');
}
if ($this->input->post('category')) {
    $v .= "&category=" . $this->input->post('category');
}
if ($this->input->post('warehouse')) {
    $v .= "&warehouse=" . $this->input->post('warehouse');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
?>
<script>
    $(document).ready(function () {
		
		$(document).on('click', '.transaction_link', function () {
			var transaction = $(this).attr('transaction');
			var opt = $(this).attr('opt');
			var start_date = $('#start_date').val();
			var end_date = $('#end_date').val();
			var warehouse_id = $('#warehouse').val();
			var product_id = $(this).parent().parent().attr('id');
			$('#myModal').modal({remote: site.base_url + 'reports/view_stock_modal/?o='+opt+'&t='+transaction+'&w='+warehouse_id+'&p='+product_id+'&s='+start_date+'&e='+end_date});
			$('#myModal').modal('show');
		});
		
		oTable = $('#InventoryTable').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/getStock_movement?v=1&'. $v)?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
				nRow.id = aData[0];
				nRow.className = "product_link3";
                return nRow;
            },
            "aoColumns": [
				{"bVisible":false},
				null,
				null,
				null,
				{"sClass" : "text-right", "bSearchable":false},
				
				<?php if($Owner || $Admin || $this->session->userdata('show_cost')) { ?>
					{"mRender": currencyFormat},
				<?php }else{ ?>
					{"mRender": currencyFormat, "bVisible" : false},
				<?php }if($Settings->accounting == 1){ ?>
					{"sClass" : "text-right", "bSearchable":false},
				<?php } else { ?>
					{"sClass" : "text-right", "bSearchable":false, "bVisible" : false},
				<?php } ?>
				{"sClass" : "text-right", "bSearchable":false},
				{"sClass" : "text-right", "bSearchable":false},
				{"sClass" : "text-right", "bSearchable":false},
				<?php if(!$this->config->item('one_warehouse')){ ?>	
					{"sClass" : "text-right", "bSearchable":false},
				<?php }else{ ?>
					{"sClass" : "text-right", "bSearchable":false, "bVisible" : false},
				<?php } if($this->config->item('using_stocks')){ ?>	
					{"sClass" : "text-right", "bSearchable":false},
				<?php }else{ ?>
					{"sClass" : "text-right", "bSearchable":false, "bVisible" : false},
				<?php } if($this->config->item('convert')){ ?>	
					{"sClass" : "text-right", "bSearchable":false},
				<?php }else{ ?>
					{"sClass" : "text-right", "bSearchable":false, "bVisible" : false},
				<?php } if($this->config->item('consignments')){ ?>	
					{"sClass" : "text-right", "bSearchable":false},
				<?php }else{ ?>
					{"sClass" : "text-right", "bSearchable":false, "bVisible" : false},
				<?php } if($this->config->item('concretes')){ ?>	
					{"sClass" : "text-right", "bSearchable":false},
				<?php }else{ ?>
					{"sClass" : "text-right", "bSearchable":false, "bVisible" : false},
				<?php } if($this->config->item('pawn')){ ?>	
					{"sClass" : "text-right", "bSearchable":false},
				<?php }else{ ?>
					{"sClass" : "text-right", "bSearchable":false, "bVisible" : false},
				<?php } ?>
				
				
				{"sClass" : "text-right", "bSearchable":false},
				{"sClass" : "text-right", "bSearchable":false},
				{"sClass" : "text-right", "bSearchable":false},
				<?php if(!$this->config->item('one_warehouse')){ ?>	
					{"sClass" : "text-right", "bSearchable":false},
				<?php }else{ ?>
					{"sClass" : "text-right", "bSearchable":false, "bVisible" : false},
				<?php } if($this->Settings->delivery){ ?>	
					{"sClass" : "text-right", "bSearchable":false},
				<?php }else{ ?>
					{"sClass" : "text-right", "bSearchable":false, "bVisible" : false},
				<?php } if($this->Settings->stock_using){ ?>	
					{"sClass" : "text-right", "bSearchable":false},
				<?php }else{ ?>
					{"sClass" : "text-right", "bSearchable":false, "bVisible" : false},
				<?php } if($this->Settings->module_manufacturing){ ?>	
					{"sClass" : "text-right", "bSearchable":false},
				<?php }else{ ?>
					{"sClass" : "text-right", "bSearchable":false, "bVisible" : false},
				<?php } if($this->config->item('consignments')){ ?>	
					{"sClass" : "text-right", "bSearchable":false},
				<?php }else{ ?>
					{"sClass" : "text-right", "bSearchable":false, "bVisible" : false},
				<?php } if($this->config->item('concretes')){ ?>	
					{"sClass" : "text-right", "bSearchable":false},
				<?php }else{ ?>
					{"sClass" : "text-right", "bSearchable":false, "bVisible" : false},
				<?php } ?>
				
				{"sClass" : "text-right", "bSearchable":false},
				
				<?php if($Owner || $Admin || $this->session->userdata('show_cost')) { ?>
					{"mRender": currencyFormat},
				<?php }else{ ?>
					{"mRender": currencyFormat, "bVisible" : false},
				<?php } ?>
	
			],
			<?php if($Owner || $Admin || $this->session->userdata('show_cost')) { ?>
				"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
					var begin_amount = 0, ending_amount = 0;
					for (var i = 0; i < aaData.length; i++) {
						begin_amount += parseFloat(aaData[aiDisplay[i]][5]);
						ending_amount += parseFloat(aaData[aiDisplay[i]][26]);
					}
					var nCells = nRow.getElementsByTagName('th');
					nCells[4].innerHTML = currencyFormat(parseFloat(begin_amount));
					nCells[7].innerHTML = currencyFormat(parseFloat(ending_amount));
				}
			<?php } ?>
        }).fnSetFilteringDelay().dtFilter([
			{column_number: 0, filter_default_label: "[<?=lang('id');?>]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('category');?>]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('code');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('name');?>]", filter_type: "text", data: []},
			
        ], "footer");
    });
</script>

<?php echo admin_form_open("reports/stock_movement", ' id="form-submit" '); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('stock_movement'); ?></h2>
		
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
                                <?= lang("product", "suggest_product"); ?>
                                <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ""), 'class="form-control" id="suggest_product"'); ?>
                                <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : "" ?>" id="report_product_id"/>
                            </div>
                        </div>			
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="category"><?= lang("category"); ?></label>
                                <?php
                                $ct[""] = lang('select').' '.lang('category');
                                foreach ($categories as $category) {
                                    $ct[$category->id] = $category->name;
                                }
                                echo form_dropdown('category', $ct, (isset($_POST['category']) ? $_POST['category'] : ""), 'class="form-control" id="category" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("category") . '"');
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
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : date('d/m/Y')), 'class="form-control date" id="start_date"'); ?>
                            </div>
                        </div>
						
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : date('d/m/Y')), 'class="form-control date" id="end_date"'); ?>
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
                    <table id="InventoryTable" class="table table-bordered table-hover table-striped dataTable">
                        <thead>
							<tr>
								<th rowspan="2"><?= lang("id"); ?></th>
								<th rowspan="2"><?= lang("category"); ?></th>
								<th rowspan="2"><?= lang("code"); ?></th>
								<th rowspan="2"><?= lang("name"); ?></th>
								<th colspan="2"><?= lang("begin"); ?></th>
								<th colspan="10"><?= lang("in"); ?></th>
								<th colspan="9"><?= lang("out"); ?></th>
								<th colspan="2"><?= lang("ending"); ?></th>
							</tr>
							<tr>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("quantity"); ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("amount"); ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("opening"); ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("purchase"); ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("sale"); ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("adjustment"); ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("transfer"); ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("using"); ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("convert"); ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("consignment"); ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("concrete"); ?></th>	
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("pawn"); ?></th>	
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("purchase"); ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("sale"); ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("adjustment"); ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("transfer"); ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("delivery"); ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("using"); ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("convert"); ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("consignment"); ?></th>	
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("concrete"); ?></th>		
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("quantity"); ?></th>
								<th style="border: 1px solid #357EBD; color: white; background-color:#428bca; text-align:center"><?= lang("amount"); ?></th>
							</tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="24" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
							<tr class="active">
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th><?= lang("quantity") ?></th>
								<th></th>
								<th colspan="19"></th>
								<th><?= lang("quantity") ?></th>
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
            window.location.href = "<?=admin_url('reports/getStock_movement/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getStock_movement/0/xls/?v=1'.$v)?>";
            return false;
        });
    });
</script>

