<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
    $v = "";
    if ($this->input->post('product')) {
        $v .= "&product=" . $this->input->post('product');
    }
    if ($this->input->post('category')) {
        $v .= "&category=" . $this->input->post('category');
    }
?>
<script>
    $(document).ready(function () {
        var old_qty = 0;
        $(document).on('change', '.qty_alert', function () {
            var row = $(this).closest('tr');
            row.first('td').find('input[type="checkbox"]').iCheck('check');
        });
        $(document).on('focus', '.qty_alert', function () {
            $(this).select();
            old_qty = $(this).val();
        });
        $(document).on('focusout', '.qty_alert', function () {
            if ($(this).val() == '' || isNaN($(this).val())) {
                $(this).val(parseFloat(old_qty).toFixed(2));
            } else {
                $(this).attr('data', parseFloat($(this).val()).toFixed(2));
                $(this).val(parseFloat($(this).val()).toFixed(2));
            }
        });
        $(document).on('click', '.form-submit', function () {
            var btn = $(this);
            btn.html('<i class="fa fa-circle-o-notch fa-spin fa-fw"></i>');
            var row = btn.closest('tr');
            var product_id = row.attr('id');

            row.find('input[type=text]').each(function() {
                var warehouse_id = $(this).attr('wh');
                var qty_alert    = $(this).val();
                $.ajax({
                    type: 'post',
                    url: '<?= admin_url('system_settings/update_product_qty_alert'); ?>',
                    dataType: "json",
                    data: {
                        <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>',
                        product_id: product_id, warehouse_id: warehouse_id, qty_alert: qty_alert
                    }, success: function (data) {
                        if (data.status != 1)
                            btn.removeClass('btn-primary').addClass('btn-danger').html('<i class="fa fa-times"></i>');
                        else
                            btn.removeClass('btn-primary').removeClass('btn-danger').addClass('btn-success').html('<i class="fa fa-check"></i>');
                    }, error: function (data) {
                        btn.removeClass('btn-primary').addClass('btn-danger').html('<i class="fa fa-times"></i>');
                    }
                });  
            })

            // btn.html('<i class="fa fa-check"></i>');
        });
        function qty_input(x) {
            if(x == null) return '';

            var v = x.split('__');
            return "<div class='text-center'><input type='text' name='alert_"+v[0]+"_"+v[1]+"' wh="+v[1]+" data="+((v[2] && v[2] != '') ? formatDecimals(v[2]) : '0.00')+" value="+((v[2] && v[2] != '') ? formatDecimals(v[2]) : '0.00')+" class='form-control text-center qty_alert' style='padding: 2px; height: auto;'></div>";
        }

        $('#CGData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('system_settings/getProductsAlert/?v=1' . $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                nRow.id = aData[0];
                nRow.className = "product_group_price_id";
                return nRow;
            },
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox}, null, null, 
                <?php 
                    if($warehouses){
                        for($i = 0; $i < count($warehouses); $i++){
                            echo '{"bSortable": false, "mRender": qty_input}, ';
                        }
                    }
                ?>
                {"bSortable": false}
            ]
        }).fnSetFilteringDelay();

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
        <h2 class="blue"><i class="fa-fw fa fa-building"></i><?= $page_title ?></h2>
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
                            <a href="#" id="update_qty_alert" data-action="update_qty_alert">
                                <i class="fa fa-exclamation-circle"></i> <?= lang('update_qty_alert') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo admin_url('system_settings/update_product_qty_alert_csv'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                <i class="fa fa-upload"></i> <?= lang('update_qty_alert_csv') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
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
                <div id="form">
                    <?php echo admin_form_open("system_settings/products_alert"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="product_id"><?= lang("product"); ?></label>
                                <?php
                                $pr[0] = $this->lang->line("all");;
                                foreach ($products as $product) {
                                    $pr[$product->id] = $product->name . " | " . $product->code ;
                                }
                                echo form_dropdown('product', $pr, (isset($_POST['product']) ? $_POST['product'] : ""), 'class="form-control" id="product" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("product") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("category", "category") ?>
                                <?php
                                $cat[0] = $this->lang->line("all");
                                foreach ($categories as $category) {
                                    $cat[$category->id] = $category->name;
                                }
                                echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ''), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls"> <?php echo form_submit('submit_product', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <?php echo admin_form_open('system_settings/product_alert_actions', 'id="action-form"') ?>
                <div class="table-responsive">
                    <table id="CGData" class="table table-bordered table-hover table-striped reports-table">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <th><?= lang('product') . ' (' . lang('code') . ')'; ?></th>
                                <th><?= lang('product_unit'); ?></th>
                                <?php 
                                if($warehouses){
                                    foreach($warehouses as $warehouse) { ?>
                                        <th style="text-align: center !important;"><?= ucwords($warehouse->name) ?></th>
                                <?php }
                                } ?>
                                <th style="width: 85px;"><?= lang('update'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="<?= count($warehouses) + 4 ?>" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
        $('#update_qty_alert').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });
    });
</script>