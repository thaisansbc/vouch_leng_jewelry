<?php defined('BASEPATH') or exit('No direct script access allowed'); 
$bgs = glob( 'assets/uploads/slides/*.jpg');
?>
<!-- <script>
    $(document).ready(function () {
        oTable = $('#BrandTable').dataTable({
            "aaSorting": [[3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('system_settings/getBrands') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [{"bSortable": false, "mRender": checkbox}, {"bSortable": false, "mRender": img_hl}, {"bSortable": false}]
        });
    });
</script> -->
<style>
    .box-img{
        width: 150px;
        height: 100px;
        
    }
    .box-img img{
        width: 100%;
        height: 100%;
        
    }
    
</style>
<?= admin_form_open('system_settings/_actions', 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-th-list"></i><?= lang('upload_slide'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?php echo admin_url('system_settings/add_slide'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                <i class="fa fa-plus"></i> <?= lang('add_slide') ?>
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
                <div class="table-responsive">
                    <table id="BrandTable" class="table table-bordered table-hover table-striped reports-table">
                        <thead>
                            <tr>
                                <th style=" text-align: center; ">
                                    <?= lang('image'); ?>
                                </th>
                                 <th style=" text-align: center;">
                                    <?= lang('name_image'); ?>
                                </th>
                                <th style="width:170px;"><?= lang('actions'); ?></th>  
                            </tr>
                        </thead>
                        <tbody>   
                                <?php 
                                    foreach ($bgs as &$bg) {
                                        $array = explode('/', $bg);
                                        $image_name = $array[3]; 
                                        //var_dump($image_name);
                                        ?>

                                            <tr style="width: 200px;height: 100px; ">    
                                                <td class="box-img"><img alt="" src="<?=  base_url() . $bg ?>" class="profile-image img-thumbnail"></td>
                                                <td><?= $image_name?> </td>
                                                <td>
                                                    <a style="float: left;" href="#" class="btn btn-danger btn-xs po" style="position: absolute; top: 0;" title="<?= lang('delete_slide') ?>" data-content="<p><?= lang('r_u_sure') ?></p><a class='btn btn-block btn-danger po-delete' href='<?= admin_url('system_settings/delete_slide/'. $image_name) ?>'> <?= lang('i_m_sure') ?></a> <button class='btn btn-block po-close'> <?= lang('no') ?></button>" data-html="true" rel="popover"><i class="fa fa-trash-o"></i></a>
                                                </td>
                                            </tr>                    
                                        <?php 
                                        }
                                    ?>       
                            
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

