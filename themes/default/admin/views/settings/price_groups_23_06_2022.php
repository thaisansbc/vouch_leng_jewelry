<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
	$v = "";
    if ($this->input->post('product')) {
        $v .= '&product=' . $this->input->post('product');
    }
?>
<script>
    $(document).ready(function () {
        oTable = $('#CGData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('system_settings/getPriceGroups') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bVisible": false}, null, {"bSortable": false}]
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        // $('#form').hide();
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
<!-- <?= admin_form_open('system_settings/price_group_actions', 'id="action-form"') ?> -->
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-building"></i><?= $page_title ?></h2>
        <!-- <div class="box-icon">
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
        </div> -->
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a title="<?= lang('add_price_group') ?>" class="tip" href="<?php echo admin_url('system_settings/add_price_group'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                    <i class="icon fa fa-plus"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo $this->lang->line('list_results'); ?></p>
                <div id="form" class="hide">
                    <?php echo admin_form_open('system_settings/price_groups'); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="product_id"><?= lang("product"); ?></label>
                                <?php
                                $pr[0] = $this->lang->line("select") . ' ' . $this->lang->line("product");
                                foreach ($products as $product) {
                                    $pr[$product->id] = $product->name . " | " . $product->code ;
                                }
                                echo form_dropdown('product', $pr, (isset($_POST['product']) ? $_POST['product'] : ""), 'class="form-control" id="product" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("product") . '"');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <?php
                        if($price_groups){
                            foreach($price_groups as $price_group) { ?>
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label class="control-label" for="price_group_<?= $price_group->id ?>"><?= ucwords($price_group->name) ?></label>
                                        <?= form_input('price_group_' . $price_group->id, (isset($_POST['price_group_' . $price_group->id]) ? $_POST['price_group_' . $price_group->id] : ''), 'class="form-control tip price_group" id="' . $price_group->id . '" required="required"'); ?>
                                    </div>
                                </div>
                            <?php }
                        } ?>
                    </div>
                    <div class="form-group">
                        <div class="controls"><?php echo form_submit('submit_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?></div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
                <div class="clearfix"></div>
                <div class="table-responsive">
                    <table id="CGData" class="table table-bordered table-hover table-striped reports-table">
                        <thead>
                        <tr>
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th><?php echo $this->lang->line('name'); ?></th>
                            <th style="max-width:85px;"><?php echo $this->lang->line('actions'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="3" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
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
<script type="text/javascript">
    $(document).ready(function () {
        $('#product').change(setPriceGroup);
        function setPriceGroup(){
            var product_id =  $('#product').val() ?  $('#product').val() : '';
            if(product_id != ""){
                $.ajax({
                    type: "get",
                    url: site.base_url + "system_settings/getPriceGroupsByProductID/" + product_id,
                    dataType: "json",
                    success: function (data) {
                        if(data != false){
                            $('.price_group').each(function(index, element) {
                                let price_group = data['price_groups'].find(x => x.id === element.id);
                                if (typeof price_group != "undefined") {
                                    $(element).val(price_group['price']);
                                } else {
                                    $(element).val(data['product']['price']);
                                }
                            });
                        }
                    },
                }).fail(function(xhr, error){
                    console.debug(xhr); 
                    console.debug(error);
                });
            }
        }
    });
</script>
