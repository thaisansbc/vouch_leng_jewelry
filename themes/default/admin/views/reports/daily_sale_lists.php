<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";
if ($this->input->post('customer')) {
    $v .= "&customer=" . $this->input->post('customer');
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
if ($this->input->post('user')) {
    $v .= "&user=" . $this->input->post('user');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
if ($this->input->post('sale_type')) {
    $v .= "&sale_type=" . $this->input->post('sale_type');
}
if ($this->input->post('sale_tax')) {
    $v .= "&sale_tax=" . $this->input->post('sale_tax');
}
?>
<script>
    $(document).ready(function () {
		oTable = $('#MLTable').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?=$Settings->rows_per_page?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/get_daily_sales?v=1&'. $v)?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?=$this->security->get_csrf_token_name()?>",
                    "value": "<?=$this->security->get_csrf_hash()?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                return nRow;
            },
            "aoColumns": [
			{"sClass" : "center", "mRender": fsd}, 
			{"mRender" : formatQuantity},
			{"mRender" : formatQuantity},
			{"mRender" : currencyFormat},
			{"mRender" : currencyFormat}, 
			{"mRender" : currencyFormat}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
				var total_sale = 0, total_qty =0, total_grand = 0, total_cost = 0, total_margin = 0;
				for (var i = 0; i < aaData.length; i++) {
					total_sale += parseFloat(aaData[aiDisplay[i]][1]);
					total_qty += parseFloat(aaData[aiDisplay[i]][2]);
					total_grand += parseFloat(aaData[aiDisplay[i]][3]);
					total_cost += parseFloat(aaData[aiDisplay[i]][4]);
					total_margin += parseFloat(aaData[aiDisplay[i]][5]);
				}
				var nCells = nRow.getElementsByTagName('th');
				nCells[1].innerHTML = formatQuantity(parseFloat(total_sale));
				nCells[2].innerHTML = formatQuantity(parseFloat(total_qty));
				nCells[3].innerHTML = currencyFormat(parseFloat(total_grand));
				nCells[4].innerHTML = currencyFormat(parseFloat(total_cost));
				nCells[5].innerHTML = currencyFormat(parseFloat(total_margin));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
			
        ], "footer");
    });
</script>

<?php echo admin_form_open("reports/daily_sale_lists", ' id="form-submit" '); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('daily_sale_lists'); ?></h2>
		
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
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
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
                                <label class="control-label" for="sale_tax"><?= lang("sale_tax"); ?></label>
                                <?php
                                $stax[""] = lang('select').' '.lang('sale_tax');
                                $stax["yes"] = lang('yes');
								$stax["no"] = lang('no');
                                echo form_dropdown('sale_tax', $stax, (isset($_POST['sale_tax']) ? $_POST['sale_tax'] : ""), 'class="form-control" id="sale_tax" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("sale_tax") . '"');
                                ?>
                            </div>
                        </div>
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="sale_type"><?= lang("sale_type"); ?></label>
                                <?php
                                $st[""] = lang('select').' '.lang('sale_type');
                                $st["sale"] = lang('sale');
								$st["pos"] = lang('pos');
                                echo form_dropdown('sale_type', $st, (isset($_POST['sale_type']) ? $_POST['sale_type'] : ""), 'class="form-control" id="sale_type" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("sale_type") . '"');
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
                    <table id="MLTable" class="table table-bordered table-hover table-striped dataTable">
                        <thead>
                        <tr>
                            <th><?= lang("date"); ?></th>
							<th><?= lang("sales"); ?></th>
							<th><?= lang("item_qty"); ?></th>
                            <th><?= lang("grand_total"); ?></th>
                            <th><?= lang("cost"); ?></th>
							<th><?= lang("gross_margin"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="6" class="dataTables_empty"><?= lang("loading_data"); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
							<tr class="active">
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
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
            window.location.href = "<?=site_url('reports/get_daily_sales/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/get_daily_sales/0/xls/?v=1'.$v)?>";
            return false;
        });
		$("#biller").change(biller);biller();
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

<style>
	<?php if(!$Owner && !$Admin && !$this->session->userdata('show_cost')){ ?>
		#MLTable td:nth-child(5),#MLTable th:nth-child(5) {
		   display: none !important;
		}
		#MLTable td:nth-child(6),#MLTable th:nth-child(6) {
		   display: none !important;
		}
	<?php } ?>
	
</style>
