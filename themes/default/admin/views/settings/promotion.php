<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$v = "";

if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}

if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}

if ($this->input->post('product')) {
    $v .= '&product=' . $this->input->post('product');
}

?>

<script>
    $(document).ready(function () {
        $('#CGData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('system_settings/getPromotion/?v=1'.$v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false, "mRender": checkbox}, null, null,{"mRender": fsd},{"mRender": fsd},
            {"bSortable": false}]
        });
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-building"></i><?= $page_title ?>
        <?php
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
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?php echo admin_url('system_settings/add_promotion'); ?>" data-toggle="modal" data-target="#myModal"><i class="fa fa-plus"></i> <?= lang('add_promotion') ?></a></li>
                        <li>
                            <a href="<?php echo admin_url('system_settings/import_promotions'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                <i class="fa fa-plus"></i> <?= lang('import_promotions') ?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li><a href="#" id="delete" data-action="delete"><i class="fa fa-trash-o"></i> <?= lang('delete_promotion') ?></a></li>
                    </ul>
                </li>
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div id="form">
                    <?php echo admin_form_open("system_settings/promotion"); ?>
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <?= lang('product', 'suggest_product'); ?>
                                    <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ''), 'class="form-control" id="suggestproduct"'); ?>
                                    <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : '' ?>" id="report_product_id"/>
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
                        <div class="form-group">
                            <div class="controls"> <?php echo form_submit('submit_promotion', $this->lang->line("submit"), 'class="btn btn-primary"'); ?> </div>
                        </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
            <?= admin_form_open('system_settings/promotion_actions', 'id="action-form"') ?>
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="CGData" class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th><?php echo $this->lang->line("Description"); ?></th>
                            <th><?php echo $this->lang->line("warehouse"); ?></th>
                            <th><?php echo $this->lang->line("start_date"); ?></th>
                            <th><?php echo $this->lang->line("end_date"); ?></th>
                            <th style="width:65px;"><?php echo $this->lang->line("actions"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="7" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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
     $('#form').hide();
        $('.toggle_down').click(function() {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function() {
            $("#form").slideUp();
            return false;
        });
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

        $('#suggestproduct').autocomplete({
        source: site.base_url + 'reports/suggestions',
        select: function(event, ui) {
            $('#report_product_id').val(ui.item.id);
        },
        minLength: 1,
        autoFocus: false,
        delay: 250,
        response: function(event, ui) {
      
            if (ui.content.length == 1 && ui.content[0].id != 0) {
                ui.item = ui.content[0];
                $(this).val(ui.item.label);
                $(this)
                    .data('ui-autocomplete')
                    ._trigger('select', 'autocompleteselect', ui);
                $(this).autocomplete('close');
                $(this).removeClass('ui-autocomplete-loading');
            }
        },
    });
    
    $(document).on('blur', '#suggestproduct', function(e) {
        if (!$(this).val()) {
            $('#report_product_id').val('');
        }
    });

    });
</script>

