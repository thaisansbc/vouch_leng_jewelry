<script>
    function amount(x){
        if(x){
            amount = x.split('__');
            total  = formatMoney((parseFloat(amount[0]) * parseFloat(amount[1]))/100); 
            console.log(total);
            total_amount = formatMoney(parseFloat(amount[0]) - total);
            return total_amount;
        }
        return 'N/A';
    }
    $(document).ready(function () {
        $('#CGData').dataTable({
            "aaSorting": [[1, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('system_settings/getview/'. $id) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [null, null,null, null, {"mRender": currencyFormat},null,{"mRender": amount}]
        });
    });
</script>
<?= admin_form_open('system_settings/promotion_actions', 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-building"></i><?= $page_title ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="preview" class="tip" title="<?= lang('print') ?>">
                        <i class="icon fa fa-print"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
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
                                <th><?php echo $this->lang->line("product_code"); ?></th>
                                <th><?php echo $this->lang->line("product_name"); ?></th>
                                <th><?php echo $this->lang->line("category"); ?></th>
                                <th><?php echo $this->lang->line("quantity"); ?></th>
                                <th><?php echo $this->lang->line("price"); ?></th>
                                <th><?php echo $this->lang->line("discount"); ?></th>
                                <th><?php echo $this->lang->line("amount"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
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

        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('system_settings/getview/'.$id.'/xls')?>";
            return false;
        });

        $('#preview').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('system_settings/getview/'.$id.'/0/preview')?>";
            return false;
        });

    });
</script>

