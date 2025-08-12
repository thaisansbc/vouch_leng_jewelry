<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
$(document).ready(function () {
    var customer = $('#slcustomer');
    customer.change(function(e) {
        $('#slcustomer').val($(this).val());
    });
    slcustomer = $('#slcustomer').val();
    customer.val(slcustomer).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function(element, callback) {
                $.ajax({
                    type: 'get',
                    async: false,
                    url: site.base_url + 'customers/getCustomer/' + $(element).val(),
                    dataType: 'json',
                    success: function(data) {
                        callback(data[0]);
                    },
                });
            },
            ajax: {
                url: site.base_url + 'customers/suggestions',
                dataType: 'json',
                quietMillis: 15,
                data: function(term, page) {
                    return {
                        term: term,
                        limit: 10,
                    };
                },
                results: function(data, page) {
                    if (data.results != null) {
                        return { results: data.results };
                    } else {
                        return { results: [{ id: '', text: 'No Match Found' }] };
                    }
                },
            },
        });
 
  });

</script>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_Skins'); ?></h4>
    
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open("system_settings/add_skins", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
               <div class="form-group">
                    <?= lang('customer', 'slcustomer'); ?>
                    <div class="input-group">
                        <?php
                        echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ''), 'id="slcustomer" data-placeholder="' . lang('select') . ' ' . lang('customer') . '" required="required" class="form-control input-tip" style="width:100%;"');
                        ?>
                    
                        <div class="input-group-addon no-print" style="padding: 2px 7px; border-left: 0;">
                            <a href="#" id="view-customer" class="external" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                <i class="fa fa-eye" id="addIcon" style="font-size: 1.2em;"></i>
                            </a>
                        </div>
                      
                    </div>
                </div>
         </div>
            
            <div class="form-group">
            <?= lang("target_types", "target_type"); ?>
						<?php 
							$target_types = array("Amount", "Quality");
							$target_types_opts = array();
							foreach($target_types as $ckey => $name){
								$target_types_opts[$ckey] = $name;
							}
                         
						 echo form_dropdown("target_type", $target_types_opts,1, " id='target_type' class='form-control' required='required' ");
						?>
                    </div>
            <div class="form-group">
                <label class="control-label" for="amount"><?php echo $this->lang->line("Amount"); ?></label>

                <div
                    class="controls"> <?php echo form_input('amount', '', 'class="form-control "  id="amount" required="required"'); ?> </div>
            </div>
            <div class="form-group">
                <label class="control-label" for="start_date"><?php echo $this->lang->line("Start_date"); ?></label>

                <div
                    class="controls"> <?php echo form_input('start_date', '', 'class="form-control date"  id="start_date" required="required"'); ?> </div>
            </div>
            <div class="form-group">
                <label class="control-label" for="end_date"><?php echo $this->lang->line("End_date"); ?></label>

                <div
                    class="controls"> <?php echo form_input('end_date', '', 'class="form-control date" id="end_date" required="required"'); ?> </div>
            </div>
            <div class="form-group">
               
            <div class="form-group">
                <?= lang('invetory', 'products'); ?>
                <?php
                        $mbiller_id = explode(',', $pos->product);
                        foreach ($product as $products) {
                            $st[$products->id] = $products->name;
                            // && $products->name != '-' ? $products->name : $products->name;
                        }
                        //  echo form_dropdown('product[]', $st, (isset($_POST['product']) ? $_POST['product'] : $mbiller_id),
                        //  'id="product" class="form-control select" data-placeholder="' . lang('select') . ' ' . lang('products') . '" style="width:100%;" multiple="multiple"');
                        //     foreach ($product as $products) 
                        //         {
                        //             $st[$products->id] = ucfirst($products->name) && ucfirst($products->name) != '-' ? ucfirst($products->name) : ucfirst($products->name);
                        //         }
                         echo form_dropdown('products[]', $st, '', 'id="products" class="form-control select" data-placeholder="' . lang('select') . ' ' . lang('products') . '"  multiple="multiple"');
                 ?>
            </div>
            <div class="form-group">
                <?= lang('status', 'status'); ?>
                <?php $sstatus = ['1' => lang('active'), '0' => lang('inactive')]; ?>
                <?= form_dropdown('status', $sstatus, '' , 'class="form-control tip" required="required"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="commission"><?php echo $this->lang->line("commission"); ?></label>

                <div
                    class="controls"> <?php echo form_input('commission', '', 'class="form-control" id="commission" required="required"'); ?> </div>
            </div>
			
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_skins', lang('add_skins'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>

