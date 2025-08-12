<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php

$v = '';
if ($this->input->post('reference_no')) {
    $v .= '&reference_no=' . $this->input->post('reference_no');
}
if ($this->input->post('category')) {
    $v .= '&category=' . $this->input->post('category');
}
if ($this->input->post('warehouse')) {
    $v .= '&warehouse=' . $this->input->post('warehouse');
}
if ($this->input->post('note')) {
    $v .= '&note=' . $this->input->post('note');
}
if ($this->input->post('user')) {
    $v .= '&user=' . $this->input->post('user');
}
if ($this->input->post('year')) {
    $v .= '&year=' . $this->input->post('year');
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
        oTable = $('#EXPData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/getExpensesMonthlyReport' . ($biller_id ? '/' . $biller_id : '') . '/?v=1' . $v); ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[7];
                nRow.className = "expense_link2";
                return nRow;
            },
            "aoColumns": [
                    null,  
                    {"mRender": currencyFormat},
                    {"mRender": currencyFormat},
                    {"mRender": currencyFormat}, 
                    {"mRender": currencyFormat}, 
                    {"mRender": currencyFormat}, 
                    {"mRender": currencyFormat},
                    {"mRender": currencyFormat},
                    {"mRender": currencyFormat},
                    {"mRender": currencyFormat},
                    {"mRender": currencyFormat},
                    {"mRender": currencyFormat},
                    {"mRender": currencyFormat},
                    {"mRender": currencyFormat},
                ],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var total1 = 0,total2 = 0,total3 = 0,total4 = 0,total5 = 0,total6 = 0,total7 = 0,total8 = 0,total9 = 0,total10 = 0,total11 = 0,total12 = 0,total = 0;
                for (var i = 0; i < aaData.length; i++) {
                    total1 += parseFloat(aaData[aiDisplay[i]][1]);
                    total2 += parseFloat(aaData[aiDisplay[i]][2]);
                    total3 += parseFloat(aaData[aiDisplay[i]][3]);
                    total4 += parseFloat(aaData[aiDisplay[i]][4]);
                    total5 += parseFloat(aaData[aiDisplay[i]][5]);
                    total6 += parseFloat(aaData[aiDisplay[i]][6]);
                    total7 += parseFloat(aaData[aiDisplay[i]][7]);
                    total8 += parseFloat(aaData[aiDisplay[i]][8]);
                    total9 += parseFloat(aaData[aiDisplay[i]][9]);
                    total10 += parseFloat(aaData[aiDisplay[i]][10]);
                    total11 += parseFloat(aaData[aiDisplay[i]][11]);
                    total12 += parseFloat(aaData[aiDisplay[i]][12]);
                    total += parseFloat(aaData[aiDisplay[i]][13]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[1].innerHTML = currencyFormat(total1);
                nCells[2].innerHTML = currencyFormat(total2);
                nCells[3].innerHTML = currencyFormat(total3);
                nCells[4].innerHTML = currencyFormat(total4);
                nCells[5].innerHTML = currencyFormat(total5);
                nCells[6].innerHTML = currencyFormat(total6);
                nCells[7].innerHTML = currencyFormat(total7);
                nCells[8].innerHTML = currencyFormat(total8);
                nCells[9].innerHTML = currencyFormat(total9);
                nCells[10].innerHTML = currencyFormat(total10);
                nCells[11].innerHTML = currencyFormat(total11);
                nCells[12].innerHTML = currencyFormat(total12);
                nCells[13].innerHTML = currencyFormat(total);
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('category');?>]", filter_type: "text", data: []},

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
<div class="box">
    <div class="box-header">
        <?php 
            $biller_title = "";
            if ($biller_id) {
                $biller_title = $biller->name;
            } elseif ($multi_biller) {
                if (count($multi_biller) > 1) {
                    $biller_title = lang('all_billers');
                } else {
                    $biller_title = $multi_biller[0]->name;
                }
            } else {
                $biller_title = lang('all_billers');
            }
        ?>
        <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('expenses_monthly_report') . ' (' . $biller_title . ')'; ?> <?php
            if ($this->input->post('start_date')) {
                echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
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
                <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <?php if (($this->Owner || $this->Admin) || !$user_biller_id) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('billers') ?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('reports/expenses') ?>"><i class="fa fa-building-o"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($billers as $biller) {
                                echo '<li><a href="' . admin_url('reports/expenses/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company.'/'.$biller->name . '</a></li>';
                            } ?>
                        </ul>
                    </li>
                <?php } elseif (!empty($billers)){ ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('billers') ?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('reports/expenses') ?>"><i class="fa fa-building-o"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            $biller_id_ = explode(',', $user_biller_id);
                            foreach ($billers as $biller) {
                                foreach ($biller_id_ as $key => $value) {
                                    if ($biller->id==$value) {
                                        echo '<li><a href="' . admin_url('reports/expenses/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company.'/'.$biller->name . '</a></li>';
                                    }
                                }
                                
                            } ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
                    <?php echo admin_form_open('reports/expenses_monthly' . ($biller_id ? '/' . $biller_id : '')); ?>
                    <div class="row">
                        <div class="col-sm-2">
                            <div class="form-group">
                                <?= lang('year', 'year'); ?>
                                <?php echo form_input('year', (isset($_POST['year']) ? $_POST['year'] : date('Y')), 'class="form-control year" id="year"'); ?>
                            </div>
                        </div>
                        

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="category"><?= lang('category'); ?></label>
                                <?php
                                $ct[''] = lang('select') . ' ' . lang('category');
                                foreach ($categories as $category) {
                                    $ct[$category->id] = $category->name;
                                }
                                echo form_dropdown('category', $ct, (isset($_POST['category']) ? $_POST['category'] : ''), 'class="form-control" id="category" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('category') . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang('warehouse'); ?></label>
                                <?php
                                $ct[''] = lang('select') . ' ' . lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    $ct[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $ct, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
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
                    </div>
                    <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>
                <div class="table-responsive">
                    <table id="EXPData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th class="col-xs-1"><?= lang('category'); ?></th>
                            <th class="col-xs-1" style="text-align:right !important;"><?= lang('jan'); ?></th>
                            <th class="col-xs-1" style="text-align:right !important;"><?= lang('feb'); ?></th>
                            <th class="col-xs-1" style="text-align:right !important;"><?= lang('mar'); ?></th>
                            <th class="col-xs-1" style="text-align:right !important;"><?= lang('apr'); ?></th>
                            <th class="col-xs-1" style="text-align:right !important;"><?= lang('may'); ?></th>
                            <th class="col-xs-1" style="text-align:right !important;"><?= lang('jun'); ?></th>
                            <th class="col-xs-1" style="text-align:right !important;"><?= lang('jul'); ?></th>
                            <th class="col-xs-1" style="text-align:right !important;"><?= lang('aug'); ?></th>
                            <th class="col-xs-1" style="text-align:right !important;"><?= lang('sep'); ?></th>
                            <th class="col-xs-1" style="text-align:right !important;"><?= lang('oct'); ?></th>
                            <th class="col-xs-1" style="text-align:right !important;"><?= lang('nov'); ?></th>
                            <th class="col-xs-1" style="text-align:right !important;"><?= lang('dec'); ?></th>
                            <th class="col-xs-1" style="text-align:right !important;"><?= lang('total'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="14" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                            <th></th><th></th>
                            <th></th>
                        </tr>
                        </tfoot>
                    </table>
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
            window.location.href = "<?=admin_url('reports/getExpensesMonthlyReport/' . ($biller_id ? $biller_id : 0) . '/pdf/?v=1' . $v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getExpensesMonthlyReport/' . ($biller_id ? $biller_id : 0) . '/0/xls/?v=1' . $v)?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    openImg(canvas.toDataURL());
                }
            });
            return false;
        });
    });
</script>
