<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_promo'); ?></h2>
    </div>
    <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
    echo admin_form_open('promos/add', $attrib); ?>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('enter_info'); ?></p>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?= lang('name', 'name'); ?>
                            <?php echo form_input('name', '', 'class="form-control tip" id="name" data-bv-notempty="true"'); ?>
                        </div>
                          <div class="form-group">
                            <?= lang('start_date', 'start_date'); ?>
                            <?php echo form_input('start_date', '', 'class="form-control tip datetime" id="start_date"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang('end_date', 'end_date'); ?>
                            <?php echo form_input('end_date', '', 'class="form-control tip datetime" id="end_date"'); ?>
                        </div>
                        <!-- <div class="form-group">
                            <?= lang('product2buy', 'suggest_product'); ?>
                            <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ''), 'class="form-control" id="suggest_product" data-bv-notempty="true"'); ?>
                            <input type="hidden" name="product2buy" value="<?= isset($_POST['product2buy']) ? $_POST['product2buy'] : '' ?>" id="report_product_id"/>
                        </div> -->
                        <div class="form-group">
                        <?= lang('product2buy', 'suggest_product'); ?>
                          <div id="sproductid">
                            <table class="table">
                                <tbody>
                                        <tr>
                                        <td style="width:75%">
                                            <?php
                                            $st[''] = [];
                                                foreach ($items as $stocktypes) {
                                                $st[$stocktypes->id] = $stocktypes->name;
                                            }
                                                echo form_dropdown('product2buy[]', $st, (isset($_POST['sgproduct']) ? $_POST['sgproduct'] : ''), ' class="form-control select" id="sproduct" data-bv-notempty="true"');
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo form_input('qtytosale[]', '', 'class="form-control tip" id="qty" type="number" data-bv-notempty="true" placeholder="Quantity"'); ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                            </div>
                            <div id="multi-sproduct"></div>
                        <button type="button" class="btn btn-primary col-md-12 addButton2" disabled><i class="fa fa-plus"></i> <?php lang('add_more_sproductid');?></button>
                         <div class="form-group">
                        <?= lang('product2get', 'suggest_product2'); ?>
                          <div id="product2getid">
                            <table class="table">
                                <tbody>
                                        <tr>
                                        <td style="width:75%">
                                            <?php
                                            $st[''] = [];
                                                foreach ($items as $stocktypes) {
                                                $st[$stocktypes->id] = $stocktypes->name;
                                            }
                                                echo form_dropdown('product2get[]', $st, (isset($_POST['sgproduct']) ? $_POST['sgproduct'] : ''), ' class="form-control select" id="product2get" data-bv-notempty="true"');
                                            ?>
                                        </td>
                                        <td>
                                                <?php echo form_input('qtytoget[]', '', 'class="form-control tip" id="qty" type="number" data-bv-notempty="true" placeholder="Quantity"'); ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                            </div>
                        <div id="multi-product2get"></div>
                        <button type="button" class="btn btn-primary col-md-12 addButton"><i class="fa fa-plus"></i> <?php lang('add_more_product2getid')
                                                                                                                                ?></button>
                        <!-- <div class="form-group">
                                    <?= lang('product2get', 'suggest_product2'); ?>
                                    <?php
                                    $st[''] = [];
                                         foreach ($items as $stocktypes) {
                                        $st[$stocktypes->id] = $stocktypes->name;
                                    }
                                        echo form_dropdown('product2get[]', $st, (isset($_POST['sgproduct']) ? $_POST['sgproduct'] : ''), 'id="suggest_product2" class="form-control select"  multiple="multiple" data-bv-notempty="true"');
                                    ?>
                                </div> -->
                        <!-- <div class="form-group">
                            <?= lang('product2get', 'suggest_product2'); ?>
                            <?php echo form_input('sgproduct', (isset($_POST['sgproduct']) ? $_POST['sgproduct'] : ''), 'class="form-control" id="suggest_product2" data-bv-notempty="true"'); ?>
                            <input type="hidden" name="product2get" value="<?= isset($_POST['product2get']) ? $_POST['product2get'] : '' ?>" id="report_product_id2"/>
                        </div> -->
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <?= lang('description', 'description'); ?>
                            <?php echo form_textarea('description', '', 'class="form-control skip" id="description" style="height:100px;"'); ?>
                        </div>
                        <?php echo form_submit('add_promo', lang('add_promo'), 'class="btn btn-primary"'); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
        <div class="hide">
  
        </div>
    </div>
    <script>
    $(document).on('click', '.addButton2', function() {
        var pa = 2;

                if (pa <= 5) {
                    console.log(pa);
                    $('#paid_by_1, #sproduct').select2('destroy');
                    var phtml = $('#sproductid').html(),
                        update_html = phtml.replace(/_1/g, '_' + pa);
                        console.log(phtml);
                    pi = 'amount_' + pa;
                    $('#multi-sproduct').append('<button type="button" class="close close-payment"><i class="fa fa-2x">&times;</i></button>' + update_html);
                    pa++;
                } else {
                    bootbox.alert('<?= lang('max_reached') ?>');
                    return false;
                }
                // $('#paymentModal').css('overflow-y', 'scroll');
            });
             $(document).on('click', '.close-payment', function() {
                $(this).next().remove();
                $(this).remove();
                pa--;
            });
    </script>
    <script>
    $(document).on('click', '.addButton', function() {
        var pa = 2;

                if (pa <= 5) {
                    console.log(pa);
                    $('#paid_by_1, #product2get').select2('destroy');
                    var phtml = $('#product2getid').html(),
                        update_html = phtml.replace(/_1/g, '_' + pa);
                        console.log(phtml);
                    pi = 'amount_' + pa;
                    $('#multi-product2get').append('<button type="button" class="close close-payment"><i class="fa fa-2x">&times;</i></button>' + update_html);
                    pa++;
                } else {
                    bootbox.alert('<?= lang('max_reached') ?>');
                    return false;
                }
                // $('#paymentModal').css('overflow-y', 'scroll');
            });
             $(document).on('click', '.close-payment', function() {
                $(this).next().remove();
                $(this).remove();
                pa--;
            });
           
    </script>