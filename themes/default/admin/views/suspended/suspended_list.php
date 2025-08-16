<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<script>
    $(document).ready(function () {
        CURI = '<?= admin_url('suspended_note/index'); ?>';
    });

    $(document).ready(function () {
        function isAvailable(y) {
            if (y == 1) {
                return '<div class="text-center"><span class="payment_status label label-danger">Not Available</span></div>';
            } else if (y == 0) {
                return '<div class="text-center"><span class="payment_status label label-success">Available</span></div>';
            } else if (y == 2) {
                return '<div class="text-center"><span class="payment_status label label-default">Booking</span></div>';
            }
        }
        function img_qrcode(x) {
            return (
                '<div class="text-center"><a href="' +
                site.url +'admin/table/qrcode/'+x +'" data-toggle="modal" data-backdrop="static" data-target="#myModal"><i class="fa fa-qrcode"></i></a></div>'
            );
        }

        oTable = $('#STData').dataTable({
            "aaSorting": [[1, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('table/getSuspended'.($warehouse_id ? '/index/'.$warehouse_id : '')) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox}, 
				{"bSortable": false,"mRender": img_qrcode},
                {"bSortable": true},
                {"bSortable": true},
                {"bSortable": true},
				{"bSortable": false},
                { "mRender": isAvailable },
				{"bSortable": false}
            ]
        });

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
<?= admin_form_open('suspended_note/suspended_actions', 'id="action-form"') ?>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-fw fa fa-money"></i><?= $page_title ?></h2>
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
    <div class="box-icon hide">
        <div class="form-group choose-date hidden-xs">
            <div class="controls">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    <input type="text"
                        value="<?= ($start ? $this->bpas->hrld($start) : '') . ' - ' . ($end ? $this->bpas->hrld($end) : ''); ?>"
                        id="daterange" class="form-control">
                    <span class="input-group-addon"><i class="fa fa-chevron-down"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="box-icon">
        <ul class="btn-tasks">
            <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                    <li><a href="<?= admin_url('table/add_room'); ?>" data-toggle="modal" data-target="#myModal"><i class="fa fa-plus"></i> <?= lang('add_room') ?></a></li>
                    <li><a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?></a></li>
                    <li class="divider"></li>
                    <li><a href="#" id="delete" data-action="delete"><i class="fa fa-trash-o"></i> <?= lang('delete_currencies') ?></a></li>
                </ul>
            </li>
            <?php if (!empty($warehouses)) { ?>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?= admin_url('suspended_note') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        foreach ($warehouses as $warehouse) {
                            echo '<li><a href="' . admin_url('suspended_note/index/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                        }
                        ?>
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
                <p class="introtext"><?= lang("list_results"); ?></p>
                <div id="form">

                    <?php echo admin_form_open("sales"); ?>
                    <div class="row">

                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("start_date", "start_date"); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : date('d/m/Y H:i')), 'class="form-control date" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("end_date", "end_date"); ?>
                                <?php 
                                $tomorrow = date('d/m/Y H:i', strtotime("+1 day"));
                                echo form_input('departure',(isset($_POST['departure']) ? $_POST['departure'] : $tomorrow), 'id="departure" class="form-control datetime" ');?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="end_date">&nbsp;</label>
                                <div class="controls"> <?php echo form_submit('submit', $this->lang->line("search_available"), 'class="btn btn-primary"'); ?> </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php echo form_close(); ?>
                </div>
                <div class="table-responsive">
                    <table id="STData" class="table table-hover table-striped reports-table">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <th style="min-width:100px; width: 60px; text-align: center;"><?= lang("QR_Code") ?></th>
                                <th><?= lang("Room") ?></th>
                                <th><?= lang("price") ?></th>
                                <th><?= lang("bed") ?></th>
                                <th><?= lang("type") ?></th>
                                <th><?= lang("status") ?></th>
                                <th style="width:65px;"><?= lang("actions"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                            </tr>

                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>

<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action"/>
    <?= form_submit('submit', 'submit', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>
<script language="javascript">
    $(document).ready(function () {

        $('#delete').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#excel').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#pdf').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

    });
</script>

