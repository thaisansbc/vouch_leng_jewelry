<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('import_member_card_by_excel'); ?></h2>
        </div>
        <div class="box-content">
            <div class="row">
                <div class="col-lg-12">

                    <?php
                    $attrib = ['class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form'];
                    echo admin_form_open_multipart('member_cards/import_excel', $attrib)
                    ?>
                    <div class="row">
                        <div class="col-md-12">

                            <div class="well well-small">
                                <a href="<?php echo base_url(); ?>assets/excel/sample_member.xlsx"
                                class="btn btn-primary pull-right"><i
                                        class="fa fa-download"></i> <?= lang('download_sample_file') ?></a>
                                <p>
                                    <span class="text-warning"><?= lang('csv1'); ?></span><br/><?= lang('csv2'); ?> <span
                                    class="text-info">(<?= lang('card_no') . ', ' . lang('discount') . ', '.lang('expiry');?>)
                                </p>
                                <p><?= lang('images_location_tip'); ?></p>
                                <span class="text-primary"><?= lang('csv_update_tip'); ?></span>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="excel_file"><?= lang('upload_file'); ?></label>
                                    <input type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" class="form-control file" data-show-upload="false" data-show-preview="false" id="excel_file" required="required"/>
                                </div>

                                <div class="form-group">
                                    <?php echo form_submit('import', $this->lang->line('import'), 'class="btn btn-primary"'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?= form_close(); ?>
                </div>
            </div>
        </div>
    </div>