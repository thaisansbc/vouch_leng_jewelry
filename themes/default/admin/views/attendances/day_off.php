<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script>
    $(document).ready(function () {
        oTable = $('#day_off_data').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('attendances/getDayOff/'); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"mRender": checkbox}, {"mRender": fld}, null, {"mRender": decode_html}, {"bSortable": false,"mRender": attachment}, {"bSortable": false}],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[0];
                nRow.className = "day_off_link";
				var action = $('td:eq(6)', nRow);
                return nRow;
            },
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('note');?>]", filter_type: "text", data: []},
        ], "footer");


        <?php if ($this->session->userdata('remove_dfls')) { ?>
            if (localStorage.getItem('dflsitems')) {
				localStorage.removeItem('dflsitems');
			}
			if (localStorage.getItem('dflsnote')) {
				localStorage.removeItem('dflsnote');
			}
			if (localStorage.getItem('dflsdate')) {
				localStorage.removeItem('dflsdate');
			}
			if (localStorage.getItem('dflsbiller')) {
				localStorage.removeItem('dflsbiller');
			}
        <?php $this->bpas->unset_data('remove_dfls');} ?>
    });
</script>

<?php if ($Owner || $Admin || $GP['bulk_actions']) {
        echo admin_form_open('attendances/day_off_actions', 'id="action-form"');
    }
?>
<div class="box">
    <div class="box-header">
		<h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('day_off'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= admin_url('attendances/add_day_off') ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('add_day_off') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        
						<li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" 
                                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                                data-html="true" data-placement="left">
                            <i class="fa fa-trash-o"></i> <?= lang('delete_day_off') ?>
                             </a>
                         </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div class="table-responsive">
                    <table id="day_off_data" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
							<tr>
								<th style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkft" type="checkbox" name="check"/>
								</th>
								<th><?= lang("date"); ?></th>
								<th><?= lang("created_by"); ?></th>
								<th><?= lang("note"); ?></th>
								<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
								<th style="min-width:75px; text-align:center;"><?= lang("actions"); ?></th>
							</tr>
                        </thead>
                        <tbody>
							<tr>
								<td colspan="6" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
							</tr>
                        </tbody>
                        <tfoot class="dtFilter">
							<tr class="active">
								<th style="min-width:30px; width: 30px; text-align: center;">
									<input class="checkbox checkft" type="checkbox" name="check"/>
								</th>
								<th></th><th></th><th></th>
								<th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
								<th style="width:75px; text-align:center;"><?= lang("actions"); ?></th>
							</tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($Owner || $Admin || $GP['bulk_actions']) {?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php } ?>
