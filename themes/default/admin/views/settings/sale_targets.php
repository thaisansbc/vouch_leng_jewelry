<script>
    $(document).ready(function () {
        var zones;
        $.ajax({
            url: site.base_url + 'system_settings/getZones_ajax',
            dataType: "json",
            success: function (data) {
                zones = data;
            },
            error: function (xhr, error) {
                console.debug(xhr); 
                console.debug(error);
            }
        });
        
        $('#CGData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('system_settings/getSaleTargets') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
                    { "bSortable": false, "mRender": checkbox }, null, null, null, null, { "mRender": currencyFormat }, 
                    { "fnRender": function (o) {
                        if(o.aData[6] != null){
                            var zns = [];
                            var multi_zones_by_id = o.aData[6].split(",");
                            multi_zones_by_id.forEach((element, index, array) => {
                                zns[index] = zones.find(x => x.id === element).zone_name;
                            }); 
                            var x = "";
                            var c = ["dodgerblue", "seagreen", "darkorange", "mediumslateblue", "darkviolet", "turquoise", "hotpink", "orange", "chocolate", "salmon", "slategray", "mediumpurple", "tomato", "deepskyblue"];
                            zns.forEach((element, index) => {
                                var randomColor = Math.floor(Math.random() * 16777215).toString(16);
                                while(randomColor.length < 6) { randomColor = "0" + randomColor; }
                                x += "<span class='label' style='font-size: 12px; padding: 7px; margin: 5px; display: inline-block; color: white; background-color: #" + randomColor + ";'>" + element + "</span>";
                            });
                            return $(this).innerHTML = "<div style='overflow-wrap: anywhere;'>" + x + "</div>";
                        } else {
                            return null;
                        }
                    }}, null, {"bSortable": false}]
        });
    });
</script>
<?= admin_form_open('system_settings/saleTarget_actions', 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-building"></i><?= $page_title ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i></a>
                    <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?php echo admin_url('system_settings/add_saleTarget'); ?>" data-toggle="modal"
                            data-target="#myModal"><i class="fa fa-plus"></i> <?= lang('add_sale_target') ?></a>
                        </li>
                        <li class="divider"></li>
                        <li><a href="#" id="delete" data-action="delete"><i class="fa fa-trash-o"></i> <?= lang('delete_sale_targets') ?></a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo $this->lang->line("list_results"); ?></p>
                <div class="table-responsive">
                    <table id="CGData" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <th><?php echo $this->lang->line("start_date"); ?></th>
                                <th><?php echo $this->lang->line("end_date"); ?></th>
                                <th><?php echo $this->lang->line("biller"); ?></th>
                                <th><?php echo $this->lang->line("saleman"); ?></th>
                                <th style="text-align: right !important;"><?php echo $this->lang->line("target"); ?></th>
                                <th style="width: 20%;"><?php echo $this->lang->line("zone"); ?></th>
                                <th><?php echo $this->lang->line("description"); ?></th>
                                <th style="width:65px;"><?php echo $this->lang->line("actions"); ?></th>
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

