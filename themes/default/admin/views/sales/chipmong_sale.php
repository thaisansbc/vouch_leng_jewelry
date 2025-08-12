<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$v = "";
if ($this->input->post('biller')) {
    $v .= "&biller=" . $this->input->post('biller');
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
} ?>
<script>
$(document).ready(function() {
    function send_status(x) {
        if (x == 0) {
            return '<div class="text-center"><span class="payment_status label label-warning">Pending</span></div>';
        } else {
            return '<div class="text-center"><span class="payment_status label label-default">Sent</span></div>';
        }
    }
    oTable = $('#SLData').dataTable({
        "aaSorting": [
            [2, "desc"],
            [3, "desc"]
        ],
        "aLengthMenu": [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "<?= lang('all') ?>"]
        ],
        "iDisplayLength": <?= $Settings->rows_per_page ?>,
        'bProcessing': true,
        'bServerSide': true,
        'sAjaxSource': '<?= admin_url('chipmong/getSales' . ($biller_id ? '/' . $biller_id : '') . '?v=1' . $v . ($this->input->get('shop') ? '&shop=' . $this->input->get('shop') : '') . ($this->input->get('attachment') ? '&attachment=' . $this->input->get('attachment') : '') . ($this->input->get('delivery') ? '&delivery=' . $this->input->get('delivery') : '')); ?>',
        'fnServerData': function(sSource, aoData, fnCallback) {
            aoData.push({
                "name": "<?= $this->security->get_csrf_token_name() ?>",
                "value": "<?= $this->security->get_csrf_hash() ?>"
            });
            $.ajax({
                'dataType': 'json',
                'type': 'POST',
                'url': sSource,
                'data': aoData,
                'success': fnCallback
            });
        },
        'fnRowCallback': function(nRow, aData, iDisplayIndex) {
            var oSettings = oTable.fnSettings();
            nRow.id = aData[0];
            var action =$('td:eq(14)',nRow);
            if(aData[13] == 1) {
                action.find('.delete').remove();
            }
            return nRow;
        },
        "aoColumns": [
            { "bSortable": false, "mRender": checkbox },
            { "mRender": fd },
            { "mRender": currencyFormat },
            { "mRender": currencyFormat },
            { "mRender": currencyFormat },
            { "mRender": currencyFormat },
            { "mRender": currencyFormat },
            { "mRender": currencyFormat },
            { "mRender": currencyFormat },
            { "mRender": currencyFormat },
            { "mRender": currencyFormat },
            { "mRender": currencyFormat },
            { "mRender": currencyFormat },
            { "mRender": send_status },
            { "bSortable": false }
        ],
        "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
            var gross_sale         = 0,
                tax_amount         = 0,
                net_sale           = 0,
                net_cash_sales     = 0,
                credit_card_amount = 0,
                other_amount       = 0,
                total_credit_card  = 0,
                total_transaction  = 0,
                deposit_usd        = 0,
                deposit_riel       = 0;
            for (var i = 0; i < aaData.length; i++) {
                gross_sale         += parseFloat(aaData[aiDisplay[i]][2]);
                tax_amount         += parseFloat(aaData[aiDisplay[i]][3]);
                net_sale           += parseFloat(aaData[aiDisplay[i]][4]);
                net_cash_sales     += parseFloat(aaData[aiDisplay[i]][5]);
                credit_card_amount += parseFloat(aaData[aiDisplay[i]][6]);
                other_amount       += parseFloat(aaData[aiDisplay[i]][7]);
                total_credit_card  += parseFloat(aaData[aiDisplay[i]][8]);
                total_transaction  += parseFloat(aaData[aiDisplay[i]][9]);
                deposit_usd        += parseFloat(aaData[aiDisplay[i]][10]);
                deposit_riel       += parseFloat(aaData[aiDisplay[i]][11]);
            }
            var nCells = nRow.getElementsByTagName('th');
            nCells[2].innerHTML  = currencyFormat(parseFloat(gross_sale));
            nCells[3].innerHTML  = currencyFormat(parseFloat(tax_amount));
            nCells[4].innerHTML  = currencyFormat(parseFloat(net_sale));
            nCells[5].innerHTML  = currencyFormat(parseFloat(net_cash_sales));
            nCells[6].innerHTML  = currencyFormat(parseFloat(credit_card_amount));
            nCells[7].innerHTML  = currencyFormat(parseFloat(other_amount));
            nCells[8].innerHTML  = currencyFormat(parseFloat(total_credit_card));
            nCells[9].innerHTML = currencyFormat(parseFloat(total_transaction));
            nCells[10].innerHTML = currencyFormat(parseFloat(deposit_usd));
            nCells[11].innerHTML = currencyFormat(parseFloat(deposit_riel));
        }
    }).fnSetFilteringDelay().dtFilter([   
        { column_number: 1, filter_default_label: "[<?= lang('date'); ?> (yyyy-mm-dd)]", filter_type: "text", data: [] },
        { column_number: 12, filter_default_label: "[<?= lang('exchange_rate'); ?>]", filter_type: "text", data: [] },
        { column_number: 13, filter_default_label: "[<?= lang('status'); ?>]", filter_type: "text", data: [] },
    ], "footer");

});
</script>
<style type="text/css">
    body {
        position: static !important;
        overflow-y: auto !important;
    }
    #form > form > div > div:nth-child(4) > div > input { margin-top: 28px; }
    /*#SLData td:nth-child(3) > div, 
    #SLData td:nth-child(4) > div, 
    #SLData td:nth-child(5) > div, 
    #SLData td:nth-child(6) > div, 
    #SLData td:nth-child(7) > div, 
    #SLData td:nth-child(8) > div, 
    #SLData td:nth-child(9) > div, 
    #SLData td:nth-child(10) > div { width: 5% !important; text-align: right !important; }*/
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('chipmong');?></h2>
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
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
                    <?php echo admin_form_open("chipmong"); ?>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang("biller"); ?></label>
                                <?php
                                    $bl[""] = "";
                                    if (!empty($billers)) {
                                        foreach ($billers as $biller) {
                                            $bl[$biller->id] = $biller->company != '-' ? $biller->company : $biller->name;
                                        }
                                    }
                                    echo form_dropdown('biller', $bl,3, 'class="form-control" readonly');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', date('d/m/Y'), 'class="form-control date" readonly'); ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php echo form_input('end_date', date('d/m/Y'), 'class="form-control date" readonly'); ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?php echo form_submit('submit_report', $this->lang->line("generate_report"), 'class="btn btn-primary"'); ?>
                            </div>
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <?php if ($Owner || $GP['bulk_actions']) {
                    echo admin_form_open('sales/sale_actions', 'id="action-form"');
                } ?>
                <div class="table-responsive"> 
                    <table id="SLData" class="table table-hover table-striped" cellpadding="0" cellspacing="0" border="0">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check" />
                                </th>
                                <th><?= lang('date'); ?></th>
                                <th style="text-align: right !important; width: 5% !important;"><?= lang('gross_sale'); ?></th>
                                <th style="text-align: right !important; width: 5% !important;"><?= lang('tax_amount'); ?></th>
                                <th style="text-align: right !important; width: 5% !important;"><?= lang('net_sale'); ?></th>
                                <th style="text-align: right !important; width: 5% !important;"><?= lang('net_cash_sales'); ?></th>
                                <th style="text-align: right !important; width: 5% !important;"><?= lang('credit_card_amount'); ?></th>
                                <th style="text-align: right !important; width: 5% !important;"><?= lang('other_amount'); ?></th>
                                <th style="text-align: right !important; width: 5% !important;"><?= lang('total_credit_card'); ?></th>
                                <th style="text-align: right !important; width: 5% !important;"><?= lang('total_transaction'); ?></th>
                                <th style="text-align: right !important; width: 5% !important;"><?= lang('deposit_usd'); ?></th>
                                <th style="text-align: right !important; width: 5% !important;"><?= lang('deposit_riel'); ?></th>
                                <th style="text-align: right !important; width: 5% !important;"><?= lang('exchange_rate'); ?></th>
                                <th><?= lang('status'); ?></th>
                                <th style="width:80px; text-align:center;"><?= lang('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="15" class="dataTables_empty"><?= lang('loading_data'); ?></td>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                            <tr class="active">
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkft" type="checkbox" name="check" />
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
                                <th></th>
                                <th></th>
                                <th style="width:80px; text-align:center;"><?= lang('actions'); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action" />
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>