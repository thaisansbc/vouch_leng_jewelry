<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-star-o nb"></i><?= lang('schedule'); ?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" id="pdf3" class="tip" title="<?= lang('download_pdf') ?>"><i
                            class="icon fa fa-file-pdf-o"></i></a></li>
                <li class="dropdown"><a href="#" id="xls3" class="tip" title="<?= lang('download_xls') ?>"><i
                            class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a href="#" id="image3" class="tip image"
                                        title="<?= lang('save_image') ?>"><i
                            class="icon fa fa-file-picture-o"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="TrRData" class="table table-bordered table-hover table-striped table-condensed">
                        <thead>
                        <tr>
                            <th><?= lang('no'); ?></th>
                            <th><?= lang('date'); ?></th>
                            <th><?= lang('depreciation'); ?></th>
                            <th><?= lang('accumulated'); ?></th>
                            <th><?= lang('net_value'); ?></th>
                            <th><?= lang('status'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php 
                        if(isset($evaluation_list)){
                        $i=0;
                        foreach($evaluation_list as $value){ 
                            $i++;
                        ?>
                        <tr>
                            <td><?= $i; ?></td>
                            <td><?= $this->bpas->hrsd($value->evaluation_date); ?></td>
                            <td><?= $value->current_cost; ?></td>
                            <td><?= $value->accumulated; ?></td>
                            <td><?= $value->net_value; ?></td>
                            <td>
                                <?php
                                $detail_link = anchor('admin/assets/asset_expense/'.$value->id.'', '<label class="label label-primary">' . lang('add_expense').'</label>', 'class="tip" title="' . lang('show') . '" data-toggle="modal" data-backdrop="static" data-target="#myModal"');

                                $delete_link ='<div class="text-left">
                      
                            <a href="#" data-href="'.admin_url('assets/delete_depreciation/'.$value->id.'').'" data-toggle="modal" data-target="#confirm-delete">
                                <i class="fa fa-trash-o"></i> '.lang("delete").'</a>
                                    <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4><i class="fa fa-trash-o"></i> Cancel Expense</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <h2>'.lang('r_u_sure').'</h2>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">NO</button>
                                                    <a class="btn btn-danger btn-ok">
                                                    '.lang('i_m_sure').'</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>';

                                echo $value->is_expense ? $delete_link:$detail_link;
                            
                                ?>
                                
                            </td>
                        </tr>
                        <?php }} ?>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
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
   
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
     
        $('#pdf3').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getTransfersReport/pdf/?v=1&product=')?>";
            return false;
        });
        $('#xls3').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getTransfersReport/0/xls/?v=1&product=')?>";
            return false;
        });
     
        $('.image').click(function (event) {
            var box = $(this).closest('.box');
            event.preventDefault();
            html2canvas(box, {
                onrendered: function (canvas) {
                    openImg(canvas.toDataURL());
                }
            });
            return false;
        });

        $('#confirm-delete').on('show.bs.modal', function(e) {
            $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
                            
            $('.debug-url').html('Delete URL: <strong>' + $(this).find('.btn-ok').attr('href') + '</strong>');
        });
    });
</script>

