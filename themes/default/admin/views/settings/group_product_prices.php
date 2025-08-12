<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        var ti = 0;
        $(document).on('change', '.price', function () {
            var row = $(this).closest('tr');
            row.first('td').find('input[type="checkbox"]').iCheck('check');
        });
        $(document).on('click', '.form-submit', function () {
            var btn = $(this);
            btn.html('<i class="fa fa-circle-o-notch fa-spin fa-fw"></i>');
            var row = btn.closest('tr');
            var product_id = row.attr('id');
            var academic_year = $('#academic_year').val();

            var arr_data = [];
            $(row.find('td.price_units')).each(function(index) {
                var unit  = $(this).find('.price').attr('unit');
                var price = $(this).find('.price').val();
                arr_data.push({ academic_year: academic_year, product_id: product_id, unit_id: unit, price: price });
            });
            $.ajax({
                type: 'post',
                url: '<?= admin_url('system_settings/update_product_group_price/' . $price_group->id); ?>',
                dataType: "json",
                data: {
                    <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>',
                    data: arr_data
                }, success: function (data) {
                    if (data.status != 1)
                        btn.removeClass('btn-primary').addClass('btn-danger').html('<i class="fa fa-times"></i>');
                    else
                        btn.removeClass('btn-primary').removeClass('btn-danger').addClass('btn-success').html('<i class="fa fa-check"></i>');
                }, error: function (data) {
                    btn.removeClass('btn-primary').addClass('btn-danger').html('<i class="fa fa-times"></i>');
                }
            });
            btn.html('<i class="fa fa-check"></i>');
        });
        function price_input(x) {
            ti = ti+1;
            var v = x.split('__');
            return "<div class=\"text-center\"><input type=\"text\" name=\"price"+v[0]+"\" value=\""+(v[1] != '' ? formatDecimals(v[1]) : '')+"\" class=\"form-control text-center price\" tabindex=\""+(ti)+"\" style=\"padding:2px;height:auto;\"></div>"; // onclick=\"this.select();\"
        }
        $('#CGData_____').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('system_settings/getProductPrices/' . $price_group->id) ?>',
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
                {"bSortable": false, "mRender": checkbox}, null, null,null,{"mRender": currencyFormat},
                {"bSortable": false, "mRender": price_input}, {"bSortable": false}
            ]
        }).fnSetFilteringDelay();
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-building"></i><?= $page_title ?> (<?= $price_group->name; ?>) <?= $academic_year ? (' (' . $academic_year . ' - ' . ($academic_year +1) . ')') : ''; ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="#" id="update_price" data-action="update_price">
                                <i class="fa fa-dollar"></i> <?= lang('update_price') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo admin_url('system_settings/update_prices_csv/' . $price_group->id . '/' . $academic_year); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                <i class="fa fa-upload"></i> <?= lang('update_prices_csv') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" id="delete" data-action="delete">
                                <i class="fa fa-trash-o"></i> <?= lang('delete_product_group_prices') ?>
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
                <div id="form" class="<?= $Settings->module_school ? '' : 'hide'; ?>">
                    <?php echo admin_form_open("system_settings/group_product_prices/" . $price_group->id); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="academic_year"><?= lang("academic_year"); ?></label>
                                <?php echo form_input('academic_year', (isset($_POST['academic_year']) ? $_POST['academic_year'] : $academic_year), 'class="form-control year" id="academic_year"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="submit_report">&nbsp;</label>
                                <div class="controls"> <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                            </div>
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <?= admin_form_open('system_settings/product_group_price_actions/' . $price_group->id, 'id="action-form"') ?>
                <input type="hidden" name="academic_year" id="academic_year" value="<?= $academic_year; ?>">
                <?php if (!empty($results)) { ?>
                    <?php foreach ($results as $key => $data) { ?>
                    <div class="table-responsive">
                        <table id="CGData_<?= $key ?>" class="table table-striped table-bordered CGData" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="no-sort" style="min-width:30px; width: 30px; text-align: center;">
                                        <input class="checkbox checkth" type="checkbox" name="check"/>
                                    </th>
                                    <th class="col-xs-2"><?= lang('product_code'); ?></th>
                                    <th class="col-xs-2"><?= lang('product_name'); ?></th>
                                    <?php foreach($data[0]['units'] as $unit) { ?>
                                        <th class="no-sort" style="text-align: center !important;"><?= $unit->unit_name ?></th>
                                    <?php } ?>
                                    <th class="no-sort" style="width:85px;"><?= lang('update'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $row) {
                                    $product     = $row['product'];
                                    $units       = $row['units'];
                                    $price_group = $row['price_group']; ?>
                                    <tr class="product_group_price_id" id="<?= $product->id; ?>">
                                        <td style="min-width:30px; width: 30px; text-align: center;">
                                            <input class="checkbox multi-select input-xs" type="checkbox" name="val[]" value="<?= $product->id; ?>" style="position: absolute; top: -20%; left: -20%; display: block; width: 140%; height: 140%; margin: 0px; padding: 0px; background: rgb(255, 255, 255); border: 0px; opacity: 0;">
                                        </td>
                                        <td><?= $product->code; ?></td>
                                        <td><?= $product->name; ?></td>
                                        <?php foreach($units as $index => $unit) { ?>
                                            <td class="price_units">
                                                <div class="text-center">
                                                    <input 
                                                        type="text" 
                                                        unit="<?= $unit->unit_id; ?>"
                                                        name="price_<?= $product->id ?>[]" 
                                                        value="<?= $this->bpas->formatDecimal($unit->price); ?>" 
                                                        class="form-control text-center price" 
                                                        tabindex="<?= $index; ?>" 
                                                        style="padding: 2px; height: auto;">
                                                    <input type="hidden" class="form-control" name="unit_<?= $product->id ?>[]" value="<?= $unit->unit_id; ?>" />
                                                </div>
                                            </td>
                                        <?php } ?>
                                        <td>
                                            <div class="text-center"><button class="btn btn-primary btn-xs form-submit" type="button"><i class="fa fa-check"></i></button></div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th style="min-width:30px; width: 30px; text-align: center;">
                                        <input class="checkbox checkth" type="checkbox" name="check"/>
                                    </th>
                                    <th class="col-xs-2"></th>
                                    <th class="col-xs-2"></th>
                                    <?php foreach($data[0]['units'] as $unit) { ?>
                                        <th style="text-align: center !important;"><?= $unit->unit_name ?></th>
                                    <?php } ?>
                                    <th style="width:85px;"><?= lang('update'); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action" />
    <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
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
        $('#update_price').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });
    });
</script>
<link rel="stylesheet" href="<?= $assets; ?>style/jquery.dataTables.min.css">
<script type="text/javascript" src="<?= $assets; ?>js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.CGData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            "aoColumnDefs": [{ aTargets: [ "no-sort" ], bSortable: false }]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('product_code');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('product_name');?>]", filter_type: "text", data: []},
        ], "footer");
    } );
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.price').click(function() {
            $(this).select();
        });
        $('.price').blur(function() {
            var price = $(this).val();
            if (price == '') $(this).val(0);
            $(this).val(formatDecimals(parseFloat($(this).val())));
        });
        $('.price').keypress(function(event) {
            if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        });
    });
</script>