<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right: 15px; margin-top: 9.5px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title no-print" id="myModalLabel"><?php echo lang('qrcode'); ?></h4>
        </div>
        <div class="modal-body">
            <div class="row">

                <div id="qr_content" class="col-md-12 align-self-center text-center">
                    <div id="logo-con" class="text-center">
                         <img class="mt-3" src="<?= base_url('assets/uploads/logos/' . $Settings->logo)  ?>" height="100">
                    </div>
                    <?php
                        echo '<img src="'.$file_name.'" height="300px;" style="margin-left: auto;margin-right: auto;display: block;">';
                    ?>
                </div>
                <div class="text-center">
                    <h1><?= $room->name;?></h1>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>

