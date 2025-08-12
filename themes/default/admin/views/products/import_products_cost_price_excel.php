<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add/update_products_cost_and_price_excel'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                    $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'stForm'];
                    echo admin_form_open_multipart('products/import_products_cost_and_price_excel');
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <label><?= lang('type'); ?> *</label>
                            <div class="form-group">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-2">
                                                <input type="radio" class="checkbox type" value="full" name="type" id="full" <?= $this->input->post('type') == 'full' ? 'checked="checked"' : ''; ?> required="required">
                                                <label for="full" class="padding05">
                                                    <?= lang('full'); ?>
                                                </label>
                                            </div>
                                            <div class="col-xs-6 col-sm-2">
                                                <input type="radio" class="checkbox type" value="partial" name="type" id="partial" <?= $this->input->post('type') == 'partial' ? 'checked="checked"' : ''; ?>>
                                                <label for="partial" class="padding05">
                                                    <?= lang('partial'); ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-12 partials" style="display:none;">
                            <div class="well well-sm">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('brands', 'brand'); ?>
                                        <?php
                                        foreach ($brands as $brand) {
                                            $wh[$brand->id] = $brand->name;
                                        }
                                        echo form_dropdown('brand[]', $wh, (isset($_POST['brand']) ? $_POST['brand'] : 0), 'id="brand" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('brand') . '" style="width:100%;" multiple');
                                        ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <?= lang('categories', 'category'); ?>
                                        <?php
                                        foreach ($categories as $category) {
                                            $wh[$category->id] = $category->name;
                                        }
                                        echo form_dropdown('category[]', $wh, (isset($_POST['category']) ? $_POST['category'] : 0), 'id="category" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('category') . '" style="width:100%;" multiple');
                                        ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="fprom-group">
                                <?= form_submit('download', lang('download_sample_file'), 'id="download" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?>
                            </div>
                        </div>
                        <?php echo form_close(); ?>
                        <?php
                            $attrib_ = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'stForm___'];
                            echo admin_form_open_multipart('products/add_update_products_cost_price');
                        ?>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('excel_file', 'excel_file') ?>
                                <input id="" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false" class="form-control file" required="required">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="from-group"><?php echo form_submit('submit', $this->lang->line('submit'), 'id="add_cost_price" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?></div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $("#brand option[value=''], #category option[value='']").remove();
        $('.type').on('ifChecked', function(e){
            var type_opt = $(this).val();
            if (type_opt == 'partial')
                $('.partials').slideDown();
            else
                $('.partials').slideUp();
            $('#stForm').bootstrapValidator('revalidateField', $(this));
        });
        $("#date").datetimepicker({format: site.dateFormats.js_ldate, fontAwesome: true, language: 'sma', weekStart: 1, todayBtn: 1, autoclose: 1, todayHighlight: 1, startView: 2, forceParse: 0, startDate: "<?= $this->bpas->hrld(date('Y-m-d H:i:s')); ?>"}); 

        $('#reset').click(function () {
            location.reload();
        });
    });
</script>