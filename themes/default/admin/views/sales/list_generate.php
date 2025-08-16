<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    function gen_status(x) {
        if (x == 0) {
            return '<div class="text-center"><span class="label label-warning">' + lang['pending'] + '</span></div>';
        } else {
            return '<div class="text-center"><span class="label label-success">' + lang['completed'] + '</span></div>';
        }
    }
    $(document).ready(function () {
        oTable = $('#QUData').dataTable({
            "aaSorting": [[1, "desc"], [2, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('sales/getListGenerate' . ($biller_id ? '/' . $biller_id : '')) ?>',
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
                nRow.className = "quote_link";
                return nRow;
            },
            "aoColumns": [
                {"bSortable": false,"mRender": checkbox}, 
                {"mRender": fld}, 
                null, null, 
                null, 
                null, 
                {"mRender": currencyFormat}, 
                {"mRender": gen_status},
                null, 
                {"bSortable": false}
            ]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('saleman');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('total');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
        ], "footer");
        <?php if ($this->session->userdata('remove_quls')) {
    ?>
        if (localStorage.getItem('quitems')) {
            localStorage.removeItem('quitems');
        }
        if (localStorage.getItem('qudiscount')) {
            localStorage.removeItem('qudiscount');
        }
        if (localStorage.getItem('qutax2')) {
            localStorage.removeItem('qutax2');
        }
        if (localStorage.getItem('qushipping')) {
            localStorage.removeItem('qushipping');
        }
        if (localStorage.getItem('quref')) {
            localStorage.removeItem('quref');
        }
        if (localStorage.getItem('quwarehouse')) {
            localStorage.removeItem('quwarehouse');
        }
        if (localStorage.getItem('qusupplier')) {
            localStorage.removeItem('qusupplier');
        }
        if (localStorage.getItem('qunote')) {
            localStorage.removeItem('qunote');
        }
        if (localStorage.getItem('qucustomer')) {
            localStorage.removeItem('qucustomer');
        }
        if (localStorage.getItem('qubiller')) {
            localStorage.removeItem('qubiller');
        }
        if (localStorage.getItem('qucurrency')) {
            localStorage.removeItem('qucurrency');
        }
        if (localStorage.getItem('qudate')) {
            localStorage.removeItem('qudate');
        }
        if (localStorage.getItem('qustatus')) {
            localStorage.removeItem('qustatus');
        }
        <?php $this->bpas->unset_data('remove_quls');
} ?>
    });

</script>

<?php if ($Owner || $GP['bulk_actions']) {
    echo admin_form_open('quotes/quote_actions', 'id="action-form"');
} ?>
<div class="breadcrumb-header">
    <?php $biller_title = ($biller_id ? $biller->name : ((isset($user_biller) && !empty($user_biller)) ? $user_biller->name : lang('all_billers'))); ?>
    <h2 class="blue"><i class="fa-fw fa fa-heart-o"></i><?= lang('list_generate') . ' (' . $biller_title . ')';?></h2>
    <div class="box-icon">
        <ul class="btn-tasks">
            <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i></a>
                <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                    <li>
                        <a href="<?= admin_url('sales/add_generate') ?>">
                            <i class="fa fa-plus-circle"></i> <?= lang('add_generate') ?>
                        </a>
                    </li>
                    <li>
                        <a href="#" id="excel" data-action="export_excel">
                            <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                        </a>
                    </li>
                    <li class="divider"></li>
                    <li>
                        <a href="#" class="bpo" title="<b><?= $this->lang->line('delete_quotes') ?></b>" 
                            data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" 
                            data-html="true" data-placement="left"><i class="fa fa-trash-o"></i> <?= lang('delete_quotes') ?>
                        </a>
                    </li>
                </ul>
            </li>
            <?php if (($this->Owner || $this->Admin) || empty($count_billers)) { ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('billers') ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('quotes') ?>"><i class="fa fa-building-o"></i> <?= lang('all_billers') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        foreach ($billers as $biller) {
                            echo '<li><a href="' . admin_url('quotes/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company.'/'.$biller->name . '</a></li>';
                        } ?>
                    </ul>
                </li>
            <?php } elseif (!empty($billers)){ ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('billers') ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('quotes') ?>"><i class="fa fa-building-o"></i> <?= lang('all_billers') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        $biller_id_ = $count_billers;
                        foreach ($billers as $biller) {
                            foreach ($biller_id_ as $key => $value) {
                                if ($biller->id == $value) {
                                    echo '<li><a href="' . admin_url('quotes/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company.'/'.$biller->name . '</a></li>';
                                }
                            }
                        } ?>
                    </ul>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>
<div class="box">

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="QUData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th style="min-width:150px; width: 150px; text-align: center;"><?= lang('date'); ?></th>
                            <th><?= lang('reference_no'); ?></th>
                            <th><?= lang('biller'); ?></th>
                            <th><?= lang('month'); ?></th>
                            <th><?= lang('zone'); ?></th>
                            <th><?= lang('total_invoice'); ?></th>
                            <th><?= lang('status'); ?></th>
                            <th><?= lang('created_by'); ?></th>

                            <th style="width:115px; text-align:center;"><?= lang('actions'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="10"
                                class="dataTables_empty"><?= lang('loading_data'); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check"/>
                            </th>
                            <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                       
                            <th style="width:115px; text-align:center;"><?= lang('actions'); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) {
                                ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php
                            } ?>