<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('edit_promo'); ?></h2>
    </div>
    <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
    echo admin_form_open('promos/edit/' . $promo->id, $attrib); ?>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('enter_info'); ?></p>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?= lang('name', 'name'); ?>
                            <?php echo form_input('name', $promo->name, 'class="form-control tip" id="name" data-bv-notempty="true"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang('start_date', 'start_date'); ?>
                            <?php echo form_input('start_date', $promo->start_date ? $this->bpas->fldc($promo->start_date) : '', 'class="form-control tip datetime" id="start_date"'); ?>
                        </div>
                        <div class="form-group">
                            <?= lang('end_date', 'end_date'); ?>
                            <?php echo form_input('end_date', $promo->end_date ? $this->bpas->fldc($promo->end_date) : '', 'class="form-control tip datetime" id="end_date"'); ?>
                        </div>
                         <div class="form-group">
                        <?= lang('product2buy', 'suggest_product'); ?>
                        <table width="100%" id="dynamic_field" border="0"> 
                        <?php
                            $i = 0;
                            foreach ($bproducts as $value) {
                                $i++; ?>
                                <tr id="row<?= $i; ?>" class="dynamic-added">
                                    <td style="width:80%">
                                        <div class="form-group" >
                                                <?php
                                                        foreach ($items as $stocktypes) {
                                                        $st[$stocktypes->id] = $stocktypes->name;
                                                    }
                                                        echo form_dropdown('product2buy[]', $st, $value->product_id, ' class="form-control select" id="sproduct" data-bv-notempty="true"');
                                                    ?>
                                            <input name="id[]" type="hidden" id="id-.'<?= $value->id ?>'." value="<?= $value->id ?>" class="pa form-control kb-pad id" />
                                        </div>
                                    </td>
                                    <td style="width:20%">
                                        <?php echo form_input('qtytosale[]', $value->qty, 'class="form-control tip" id="qtytosale" style="margin:0 6px; type="number" data-bv-notempty="true" placeholder="Quantity"'); ?>
                                    </td>
                                    <td>
                                        <button type="button" name="remove" id="<?= $i ?>" class="btn btn-danger btn_removes">
                                                <li class="fa fa-remove"></li>
                                            </button>
                                    </td>
                                </tr>
                        <?php } ?>
                        </table>
                            <div id="multi-sproduct"></div>
                        <button type="button" class="btn btn-primary col-md-12" id="addb" disabled><i class="fa fa-plus"></i> <?php lang('add_more_sproductid');?></button>
                        <div class="form-group">
                        <?= lang('product2get', 'suggest_product2'); ?>
                        <table width="100%" id="dynamic_field2" border="0"> 

                        <?php
                            $i = 0;
                            foreach ($gproducts as $value) {
                                $i--; ?>
                                <tr id="row<?= $i; ?>" class="dynamic-added">
                                    <td style="width:80%">
                                        <div class="form-group" >
                                                <?php
                                                        foreach ($items as $stocktypes) {
                                                        $st[$stocktypes->id] = $stocktypes->name;
                                                    }
                                                        echo form_dropdown('product2get[]', $st, $value->product_id, ' class="form-control select" id="product2get" data-bv-notempty="true"');
                                                    ?>
                                            <input name="id[]" type="hidden" id="id-.'<?= $value->id ?>'." value="<?= $value->id ?>" class="pa form-control kb-pad id" />
                                        </div>
                                    </td>
                                    <td style="width:20%">
                                        <?php echo form_input('qtytoget[]', $value->qty, 'class="form-control tip" id="qtytoget" style="margin:0 6px; type="number" data-bv-notempty="true" placeholder="Quantity"'); ?>
                                    </td>
                                    <td>
                                        <button type="button" name="remove" id="<?= $i ?>" class="btn btn-danger btn_removes">
                                                <li class="fa fa-remove"></li>
                                            </button>
                                    </td>
                                </tr>
                        <?php } ?>
                        </table>
                        <div id="multi-product2get"></div>
                        <button type="button" class="btn btn-primary col-md-12 addButton" id="addg"><i class="fa fa-plus"></i> <?php lang('add_more_product2getid')
                                                                                                                                ?></button>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="form-group">
                            <?= lang('description', 'description'); ?>
                            <?php echo form_textarea('description', $promo->description, 'class="form-control skip" id="description" style="height:100px;"'); ?>
                        </div>
                        <?php echo form_submit('edit_promo', lang('edit_promo'), 'class="btn btn-primary"'); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
         <!-- <table width="100%" id="dynamic_field" border="0"> 
         </table> -->
    </div>
    
   <!-- <script>
    $(document).on('click', '.addButton2', function() {
        var pa = 2;
                if (pa <= 5) {
                    $('#sproduct').select2('destroy');
                    var phtml = $('#sproductid').html(),
                        update_html = phtml.replace(/_1/g, '_' + pa);
                    pi = 'amount_' + pa;
                    $('#multi-sproduct').append(update_html);
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
    </script>-->
    <script>
    $(document).on('click', '.addButton', function() {
        var pa = 2;

                if (pa <= 5) {
                    $('#product2get').select2('destroy');
                    var phtml = $('#product2getid').html(),
                        update_html = phtml.replace(/_1/g, '_' + pa);
                    pi = 'amount_' + pa;
                    $('#multi-product2get').append(update_html);
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
            //  var button_id = $(this).attr("atrr");
            // if (button_id != 1) {
            //     // if (confirm("Are You Sure?")) {
            //     $('.grow' + button_id + '').remove();
            //     //     return true;
            //     // } else {
            //     //     return false;
            //     // }
            //     pa--;
            // } else {
            //     alert("The row can't removed!");
            //     return false;
            // }
            
    </script> 
    <script type="text/javascript">
    $(document).ready(function() {
        var i = 1;
        <?php
         $st = array('0' => '-- Select Product --');
        //  $st[''] = [];
            foreach ($items as $stocktypes) {
                $st[$stocktypes->id] = $stocktypes->name;
            }
        $dropdown = form_dropdown('product2buy[]', $st, '', 'id="" class="ba form-control kb-pad" id="product2buy"data-bv-notempty="true"');
         $st2 = array('0' => '-- Select Product --');
        //  $st[''] = [];
            foreach ($items as $stocktypes) {
                $st[$stocktypes->id] = $stocktypes->name;
            }
        $dropdown2 = form_dropdown('product2get[]', $st, '', 'id="" class="ba form-control kb-pad" id="product2get" data-bv-notempty="true"');
        ?>
        var complex = <?php echo json_encode($dropdown); ?>;
        var complex2 = <?php echo json_encode($dropdown2); ?>;
        $('#addb').click(function() {
             $('#dynamic_field').append('<tr id="row2' + i + '" class="dynamic-added"><td> <div class="form-group" style="margin:0px 6px 0px 0px;">' + complex + '<input name="id[]" type="hidden" id="id-.' + i + '." value="" class="pa form-control kb-pad id" /></div></td><td><div class="form-group" ><input name="qtytosale[]" type="number" id="qtytosale" value="" class="pa form-control kb-pad qtytosale" placeholder="Quantity" required="required" /></div></td><td><div class="form-group" "><button type="button" name="remove" id="' + i + '" class="btn btn-danger btn_remove"><li class="fa fa-remove"></li></button></div></td></tr>');
             i++;
        });
        $('#addg').click(function() {
             $('#dynamic_field2').append('<tr id="row2' + -i + '" class="dynamic-added"><td> <div class="form-group" style="margin:0px 6px 0px 0px;">' + complex2 + '<input name="id[]" type="hidden" id="id-.' + -i + '." value="" class="pa form-control kb-pad id" /></div></td><td><div class="form-group" ><input name="qtytoget[]" type="number" id="qtytoget" value="" class="pa form-control kb-pad qtytoget" placeholder="Quantity" required="required" /></div></td><td><div class="form-group" "><button type="button" name="remove" id="' + -i + '" class="btn btn-danger btn_remove"><li class="fa fa-remove"></li></button></div></td></tr>');
             i++;
        });
        $(document).on('click', '.btn_remove', function() {
            var button_id = $(this).attr("id");
            $('#row2' + button_id + '').remove();
        });
        $(document).on('click', '.btn_removes', function() {
            var button_id = $(this).attr("id");
            if (button_id != 1 && button_id != -1) {
                $('#row' + button_id + '').remove();
            } else {
                alert("The row can't removed!");
                return false;
            }
        });
    });
</script>