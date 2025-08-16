<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$v = "";

// if ($this->input->post('reference_no')) {
//     $v .= "&reference_no=" . $this->input->post('reference_no');
// }
if ($this->input->post('customer')) {
    $v .= "&customer=" . $this->input->post('customer');
}
if ($this->input->post('saleman')) {
    $v .= "&saleman=" . $this->input->post('saleman');
}
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
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
?>

<script>
    $(document).ready(function () {
        oTable = $('#LTable').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[-1], ["<?= lang('all') ?>"]],
            "iDisplayLength": -1,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('calendar/getTickets'.($biller_id ?'/'.$biller_id:'').'?v=1'.$v); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
				{"bSortable": false,"mRender": checkbox},
				{"sClass":"left"},
                {"bSortable": false},
                null,
				{"mRender": fld},
				
				{"mRender": row_status},			
				{"bSortable": false}
			],
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "view_ticket_link";
                return nRow;var oSettings = oTable.fnSettings();
                return nRow;
            },
		
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('title');?>]", filter_type: "text", data: []},
			{column_number: 2, filter_default_label: "[<?=lang('description');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('limit');?>]", filter_type: "text", data: []},
			{column_number: 4, filter_default_label: "[<?=lang('created_date');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
        $('#form').hide();
        $('.toggle_down').click(function() {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function() {
            $("#form").slideUp();
            return false;
        });
    });
</script>
<?php 
/*if ($Owner || $GP['bulk_actions']) {
	    echo admin_form_open('calendar/calendar_actions', 'id="action-form"');
	}*/
?>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-solid fa-ticket"></i><?= lang('tickets'); ?></h2>
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
                <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                    <i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i>
                </a>
                <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                   <li>
                        <a href="<?= admin_url('calendar/add_ticket') ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                            <i class="fa fa-plus-circle"></i> <?= lang('add_ticket') ?>
                        </a>
                    </li>
                    <li>
                        <a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> 
                            <?= lang('export_to_excel') ?>
                        </a>
                    </li>   
                    <li class="divider"></li>
                    <li>
                        <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_calendars") ?></b>"
                            data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                            data-html="true" data-placement="left">
                        <i class="fa fa-trash-o"></i> <?= lang('delete_template') ?>
                         </a>
                     </li>
            
                </ul>
            </li>
        </ul>
    </div>
</div>
<div class="box">
  
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div id="form">
                    <?php echo admin_form_open("calendar/events"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('customer', 'customer'); ?>
                
                                        <?php if ($Owner || $Admin || $GP['customers-add']) { ?><div class="input-group"><?php } ?>
                                        <?php
                                        $cust[] = lang('select');
                                        foreach ($customers as $customer) {
                                            $cust[$customer->id] = $customer->text;
                                        }
                                        echo form_dropdown('customer', $cust, (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control"  id="" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '" required="required"');
                                        ?>

                                        <div class="input-group-addon no-print" style="padding: 2px 5px; border-left: 0;">
                                            <!-- <a href="#" id="view-customer" class="external" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                                <i class="fa fa-2x fa-user" id="addIcon"></i>
                                            </a> -->
                                        </div>
                                    </div>                 
                            </div>
                        </div>
                        <?php if (($this->Owner || $this->Admin) || empty($count_billers)) { ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
                        <?php } elseif (!empty($billers) && count($count_billers) > 1) { ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                <?php
                                    $bl[""] = "";
                                    $biller_id_ = $count_billers;
                                    foreach ($billers as $biller) {
                                        foreach ($biller_id_ as $key => $value) {
                                            if ($biller->id == $value) {
                                                $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                            }
                                        }
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('saleman', 'saleman'); ?>
                                <?php
                                $opt[''] = '';
                                foreach ($salemans as $saleman) {
                                    $opt[$saleman->id] = $saleman->first_name .' '.$saleman->last_name;
                                }
                                ?>
                                <?= form_dropdown('saleman', $opt, (isset($_POST['saleman']) ? $_POST['saleman'] : ""), 'class="form-control" id="saleman" style="width:100%;" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("saleman") . '"'); ?>
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
                    <div class="form-group" style="margin-bottom: 20px;">
                        <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
        <?php if ($Owner || $GP['bulk_actions']) {
                    echo admin_form_open('sales/sale_actions', 'id="action-form"');
                }
                ?>
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div class="table-responsive">
                    <table id="LTable" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            
							<th><?= lang("schedule") ?></th>
							<th><?= lang("ticket_code") ?></th>
                            <th><?= lang("assign_to") ?></th>
							<th><?= lang("create_date") ?></th>
							<th><?= lang("status") ?></th>
							<th width='5%'><?= lang("action") ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="7" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
							<th>&nbsp;</th>
							<th></th>
                            <th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($Owner || $GP['bulk_actions']) {?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php } ?>