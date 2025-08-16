<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
	function translateType(x){
		if(x == "sale"){
			return '<?= lang("sale") ?>';
		}else if(x == "expense"){
			return '<?= lang("expense") ?>';
		}else{
			return '<?= lang("purchase") ?>';
		}
	}
    $(document).ready(function () {
        oTable = $('#TaxData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('taxs/getTaxs/'.($biller ? $biller->id : ''));?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "tax_link";
                return nRow;
            },
            "aoColumns": [{"mRender": checkbox}, {"mRender": fld}, null, {"mRender": translateType},{"mRender": fsd},{"mRender": fsd},{"mRender": currencyFormat},{"mRender": currencyFormat},{"mRender": currencyFormat},{"bSortable": false}],
			"fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total = 0, vat = 0, grand_total = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total += parseFloat(aaData[aiDisplay[i]][6]);
					vat += parseFloat(aaData[aiDisplay[i]][7]);
                    grand_total += parseFloat(aaData[aiDisplay[i]][8]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[6].innerHTML = currencyFormat(parseFloat(total));
				nCells[7].innerHTML = currencyFormat(parseFloat(vat));
                nCells[8].innerHTML = currencyFormat(parseFloat(grand_total));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('from_date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
			{column_number: 5, filter_default_label: "[<?=lang('to_date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
        ], "footer");
    });
</script>
<?php if ($Owner || $Admin || $GP['bulk_actions']) {
    echo admin_form_open('taxs/tax_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
	  <h2 class="blue"><i class="fa-fw fa fa-heart-o"></i><?= lang('taxs').' ('.($biller ? $biller->name : lang('all_billers')).')'; ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= admin_url('taxs/add_tax') ?>"><i class="fa fa-plus-circle"></i> <?= lang('add_tax') ?>
                            </a>
                        </li>
						<li>
                            <a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?></a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_taxs") ?></b>" 
                                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" 
                                data-html="true" data-placement="left"><i class="fa fa-trash-o"></i> <?= lang('delete_taxs') ?>
                            </a>
                        </li>
                    </ul>
                </li>
				
				<?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
					<li class="dropdown">
						<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
						<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
							<li><a href="<?= site_url('taxs/taxs') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
							<li class="divider"></li>
							<?php
							foreach ($billers as $biller) {
								echo '<li><a href="' . site_url('taxs/taxs/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
							}
							?>
						</ul>
					</liv>
				<?php } ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div class="table-responsive">
                    <table id="TaxData" class="table table-bordered table-hover table-striped">
                        <thead>
							<tr class="active">
								<th style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkft" type="checkbox" name="check"/>
								</th>
								<th><?= lang("date"); ?></th>
								<th><?= lang("biller"); ?></th>       
								<th><?= lang("type"); ?></th>   
								<th><?= lang("from_date"); ?></th>
								<th><?= lang("to_date"); ?></th>
								<th><?= lang("total"); ?></th>
								<th><?= lang("vat"); ?></th>
								<th><?= lang("grand_total"); ?></th>
								<th style="width:115px; text-align:center;"><?= lang("actions"); ?></th>
							</tr>
                        </thead>
                        <tbody>
							<tr>
								<td colspan="10" class="dataTables_empty"><?= lang("loading_data"); ?></td>
							</tr>
                        </tbody>
                        <tfoot class="dtFilter">
							<tr class="active">
								<th style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkft" type="checkbox" name="check"/>
								</th>
								<th></th><th></th><th></th><th></th><th></th></th><th></th><th></th><th></th>
								<th style="width:115px; text-align:center;"><?= lang("actions"); ?></th>
							</tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $Admin || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>