pproeri<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
            <i class="fa fa-2x">&times;</i>
        </button>
        <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
            <i class="fa fa-print"></i> <?= lang('print'); ?>
        </button>
        <h4 class="modal-title" id="myModalLabel"><?= $promos->name; ?></h4>
    </div>
        <div class="modal-body">

                <div class="col-xs-5 pull-left">
                    <div class="table-responsive">
                    <table class="table table-borderless table-striped dfTable">
                    <tr>
                     <td colspan="2"><?= lang('start_date'); ?></td>
                        <td><?= $promos->start_date; ?></td>
                    </tr>
                    </table>
                        <table class="table table-borderless table-striped dfTable">
                            <tbody>
                              
                                <tr>
                                    <td colspan="3" style="background-color:#ADD8E6;"><b><?= lang('product2buy'); ?></b></td>
                                    <!-- <td><?= $promos->start_date; ?></td> -->
                                </tr>
                            <?php $j = 1; foreach ($product2buy as $value) {?>
                                <!-- <tr>
                                    <td colspan="3" style="background-color:#ADD8E6;"></td>
                                </tr> -->
                                <!-- <tr>
                                    <td><?= lang('type'); ?></td>
                                    <td><?= lang($value->type); ?></td>
                                </tr> -->
                                <tr>
                                    <td><?= $j."." ?></td>
                                    <td><?= $value->product_name." (". $value->product_code.")";?></td>
                                    <td><?= $value->qty;?></td>
                                </tr>
                                <!-- <tr>
                                    <td><?= lang('code'); ?></td>
                                    <td><?= $value->product_code; ?></td>
                                </tr> -->
                                    <?php $j++;} ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                     <div class="col-xs-5 pull-right">
                    <div class="table-responsive">
                     <table class="table table-borderless table-striped dfTable">
                    <tr>
                     <td colspan="2"><?= lang('end_date'); ?></td>
                                    <td><?= $promos->end_date; ?></td>
                    </tr>
                    </table>
                        <table class="table table-borderless table-striped dfTable">
                            <tbody>
                            
                                <tr>
                                    <td colspan="3" style="background-color:#ADD8E6;"><b><?= lang('product2get'); ?></b></td>
                                </tr>
                            <?php $i = 1; foreach ($product2get as $value) {?>
                                <!-- <tr>
                                    <td colspan="3" style="background-color:#ADD8E6;"></td>
                                </tr> -->
                                <!-- <tr>
                                    <td><?= lang('type'); ?></td>
                                    <td><?= lang($value->type); ?></td>
                                </tr> -->
                                <tr>
                                    <td><?= $i."." ?></td>
                                    <td><?= $value->product_name." (". $value->product_code.")";?></td>
                                    <td><?= $value->qty;?></td>
                                </tr>
                                <!-- <tr>
                                    <td><?= lang('code'); ?></td>
                                    <td><?= $value->product_code; ?></td>
                                </tr> -->
                                    <?php $i++;} ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                <div class="col-xs-12">
                    <?php
                    if ($promos->description || $promos->description != '') {
                    ?>
                        <div class="well well-sm">
                            <p class="bold"><?= lang('note'); ?>:</p>
                            <div><?= $this->bpas->decode_html($promos->description); ?></div>
                        </div>
                    <?php
                    } ?>
                </div>
                </div>
<?php if (!$Supplier || !$Customer) {
                                        ?>
    <div class="buttons">
        <div class="btn-group btn-group-justified">
           
            <div class="btn-group">
                <a href="<?= admin_url('promos/pdf/' . $promos->id) ?>" class="tip btn btn-primary" title="<?= lang('pdf') ?>">
                    <i class="fa fa-download"></i>
                    <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                </a>
            </div>
            <div class="btn-group">
                <a href="<?= admin_url('promos/edit/' . $promos->id) ?>" class="tip btn btn-warning tip" title="<?= lang('edit_promos') ?>">
                    <i class="fa fa-edit"></i>
                    <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                </a>
            </div>
            <div class="btn-group">
                <a href="#" class="tip btn btn-danger bpo" title="<b><?= lang('delete_promos') ?></b>"
                    data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('promos/delete/' . $promos->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                    data-html="true" data-placement="top">
                    <i class="fa fa-trash-o"></i>
                    <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                </a>
            </div>
        </div>
    </div> 
    <script type="text/javascript">
    $(document).ready(function () {
        $('.tip').tooltip();
    });
    </script>
<?php
                                    } ?>
</div>
</div>
</div>
