<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-xs-3">
                <table width="100%">
                    <tr>
                        <td width="50%"><img src="<?= base_url('assets/images/'.$customer->attachment?$customer->attachment:'male.png'.'') ?>" class="img-responsive" width="100"></td>
                        <td width="50%"><?= $this->bpas->qrcode('link', urlencode(admin_url('products/view/' . $customer->name)), 2); ?></td>
                    </tr>
                </table>
                <table class="table table-striped" style="margin-bottom:0;">      
                    <tr>
                        <td><strong><?= lang('code'); ?></strong></td>
                        <td>: <?= $customer->code; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang('name'); ?></strong></td>
                        <td>: <?= $customer->name; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang('phone'); ?></strong></td>
                        <td>: <?= $customer->phone; ?></strong></td>
                    </tr>
                    <?php 
                    if($customer->gender){?>
                    <tr>
                        <td><strong><?= lang('gender'); ?></strong></td>
                        <td><?= $customer->gender; ?></strong></td>
                    </tr>
                    <?php }
                    if($customer->age){?>
                        <tr>
                            <td><strong><?= lang('age'); ?></strong></td>
                            <td><?= $customer->age; ?></strong></td>
                        </tr>
                    <?php }
                    ?>
                    <tr>
                        <td><strong><?= lang('email'); ?></strong></td>
                        <td>: <?= $customer->email; ?></strong></td>
                    </tr>
                    <tr>
                        <td><strong><?= lang('address'); ?></strong></td>
                        <td>: <?= $customer->address; ?></strong></td>
                    </tr>
                </table>
            </div>
            <div class="col-xs-5">
                <div class="table-responsive">
                    <table class="table table-striped" style="border-left:1px solid #dddddd;border-right: 1px solid #dddddd;">
                        <tr>
                            <td><strong><?= lang('company'); ?></strong></td>
                            <td>: <?= $customer->company; ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong><?= lang('customer_group'); ?></strong></td>
                            <td>: <?= $customer->customer_group_name; ?></strong></td>
                        </tr>

                        <?php 
                        
                        if($customer->cf1){?>
                        <tr>
                            <td><strong><?= lang('ccf1'); ?></strong></td>
                            <td><?= $customer->cf1; ?></strong></td>
                        </tr>
                        <?php }
                        if($customer->cf2){?>
                        <tr>
                            <td><strong><?= lang('ccf2'); ?></strong></td>
                            <td><?= $customer->cf2; ?></strong></td>
                        </tr>
                        <?php }
                        if($customer->cf3){?>
                        <tr>
                            <td><strong><?= lang('ccf3'); ?></strong></td>
                            <td><?= $customer->cf3; ?></strong></td>
                        </tr>
                        <?php }
                        if($customer->cf4){?>
                        <tr>
                            <td><strong><?= lang('ccf4'); ?></strong></td>
                            <td><?= $customer->cf4; ?></strong></td>
                        </tr>
                        <?php }
                        if ($customer->package) { ?>
                        <tr>
                            <td><strong><?= lang('package'); ?></strong></td>
                            <td>: <?= $customer->package; ?></strong></td>
                        </tr>
                        <?php } if ($customer->vat_no) { ?>
                        <tr>
                            <td><strong><?= lang('vat_no'); ?></strong></td>
                            <td>: <?= $customer->vat_no; ?></strong></td>
                        </tr>
                        <?php } if ($customer->gst_no) { ?>
                        <tr>
                            <td><strong><?= lang('gst_no'); ?></strong></td>
                            <td>: <?= $customer->gst_no; ?></strong></td>
                        </tr>
                        <?php } if ($customer->deposit_amount >0) { ?>
                        <tr>
                            <td><strong><?= lang('deposit'); ?></strong></td>
                            <td>: <?= $this->bpas->formatMoney($customer->deposit_amount); ?></strong></td>
                        </tr>
                        <?php } if ($customer->award_points) { ?>
                        <tr>
                            <td><strong><?= lang('award_points'); ?></strong></td>
                            <td>: <?= $customer->award_points; ?></strong></td>
                        </tr>
                        <?php } if ($customer->city) { ?>
                        <tr>
                            <td><strong><?= lang('city'); ?></strong></td>
                            <td><?= $customer->city; ?></strong></td>
                        </tr>
                        <?php } if (isset($state)) { ?>
                        <tr>
                            <td><strong><?= lang('state'); ?></strong></td>
                            <td><?= $customer->state; ?></strong></td>
                        </tr>
                        <?php }
                        if (isset($zone)) { ?>
                        <tr>
                            <td><strong><?= lang('zone'); ?></strong></td>
                            <td><?php echo $zone->zone_name; ?></strong></td>
                        </tr>
                        <?php } 
                        if($customer->postal_code){?>
                        <tr>
                            <td><strong><?= lang('postal_code'); ?></strong></td>
                            <td><?= $customer->postal_code; ?></strong></td>
                        </tr>
                        <?php }
                        if($customer->country){?>
                        <tr>
                            <td><strong><?= lang('country'); ?></strong></td>
                            <td><?= $customer->country; ?></strong></td>
                        </tr>
                        <?php }
                        
                        if($customer->cf5){?>
                        <tr>
                            <td><strong><?= lang('ccf5'); ?></strong></td>
                            <td><?= $customer->cf5; ?></strong></td>
                        </tr>
                        <?php }
                        if($customer->cf6){?>
                        <tr>
                            <td><strong><?= lang('ccf6'); ?></strong></td>
                            <td><?= $customer->cf6; ?></strong></td>
                        </tr>
                        <?php }
                        if($customer->credit_limit){
                        ?>
                        <tr>
                            <td><strong><?= lang('credit_limit'); ?></strong></td>
                            <td><?= $customer->credit_limit; ?></strong></td>
                        </tr>
                        <?php }
                        if($customer->package){?>
                        <tr>
                            <td><strong><?= lang('service_package'); ?></strong></td>
                            <td><?= $customer->package; ?></strong></td>
                        </tr>
                        <?php }
                        if ($customer->attachment) {
                            ?>
                            <td><strong><?= lang('attachment'); ?></strong></td>
                            <td>
                                <a href="<?= admin_url('welcome/download/' . $customer->attachment) ?>" class="tip btn btn-warning" title="<?= lang('attachment') ?>">
                                    <i class="fa fa-chain"></i>
                                    <span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
                                </a>
                            </td>
                            <?php
                            } 
                        ?>
                        
                    </table>
                </div>
            </div>
            <div class="col-xs-4">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="small-box padding1010 bblue">
                            <h3><?= isset($sales->total_amount) ? $this->bpas->formatMoney($sales->total_amount) : '0.00' ?></h3>

                            <p><?= lang('sales_amount') ?></p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="small-box padding1010 bdarkGreen">
                            <h3><?= isset($sales->paid) ? $this->bpas->formatMoney($sales->paid) : '0.00' ?></h3>

                            <p><?= lang('total_paid') ?></p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="small-box padding1010 borange">
                            <h3><?= (isset($sales->total_amount) || isset($sales->paid)) ? $this->bpas->formatMoney($sales->total_amount - $sales->paid) : '0.00' ?></h3>

                            <p><?= lang('due_amount') ?></p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="small-box padding1010 bblue">
                            <div class="inner clearfix">
                                <a>
                                    <h3><?= $total_sales ?></h3> 
                                    <p><?= lang('total_sales') ?></p>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="small-box padding1010 blightBlue">
                            <div class="inner clearfix">
                                <a>
                                    <h3><?= $total_quotes ?></h3> 
                                    <p><?= lang('total_quotes') ?></p>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="small-box padding1010 borange">
                            <div class="inner clearfix">
                                <a>
                                    <h3><?= $total_returns ?></h3>

                                    <p><?= lang('total_returns') ?></p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
    </div>
</div>
<ul id="myTab" class="nav nav-tabs no-print">
    <?php if($this->Settings->module_clinic){?>
    <li class=""><a href="#progress_note-con" class="tab-grey"><?= lang('progress_note') ?></a></li>
    <li class=""><a href="#prescription-con" class="tab-grey"><?= lang('prescription') ?></a></li>
    <li class=""><a href="#medication_dose-con" class="tab-grey"><?= lang('medication_dose') ?></a></li>
    <li class=""><a href="#operation-con" class="tab-grey"><?= lang('operation') ?></a></li>
    <li class=""><a href="#lab-con" class="tab-grey"><?= lang('lab') ?></a></li>
    <li class=""><a href="#treaments-con" class="tab-grey"><?= lang('treatments') ?></a></li>
    <?php }?>
    <li class=""><a href="#sales-con" class="tab-grey"><?= lang('sales') ?></a></li>
    <li class=""><a href="#payments-con" class="tab-grey"><?= lang('payments') ?></a></li>
    <?php if($Settings->module_sale){?>
    <li class=""><a href="#quotes-con" class="tab-grey"><?= lang('quotes') ?></a></li>
    <?php }?>
    <li class=""><a href="#deposits-con" class="tab-grey"><?= lang('deposits') ?></a></li>
    <?php if($Settings->module_installment){?>
    <li class=""><a href="#installment-con" class="tab-grey"><?= lang('installments') ?></a></li>
    <?php }?>
    
</ul>

