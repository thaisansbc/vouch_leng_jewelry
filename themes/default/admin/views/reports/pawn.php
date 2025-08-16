<?php defined('BASEPATH') OR exit('No direct script access allowed'); 

	$v = "";
	if ($this->input->post('biller')) {
		$v .= "&biller=" . $this->input->post('biller');
	}
	if ($this->input->post('reference_no')) {
		$v .= "&reference_no=" . $this->input->post('reference_no');
	}
	if ($this->input->post('start_date')) {
		$v .= "&start_date=" . $this->input->post('start_date');
	}
	if ($this->input->post('end_date')) {
		$v .= "&end_date=" . $this->input->post('end_date');
	}
	
	if ($this->input->post('customer')) {
		$v .= "&customer=" . $this->input->post('customer');
	}
	
?>

<script>
    $(document).ready(function () {
        oTable = $('#SARData').dataTable({
            "aaSorting": [[0, "desc"] , [1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/getPawnReport/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[11];
				nRow.className = "pawn_link";
                return nRow;
            },
            "aoColumns": [
			{"sClass" : "Left", "mRender": fld}, 
			{"sClass": "Left"}, 
			{"sClass": "Left"}, 
			{"sClass": "Left"},
			{"mRender": currencyFormat, "bSearchable" : false},
			{"mRender": currencyFormat, "bSearchable" : false},
			{"mRender": currencyFormat, "bSearchable" : false},
			{"mRender": currencyFormat, "bSearchable" : false},
			{"mRender": currencyFormat, "bSearchable" : false},
			{"mRender" : row_status},
			{"mRender" : row_status}],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var principal = 0,total_return = 0, total_purchase = 0 , balance = 0,payment_rate = 0;
				for (var i = 0; i < aaData.length; i++) {
					principal 		+= parseFloat(aaData[aiDisplay[i]][4]);
					total_return 	+= parseFloat(aaData[aiDisplay[i]][5]);
					total_purchase 	+= parseFloat(aaData[aiDisplay[i]][6]);
					balance		 	+= parseFloat(aaData[aiDisplay[i]][7]);
					payment_rate	+= parseFloat(aaData[aiDisplay[i]][8]);
				}
                var nCells = nRow.getElementsByTagName('th');
				nCells[4].innerHTML = currencyFormat(parseFloat(principal));
				nCells[5].innerHTML = currencyFormat(parseFloat(total_return));
				nCells[6].innerHTML = currencyFormat(parseFloat(total_purchase));
				nCells[7].innerHTML = currencyFormat(parseFloat(balance));
				nCells[8].innerHTML = currencyFormat(parseFloat(payment_rate));
            }   
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?>](yyyy-mm-dd)]", filter_type: "text", data: []},
			{column_number: 1, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
			{column_number: 9, filter_default_label: "[<?=lang('pawn_status');?>]", filter_type: "text", data: []},
			{column_number: 10, filter_default_label: "[<?=lang('payment_status');?>]", filter_type: "text", data: []},
	   ], "footer");
    });
</script>
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
		}else{
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
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('pawn_report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
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
                <li class="dropdown hidden">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        
		<div class="row">
		
            <div class="col-lg-12">
                
				<p class="introtext"><?= lang('customize_report'); ?></p>
				
                <div id="form">
				
                    <?php echo form_open("reports/pawn"); ?>
					
                    <div class="row">
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>

                            </div>
                        </div>
						
						  <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
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
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer_id" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
                            </div>
                        </div>
						
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control date" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control date" id="end_date"'); ?>
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
                    <table id="SARData"
                           class="table table-bordered table-hover table-striped table-condensed reports-table">
                        <thead>
                        <tr>
							<th width="150"><?= lang("date"); ?></th>
                            <th	width="150"><?= lang("reference_no"); ?></th>
                            <th	width="150"><?= lang("biller"); ?></th>
                            <th	width="150"><?= lang("customer"); ?></th>
							<th	width="150"><?= lang("principal"); ?></th>
							<th	width="150"><?= lang("return"); ?></th>
							<th	width="150"><?= lang("purchase"); ?></th>
							<th	width="150"><?= lang("balance"); ?></th>
							<th	width="150"><?= lang("payment_rate"); ?></th>
							<th	width="150"><?= lang("pawn_status"); ?></th>
							<th	width="150"><?= lang("payment_status"); ?></th>
						</tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
<script type="text/javascript">
	$(function(){
		
		$('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getpawn_report/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getpawn_report/0/xls/?v=1'.$v)?>";
            return false;
        });
	});
</script>