<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-md">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('generate_qr_code'); ?></h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <style type="text/css">
                    #qr_content img{
                        width: 150px;
                    }
                </style>
                <div id="qr_content" class="col-lg-6 col-12 align-self-center text-center">
                    <?= $this->bpas->qrcode($biller->code,$biller->qr_code,66, false); ?>

                    <?php if(isset($biller->qr_code)) {
                        echo '<img src="'.base_url() . 'assets/qr_code/' . $file_name;.'" height="300px;" style="margin-left: auto;margin-right: auto;display: block;">';
                    } else {
                        echo lang('qr_code_not_found');
                    } ?>
                </div>
                <div class="col-lg-6 align-self-center text-center">
                    <?php if(isset($biller->qr_code)) { ?>
                    <a href="<?php echo base_url('admin/billers/get_qr_code/qr_'. $biller->qr_code.'.png'); ?>" id="print_qr" target="_blank" title="<?php echo lang('print_qr_code'); ?>" class="btn btn-primary" style="margin-bottom: 20px;">
                        <?php echo lang('print_qr_code'); ?>
                    </a>
                    <?php }
                        $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'class' => 'text-center'];
                        echo admin_form_open('billers/qrcode/' . $biller->id, $attrib);
                        echo form_submit('generate_qr_code', lang('generate_qr_code'), 'class="btn btn-primary"');
                        echo form_close();
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
 
<?= $modal_js ?>

