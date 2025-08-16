<style>

	ul.ui-autocomplete {
	    z-index: 1100;
	}
	
</style>
<?php
//$this->erp->print_arrays($cate_id);

if (!empty($categories)) {
    foreach ($categories as $category) {
        $vars[] = $category->id .'-'. addslashes($category->name);
    }
} else {
    $vars = array();
}
?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_promotion'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open("system_settings/edit_promotion/" . $id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <label for="name"><?php echo lang("description",'description'); ?></label>

                <div class="controls"> <?php echo form_input('description', $promotions->description, 'class="form-control" id="description" required="required"'); ?> </div>
            </div>
			<div id="attrs"></div>
			
		   <div>	
		   			<div class="form-group">
                        <?= lang('start_date', 'start_date'); ?>
                        <?php echo form_input('start_date', $promotions->start_date ? $this->bpas->hrsd($promotions->start_date) : '', 'class="form-control tip date" id="start_date" required="required"'); ?>
                    </div>
                    <div class="form-group">
                        <?= lang('end_date', 'end_date'); ?>
                        <?php echo form_input('end_date', $promotions->end_date ? $this->bpas->hrsd($promotions->end_date) : '', 'class="form-control tip date" id="end_date" required="required"'); ?>
                    </div>
		   			<div class="form-group">
	                	<?= lang('warehouse', 'warehouse'); ?>
		                <?php
		                foreach ($warehouses as $warehouse) {
		                    $wh[$warehouse->id] = $warehouse->name;
		                }
		                echo form_dropdown('warehouse',$wh, $promotions->warehouse_id, 'id="warehouse" class="form-control select" placeholder="'.lang('select') . ' ' . lang('warehouse').'" style="width:100%;" required="required" ');
		                ?>
	           		</div>
						
					<div class="form-group">
	                	<?= lang('promotion_type', 'promotion_type'); ?>
		                <?php
						if($pro_code[0]->name != NULL){
							$type = 2;
						}elseif($cate_id[0]->name != NULL){
							$type = 1;
						}
						// var_dump($pro_code);exit();
		                $ps = ['0' => lang('---- Select_one ----'), '1' => lang('by_category'),'2' => lang('by_product')];
		                echo form_dropdown('promotion_type',$ps, (isset($_POST['promotion_type']) ? $_POST['promotion_type'] : $type), 'id="promotion_type" class="form-control select" placeholder="'.lang('select') . ' ' . lang('promotion_type').'" style="width:100%;" required="required" ');
		                ?>
	            	</div>

					<div class="form-group" id="ui">
						<?= lang('enter_categories', 'enter_categories'); ?>
						<div class="input-group">
							<?php
							echo form_input('categories', '', 'class="form-control select-tags" id="categories" placeholder="' . $this->lang->line("enter_categories") . '"'); ?>
							<div class="input-group-addon" style="padding: 2px 5px;">
								<a href="#" id="addAttributes">
									<i class="fa fa-2x fa-plus-circle" id="addIcon"></i>
								</a>
							</div>
						</div>
						<div style="clear:both;"></div>
					</div>

					<div class="form-group" id="pr">
						<?= lang('enter_product', 'enter_product'); ?>
							<?php
							echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ''),  'class="form-control" id="suggest_product" placeholder="' . $this->lang->line("enter_product") . '"'); ?>
						<div style="clear:both;"></div>
					</div>
					
					<div class="table-responsive">
						<table id="attrTable" class="table table-bordered table-condensed table-striped" style="margin-bottom: 0; margin-top: 10px;">
							<thead>
							<tr class="active">
								<th><?= lang('description') ?></th>
								<th><?= lang('discount') ?></th>
								<th><i class="fa fa-times attr-remove-all"></i></th>
							</tr>
							</thead>
							<tbody>
							
							<?php
							
			
							if($pro_code[0]->name != NULL){
								for($i=0;$i<count($pro_code);$i++){
									?>								
										<tr class="attr">
											<td><input type="hidden" name="arr_pr[]" value="<?=$pro_code[$i]->code?>"><input type="hidden" name="arr_pro_id[]" value="<?=$pro_code[$i]->id?>"><span><?=$pro_code[$i]->name?></span></td>
											<td class="form-control"><input type="text" name="percent_tag[]" value="<?=$pro_code[$i]->discount?>"><span></span></td>
											<td class="text-center"><i class="fa fa-times delAttr"></i></td>
										</tr>
									<?php
								}
							}elseif ($cate_id[0]->name != NULL){
								for($i=0;$i<count($cate_id);$i++){
									?>								
										<tr class="attr">
											<td><input type="hidden" name="arr_cate[]" value="<?=$cate_id[$i]->id?>"><span><?=$cate_id[$i]->name?></span></td>
											<td class="form-control"><input type="text" name="percent_tag[]" value="<?=$cate_id[$i]->discount?>"><span></span></td>
											<td class="text-center"><i class="fa fa-times delAttr"></i></td>
										</tr>
									
									<?php
								}
							}
							?></tbody>
						</table>
					</div>
			</div>

			
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_promotion', lang('edit_promotion'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<script type="text/javascript">
	$(document).ready(function() { 
	var variants = <?=json_encode($vars);?>;

	$(".select-tags").select2({
		tags: variants,
		tokenSeparators: [","],
		multiple: true
	});


	if($('#promotion_type').val() == 2){
		$('#ui').hide();
		$('#pr').show(); 
	}else if($('#promotion_type').val() == 1){
		$('#ui').show();
		$('#pr').hide(); 
	};
	$('#promotion_type').change(function(){
		var val = $(this).val();
	
		if(val == 0 ){
			$('#ui').hide();
			$('#pr').hide(); 
		}if(val == 1 ){
			$('#ui').show();
			$('#pr').hide(); 
		}else if(val == 2){
			$('#ui').hide();
			$('#pr').show();
		}
	});

	$('#suggest_product').autocomplete({
        source: site.base_url + 'system_settings/productSuggestionByMultiUnit',
        select: function(event, ui){
			event.preventDefault();
            // $('#report_product_id').val(ui.item.id);
			$('#attrTable').show().append('<tr class="attr"><td><input type="hidden" name="arr_pr[]" value="'+ ui.item.code + '"><input type="hidden" name="arr_pro_id[]" value="'+ ui.item.id + '"><span>'+ ui.item.label + '</span></td><td class="form-control"><input type="text" name="percent_tag[]" value=""><span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>');
			$(this).val('');
			$("#suggest_product").removeClass('ui-autocomplete-loading');
			
		}, 
        minLength: 1,
        autoFocus: false,
        delay: 250,
        response: function(event, ui) {
			console.log(ui);
            if (ui.content.length == 1 && ui.content[0].id != 0){
                ui.item = ui.content[0];
                $(this).val(ui.item.label);
                $(this)
                    .data('ui-autocomplete')
                    ._trigger('select', 'autocompleteselect', ui);
                $(this).autocomplete('close');
                $(this).removeClass('ui-autocomplete-loading');
            }
        },
    });
	
	
	
	$(document).on('ifUnchecked', '#makeup_cost', function (e) {
		$(".select-tags").select2("val", "");
		$('#attr-con').slideUp();
	});
	
	$('#addAttributes').click(function (e) {
            e.preventDefault();
            var attrs_val = $('#categories').val(), attrs;
            attrs = attrs_val.split(',');
            for (var i in attrs) {
                if (attrs[i] !== '') {
                  
				  var cate = attrs[i].split('-');
				  
				  $('#attrTable').show().append('<tr class="attr"><td><input type="hidden" name="arr_cate[]" value="' + cate[0] + '"><span>' + cate[1] + '</span></td><td class="form-control"><input type="text" name="percent_tag[]" value=""><span></span></td><td class="text-center"><i class="fa fa-times delAttr"></i></td></tr>');
                }
            }
        });
	
	
	
	
		//=====================Related Strap=========================
	$(document).on('ifChecked', '#related_strap', function (e) {
		$('#strap-con').slideDown();
	});
	$(document).on('ifUnchecked', '#related_strap', function (e) {
		$(".select-strap").select2("val", "");
		$('.attr-remove-all').trigger('click');
		$('#strap-con').slideUp();
	});
	//=====================end===================================
	$(document).on('click', '.delAttr', function () {
		$(this).closest("tr").remove();
	});
	$(document).on('click', '.attr-remove-all', function () {
		$('#attrTable tbody').empty();
		$('#attrTable').hide();
	});
	});
</script>