<div class="tab-content">
    <div id="progress_note-con" class="tab-pane fade in">
        <?php
        $v = '&customer=' . $customer_id;
        if ($this->input->post('submit_sale_report')) {
            if ($this->input->post('biller')) {
                $v .= '&biller=' . $this->input->post('biller');
            }
            if ($this->input->post('user')) {
                $v .= '&user=' . $this->input->post('user');
            }
            if ($this->input->post('serial')) {
                $v .= '&serial=' . $this->input->post('serial');
            }
            if ($this->input->post('start_date')) {
                $v .= '&start_date=' . $this->input->post('start_date');
            }
            if ($this->input->post('end_date')) {
                $v .= '&end_date=' . $this->input->post('end_date');
            }
        }
        ?>
        <script>
        $(document).ready(function () {
            function attachment(x) {
                if (x != null) {
                    return '<a href="' + site.url + 'assets/uploads/' + x + '" target="_blank"><i class="fa fa-chain"></i></a>';
                }
                return x;
            }
            function checkbox(y) {
                return '<div class="text-center"><input class="checkbox multi-select" type="checkbox" name="val[]" value="' + y + '" /></div>';
            }
            oTable = $('#ProNData').dataTable({
                "aaSorting": [
                    [1, "desc"]
                ],
                "aLengthMenu": [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "<?= lang('all') ?>"]
                ],
                "iDisplayLength": <?= $Settings->rows_per_page ?>,
                'bProcessing': true,
                'bServerSide': true,
                'sAjaxSource': '<?= admin_url('clinic/getProgressNote?v=1'.$v); ?>',
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
                "aoColumns": [
                {
                    "bSortable": false,
                    "mRender": checkbox
                }, 
                {"mRender": fld}, 
                null, 
                null, 
                null,
                null,
                {"mRender": fld},
                null,
                 null,
                 null, {
                    "bSortable": false,
                    "mRender": attachment
                }, {
                    "bSortable": false
                }],
                'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                    var oSettings = oTable.fnSettings();
                    nRow.id = aData[0];
                    nRow.reference = aData[2];
                    nRow.className = "progress_note_link";
                    return nRow;
                },
                "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
                    var total = 0;
                    for (var i = 0; i < aaData.length; i++) {
                        total += parseFloat(aaData[aiDisplay[i]][4]);
                    }
                    var nCells = nRow.getElementsByTagName('th');
                    nCells[4].innerHTML = currencyFormat(total);
                }
            }).fnSetFilteringDelay().dtFilter([{
                    column_number: 1,
                    filter_default_label: "[<?= lang('date'); ?> (yyyy-mm-dd)]",
                    filter_type: "text",
                    data: []
                },
                {
                    column_number: 2,
                    filter_default_label: "[<?= lang('reference'); ?>]",
                    filter_type: "text",
                    data: []
                },
                {
                    column_number: 3,
                    filter_default_label: "[<?= lang('biller'); ?>]",
                    filter_type: "text",
                    data: []
                },
                {
                    column_number: 5,
                    filter_default_label: "[<?= lang('note'); ?>]",
                    filter_type: "text",
                    data: []
                },
                {
                    column_number: 6,
                    filter_default_label: "[<?= lang('paid_by'); ?>]",
                    filter_type: "text",
                    data: []
                },
                {
                    column_number: 7,
                    filter_default_label: "[<?= lang('created_by'); ?>]",
                    filter_type: "text",
                    data: []
                },
            ], "footer");
        });
        </script>
        <script type="text/javascript">
        $(document).ready(function () {
            $('#PNform').hide();
            $('.PNtoggle_down').click(function () {
                $("#PNform").slideDown();
                return false;
            });
            $('.PNtoggle_up').click(function () {
                $("#PNform").slideUp();
                return false;
            });
        });
        </script>

        <div class="box sales-table">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-heart nb"></i><?= lang('progress_note'); ?> <?php
                if ($this->input->post('start_date')) {
                    echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
                }
                ?></h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a href="#" class="PNtoggle_up tip" title="<?= lang('hide_form') ?>">
                                <i class="icon fa fa-toggle-up"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="PNtoggle_down tip" title="<?= lang('show_form') ?>">
                                <i class="icon fa fa-toggle-down"></i>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                            </a>
                            <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                                <li>
                                    <a href="<?= admin_url('clinic/add_progress_note/'.$customer_id) ?>" data-toggle="modal" data-target="#myModal">
                                        <i class="fa fa-plus-circle"></i> <?= lang('add_progress_note') ?>
                                    </a>
                                </li>
                               
                                <li class="divider"></li>
                                <li>
                                    <a href="#" class="bpo" title="<b><?= $this->lang->line('delete_customers') ?></b>"
                                        data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" data-html="true" data-placement="left">
                                        <i class="fa fa-trash-o"></i> <?= lang('delete_customers') ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                                <i
                                class="icon fa fa-file-excel-o"></i>
                            </a>
                        </li>

                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?= lang('progress_note'); ?></p>

                 
                        <div class="clearfix"></div>
                        <div class="table-responsive">
                            <table id="ProNData" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped">
                                <thead>
                                    <tr class="active">
                                        <th style="min-width:30px; width: 30px; text-align: center;">
                                            <input class="checkbox checkft" type="checkbox" name="check" />
                                        </th>
                                        <th style="width:180px;"><?= lang('date'); ?></th>
                                        <th><?= lang('reference'); ?></th>
                                        <th><?= lang('biller'); ?></th>
                                        <th><?= lang('customer'); ?></th>
                                        <th><?= lang('type'); ?></th>
                                        <th><?= lang('effective_date'); ?></th>
                                        <th><?= lang('doctor'); ?></th>
                                        <th><?= lang('noted'); ?></th>
                                        <th><?= lang('created_by'); ?></th>
                                        <th style="min-width:30px; width: 30px; text-align: center !important;"><i class="fa fa-chain"></i>
                                        </th>
                                        <th style="width: 100px; text-align: center !important;"><?= lang('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
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
                                        <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i>
                                        </th>
                                        <th style="width:100px; text-align: center;"><?= lang('actions'); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="prescription-con" class="tab-pane fade in">

        <?php
        $v = '&customer=' . $customer_id;
        if ($this->input->post('submit_sale_report')) {
            if ($this->input->post('biller')) {
                $v .= '&biller=' . $this->input->post('biller');
            }
            if ($this->input->post('warehouse')) {
                $v .= '&warehouse=' . $this->input->post('warehouse');
            }
            if ($this->input->post('user')) {
                $v .= '&user=' . $this->input->post('user');
            }
            if ($this->input->post('serial')) {
                $v .= '&serial=' . $this->input->post('serial');
            }
            if ($this->input->post('start_date')) {
                $v .= '&start_date=' . $this->input->post('start_date');
            }
            if ($this->input->post('end_date')) {
                $v .= '&end_date=' . $this->input->post('end_date');
            }
        }
        ?>
        <script>
        $(document).ready(function () {
            oTable = $('#PreData').dataTable({
                "aaSorting": [[1, "desc"], [2, "desc"]],
                "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
                "iDisplayLength": <?=$Settings->rows_per_page?>,
                'bProcessing': true, 'bServerSide': true,
                'sAjaxSource': '<?=admin_url('clinic/getPrescriptions?v=1' . $v); ?>',
                'fnServerData': function (sSource, aoData, fnCallback) {
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name()?>",
                        "value": "<?= $this->security->get_csrf_hash()?>"
                    });
                    $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                },
                'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                    var oSettings = oTable.fnSettings();
                    // $("td:first", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                    nRow.id = aData[0];
                    nRow.className = "prescription_link re" + aData[9];
                    var action = $('td:eq(10)', nRow);
                    return nRow;
                },
                "aoColumns": [
                    {"bSortable": false, "mRender": checkbox},
                    {"mRender": fld}, 
                    null,null, null, 
                    null, 
                    {"mRender": decode_html},
                    {"bSortable": false,"mRender": attachment}, 
                    {"bSortable": false}],
                "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                    var gtotal = 0, paid = 0, balance = 0;
              
                }
            }).fnSetFilteringDelay().dtFilter([
                {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                {column_number: 3, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
                {column_number: 4, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
                {column_number: 5, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
                {column_number: 6, filter_default_label: "[<?=lang('sale_status');?>]", filter_type: "text", data: []},
             
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
        });
        </script>

        <div class="box sales-table">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-heart nb"></i><?= lang('prescription'); ?> <?php
                if ($this->input->post('start_date')) {
                    echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
                }
                ?></h2>

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
                                <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                            </a>
                            <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                                <li>
                                    <a href="<?= admin_url('clinic/add_prescription/'.$customer_id) ?>">
                                        <i class="fa fa-plus-circle"></i> <?= lang('add_prescription') ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" id="excel" data-action="export_excel">
                                        <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                                    </a>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <a href="#" class="bpo" title="<b><?= $this->lang->line('delete_customers') ?></b>"
                                        data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" data-html="true" data-placement="left">
                                        <i class="fa fa-trash-o"></i> <?= lang('delete_customers') ?>
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
                        <p class="introtext"><?= lang('customize_report'); ?></p>

                        <div id="form">

                        <?php echo admin_form_open('reports/customer_report/' . $customer_id); ?>
                        <div class="row">

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="user"><?= lang('created_by'); ?></label>
                                    <?php
                                    $us[''] = lang('select') . ' ' . lang('user');
                                    foreach ($users as $user) {
                                        $us[$user->id] = $user->first_name . ' ' . $user->last_name;
                                    }
                                    echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ''), 'class="form-control" id="user" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('user') . '"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="biller"><?= lang('biller'); ?></label>
                                    <?php
                                    $bl[''] = lang('select') . ' ' . lang('biller');
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('biller') . '"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="warehouse"><?= lang('warehouse'); ?></label>
                                    <?php
                                    $wh[''] = lang('select') . ' ' . lang('warehouse');
                                    foreach ($warehouses as $warehouse) {
                                        $wh[$warehouse->id] = $warehouse->name;
                                    }
                                    echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '"');
                                    ?>
                                </div>
                            </div>
                            <?php if ($Settings->product_serial) {
                                        ?>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang('serial_no', 'serial'); ?>
                                        <?= form_input('serial', '', 'class="form-control tip" id="serial"'); ?>
                                    </div>
                                </div>
                                <?php
                                    } ?>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang('start_date', 'start_date'); ?>
                                        <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang('end_date', 'end_date'); ?>
                                        <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div
                                class="controls"> <?php echo form_submit('submit_sale_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?> </div>
                            </div>
                            <?php echo form_close(); ?>
                        </div>
                        <div class="clearfix"></div>
                        <div class="table-responsive">
                            <table id="PreData" class="table table-hover table-bordered" cellpadding="0" cellspacing="0" border="0">
                                <thead>
                                <tr>
                                    <th style="min-width:30px; max-width: 30px; text-align: center;">
                                        <input class="checkbox checkft" type="checkbox" name="check"/>
                                    </th>
                                    <th><?= lang('date'); ?></th>
                                    <th><?= lang('doctor'); ?></th>
                                    <th><?= lang('reference_no'); ?></th>
                                    <th><?= lang('biller'); ?></th>
                                    <th><?= lang('customer'); ?></th>
                                    <th><?= lang('diagnosis'); ?></th>
                                    <th style="min-width:30px; max-width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                                    <th style="width:80px; text-align:center;"><?= lang('actions'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td colspan="9" class="dataTables_empty"><?= lang('loading_data'); ?></td>
                                </tr>
                                </tbody>
                                <tfoot class="dtFilter">
                                <tr class="active">
                                    <th style="min-width:30px; width: 30px; text-align: center;">
                                        <input class="checkbox checkft" type="checkbox" name="check"/>
                                    </th>
                                    <th></th><th></th><th></th><th></th><th></th>
                                    <th></th>
                                    <th style="min-width:30px; max-width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                                    <th style="width:80px; text-align:center;"><?= lang('actions'); ?></th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                            
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="operation-con" class="tab-pane fade in">
        <?php
        $v = '&customer=' . $customer_id;
        if ($this->input->post('submit_sale_report')) {
            if ($this->input->post('biller')) {
                $v .= '&biller=' . $this->input->post('biller');
            }
            if ($this->input->post('warehouse')) {
                $v .= '&warehouse=' . $this->input->post('warehouse');
            }
            if ($this->input->post('user')) {
                $v .= '&user=' . $this->input->post('user');
            }
            if ($this->input->post('serial')) {
                $v .= '&serial=' . $this->input->post('serial');
            }
            if ($this->input->post('start_date')) {
                $v .= '&start_date=' . $this->input->post('start_date');
            }
            if ($this->input->post('end_date')) {
                $v .= '&end_date=' . $this->input->post('end_date');
            }
        }
        ?>
        <script>
            $(document).ready(function() {
                function attachment(x) {
                    if (x != null) {
                        return '<a href="' + site.url + 'assets/uploads/' + x + '" target="_blank"><i class="fa fa-chain"></i></a>';
                    }
                    return x;
                }
                function checkbox(y) {
                    return '<div class="text-center"><input class="checkbox multi-select" type="checkbox" name="val[]" value="' + y + '" /></div>';
                }
                oTable = $('#OperData').dataTable({
                    "aaSorting": [[1, "desc"]],
                    "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                    "iDisplayLength": <?= $Settings->rows_per_page ?>,
                    'bProcessing': true,
                    'bServerSide': true,
                    'sAjaxSource': '<?= admin_url('clinic/getOperations?v=1' . $v); ?>',
                    'fnServerData': function(sSource, aoData, fnCallback) {
                        aoData.push({
                            "name": "<?= $this->security->get_csrf_token_name() ?>",
                            "value": "<?= $this->security->get_csrf_hash() ?>"
                        });
                        $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback });
                    },
                    "aoColumns": [
                        {"bSortable": false,"mRender": checkbox}, 
                        {"mRender": fld}, 
                        null, 
                        null, 
                        null,
                        null,
                        null,
                        null,
                        null,
                        null, 
                        null, 
                        null, 
                        null, 
                        {"bSortable": false, "mRender": attachment},
                        {"bSortable": false}
                    ],
                    'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                        var oSettings = oTable.fnSettings();
                        nRow.id = aData[0];
                        nRow.className = "operation_link";
                        return nRow;
                    },
                    "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
                    }
                }).fnSetFilteringDelay().dtFilter([
                    {column_number: 1, filter_default_label: "[<?= lang('date'); ?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                    {column_number: 2, filter_default_label: "[<?= lang('biller'); ?>]", filter_type: "text", data: []},
                    {column_number: 3, filter_default_label: "[<?= lang('patience'); ?>]", filter_type: "text", data: []},
                    {column_number: 4, filter_default_label: "[<?= lang('operation_category'); ?>]", filter_type: "text", data: []},
                    {column_number: 5, filter_default_label: "[<?= lang('operation_name'); ?>]", filter_type: "text", data: []},
                    {column_number: 6, filter_default_label: "[<?= lang('doctor'); ?>]", filter_type: "text", data: []},
                    {column_number: 7, filter_default_label: "[<?= lang('assistant'); ?>]", filter_type: "text", data: []},
                    {column_number: 8, filter_default_label: "[<?= lang('anesthetist'); ?>]", filter_type: "text", data: []},
                    {column_number: 9, filter_default_label: "[<?= lang('ot_technician'); ?>]", filter_type: "text", data: []},
                    {column_number: 10, filter_default_label: "[<?= lang('note'); ?>]", filter_type: "text", data: []},
                    {column_number: 11, filter_default_label: "[<?= lang('result'); ?>]", filter_type: "text", data: []},
                    {column_number: 12, filter_default_label: "[<?= lang('created_by'); ?>]", filter_type: "text", data: []},
                ], "footer");
            });
        </script>
            <script type="text/javascript">
            $(document).ready(function () {
                $('#Operform').hide();
                $('.Opertoggle_down').click(function () {
                    $("#Operform").slideDown();
                    return false;
                });
                $('.Opertoggle_up').click(function () {
                    $("#Operform").slideUp();
                    return false;
                });
            });
            </script>

            <div class="box sales-table">
                <div class="box-header">
                    <h2 class="blue"><i class="fa-fw fa fa-heart nb"></i><?= lang('operation'); ?> <?php
                    if ($this->input->post('start_date')) {
                        echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
                    }
                    ?></h2>

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
                                    <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                                </a>
                                <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                                    <li>
                                        <a href="<?= admin_url('clinic/add_operation/'.$customer_id) ?>" data-toggle="modal" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i> <?= lang('add_operation') ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" id="excel" data-action="export_excel">
                                            <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                                        </a>
                                    </li>
                                    <li class="divider"></li>
                                    <li>
                                        <a href="#" class="bpo" title="<b><?= $this->lang->line('delete_customers') ?></b>"
                                            data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" data-html="true" data-placement="left">
                                            <i class="fa fa-trash-o"></i> <?= lang('delete_customers') ?>
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
                            <p class="introtext"><?= lang('customize_report'); ?></p>

                            <div id="Operform">

                            <?php echo admin_form_open('reports/customer_report/' . $customer_id); ?>
                            <div class="row">

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="user"><?= lang('created_by'); ?></label>
                                        <?php
                                        $us[''] = lang('select') . ' ' . lang('user');
                                        foreach ($users as $user) {
                                            $us[$user->id] = $user->first_name . ' ' . $user->last_name;
                                        }
                                        echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ''), 'class="form-control" id="user" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('user') . '"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="biller"><?= lang('biller'); ?></label>
                                        <?php
                                        $bl[''] = lang('select') . ' ' . lang('biller');
                                        foreach ($billers as $biller) {
                                            $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;
                                        }
                                        echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('biller') . '"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="warehouse"><?= lang('warehouse'); ?></label>
                                        <?php
                                        $wh[''] = lang('select') . ' ' . lang('warehouse');
                                        foreach ($warehouses as $warehouse) {
                                            $wh[$warehouse->id] = $warehouse->name;
                                        }
                                        echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '"');
                                        ?>
                                    </div>
                                </div>
                                <?php if ($Settings->product_serial) {
                                            ?>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang('serial_no', 'serial'); ?>
                                            <?= form_input('serial', '', 'class="form-control tip" id="serial"'); ?>
                                        </div>
                                    </div>
                                    <?php
                                        } ?>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang('start_date', 'start_date'); ?>
                                            <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang('end_date', 'end_date'); ?>
                                            <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div
                                    class="controls"> <?php echo form_submit('submit_sale_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?> </div>
                                </div>
                                <?php echo form_close(); ?>
                            </div>
                            <div class="clearfix"></div>
                            <div class="table-responsive">
                                <table id="OperData" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped">
                                    <thead>
                                        <tr class="active">
                                            <th style="min-width:30px; width: 30px; text-align: center;">
                                                <input class="checkbox checkft" type="checkbox" name="check" />
                                            </th>
                                            <th style="width:180px;"><?= lang('date'); ?></th>
                                            <th><?= lang('biller'); ?></th>
                                            <th><?= lang('patience'); ?></th>
                                            <th><?= lang('operation_category'); ?></th>
                                            <th><?= lang('operation_name'); ?></th>
                                            <th><?= lang('doctor'); ?></th>
                                            <th><?= lang('assistant'); ?></th>
                                            <th><?= lang('anesthetist'); ?></th>
                                            <th><?= lang('ot_technician'); ?></th>
                                            <th><?= lang('note'); ?></th>
                                            <th><?= lang('result'); ?></th>
                                            <th><?= lang('created_by'); ?></th>
                                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                                            <th style="width: 100px; text-align: center !important;"><?= lang('actions'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="10" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
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
                                            <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                                            <th style="width:100px; text-align: center;"><?= lang('actions'); ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                                        
                        </div>
                    </div>
                </div>
            </div>
    </div>
    <div id="lab-con" class="tab-pane fade in">
        <?php
        $v = '&customer=' . $customer_id;
        if ($this->input->post('submit_sale_report')) {
            if ($this->input->post('biller')) {
                $v .= '&biller=' . $this->input->post('biller');
            }
            if ($this->input->post('warehouse')) {
                $v .= '&warehouse=' . $this->input->post('warehouse');
            }
            if ($this->input->post('user')) {
                $v .= '&user=' . $this->input->post('user');
            }
            if ($this->input->post('serial')) {
                $v .= '&serial=' . $this->input->post('serial');
            }
            if ($this->input->post('start_date')) {
                $v .= '&start_date=' . $this->input->post('start_date');
            }
            if ($this->input->post('end_date')) {
                $v .= '&end_date=' . $this->input->post('end_date');
            }
        }
        ?>
        <script>
            $(document).ready(function() {
                function attachment(x) {
                    if (x != null) {
                        return '<a href="' + site.url + 'assets/uploads/' + x + '" target="_blank"><i class="fa fa-chain"></i></a>';
                    }
                    return x;
                }
                function checkbox(y) {
                    return '<div class="text-center"><input class="checkbox multi-select" type="checkbox" name="val[]" value="' + y + '" /></div>';
                }
                oTable = $('#PathData').dataTable({
                    "aaSorting": [[1, "desc"]],
                    "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                    "iDisplayLength": <?= $Settings->rows_per_page ?>,
                    'bProcessing': true,
                    'bServerSide': true,
                    'sAjaxSource': '<?= admin_url('clinic/getPathology'); ?>',
                    'fnServerData': function(sSource, aoData, fnCallback) {
                        aoData.push({
                            "name": "<?= $this->security->get_csrf_token_name() ?>",
                            "value": "<?= $this->security->get_csrf_hash() ?>"
                        });
                        $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback });
                    },
                    "aoColumns": [
                        {"bSortable": false,"mRender": checkbox}, 
                        {"mRender": fld}, 
                        null, 
                        null, 
                        null,
                        null,
                        null,
                        null,
                        null,
                        null, 
                        {"bSortable": false}
                    ],
                    'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                        var oSettings = oTable.fnSettings();
                        nRow.id = aData[0];
                        nRow.className = "operation_link";
                        return nRow;
                    },
                    "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
                    }
                }).fnSetFilteringDelay().dtFilter([
                    {column_number: 1, filter_default_label: "[<?= lang('date'); ?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                    {column_number: 2, filter_default_label: "[<?= lang('biller'); ?>]", filter_type: "text", data: []},
                    {column_number: 3, filter_default_label: "[<?= lang('patience'); ?>]", filter_type: "text", data: []},
                    {column_number: 4, filter_default_label: "[<?= lang('operation_category'); ?>]", filter_type: "text", data: []},
                    {column_number: 5, filter_default_label: "[<?= lang('operation_name'); ?>]", filter_type: "text", data: []},
                    {column_number: 6, filter_default_label: "[<?= lang('doctor'); ?>]", filter_type: "text", data: []},
                    {column_number: 7, filter_default_label: "[<?= lang('assistant'); ?>]", filter_type: "text", data: []},
                    {column_number: 8, filter_default_label: "[<?= lang('anesthetist'); ?>]", filter_type: "text", data: []},
                    {column_number: 9, filter_default_label: "[<?= lang('ot_technician'); ?>]", filter_type: "text", data: []},
           
                ], "footer");
            });
        </script>
            <script type="text/javascript">
            $(document).ready(function () {
                $('#pathform').hide();
                $('.Pathtoggle_down').click(function () {
                    $("#pathform").slideDown();
                    return false;
                });
                $('.Pathtoggle_up').click(function () {
                    $("#pathform").slideUp();
                    return false;
                });
            });
            </script>

            <div class="box sales-table">
                <div class="box-header">
                    <h2 class="blue"><i class="fa-fw fa fa-heart nb"></i><?= lang('pathology'); ?> <?php
                    if ($this->input->post('start_date')) {
                        echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
                    }
                    ?></h2>

                    <div class="box-icon">
                        <ul class="btn-tasks">
                            <li class="dropdown">
                                <a href="#" class="Pathtoggle_up tip" title="<?= lang('hide_form') ?>">
                                    <i class="icon fa fa-toggle-up"></i>
                                </a>
                            </li>
                            <li class="dropdown">
                                <a href="#" class="Pathtoggle_down tip" title="<?= lang('show_form') ?>">
                                    <i class="icon fa fa-toggle-down"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="box-icon">
                        <ul class="btn-tasks">
                            <li class="dropdown">
                                <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                    <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                                </a>
                                <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                                    <li>
                                        <a href="<?= admin_url('clinic/add_pathology/'.$customer_id) ?>" data-toggle="modal" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i> <?= lang('add_pathology') ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" id="excel" data-action="export_excel">
                                            <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                                        </a>
                                    </li>
                                    <li class="divider"></li>
                                    <li>
                                        <a href="#" class="bpo" title="<b><?= $this->lang->line('delete_customers') ?></b>"
                                            data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" data-html="true" data-placement="left">
                                            <i class="fa fa-trash-o"></i> <?= lang('delete_customers') ?>
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
                            <p class="introtext"><?= lang('pathology'); ?></p>

                            <div id="pathform">

                            <?php echo admin_form_open('reports/customer_report/' . $customer_id); ?>
                            <div class="row">

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="user"><?= lang('created_by'); ?></label>
                                        <?php
                                        $us[''] = lang('select') . ' ' . lang('user');
                                        foreach ($users as $user) {
                                            $us[$user->id] = $user->first_name . ' ' . $user->last_name;
                                        }
                                        echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ''), 'class="form-control" id="user" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('user') . '"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="biller"><?= lang('biller'); ?></label>
                                        <?php
                                        $bl[''] = lang('select') . ' ' . lang('biller');
                                        foreach ($billers as $biller) {
                                            $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;
                                        }
                                        echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('biller') . '"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="warehouse"><?= lang('warehouse'); ?></label>
                                        <?php
                                        $wh[''] = lang('select') . ' ' . lang('warehouse');
                                        foreach ($warehouses as $warehouse) {
                                            $wh[$warehouse->id] = $warehouse->name;
                                        }
                                        echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '"');
                                        ?>
                                    </div>
                                </div>
                                <?php if ($Settings->product_serial) {
                                            ?>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang('serial_no', 'serial'); ?>
                                            <?= form_input('serial', '', 'class="form-control tip" id="serial"'); ?>
                                        </div>
                                    </div>
                                    <?php
                                        } ?>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang('start_date', 'start_date'); ?>
                                            <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang('end_date', 'end_date'); ?>
                                            <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div
                                    class="controls"> <?php echo form_submit('submit_sale_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?> </div>
                                </div>
                                <?php echo form_close(); ?>
                            </div>
                            <div class="clearfix"></div>
                            <div class="table-responsive">
                                <table id="PathData" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped">
                                    <thead>
                                        <tr class="active">
                                            <th style="min-width:30px; width: 30px; text-align: center;">
                                                <input class="checkbox checkft" type="checkbox" name="check" />
                                            </th>
                                            <th style="width:180px;"><?= lang('date'); ?></th>
                                            <th><?= lang('biller'); ?></th>
                                            <th><?= lang('test_name'); ?></th>
                                            <th><?= lang('test_type'); ?></th>
                                            <th><?= lang('category'); ?></th>
                                            <th><?= lang('patience'); ?></th>
                                            <th><?= lang('method'); ?></th>
                                            <th><?= lang('report_day'); ?></th>
                                            <th><?= lang('created_by'); ?></th>
                                            
                                            <th style="width: 100px; text-align: center !important;"><?= lang('actions'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
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

                                            <th style="width:100px; text-align: center;"><?= lang('actions'); ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                                        
                        </div>
                    </div>
                </div>
            </div>
    </div>
    <div id="medication_dose-con" class="tab-pane fade in">
        <?php
        $v = '&customer=' . $customer_id;
        if ($this->input->post('submit_sale_report')) {
            if ($this->input->post('biller')) {
                $v .= '&biller=' . $this->input->post('biller');
            }
            if ($this->input->post('warehouse')) {
                $v .= '&warehouse=' . $this->input->post('warehouse');
            }
            if ($this->input->post('user')) {
                $v .= '&user=' . $this->input->post('user');
            }
            if ($this->input->post('serial')) {
                $v .= '&serial=' . $this->input->post('serial');
            }
            if ($this->input->post('start_date')) {
                $v .= '&start_date=' . $this->input->post('start_date');
            }
            if ($this->input->post('end_date')) {
                $v .= '&end_date=' . $this->input->post('end_date');
            }
        }
        ?>
        <script>
            $(document).ready(function() {
                function checkbox(y) {
                    return '<div class="text-center"><input class="checkbox multi-select" type="checkbox" name="val[]" value="' + y + '" /></div>';
                }
                oTable = $('#DoseData').dataTable({
                    "aaSorting": [[1, "desc"]],
                    "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                    "iDisplayLength": <?= $Settings->rows_per_page ?>,
                    'bProcessing': true,
                    'bServerSide': true,
                    'sAjaxSource': '<?= admin_url('clinic/getMedicationDose?v=1' . $v); ?>',
                    'fnServerData': function(sSource, aoData, fnCallback) {
                        aoData.push({
                            "name": "<?= $this->security->get_csrf_token_name() ?>",
                            "value": "<?= $this->security->get_csrf_hash() ?>"
                        });
                        $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback });
                    },
                    "aoColumns": [
                        {"bSortable": false,"mRender": checkbox}, 
                        {"mRender": fld}, 
                        null, 
                        null, 
                        null,
                        null,
                        null,
                        null,
                        null,
                        {"bSortable": false}
                    ],
                    'fnRowCallback': function(nRow, aData, iDisplayIndex) {
                        var oSettings = oTable.fnSettings();
                        nRow.id = aData[0];
                        nRow.className = "operation_link";
                        return nRow;
                    },
                    "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
                    }
                }).fnSetFilteringDelay().dtFilter([
                    {column_number: 1, filter_default_label: "[<?= lang('date'); ?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                    {column_number: 2, filter_default_label: "[<?= lang('biller'); ?>]", filter_type: "text", data: []},
                    {column_number: 3, filter_default_label: "[<?= lang('patience'); ?>]", filter_type: "text", data: []},
                    {column_number: 4, filter_default_label: "[<?= lang('operation_category'); ?>]", filter_type: "text", data: []},
                    {column_number: 5, filter_default_label: "[<?= lang('operation_name'); ?>]", filter_type: "text", data: []},
                    {column_number: 6, filter_default_label: "[<?= lang('doctor'); ?>]", filter_type: "text", data: []},
                    {column_number: 7, filter_default_label: "[<?= lang('created_by'); ?>]", filter_type: "text", data: []},
                ], "footer");
            });
        </script>
            <script type="text/javascript">
            $(document).ready(function () {
                $('#doseform').hide();
                $('.dosetoggle_down').click(function () {
                    $("#doseform").slideDown();
                    return false;
                });
                $('.dosetoggle_up').click(function () {
                    $("#doseform").slideUp();
                    return false;
                });
            });
            </script>

            <div class="box sales-table">
                <div class="box-header">
                    <h2 class="blue"><i class="fa-fw fa fa-heart nb"></i><?= lang('medication_dose'); ?> <?php
                    if ($this->input->post('start_date')) {
                        echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
                    }
                    ?></h2>

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
                                    <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                                </a>
                                <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                                    <li>
                                        <a href="<?= admin_url('clinic/add_medication_dose/'.$customer_id) ?>" data-toggle="modal" data-target="#myModal">
                                            <i class="fa fa-plus-circle"></i> <?= lang('add_medication_dose') ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" id="excel" data-action="export_excel">
                                            <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                                        </a>
                                    </li>
                                    <li class="divider"></li>
                                    <li>
                                        <a href="#" class="bpo" title="<b><?= $this->lang->line('delete_customers') ?></b>"
                                            data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>" data-html="true" data-placement="left">
                                            <i class="fa fa-trash-o"></i> <?= lang('delete_customers') ?>
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
                            <p class="introtext"><?= lang('medication_dose'); ?></p>

                            <div id="doseform">

                            <?php echo admin_form_open('reports/customer_report/' . $customer_id); ?>
                            <div class="row">

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="user"><?= lang('created_by'); ?></label>
                                        <?php
                                        $us[''] = lang('select') . ' ' . lang('user');
                                        foreach ($users as $user) {
                                            $us[$user->id] = $user->first_name . ' ' . $user->last_name;
                                        }
                                        echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ''), 'class="form-control" id="user" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('user') . '"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="biller"><?= lang('biller'); ?></label>
                                        <?php
                                        $bl[''] = lang('select') . ' ' . lang('biller');
                                        foreach ($billers as $biller) {
                                            $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;
                                        }
                                        echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('biller') . '"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="warehouse"><?= lang('warehouse'); ?></label>
                                        <?php
                                        $wh[''] = lang('select') . ' ' . lang('warehouse');
                                        foreach ($warehouses as $warehouse) {
                                            $wh[$warehouse->id] = $warehouse->name;
                                        }
                                        echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '"');
                                        ?>
                                    </div>
                                </div>
                                <?php if ($Settings->product_serial) {
                                            ?>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang('serial_no', 'serial'); ?>
                                            <?= form_input('serial', '', 'class="form-control tip" id="serial"'); ?>
                                        </div>
                                    </div>
                                    <?php
                                        } ?>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang('start_date', 'start_date'); ?>
                                            <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <?= lang('end_date', 'end_date'); ?>
                                            <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div
                                    class="controls"> <?php echo form_submit('submit_sale_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?> </div>
                                </div>
                                <?php echo form_close(); ?>
                            </div>
                            <div class="clearfix"></div>
                            <div class="table-responsive">
                                <table id="DoseData" cellpadding="0" cellspacing="0" border="0" class="table table-hover table-striped">
                                    <thead>
                                        <tr class="active">
                                            <th style="min-width:30px; width: 30px; text-align: center;">
                                                <input class="checkbox checkft" type="checkbox" name="check" />
                                            </th>
                                            <th style="width:180px;"><?= lang('date'); ?></th>
                                            <th><?= lang('biller'); ?></th>
                                            <th><?= lang('patience'); ?></th>
                                            <th><?= lang('category'); ?></th>
                                            <th><?= lang('name'); ?></th>
                                            <th><?= lang('dosage'); ?></th>
                                            <th><?= lang('note'); ?></th>
                                            <th><?= lang('created_by'); ?></th>
                                            <th style="width: 100px; text-align: center !important;"><?= lang('actions'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="5" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
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

                                            <th style="width:100px; text-align: center;"><?= lang('actions'); ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                                        
                        </div>
                    </div>
                </div>
            </div>
    </div>
    <div id="treaments-con" class="tab-pane fade in">
        <?php
        $v = '&customer=' . $customer_id;
        if ($this->input->post('submit_sale_report')) {

            if ($this->input->post('reference_no')) {
                $v .= "&reference_no=" . $this->input->post('reference_no');
            }
            if ($this->input->post('customer')) {
                $v .= "&customer=" . $this->input->post('customer');
            }
            if ($this->input->post('saleman')) {
                $v .= "&saleman=" . $this->input->post('saleman');
            }
            if ($this->input->post('biller')) {
                $v .= "&biller=" . $this->input->post('biller');
            }
            if ($this->input->post('warehouse')) {
                $v .= "&warehouse=" . $this->input->post('warehouse');
            }
            if ($this->input->post('delivered_by')) {
                $v .= "&delivered_by=" . $this->input->post('delivered_by');
            }
            if ($this->input->post('user')) {
                $v .= "&user=" . $this->input->post('user');
            }
            if ($this->input->post('serial')) {
                $v .= "&serial=" . $this->input->post('serial');
            }
            if ($this->input->post('start_date')) {
                $v .= "&start_date=" . $this->input->post('start_date');
            }
            if ($this->input->post('end_date')) {
                $v .= "&end_date=" . $this->input->post('end_date');
            }
            if ($this->input->post('product_id')) {
                $v .= "&product_id=" . $this->input->post('product_id');
            }
            if ($this->input->post('group_area')) {
                $v .= "&group_area=" . $this->input->post('group_area');
            }
            if ($this->input->post('payment_status')) {
                $arr = array();
                $arr = $this->input->post('payment_status');
                $get_status = "";
                $get_status = implode('_', $arr);
                $v .= "&payment_status=" . $get_status;
            }
            if (isset($alert_id)) {
                $v .= "&a=" . $alert_id;
            }

        }
        ?>
        <script>
            function checkboxss(x) {
                console.log(x);
                return '<div class="text-center"><input class="checkbox multi-select" type="checkbox" name="val[]" value="' + x + '" /></div>';
            }
            $(document).ready(function () {
                oTable = $('#TTData').dataTable({
                    "aaSorting": [[1, "desc"], [2, "desc"]],
                    "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
                    "iDisplayLength": <?=$Settings->rows_per_page?>,
                    'bProcessing': true, 'bServerSide': true,
                    'sAjaxSource': '<?=admin_url('clinic/getTreatments'.'?v=1'.($this->input->get('attachment') ? '&attachment=' . $this->input->get('attachment') : '') . ($this->input->get('delivery') ? '&delivery=' . $this->input->get('delivery') : '')); ?>',
                    'fnServerData': function (sSource, aoData, fnCallback) {
                        aoData.push({
                            "name": "<?= $this->security->get_csrf_token_name()?>",
                            "value": "<?= $this->security->get_csrf_hash()?>"
                        });
                        $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                    },
                    'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                      
                        var oSettings = oTable.fnSettings();
                        nRow.id = aData[0];
                        nRow.setAttribute('data-return-id', aData[9]);
                        nRow.className = "sale_order_link re" + aData[9];
                        var action = $('td:eq(14)', nRow);

                        if(aData[6] != 'order' || aData[11] == 'partial' || aData[11] == 'completed'){
                            action.find('.unapproved').remove();
                            action.find('.rejected').remove();
                        }
                        if (aData[6] == 'sale' || aData[6] == 'completed' || aData[11] == 'partial' || aData[11] == 'completed') {
                            action.find('.edit').remove();
                            action.find('.delete').remove();
                        }
                        if (aData[10] == 'approved') {
                            action.find('.edit').remove();
                            action.find('.approved').remove();
                            action.find('.delete').remove();
                        }
                        if (aData[10] == 'pending' || aData[10] == 'rejected' || aData[11] == 'partial' || aData[11] == 'completed') {
                            action.find('.add').remove();
                            action.find('.unapproved').remove();
                            action.find('.view_deposit').remove();
                            action.find('.add_deposit').remove(); 
                            if (aData[10] == 'rejected' || aData[11] == 'partial' || aData[11] == 'completed') {
                                action.find('.reject').remove();
                            }
                        }
                        if (aData[6] == 'sale' || aData[6] == 'completed' || aData[10] == 'pending' || aData[10] == 'rejected' || aData[11] == 'completed') {
                            action.find('.add_delivery').remove();
                        }
                        return nRow;
                    },
                    "aoColumns": [
                        { "bSortable": false, "mRender": checkboxss },
                        {"mRender": fld}, 
                        null, null, null, null, 
                        {"mRender": row_status}, 
                        {"mRender": currencyFormat}, 
                        {"mRender": currencyFormat}, 
                        {"mRender": currencyFormat}, 
                        {"mRender": approved_status3}, 
                        {"mRender": row_status}, 
                        null, 
                        {"bSortable": false,"mRender": attachment}, 
                        {"bSortable": false}
                    ],
                    "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                        var gtotal = 0, paid = 0, balance = 0;
                        for (var i = 0; i < aaData.length; i++) {
                            gtotal      += parseFloat(aaData[aiDisplay[i]][7]);
                            paid        += parseFloat(aaData[aiDisplay[i]][8]);
                            balance     += parseFloat(aaData[aiDisplay[i]][9]);
                        }
                        var nCells          = nRow.getElementsByTagName('th');
                        nCells[7].innerHTML = currencyFormat(parseFloat(gtotal));
                        nCells[8].innerHTML = currencyFormat(parseFloat(paid));
                        nCells[9].innerHTML = currencyFormat(parseFloat(balance));
                    }
                }).fnSetFilteringDelay().dtFilter([
                    {column_number: 1, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                    {column_number: 2, filter_default_label: "[<?=lang('project');?>]", filter_type: "text", data: []},
                    {column_number: 3, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
                    {column_number: 4, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
                    {column_number: 5, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
                    {column_number: 6, filter_default_label: "[<?=lang('sale_status');?>]", filter_type: "text", data: []},
                    {column_number: 10, filter_default_label: "[<?=lang('order_status');?>]", filter_type: "text", data: []},
                    {column_number: 11, filter_default_label: "[<?=lang('delivery_status');?>]", filter_type: "text", data: []},
                ], "footer");
                if (localStorage.getItem('remove_slls')) {
                    if (localStorage.getItem('slitems')) {
                        localStorage.removeItem('slitems');
                    }
                    if (localStorage.getItem('sldiscount')) {
                        localStorage.removeItem('sldiscount');
                    }
                    if (localStorage.getItem('sltax2')) {
                        localStorage.removeItem('sltax2');
                    }
                    if (localStorage.getItem('srref')) {
                        localStorage.removeItem('srref');
                    }
                    if (localStorage.getItem('slshipping')) {
                        localStorage.removeItem('slshipping');
                    }
                    if (localStorage.getItem('slwarehouse')) {
                        localStorage.removeItem('slwarehouse');
                    }
                    if (localStorage.getItem('slnote')) {
                        localStorage.removeItem('slnote');
                    }
                    if (localStorage.getItem('slinnote')) {
                        localStorage.removeItem('slinnote');
                    }
                    if (localStorage.getItem('slcustomer')) {
                        localStorage.removeItem('slcustomer');
                    }
                    if (localStorage.getItem('slbiller')) {
                        localStorage.removeItem('slbiller');
                    }
                    if (localStorage.getItem('slsaleman_by')) {
                        localStorage.removeItem('slsaleman_by');
                    }
                    if (localStorage.getItem('slcurrency')) {
                        localStorage.removeItem('slcurrency');
                    }
                    if (localStorage.getItem('sldate')) {
                        localStorage.removeItem('sldate');
                    }
                    if (localStorage.getItem('slsale_status')) {
                        localStorage.removeItem('slsale_status');
                    }
                    if (localStorage.getItem('slpayment_status')) {
                        localStorage.removeItem('slpayment_status');
                    }
                    if (localStorage.getItem('paid_by')) {
                        localStorage.removeItem('paid_by');
                    }
                    if (localStorage.getItem('amount_1')) {
                        localStorage.removeItem('amount_1');
                    }
                    if (localStorage.getItem('paid_by_1')) {
                        localStorage.removeItem('paid_by_1');
                    }
                    if (localStorage.getItem('pcc_holder_1')) {
                        localStorage.removeItem('pcc_holder_1');
                    }
                    if (localStorage.getItem('pcc_type_1')) {
                        localStorage.removeItem('pcc_type_1');
                    }
                    if (localStorage.getItem('pcc_month_1')) {
                        localStorage.removeItem('pcc_month_1');
                    }
                    if (localStorage.getItem('pcc_year_1')) {
                        localStorage.removeItem('pcc_year_1');
                    }
                    if (localStorage.getItem('pcc_no_1')) {
                        localStorage.removeItem('pcc_no_1');
                    }
                    if (localStorage.getItem('cheque_no_1')) {
                        localStorage.removeItem('cheque_no_1');
                    }
                    if (localStorage.getItem('slpayment_term')) {
                        localStorage.removeItem('slpayment_term');
                    }
                    localStorage.removeItem('remove_slls');
                }
                <?php if ($this->session->userdata('remove_slls')) { ?>
                if (localStorage.getItem('slitems')) {
                    localStorage.removeItem('slitems');
                }
                if (localStorage.getItem('sldiscount')) {
                    localStorage.removeItem('sldiscount');
                }
                if (localStorage.getItem('sltax2')) {
                    localStorage.removeItem('sltax2');
                }
                if (localStorage.getItem('srref')) {
                    localStorage.removeItem('srref');
                }
                if (localStorage.getItem('slshipping')) {
                    localStorage.removeItem('slshipping');
                }
                if (localStorage.getItem('slwarehouse')) {
                    localStorage.removeItem('slwarehouse');
                }
                if (localStorage.getItem('slnote')) {
                    localStorage.removeItem('slnote');
                }
                if (localStorage.getItem('slinnote')) {
                    localStorage.removeItem('slinnote');
                }
                if (localStorage.getItem('slcustomer')) {
                    localStorage.removeItem('slcustomer');
                }
                if (localStorage.getItem('slbiller')) {
                    localStorage.removeItem('slbiller');
                }
                if (localStorage.getItem('slsaleman_by')) {
                    localStorage.removeItem('slsaleman_by');
                }
                if (localStorage.getItem('slcurrency')) {
                    localStorage.removeItem('slcurrency');
                }
                if (localStorage.getItem('sldate')) {
                    localStorage.removeItem('sldate');
                }
                if (localStorage.getItem('slsale_status')) {
                    localStorage.removeItem('slsale_status');
                }
                if (localStorage.getItem('slpayment_status')) {
                    localStorage.removeItem('slpayment_status');
                }
                if (localStorage.getItem('paid_by')) {
                    localStorage.removeItem('paid_by');
                }
                if (localStorage.getItem('amount_1')) {
                    localStorage.removeItem('amount_1');
                }
                if (localStorage.getItem('paid_by_1')) {
                    localStorage.removeItem('paid_by_1');
                }
                if (localStorage.getItem('pcc_holder_1')) {
                    localStorage.removeItem('pcc_holder_1');
                }
                if (localStorage.getItem('pcc_type_1')) {
                    localStorage.removeItem('pcc_type_1');
                }
                if (localStorage.getItem('pcc_month_1')) {
                    localStorage.removeItem('pcc_month_1');
                }
                if (localStorage.getItem('pcc_year_1')) {
                    localStorage.removeItem('pcc_year_1');
                }
                if (localStorage.getItem('pcc_no_1')) {
                    localStorage.removeItem('pcc_no_1');
                }
                if (localStorage.getItem('cheque_no_1')) {
                    localStorage.removeItem('cheque_no_1');
                }
                if (localStorage.getItem('slpayment_term')) {
                    localStorage.removeItem('slpayment_term');
                }
                <?php $this->bpas->unset_data('remove_slls'); } ?>
                $(document).on('click', '.sledit', function (e) {
                    if (localStorage.getItem('slitems')) {
                        e.preventDefault();
                        var href = $(this).attr('href');
                        bootbox.confirm("<?=lang('you_will_loss_sale_data')?>", function (result) {
                            if (result) {
                                window.location.href = href;
                            }
                        });
                    }
                });
                $(document).on('click', '.slduplicate', function (e) {
                    if (localStorage.getItem('slitems')) {
                        e.preventDefault();
                        var href = $(this).attr('href');
                        bootbox.confirm("<?=lang('you_will_loss_sale_data')?>", function (result) {
                            if (result) {
                                window.location.href = href;
                            }
                        });
                    }
                });
            });
        </script>
        <script type="text/javascript">
        $(document).ready(function () {
            $('#ttSale').hide();
            $('.tttoggle_down').click(function () {
                $("#ttformSale").slideDown();
                return false;
            });
            $('.tttoggle_up').click(function () {
                $("#ttSale").slideUp();
                return false;
            });
        });
        </script>

        <div class="box sales-table">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-heart nb"></i><?= lang('treatments'); ?> <?php
                if ($this->input->post('start_date')) {
                    echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
                }
                ?></h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a href="#" class="tttoggle_up tip" title="<?= lang('hide_form') ?>">
                                <i class="icon fa fa-toggle-up"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="tttoggle_down tip" title="<?= lang('show_form') ?>">
                                <i class="icon fa fa-toggle-down"></i>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                                <i
                                class="icon fa fa-file-excel-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                                <i
                                class="icon fa fa-file-picture-o"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?= lang('customize_report'); ?></p>

                        <div id="ttSale">

                        <?php echo admin_form_open('reports/customer_report/' . $customer_id); ?>
                        <div class="row">

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="user"><?= lang('created_by'); ?></label>
                                    <?php
                                    $us[''] = lang('select') . ' ' . lang('user');
                                    foreach ($users as $user) {
                                        $us[$user->id] = $user->first_name . ' ' . $user->last_name;
                                    }
                                    echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ''), 'class="form-control" id="user" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('user') . '"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="biller"><?= lang('biller'); ?></label>
                                    <?php
                                    $bl[''] = lang('select') . ' ' . lang('biller');
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('biller') . '"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="warehouse"><?= lang('warehouse'); ?></label>
                                    <?php
                                    $wh[''] = lang('select') . ' ' . lang('warehouse');
                                    foreach ($warehouses as $warehouse) {
                                        $wh[$warehouse->id] = $warehouse->name;
                                    }
                                    echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '"');
                                    ?>
                                </div>
                            </div>
                            <?php if ($Settings->product_serial) {
                                        ?>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang('serial_no', 'serial'); ?>
                                        <?= form_input('serial', '', 'class="form-control tip" id="serial"'); ?>
                                    </div>
                                </div>
                                <?php
                                    } ?>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang('start_date', 'start_date'); ?>
                                        <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang('end_date', 'end_date'); ?>
                                        <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div
                                class="controls"> <?php echo form_submit('submit_sale_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?> </div>
                            </div>
                            <?php echo form_close(); ?>
                        </div>
                        <div class="clearfix"></div>

                        <div class="table-responsive">
                            <table id="TTData" class="table table-hover table-striped" cellpadding="0" cellspacing="0" border="0">
                                <thead>
                                    <tr>
                                        <th style="min-width:30px; width: 30px; text-align: center;">
                                            <input class="checkbox checkft" type="checkbox" name="check"/>
                                        </th>
                                        <th><?= lang('date'); ?></th>
                                        <th><?= lang('type'); ?></th>
                                        <th><?= lang('reference_no'); ?></th>
                                        <th><?= lang('biller'); ?></th>
                                        <th><?= lang('customer'); ?></th>
                                        <th style="text-align: center !important;"><?= lang('sale_status'); ?></th>
                                        <th style="text-align: right !important;"><?= lang('grand_total'); ?></th>
                                        <th style="text-align: right !important;"><?= lang('deposit'); ?></th>
                                        <th style="text-align: right !important;"><?= lang('balance'); ?></th>
                                        <th><?= lang('order_status'); ?></th>
                                        <th><?= lang('bed'); ?></th>
                                        <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
                                        <th></th>
                                        <th style="width:80px; text-align:center;"><?= lang('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="14" class="dataTables_empty"><?= lang('loading_data'); ?></td>
                                    </tr>
                                </tbody>
                                <tfoot class="dtFilter">
                                    <tr class="active">
                                        <th style="min-width:30px; width: 30px; text-align: center;">
                                            <input class="checkbox checkft" type="checkbox" name="check"/>
                                        </th>
                                        <th></th><th></th><th></th><th></th><th></th>
                                        <th></th>
                                        <th><?= lang('grand_total'); ?></th>
                                        <th><?= lang('deposit'); ?></th>
                                        <th><?= lang('balance'); ?></th>
                                        <th></th>
                                        <th></th>
                                        <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i></th>
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
    </div>
    <div id="sales-con" class="tab-pane fade in">
        <?php
        $v = '&customer=' . $customer_id;
        if ($this->input->post('submit_sale_report')) {

            if ($this->input->post('reference_no')) {
                $v .= "&reference_no=" . $this->input->post('reference_no');
            }
            if ($this->input->post('customer')) {
                $v .= "&customer=" . $this->input->post('customer');
            }
            if ($this->input->post('saleman')) {
                $v .= "&saleman=" . $this->input->post('saleman');
            }
            if ($this->input->post('biller')) {
                $v .= "&biller=" . $this->input->post('biller');
            }
            if ($this->input->post('warehouse')) {
                $v .= "&warehouse=" . $this->input->post('warehouse');
            }
            if ($this->input->post('delivered_by')) {
                $v .= "&delivered_by=" . $this->input->post('delivered_by');
            }
            if ($this->input->post('user')) {
                $v .= "&user=" . $this->input->post('user');
            }
            if ($this->input->post('serial')) {
                $v .= "&serial=" . $this->input->post('serial');
            }
            if ($this->input->post('start_date')) {
                $v .= "&start_date=" . $this->input->post('start_date');
            }
            if ($this->input->post('end_date')) {
                $v .= "&end_date=" . $this->input->post('end_date');
            }
            if ($this->input->post('product_id')) {
                $v .= "&product_id=" . $this->input->post('product_id');
            }
            if ($this->input->post('group_area')) {
                $v .= "&group_area=" . $this->input->post('group_area');
            }
            if ($this->input->post('payment_status')) {
                $arr = array();
                $arr = $this->input->post('payment_status');
                $get_status = "";
                $get_status = implode('_', $arr);
                $v .= "&payment_status=" . $get_status;
            }
            if (isset($alert_id)) {
                $v .= "&a=" . $alert_id;
            }

        }
        ?>
        <script>
        $(document).ready(function() {
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
                'sAjaxSource': '<?= admin_url('sales/getSales'. '?v=1' . $v . ($this->input->get('shop') ? '&shop=' . $this->input->get('shop') : '') . ($this->input->get('attachment') ? '&attachment=' . $this->input->get('attachment') : '') . ($this->input->get('delivery') ? '&delivery=' . $this->input->get('delivery') : '')); ?>',
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
                    //$("td:first", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                    nRow.id = aData[0];
                    nRow.setAttribute('data-return-id', aData[12]);
                    nRow.className = "invoice_link re" + aData[12];
                    //if(aData[7] > aData[9]){ nRow.className = "product_link warning"; } else { nRow.className = "product_link"; }

                    var action = $('td:eq(15)', nRow);              
                    if (aData[9] == 'completed' && aData[13] == 'paid') {
                        action.find('.edit').remove();
                        action.find('.delete').remove();
                        action.find('.add_payment').remove();
                        action.find('.add_downpayment').remove();
                    }
                    return nRow;
                },
                "aoColumns": [
                    { "bSortable": false, "mRender": checkbox },
                    { "mRender": fld },
                    { "mRender": "" },
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    { "mRender": row_status },
                    { "mRender": currencyFormat },
                    { "mRender": currencyFormat },
                    { "mRender": currencyFormat },
                    { "mRender": pay_status },
                    { "mRender": row_status },
                    { "bVisible": false },
                    { "bSortable": false }
                ],
                "fnFooterCallback": function(nRow, aaData, iStart, iEnd, aiDisplay) {
                    var gtotal = 0,
                        paid = 0,
                        balance = 0;
                    for (var i = 0; i < aaData.length; i++) {
                        gtotal += parseFloat(aaData[aiDisplay[i]][10]);
                        paid += parseFloat(aaData[aiDisplay[i]][11]);
                        balance += parseFloat(aaData[aiDisplay[i]][12]);
                    }
                    var nCells = nRow.getElementsByTagName('th');
                    nCells[10].innerHTML = currencyFormat(parseFloat(gtotal));
                    nCells[11].innerHTML = currencyFormat(parseFloat(paid));
                    nCells[12].innerHTML = currencyFormat(parseFloat(balance));
                }
            }).fnSetFilteringDelay().dtFilter([{
                    column_number: 1,
                    filter_default_label: "[<?= lang('date'); ?> (yyyy-mm-dd)]",
                    filter_type: "text",
                    data: []
                },
                {
                    column_number: 2,
                    filter_default_label: "[<?= lang('reference_no'); ?>]",
                    filter_type: "text",
                    data: []
                },
                {
                    column_number: 3,
                    filter_default_label: "[<?= lang('biller'); ?>]",
                    filter_type: "text",
                    data: []
                },
                {
                    column_number: 4,
                    filter_default_label: "[<?= lang('customer'); ?>]",
                    filter_type: "text",
                    data: []
                },
                {
                    column_number: 5,
                    filter_default_label: "[<?= lang('project'); ?>]",
                    filter_type: "text",
                    data: []
                },
                {
                    column_number: 6,
                    filter_default_label: "[<?= lang('deliveries'); ?>]",
                    filter_type: "text",
                    data: []
                },
                {
                    column_number: 7,
                    filter_default_label: "[<?= lang('saleman'); ?>]",
                    filter_type: "text",
                    data: []
                },
                {
                    column_number: 8,
                    filter_default_label: "[<?= lang('order_ref'); ?>]",
                    filter_type: "text",
                    data: []
                },
                
                {
                    column_number: 9,
                    filter_default_label: "[<?= lang('sale_status'); ?>]",
                    filter_type: "text",
                    data: []
                },
                {
                    column_number: 13,
                    filter_default_label: "[<?= lang('payment_status'); ?>]",
                    filter_type: "text",
                    data: []
                },
                {
                    column_number: 14,
                    filter_default_label: "[<?= lang('delivery_status'); ?>]",
                    filter_type: "text",
                    data: []
                },
            ], "footer");
        });
        </script>
        <script type="text/javascript">
        $(document).ready(function () {
            $('#formSale').hide();
            $('.Saletoggle_down').click(function () {
                $("#formSale").slideDown();
                return false;
            });
            $('.Saletoggle_up').click(function () {
                $("#formSale").slideUp();
                return false;
            });
        });
        </script>

        <div class="box sales-table">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-heart nb"></i><?= lang('customer_sales_report'); ?> <?php
                if ($this->input->post('start_date')) {
                    echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
                }
                ?></h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a href="#" class="Saletoggle_up tip" title="<?= lang('hide_form') ?>">
                                <i class="icon fa fa-toggle-up"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="Saletoggle_down tip" title="<?= lang('show_form') ?>">
                                <i class="icon fa fa-toggle-down"></i>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                                <i
                                class="icon fa fa-file-excel-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                                <i
                                class="icon fa fa-file-picture-o"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?= lang('customize_report'); ?></p>

                        <div id="formSale">

                        <?php echo admin_form_open('reports/customer_report/' . $customer_id); ?>
                        <div class="row">

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="user"><?= lang('created_by'); ?></label>
                                    <?php
                                    $us[''] = lang('select') . ' ' . lang('user');
                                    foreach ($users as $user) {
                                        $us[$user->id] = $user->first_name . ' ' . $user->last_name;
                                    }
                                    echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ''), 'class="form-control" id="user" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('user') . '"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="biller"><?= lang('biller'); ?></label>
                                    <?php
                                    $bl[''] = lang('select') . ' ' . lang('biller');
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('biller') . '"');
                                    ?>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label" for="warehouse"><?= lang('warehouse'); ?></label>
                                    <?php
                                    $wh[''] = lang('select') . ' ' . lang('warehouse');
                                    foreach ($warehouses as $warehouse) {
                                        $wh[$warehouse->id] = $warehouse->name;
                                    }
                                    echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '"');
                                    ?>
                                </div>
                            </div>
                            <?php if ($Settings->product_serial) {
                                        ?>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang('serial_no', 'serial'); ?>
                                        <?= form_input('serial', '', 'class="form-control tip" id="serial"'); ?>
                                    </div>
                                </div>
                                <?php
                                    } ?>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang('start_date', 'start_date'); ?>
                                        <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang('end_date', 'end_date'); ?>
                                        <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div
                                class="controls"> <?php echo form_submit('submit_sale_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?> </div>
                            </div>
                            <?php echo form_close(); ?>
                        </div>
                        <div class="clearfix"></div>

                        <div class="table-responsive">
                            <table id="SLData" class="table table-hover table-striped" cellpadding="0" cellspacing="0" border="0">
                                <thead>
                                    <tr>
                                        <th style="min-width:30px; width: 30px; text-align: center;">
                                            <input class="checkbox checkft" type="checkbox" name="check" />
                                        </th>
                                        <th><?= lang('date'); ?></th>
                                        <th><?= lang('reference_no'); ?></th>
                                        <th><?= lang('biller'); ?></th>
                                        <th><?= lang('customer'); ?></th>
                                        <th><?= lang('project'); ?></th>
                                        <th><?= lang('deliveries'); ?></th>
                                        <th><?= lang('saleman'); ?></th>
                                        <th><?= lang('order_ref'); ?></th>
                                        
                                        <th><?= lang('sale_status'); ?></th>
                                        <th><?= lang('grand_total'); ?></th>
                                        <th><?= lang('paid'); ?></th>
                                        <th><?= lang('balance'); ?></th>
                                        <th><?= lang('payment_status'); ?></th>
                                        <th><?= lang('delivery_status'); ?></th>
                                        <th></th>
                                        <th style="width:80px; text-align:center;"><?= lang('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="14" class="dataTables_empty"><?= lang('loading_data'); ?></td>
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
                                        <th><?= lang('grand_total'); ?></th>
                                        <th><?= lang('paid'); ?></th>
                                        <th><?= lang('balance'); ?></th>
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
    </div>
    <div id="payments-con" class="tab-pane fade in">
        <?php
        $p = '&customer=' . $customer_id;
        if ($this->input->post('submit_payment_report')) {
            if ($this->input->post('pay_user')) {
                $p .= '&user=' . $this->input->post('pay_user');
            }
            if ($this->input->post('pay_start_date')) {
                $p .= '&start_date=' . $this->input->post('pay_start_date');
            }
            if ($this->input->post('pay_end_date')) {
                $p .= '&end_date=' . $this->input->post('pay_end_date');
            }
        }
        ?>
        <script>
        $(document).ready(function () {
          
            oTable = $('#PayRData').dataTable({
                "aaSorting": [[0, "desc"]],
                "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                "iDisplayLength": <?= $Settings->rows_per_page ?>,
                'bProcessing': true, 'bServerSide': true,
                'sAjaxSource': '<?= admin_url('reports/getPaymentsReport/?v=1' . $p) ?>',
                'fnServerData': function (sSource, aoData, fnCallback) {
                    aoData.push({
                        "name": "<?= $this->security->get_csrf_token_name() ?>",
                        "value": "<?= $this->security->get_csrf_hash() ?>"
                    });
                    $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                },
                "aoColumns": [{"mRender": fld}, null, null, {"bVisible": false}, {"mRender": pay_status}, {"mRender": currencyFormat}, {"mRender": row_status}],
                'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                    nRow.id = aData[7];
                    nRow.className = "payment_link";
                    if (aData[6] == 'returned') {
                        nRow.className = "payment_link danger";
                    }
                    return nRow;
                },
                "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                    var total = 0;
                    for (var i = 0; i < aaData.length; i++) {
                        total += parseFloat(aaData[aiDisplay[i]][5]);
                    }
                    var nCells = nRow.getElementsByTagName('th');
                    nCells[4].innerHTML = currencyFormat(parseFloat(total));
                }
            }).fnSetFilteringDelay().dtFilter([
                {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                {column_number: 1, filter_default_label: "[<?=lang('payment_ref');?>]", filter_type: "text", data: []},
                {column_number: 2, filter_default_label: "[<?=lang('sale_ref');?>]", filter_type: "text", data: []},
                {column_number: 4, filter_default_label: "[<?=lang('paid_by');?>]", filter_type: "text", data: []},
                {column_number: 6, filter_default_label: "[<?=lang('type');?>]", filter_type: "text", data: []},
            ], "footer");
        });
        </script>
        <script type="text/javascript">
        $(document).ready(function () {
            $('#payform').hide();
            $('.paytoggle_down').click(function () {
                $("#payform").slideDown();
                return false;
            });
            $('.paytoggle_up').click(function () {
                $("#payform").slideUp();
                return false;
            });
        });
        </script>

        <div class="box payments-table">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-money nb"></i><?= lang('customer_payments_report'); ?> <?php
                if ($this->input->post('start_date')) {
                    echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
                }
                ?></h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a href="#" class="paytoggle_up tip" title="<?= lang('hide_form') ?>">
                                <i
                                class="icon fa fa-toggle-up"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="paytoggle_down tip" title="<?= lang('show_form') ?>">
                                <i
                                class="icon fa fa-toggle-down"></i>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a href="#" id="pdf1" class="tip" title="<?= lang('download_pdf') ?>">
                                <i class="icon fa fa-file-pdf-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="xls1" class="tip" title="<?= lang('download_xls') ?>">
                                <i class="icon fa fa-file-excel-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="image1" class="tip" title="<?= lang('save_image') ?>">
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

                        <div id="payform">

                            <?php echo admin_form_open('reports/customer_report/' . $customer_id . '/#payments-con'); ?>
                            <div class="row">

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="user"><?= lang('created_by'); ?></label>
                                        <?php
                                        $us[''] = lang('select') . ' ' . lang('user');
                                        foreach ($users as $user) {
                                            $us[$user->id] = $user->first_name . ' ' . $user->last_name;
                                        }
                                        echo form_dropdown('pay_user', $us, (isset($_POST['pay_user']) ? $_POST['pay_user'] : ''), 'class="form-control" id="user" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('user') . '"');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang('start_date', 'start_date'); ?>
                                        <?php echo form_input('pay_start_date', (isset($_POST['pay_start_date']) ? $_POST['pay_start_date'] : ''), 'class="form-control date" id="start_date"'); ?>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang('end_date', 'end_date'); ?>
                                        <?php echo form_input('pay_end_date', (isset($_POST['pay_end_date']) ? $_POST['pay_end_date'] : ''), 'class="form-control date" id="end_date"'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div
                                class="controls"> <?php echo form_submit('submit_payment_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?> </div>
                            </div>
                            <?php echo form_close(); ?>

                        </div>
                        <div class="clearfix"></div>

                        <div class="table-responsive">
                            <table id="PayRData"
                            class="table table-hover table-striped table-condensed reports-table reports-table">

                            <thead>
                                <tr>
                                    <th><?= lang('date'); ?></th>
                                    <th><?= lang('payment_ref'); ?></th>
                                    <th><?= lang('sale_ref'); ?></th>
                                    <th><?= lang('purchase_ref'); ?></th>
                                    <th><?= lang('paid_by'); ?></th>
                                    <th><?= lang('amount'); ?></th>
                                    <th><?= lang('type'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                </tr>
                            </tbody>
                            <tfoot class="dtFilter">
                                <tr class="active">
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th><?= lang('amount'); ?></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="quotes-con" class="tab-pane fade in">
        <script type="text/javascript">
        $(document).ready(function () {
            oTable = $('#QuRData').dataTable({
                "aaSorting": [[0, "desc"]],
                "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                "iDisplayLength": <?= $Settings->rows_per_page ?>,
                'bProcessing': true, 'bServerSide': true,
                'sAjaxSource': '<?= admin_url('reports/getQuotesReport/?v=1&customer=' . $customer_id) ?>',
                'fnServerData': function (sSource, aoData, fnCallback) {
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
                "aoColumns": [{"mRender": fld}, null, null, null, {
                    "bSearchable": false,
                    "mRender": pqFormat
                }, {"mRender": currencyFormat}, {"mRender": row_status}],
            }).fnSetFilteringDelay().dtFilter([
                {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                {column_number: 1, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
                {column_number: 2, filter_default_label: "[<?=lang('biller');?>]", filter_type: "text", data: []},
                {column_number: 3, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
                {column_number: 5, filter_default_label: "[<?=lang('grand_total');?>]", filter_type: "text", data: []},
                {column_number: 6, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
            ], "footer");
        });
        </script>
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-heart-o nb"></i><?=  lang('quotes'); ?>
                </h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a href="#" id="pdf1" class="tip" title="<?= lang('download_pdf') ?>">
                                <i class="icon fa fa-file-pdf-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="xls1" class="tip" title="<?= lang('download_xls') ?>">
                                <i class="icon fa fa-file-excel-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="image1" class="tip image" title="<?= lang('save_image') ?>">
                                <i class="icon fa fa-file-picture-o"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?php echo lang('list_results'); ?></p>

                        <div class="table-responsive">
                            <table id="QuRData" class="table table-hover table-striped table-condensed reports-table">
                                <thead>
                                    <tr>
                                        <th><?= lang('date'); ?></th>
                                        <th><?= lang('reference_no'); ?></th>
                                        <th><?= lang('biller'); ?></th>
                                        <th><?= lang('customer'); ?></th>
                                        <th><?= lang('product_qty'); ?></th>
                                        <th><?= lang('grand_total'); ?></th>
                                        <th><?= lang('status'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="7"
                                        class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                    </tr>
                                </tbody>
                                <tfoot class="dtFilter">
                                    <tr class="active">
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th><?= lang('product_qty'); ?></th>
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
    </div>
    <div id="deposits-con" class="tab-pane fade in">
        <script type="text/javascript">
        $(document).ready(function () {
            oTable = $('#DepData').dataTable({
                    "aaSorting": [[0, "desc"]],
                    "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
                    "iDisplayLength": <?= $Settings->rows_per_page ?>,
                    'bProcessing': true, 'bServerSide': true,
                    'sAjaxSource': '<?= admin_url('reports/get_deposits/' . $customer_id) ?>',
                    'fnServerData': function (sSource, aoData, fnCallback) {
                        aoData.push({
                            "name": "<?= $this->security->get_csrf_token_name() ?>",
                            "value": "<?= $this->security->get_csrf_hash() ?>"
                        });
                        $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
                    },
                    "aoColumns": [{"mRender": fld}, {"mRender": currencyFormat}, null, null, {"mRender": decode_html}]
                }).fnSetFilteringDelay().dtFilter([
                {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                {column_number: 1, filter_default_label: "[<?=lang('amount');?>]", filter_type: "text", data: []},
                {column_number: 2, filter_default_label: "[<?=lang('paid_by');?>]", filter_type: "text", data: []},
                {column_number: 3, filter_default_label: "[<?=lang('created_by');?>]", filter_type: "text", data: []},
                {column_number: 4, filter_default_label: "[<?=lang('note');?>]", filter_type: "text", data: []},
            ], "footer");
        });
        </script>
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-heart-o nb"></i><?=  lang('quotes'); ?>
                </h2>

                <div class="box-icon">
                    <ul class="btn-tasks">
                        <li class="dropdown">
                            <a href="#" id="pdf1" class="tip" title="<?= lang('download_pdf') ?>">
                                <i class="icon fa fa-file-pdf-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="xls1" class="tip" title="<?= lang('download_xls') ?>">
                                <i class="icon fa fa-file-excel-o"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="image1" class="tip image" title="<?= lang('save_image') ?>">
                                <i class="icon fa fa-file-picture-o"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="introtext"><?php echo lang('list_results'); ?></p>

                        <div class="table-responsive">
                            <table id="DepData" class="table table-condensed table-hover table-striped reports-table">
                                <thead>
                                <tr class="primary">
                                    <th class="col-xs-2"><?= lang('date'); ?></th>
                                    <th class="col-xs-1"><?= lang('amount'); ?></th>
                                    <th class="col-xs-1"><?= lang('paid_by'); ?></th>
                                    <th class="col-xs-2"><?= lang('created_by'); ?></th>
                                    <th class="col-xs-6"><?= lang('note'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="5" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                <tr class="primary">
                                    <th class="col-xs-2"></th>
                                    <th class="col-xs-1"></th>
                                    <th class="col-xs-1"></th>
                                    <th class="col-xs-2"></th>
                                    <th class="col-xs-6"></th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="installment-con" class="tab-pane fade in">
        <?php
        $v = '&customer=' . $customer_id;
        if ($this->input->post('sale_reference_no')) {
            $v .= "&sale_reference_no=" . $this->input->post('sale_reference_no');
        }
        if ($this->input->post('reference_no')) {
            $v .= "&reference_no=" . $this->input->post('reference_no');
        }
        // if ($this->input->post('customer')) {
        //     $v .= "&customer=" . $this->input->post('customer');
        // }
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
        if ($this->input->post('status')) {
            $v .= "&status=" . $this->input->post('status');
        }
        ?>
        <script>
            $(document).ready(function () {
                oTable = $('#InstallmentTable').dataTable({
                    "aaSorting": [[1, "desc"], [3, "desc"]],
                    "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
                    "iDisplayLength": <?=$Settings->rows_per_page?>,
                    'bProcessing': true, 'bServerSide': true,
                    'sAjaxSource': '<?=admin_url('reports/getInstallmentsReport?v=1&'. $v)?>',
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
                    {"sClass" : "center", "mRender": fld}, 
                    {"sClass" : "left"}, 
                    null,
                    {"sClass" : "center" <?php if($Settings->project != 1){ echo ', "bVisible": false'; }?>}, 
                    null,
                    null,
                    null,
                    {"mRender" : currencyFormat},
                    {"mRender" : currencyFormat},
                    {"mRender" : currencyFormat},
                    {"mRender" : currencyFormat},
                    {"mRender" : currencyFormat}, 
                    {"mRender" : currencyFormat}, 
                    {"mRender" : currencyFormat},
                    {"sClass" : "center"},
                    {"mRender" : row_status , "bSortable": false}],
                    "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                        var deposit = 0, principal =0, interest = 0, amount = 0, paid = 0, balance = 0;
                        for (var i = 0; i < aaData.length; i++) {
                            deposit += parseFloat(aaData[aiDisplay[i]][8]);
                            principal += parseFloat(aaData[aiDisplay[i]][9]);
                            interest += parseFloat(aaData[aiDisplay[i]][10]);
                            amount += parseFloat(aaData[aiDisplay[i]][11]);
                            paid += parseFloat(aaData[aiDisplay[i]][12]);
                            balance += parseFloat(aaData[aiDisplay[i]][13]);
                        }
                        var nCells = nRow.getElementsByTagName('th');
                        <?php if($Settings->project != 1){ ?>
                            nCells[7].innerHTML = currencyFormat(parseFloat(deposit));
                            nCells[8].innerHTML = currencyFormat(parseFloat(principal));
                            nCells[9].innerHTML = currencyFormat(parseFloat(interest));
                            nCells[10].innerHTML = currencyFormat(parseFloat(amount));
                            nCells[11].innerHTML = currencyFormat(parseFloat(paid));
                            nCells[12].innerHTML = currencyFormat(parseFloat(balance));
                        <?php }else{ ?>
                            nCells[8].innerHTML = currencyFormat(parseFloat(deposit));
                            nCells[9].innerHTML = currencyFormat(parseFloat(principal));
                            nCells[10].innerHTML = currencyFormat(parseFloat(interest));
                            nCells[11].innerHTML = currencyFormat(parseFloat(amount));
                            nCells[12].innerHTML = currencyFormat(parseFloat(paid));
                            nCells[13].innerHTML = currencyFormat(parseFloat(balance));
                        <?php } ?>
                    }
                }).fnSetFilteringDelay().dtFilter([
                    {column_number: 0, filter_default_label: "[<?=lang('date');?> (yyyy-mm-dd)]", filter_type: "text", data: []},
                    {column_number: 1, filter_default_label: "[<?=lang('sale_reference_no');?>]", filter_type: "text", data: []},
                    {column_number: 2, filter_default_label: "[<?=lang('reference_no');?>]", filter_type: "text", data: []},
                    {column_number: 3, filter_default_label: "[<?=lang('project');?>]", filter_type: "text", data: []},
                    {column_number: 4, filter_default_label: "[<?=lang('customer');?>]", filter_type: "text", data: []},
                    {column_number: 5, filter_default_label: "[<?=lang('phone');?>]", filter_type: "text", data: []},
                    {column_number: 6, filter_default_label: "[<?=lang('product');?>]", filter_type: "text", data: []},
                    {column_number: 7, filter_default_label: "[<?=lang('installment_amount');?>]", filter_type: "text", data: []},
                    {column_number: 14, filter_default_label: "[<?=lang('count');?>]", filter_type: "text", data: []},
                    {column_number: 15, filter_default_label: "[<?=lang('status');?>]", filter_type: "text", data: []},
                ], "footer");
            });

            
        </script>
     
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-calendar"></i><?= lang('installments_report'); ?></h2>
                
                <div class="box-icon">
                    <ul class="btn-tasks">
                    
                        <li class="dropdown">
                            <a href="#" class="install_toggle_up tip" title="<?= lang('hide_form') ?>">
                                <i class="icon fa fa-toggle-up"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="install_toggle_down tip" title="<?= lang('show_form') ?>">
                                <i class="icon fa fa-toggle-down"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="Install_xls" class="tip" title="<?= lang('download_xls') ?>">
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
                        <div id="formInsall">
                            <div class="row">
                                
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang("sale_reference_no", "sale_reference_no"); ?>
                                        <?php echo form_input('sale_reference_no', (isset($_POST['sale_reference_no']) ? $_POST['sale_reference_no'] : ""), 'class="form-control tip" id="sale_reference_no"'); ?>
                                    </div>
                                </div>
                                
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <?= lang("reference_no", "reference_no"); ?>
                                        <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ""), 'class="form-control tip" id="reference_no"'); ?>
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
                                        <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                        <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ""), 'class="form-control" id="customer_id" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"'); ?>
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

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="control-label" for="warehouse"><?= lang("status"); ?></label>
                                        <?php
                                            $status[''] = lang("select").' '.lang("status");
                                            $status['active'] = lang("active");
                                            $status['inactive'] = lang("inactive");
                                            $status['completed'] = lang("completed");
                                            $status['payoff'] = lang("payoff");
                                            echo form_dropdown('status', $status, (isset($_POST['status']) ? $_POST['status'] : ""), 'class="form-control" id="status" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("status") . '"');
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
                            <table id="InstallmentTable" class="table table-bordered table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th><?= lang("date"); ?></th>
                                        <th><?= lang("sale_reference_no"); ?></th>
                                        <th><?= lang("reference_no"); ?></th>
                                        <th><?= lang("project"); ?></th>
                                        <th><?= lang("customer"); ?></th>
                                        <th><?= lang("phone"); ?></th>
                                        <th><?= lang("product"); ?></th>
                                        <th><?= lang("installment_amount"); ?></th>
                                        <th><?= lang("deposit"); ?></th>
                                        <th><?= lang("principal"); ?></th>
                                        <th><?= lang("interest"); ?></th>
                                        <th><?= lang("payment"); ?></th>
                                        <th><?= lang("paid"); ?></th>
                                        <th><?= lang("balance"); ?></th>
                                        <th><?= lang("count"); ?></th>
                                        <th><?= lang("status"); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="16" class="dataTables_empty"><?= lang("loading_data"); ?></td>
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
    </div>

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
$(document).ready(function () {
    $('#pdf').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=admin_url('reports/getSalesReport/pdf/?v=1' . $v)?>";
        return false;
    });
    $('#xls').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=admin_url('reports/getSalesReport/0/xls/?v=1' . $v)?>";
        return false;
    });
    $('#image').click(function (event) {
        event.preventDefault();
        html2canvas($('.sales-table'), {
            onrendered: function (canvas) {
                openImg(canvas.toDataURL());
            }
        });
        return false;
    });
    $('#pdf1').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=admin_url('reports/getPaymentsReport/pdf/?v=1' . $p)?>";
        return false;
    });
    $('#xls1').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=admin_url('reports/getPaymentsReport/0/xls/?v=1' . $p)?>";
        return false;
    });
    $('#image1').click(function (event) {
        event.preventDefault();
        html2canvas($('.payments-table'), {
            onrendered: function (canvas) {
                openImg(canvas.toDataURL());
            }
        });
        return false;
    });
    $('#formInsall').hide();
    $('.install_toggle_down').click(function () {
        $("#formInsall").slideDown();
        return false;
    });
    $('.install_toggle_up').click(function () {
        $("#formInsall").slideUp();
        return false;
    });
    $('#Install_pdf').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=admin_url('reports/getInstallmentsReport/pdf/?v=1'.$v)?>";
        return false;
    });
    $('#Install_xls').click(function (event) {
        event.preventDefault();
        window.location.href = "<?=admin_url('reports/getInstallmentsReport/0/xls/?v=1'.$v)?>";
        return false;
    });
});
</script>
