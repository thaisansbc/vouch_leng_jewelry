<?php defined('BASEPATH') OR exit('No direct script access allowed');
$v = "";
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}else if($tax->biller_id){
	$v .= "&biller=" . $tax->biller_id;
}
if ($this->input->post('type')) {
    $v .= "&type=" . $this->input->post('type');
}else if($tax->type){
	$v .= "&type=" . $tax->type;
}
if ($this->input->post('from_date')) {
    $v .= "&from_date=" . $this->input->post('from_date');
}else if($tax->from_date){
	$v .= "&from_date=" . $this->bpas->hrsd($tax->from_date);
}
if ($this->input->post('to_date')) {
    $v .= "&to_date=" . $this->input->post('to_date');
}else if($tax->to_date){
	$v .= "&to_date=" . $this->bpas->hrsd($tax->to_date);
}
if($tax->id){
	$v .= "&tax_id=" . $tax->id;
}
?>
<script type="text/javascript">
	$(document).ready(function () {
		$(document).on("change", "#biller, #type, #from_date, #to_date", function () {	
			$(".search").click();
		});
		function exchange_rate_input(x){
			return '<input type="text" disabled value="'+x+'" class="form-control text-right enable_input exchange_rate" name="exchange_rate[]" style="width:100%"/>';
		}
		function tax_reference_input(x){
			return '<input type="text" disabled value="'+x+'" class="form-control text-right enable_input tax_reference" name="tax_reference[]" style="width:100%"/>';
		}
		if($("#type").val() == "sale"){
			var transaion_link = "invoice_link";
		}else if($("#type").val() == "expense"){
			var transaion_link = "expense_link";
		}else{
			var transaion_link = "purchase_link";
		}

        var oTable = $('#APTable').dataTable({
            "aaSorting": [[8, "desc"],[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": -1,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('taxs/getTransactions?v=1'.$v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
				{"mRender": checkbox, "bSortable" : false }, 
				{"mRender": fsd},
				null,
				null,
				{"mRender": currencyFormat},
				{"mRender": currencyFormat},
				{"mRender": currencyFormat},
				{"mRender": exchange_rate_input},
				{"mRender": tax_reference_input},
				{"bVisible" : false}
			],
			'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
				if(aData[9] > 0){
					var checkbox = $('td:eq(0)', nRow);	
					var exchange_rate = $('td:eq(7)', nRow);
					var tax_reference = $('td:eq(8)', nRow);
					checkbox.find(".multi-select").prop('checked', true);
					exchange_rate.find(".enable_input").prop("disabled", false);
					tax_reference.find(".enable_input").prop("disabled", false);
				}
				nRow.id = aData[0];
				nRow.className = transaion_link;
                return nRow;
            },
			"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0, vat=0, grand_total = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total += parseFloat(aaData[aiDisplay[i]][4]);
					vat += parseFloat(aaData[aiDisplay[i]][5]);
                    grand_total += parseFloat(aaData[aiDisplay[i]][6]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[4].innerHTML = currencyFormat(total);
				nCells[5].innerHTML = currencyFormat(vat);
                nCells[6].innerHTML = currencyFormat(grand_total);
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?= lang('reference_no') ?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?= lang('name') ?>]", filter_type: "text", data: []},
        ], "footer");
		
		$('.multi-select').live('ifChecked',function(){
			var parent =  $(this).closest('tr');
			parent.find('.enable_input').prop("disabled", false);
		});
		$('.multi-select').live('ifUnchecked',function(){
			var parent =  $(this).closest('tr');
			parent.find('.enable_input').prop("disabled", true);
		});
		var old_exchange_rate;
		$(document).on("focus", '.exchange_rate', function () {
			old_exchange_rate = $(this).val();
		}).on("change", '.exchange_rate', function () {
			var row = $(this).closest('tr');
			if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
				$(this).val(old_exchange_rate);
				bootbox.alert(lang.unexpected_value);
				return;
			}
		}); 
	});
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('edit_tax'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
					$attrib = array('data-toggle' => 'validator', 'role' => 'form');
					echo admin_form_open_multipart("taxs/edit_tax/".$tax->id, $attrib);
                ?>
                <div class="row">
					<div class="col-md-12">
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("date", "date"); ?>
								<?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : $this->bpas->hrld($tax->date)), 'class="form-control input-tip datetime" id="date" required="required"'); ?>
							</div>
						</div>
						<?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "biller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $tax->biller_id), 'id="biller" data-placeholder="' . $this->lang->line("select") . ' ' . $this->lang->line("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type' => 'hidden',
                                'name' => 'biller',
                                'id' => 'biller',
                                'value' => $this->session->userdata('biller_id'),
                            );
                            echo form_input($biller_input);
                        } ?>
						<div class="col-md-12">
                            <div class="panel panel-warning">
                                <div class="panel-heading"><?= lang('please_select_these_before_adding_data') ?></div>
                                <div class="panel-body" style="padding: 5px;">
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("type", "type"); ?>
											<?php
											$type_opt["purchase"] = lang("purchase");
											$type_opt["expense"] = lang("expense");
											$type_opt["sale"] = lang("sale");
											echo form_dropdown('type', $type_opt,  (isset($_POST['type']) ? $_POST['type'] : $tax->type), 'id="type" required="required" class="form-control input-tip select" style="width:100%;"');
											?>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("from_date", "from_date"); ?>
											<?php echo form_input('from_date', (isset($_POST['from_date']) ? $_POST['from_date'] : $this->bpas->hrsd($tax->from_date)), 'class="form-control input-tip date" id="from_date" required="required"'); ?>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<?= lang("to_date", "to_date"); ?>
											<?php echo form_input('to_date', (isset($_POST['to_date']) ? $_POST['to_date'] : $this->bpas->hrsd($tax->to_date)), 'class="form-control input-tip date" id="to_date" required="required"'); ?>
										</div>
									</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("order_items"); ?> *</label>
                                <div class="controls table-controls">
									<table id="APTable" class="table table-bordered table-striped table-hover table-condensed accountings-table dataTable">
										<thead>
											<tr>
												<th style="min-width:30px !important; width: 30px !important; text-align: center;">
													<input class="checkbox checkth" type="checkbox" name="check"/>
												</th>
												<th><?= lang("date") ?></th>
												<th><?= lang("reference_no") ?></th>
												<th><?= lang("name") ?></th>
												<th><?= lang("total") ?></th>
												<th><?= lang("vat") ?></th>
												<th><?= lang("grand_total") ?></th>
												<th><?= lang("exchange_rate") ?></th>
												<th><?= lang("tax_reference") ?></th>
												<th><?= lang("tax_id") ?></th>
											</tr>	
										</thead>
										<tbody>
											<tr>
												<td colspan="10" class="dataTables_empty"><?= lang("loading_data"); ?></td>
											</tr>
										</tbody>
										<tfoot>
											<tr>
												<th style="min-width:30px; width: 30px; text-align: center;">
													<input class="checkbox checkth" type="checkbox" name="check"/>
												</th>
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
                        <div class="col-sm-12">
                            <div class="fprom-group">
								<?php echo form_submit('search', $this->lang->line("search"), 'class="btn btn-primary search hidden"'); ?> 
								<?php echo form_submit('edit_tax', $this->lang->line("submit"), 'id="edit_tax" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
							</div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

