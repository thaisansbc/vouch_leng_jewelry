<?php
    $v = "";
    if ($this->input->post('reference_no')) {
        $v .= "&reference_no=" . $this->input->post('reference_no');
    }
    if ($this->input->post('start_date')) {
        $v .= "&start_date=" . $this->input->post('start_date');
    }
    if ($this->input->post('end_date')) {
        $v .= "&end_date=" . $this->input->post('end_date');
    }
?>
<style>
	.disabled {
	   pointer-events: none;
	   cursor: default;
	  
	}
	.disabled i{
		 color:gray;
	}
	.table { white-space: nowrap !important; }
	.table {
		width: 100%;
		display: block;
		overflow-y: scroll;
		white-space: nowrap;
	}
</style>
<script>
    $(document).ready(function () {
        var oTable = $('#SupData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true,
			'bServerSide': true,
			"bStateSave": true,
			"fnStateSave": function (oSettings, oData) {
				localStorage.setItem('DataTables_' + window.location.pathname, JSON.stringify(oData));
			},
			"fnStateLoad": function (oSettings) {
				var data = localStorage.getItem('DataTables_' + window.location.pathname);
				return JSON.parse(data);
			},
            'sAjaxSource': '<?= admin_url('account/getPaymenttransferReport').'/?v=1'.$v ?>',
			"bAutoWidth": false ,
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{
                "bSortable": false,
                "mRender": checkbox
            },  
			{"width": "25px"},
			null, 
			{"mRender": fld }, 
			null, 
			null,
			null,
            null,
			null, 
		
			{"mRender":currencyFormat},
			{"mRender":currencyFormat},
			null,
			{"bSortable": false}],
			"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total_debit = 0, total_credit = 0;
                for (var i = 0; i < aaData.length; i++) {
					if(isNaN(parseFloat(aaData[aiDisplay[i]][9]))){
						total_debit += parseFloat(0);
					}else{
						total_debit += parseFloat(aaData[aiDisplay[i]][9]);
					}
					
					if(isNaN(parseFloat(aaData[aiDisplay[i]][10]))){
						total_credit += parseFloat(0);
					}else{
						total_credit += parseFloat(aaData[aiDisplay[i]][10]);
					}
                }
               var nCells = nRow.getElementsByTagName('th');
                nCells[9].innerHTML = currencyFormat(parseFloat(total_debit));
                nCells[10].innerHTML = currencyFormat(parseFloat(total_credit));
            },
			'fnRowCallback': function (nRow, aData, iDisplayIndex) {
				if(aData[2] != 'JOURNAL'){
					nRow.id = aData[0];
					$('td:eq(14)', nRow).addClass( 'disabled' );
				}
                return nRow;
            }
        }).dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('no');?>]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('date');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('reference');?>]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},

            {column_number: 6, filter_default_label: "[<?=lang('account_code');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('account_name');?>]", filter_type: "text", data: []},

            {column_number: 8, filter_default_label: "[<?=lang('description');?>]", filter_type: "text", data: []},
            {column_number: 9, filter_default_label: "[<?=lang('debit');?>]", filter_type: "text", data: []},
            {column_number: 10, filter_default_label: "[<?=lang('credit');?>]", filter_type: "text", data: []},
			{column_number: 11, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
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
        $("#product").autocomplete({
            source: '<?= admin_url('reports/suggestions'); ?>',
            select: function (event, ui) {
                $('#product_id').val(ui.item.id);
                //$(this).val(ui.item.label);
            },
            minLength: 1,
            autoFocus: false,
            delay: 300,
        });
    });
</script>
<?php //if ($Owner) {
    echo admin_form_open('account/journal_actions', 'id="action-form"');
//} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('payment_transfer_report'); ?></h2>

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
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
            		<i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                    <?php if ($Owner || $Admin || $GP['accounts-add']) { ?>
                        <li><a href="<?= admin_url('account/add_journal'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal"
                               id="add"><i class="fa fa-plus-circle"></i> <?= lang("add_journal"); ?></a></li>
                    <?php } ?>
                    <?php if ($Owner || $Admin || $GP['accounts-import']) { ?>
                        <li><a href="<?= admin_url('account/import_journal_csv'); ?>" data-toggle="modal"
                               data-target="#myModal"><i class="fa fa-plus-circle"></i> <?= lang("add_journal_by_csv"); ?>
                            </a></li>
                    <?php } ?>
						<?php if ($Owner || $Admin) { ?>
							<li><a href="#" id="excel" data-action="export_excel"><i
										class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?></a></li>
							<li><a href="#" id="pdf" data-action="export_pdf"><i
                                    class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?></a></li>
						<?php }else{ ?>
							<?php if($GP['accounts-export']) { ?>
								<li>
									<a href="#" id="excel" data-action="export_excel"><i
										class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?></a>
								</li>
								<li><a href="#" id="pdf" data-action="export_pdf"><i
                                    class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?></a>
								</li>
							<?php }?>
						<?php }?>			
                    </ul>
                </li>
            </ul>
        </div>
    </div>
<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action"/>
    <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
</div>
<?php echo form_close(); ?>
	<div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">

                    <?php echo admin_form_open("account/listJournal"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang("reference_no"); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ""), 'class="form-control date" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ""), 'class="form-control date" id="end_date"'); ?>
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
                    <table id="SupData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="primary">
                            <th style="min-width:5%; width: 5%; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
							<th style='width:5%'><?= lang("no"); ?></th>
							<th><?= lang("type"); ?></th>
                            <th><?= lang("date"); ?></th>
                            <th><?= lang("reference_no"); ?></th>
							<th><?= lang("biller"); ?></th>
					
                            <th><?= lang("account_code"); ?></th>
                            <th><?= lang("account_name"); ?></th>
						
                            <th><?= lang("description"); ?></th>
                            <th><?= lang("debit"); ?></th>
							<th><?= lang("credit"); ?></th>
							<th><?= lang("created_by"); ?></th>
                            <th style="text-align:center;"><?= lang("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
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
                           
                            <th></th>
							<th></th>
                            <th><?= lang("[actions]"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

	

