<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_sale_target'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
		echo admin_form_open("system_settings/edit_sale_target/" . $id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
			<div class="form-group">
                <?= lang('start_date', 'start_date'); ?>
                <?php echo form_input('start_date', $sale_target->start_date ? $this->bpas->hrsd($sale_target->start_date) : '', 'class="form-control tip date" id="start_date" required="required" autocomplete=off'); ?>
            </div>
            <div class="form-group">
                <?= lang('end_date', 'end_date'); ?>
                <?php echo form_input('end_date', $sale_target->end_date ? $this->bpas->hrsd($sale_target->end_date) : '', 'class="form-control tip date" id="end_date" required="required" autocomplete=off'); ?>
            </div>
			<div class="form-group">
                <?= lang('biller', 'slbiller'); ?>
                <?php
                    $bl[''] = '';
                    foreach ($billers as $biller) {
						$bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company . '/' . $biller->name : $biller->name;
                    }
                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $sale_target->biller_id), 'id="biller" data-placeholder="' . lang('select') . ' ' . lang('biller') . '" required="required" class="form-control input-tip select" style="width:100%;"'); 
				?>
            </div>
			<div class="form-group">
                <?= lang("saleman", "saleman"); ?>
                <?php
					$sm[''] = '';
					foreach($salemans as $saleman){
						$sm[$saleman->id] = $saleman->first_name . ' ' . $saleman->last_name;
					}
					echo form_dropdown('saleman', $sm, (isset($_POST['saleman']) ? $_POST['saleman'] : $sale_target->staff_id), 'id="saleman" data-placeholder="' . lang('select') . ' ' . lang('saleman') . '" required="required" class="form-control input-tip select" style="width:100%;"'); 
                ?>
            </div>
            <div class="form-group">
                <?= lang('target', 'amount') . '*'; ?>
                <input name="amount" type="text" id="amount" value="<?= $this->bpas->formatDecimal($sale_target->amount); ?>" class="pa form-control amount"/>
            </div>
			<div class="form-group">
                <?= lang('zone', 'multi_zone'); ?>
                <?php
                if($zones){
                    foreach ($zones as $zone) {
                        $zns[$zone->p_id] = $zone->p_name && $zone->p_name != '-' ? $zone->p_name : $zone->p_name;
                        if($zone->c_id != null){
                            $child_zones_id = explode("___", $zone->c_id);
                            $child_zones_name = explode("___", $zone->c_name);
            
                            foreach ($child_zones_id as $key => $value) {
                                $zns[$value] = "&emsp;" . $child_zones_name[$key];
                            }
                        }
                    }
                }
                $mzone_id = explode(',', $sale_target->multi_zone);
                echo form_dropdown('multi_zone[]', $zns, (isset($_POST['multi_zone']) ? $_POST['multi_zone'] : $mzone_id), 'id="multi_zone" class="form-control select" data-placeholder="' . lang('select') . ' ' . lang('zone') . '" style="width:100%;" multiple="multiple" required="required"');

				// $mzone_id = explode(',', $sale_target->multi_zone);
                // foreach ($zones as $zone) {
                //     $zns[$zone->id] = $zone->zone_name && $zone->zone_name != '-' ? $zone->zone_name : $zone->zone_name;
                // }
                // echo form_dropdown('multi_zone[]', $zns, (isset($_POST['multi_zone']) ? $_POST['multi_zone'] : $mzone_id), 'id="multi_zone" class="form-control select" data-placeholder="' . lang('select') . ' ' . lang('zone') . '" style="width:100%;" multiple="multiple" required="required"');
                ?>
            </div>
			<div class="form-group">
                <?= lang("description",'description'); ?>
				<?php echo form_input('description', $sale_target->description, 'class="form-control" id="description"'); ?> 
            </div>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_sale_target', lang('edit_sale_target'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<?= $modal_js ?>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript">
	$(document).ready(function () {
		$(document).on('focus', '#amount', function () {
			$(this).select();
		});
		$(document).on('keypress', '#amount', function (event) {
			if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
				event.preventDefault();
			}
		});
		$(document).on('focusout', '#amount', function (event) {
			if($(this).val() == ''){
				$(this).val(0);
			}
			$(this).val(parseFloat($(this).val()).toFixed(2));
		});
	});
</script>