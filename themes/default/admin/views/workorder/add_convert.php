<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    <?php if ($this->session->userdata('remove_cvls')) { ?>
		if (localStorage.getItem('cvref')) {
			localStorage.removeItem('cvref');
		}
		if (localStorage.getItem('cvbiller')) {
			localStorage.removeItem('cvbiller');
		}
		if (localStorage.getItem('cvquantity')) {
			localStorage.removeItem('cvquantity');
		}
		if (localStorage.getItem('cvwarehouse')) {
			localStorage.removeItem('cvwarehouse');
		}
		if (localStorage.getItem('cvdate')) {
			localStorage.removeItem('cvdate');
		}
		if (localStorage.getItem('cvnote')) {
			localStorage.removeItem('cvnote');
		}
		if (localStorage.getItem('cvbom')) {
			localStorage.removeItem('cvbom');
		}
    <?php $this->bpas->unset_data('remove_cvls'); } ?>
    $(document).ready(function () {
        <?php if ($Owner || $Admin || $GP['products-converts-date']) { ?>
			if (!localStorage.getItem('cvdate')) {
				$("#cvdate").datetimepicker({
					<?= ($Settings->date_with_time == 0 ? 'format: site.dateFormats.js_sdate, minView: 2' : 'format: site.dateFormats.js_ldate') ?>,
					fontAwesome: true,
					language: 'bpas',
					weekStart: 1,
					todayBtn: 1,
					autoclose: 1,
					todayHighlight: 1,
					startView: 2,
					forceParse: 0
				}).datetimepicker('update', new Date());
			}
			$(document).on('change', '#cvdate', function (e) {
				localStorage.setItem('cvdate', $(this).val());
			});
			if (cvdate = localStorage.getItem('cvdate')) {
				$('#cvdate').val(cvdate);
			}
        <?php } ?>
		
		
		$(document).on('change', '#cvref', function (e) {
			localStorage.setItem('cvref', $(this).val());
		});
		if (cvref = localStorage.getItem('cvref')) {
			$('#cvref').val(cvref);
		}
		$(document).on('change', '#cvwarehouse', function (e) {
            localStorage.setItem('cvwarehouse', $(this).val());
        });
        if (cvwarehouse = localStorage.getItem('cvwarehouse')) {
            $('#cvwarehouse').val(cvwarehouse);
        }
		$(document).on('change', '#cvbiller', function (e) {
            localStorage.setItem('cvbiller', $(this).val());
        });
        if (cvbiller = localStorage.getItem('cvbiller')) {
            $('#cvbiller').val(cvbiller);
        }
		$('#cvnote').redactor({
			buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
			formattingTags: ['p', 'pre', 'h3', 'h4'],
			minHeight: 100,
			changeCallback: function (e) {
				var v = this.get();
				localStorage.setItem('cvnote', v);
			}
		});
		if (cvnote = localStorage.getItem('cvnote')) {
			$('#cvnote').redactor('set', cvnote);
		}
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue">
			<i class="fa-fw fa fa-plus"></i>
			<?= lang('add_convert'); ?>
		</h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
					$attrib = array('data-toggle' => 'validator', 'role' => 'form');
					echo admin_form_open_multipart("workorder/add_convert", $attrib);
                ?>
					<?php if ($Owner || $Admin || $GP['workorder-converts-date']) { ?>
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("date", "cvdate"); ?>
								<?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : ""), 'class="form-control input-tip datetime" id="cvdate" required="required"'); ?>
							</div>
						</div>
					<?php } ?>
					<div class="col-md-4 <?= ((!$Owner && !$Admin && !$GP['reference_no']) ? 'hidden' : '') ?>">
						<div class="form-group">
							<?= lang("reference_no", "cvref"); ?>
							<?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control input-tip" id="cvref"'); ?>
						</div>
					</div>
					<?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang("biller", "cvbiller"); ?>
                                    <?php
                                    $bl[""] = "";
                                    foreach ($billers as $biller) {
                                        $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                    }
                                    echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : $Settings->default_biller), 'id="cvbiller" data-placeholder="' . lang("select") . ' ' . lang("biller") . '" required="required" class="form-control input-tip select" style="width:100%;"');
                                    ?>
                                </div>
                            </div>
                        <?php } else {
                            $biller_input = array(
                                'type' => 'hidden',
                                'name' => 'biller',
                                'id' => 'cvbiller',
                                'value' => $this->session->userdata('biller_id'),
                            );
                            echo form_input($biller_input);
                     }
					?>
						
					<?php if($Settings->project == 1){ ?>
						<?php if ($Owner || $Admin) { ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("project", "project"); ?>
									<div class="no-project">
										<?php
										$pj[''] = '';
										if(isset($projects) && $projects){
											foreach ($projects as $project) {
												$pj[$project->id] = $project->name;
											}
										}
										echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
										?>
									</div>
								</div>
							</div>
						<?php } else { ?>
							<div class="col-md-4">
								<div class="form-group">
									<?= lang("project", "project"); ?>
									<div class="no-project">
										<?php
										$pj[''] = ''; $right_project = json_decode($user->project_ids);
										if(isset($projects) && $projects){
											foreach ($projects as $project) {
												if(in_array($project->id, $right_project)){
													$pj[$project->id] = $project->name;
												}
											}
										}
										echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : $Settings->project_id), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
										?>
									</div>
								</div>
							</div>
						<?php } ?>
					<?php } ?>
				
					<div class="col-md-4">
						<div class="form-group">
							<?= lang("warehouse", "cvwarehouse"); ?>
							<?php
							$wh[''] = '';
							foreach ($warehouses as $warehouse) {
								$wh[$warehouse->id] = $warehouse->name;
							}
							echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : $Settings->default_warehouse), 'id="cvwarehouse" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("warehouse") . '" required="required" style="width:100%;" ');
							?>
						</div>
					</div>
					
					
					<div class="col-md-4">
						<div class="form-group">
							<?= lang("bom", "cvbom"); ?>
							<?php
							$bopt[''] = lang("select").' '.lang("bom");
							if($boms){
								foreach ($boms as $bom) {
									$bopt[$bom->id] = $bom->name;
								}
							}
							echo form_dropdown('bom', $bopt, (isset($_POST['bom']) ? $_POST['bom'] : ''), 'id="cvbom" class="form-control input-tip select" required="required"');
							?>
						</div>
					</div>

					<div class="col-sm-4">
						<div class="form-group">
							<label class="control-label" for="user"><?= lang("quantity"); ?></label>
							<?php echo form_input("bom_quantity", 1, " class='form-control' id='cvquantity'") ?>
						</div>
					</div>

					<div class="col-md-12">
						<div class="control-group table-group">
							<label class="table-label"><?= lang("raw_material"); ?> *</label>
							<div class="controls table-controls">
								<table id="rawTable" class="table items table-striped table-bordered table-condensed table-hover">
									<thead>
										<tr>
											<th><?= lang("product_name") . " (" . lang("product_code") . ")"; ?></th>
											<th><?= lang("quantity"); ?></th>
											<th><?= lang("unit"); ?></th>											
										</tr>
									</thead>
									<tbody></tbody>
                                    <tfoot></tfoot>
								</table>
							</div>
						</div>
					</div>

					<div class="col-md-12">
						<div class="control-group table-group">
							<label class="table-label"><?= lang("finished_good"); ?> *</label>
							<div class="controls table-controls">
								<table id="finishTable" class="table items table-striped table-bordered table-condensed table-hover">
									<thead>
										<tr>
											<th><?= lang("product_name") . " (" . lang("product_code") . ")"; ?></th>
											<th><?= lang("quantity"); ?></th>	
											<th><?= lang("unit"); ?></th>		
										</tr>
									</thead>
									<tbody></tbody>
                                    <tfoot></tfoot>
								</table>
							</div>
						</div>
					</div>
					
					<div class="col-md-12">
						<div class="form-group">
							<?= lang("note", "cvnote"); ?>
							<?php echo form_textarea('note', (isset($_POST['note']) ? $_POST['note'] : ""), 'class="form-control" id="cvnote" style="margin-top: 10px; height: 100px;"'); ?>
						</div>
					</div>
					
					<div class="col-md-12">
						<div class="fprom-group"><?php echo form_submit('add_convert', lang("submit"), 'id="add_convert" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
						<button type="button" name="reset" class="btn btn-danger" id="reset"><?= lang('reset') ?></button></div>
					</div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		var old_row_qty;
		$(document).on("focus", '#cvquantity', function () {
			old_row_qty = $(this).val();
		}).on("change", '#cvquantity', function () {
			var row = $(this).closest('tr');
			if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
				$(this).val(old_row_qty);
				bootbox.alert(lang.unexpected_value);
				return;
			}
			localStorage.setItem('cvquantity', $(this).val());
			get_bom_items();
		});
		
		
		if (cvquantity = localStorage.getItem('cvquantity')) {
			$('#cvquantity').val(cvquantity);
		}
		$(document).on('change', '#cvbom', function (e) {
            localStorage.setItem('cvbom', $(this).val());
			get_bom_items();
        });
        if (cvbom = localStorage.getItem('cvbom')) {
            $('#cvbom').val(cvbom);
			get_bom_items();
        }
		
		function get_bom_items(){
			var rawTable = "";
			var finishTable = "";
			var bom_id = $("#cvbom").val();
			var warehouse_id = $("#cvwarehouse").val();
			var quantity = $("#cvquantity").val(); 
			if(bom_id > 0){
				$.ajax({
					type: "get", 
					async: true,
					url: site.base_url + "workorder/get_bom_items/",
					data : { 
							bom_id : bom_id,
							warehouse_id : warehouse_id,
							quantity : quantity
					},
					dataType: "json",
					success: function (data) {
						if (data.raw_materials != false) {
							$.each(data.raw_materials, function () {
								rawTable += "<tr>";
									rawTable += "<td><input type='hidden' class='product_id' name='product_id[]' value='"+this.product_id+"'/>"+this.product_code+" - "+this.product_name+"</td>";
									rawTable += "<td><input type='hidden' class='unit_qty' name='unit_qty[]' value='"+this.unit_qty+"'/><input type='hidden' class='quantity' name='quantity[]' value='"+this.quantity+"'/><input type='hidden' class='qoh' value='"+this.qoh+"'/>"+formatQuantity(this.unit_qty)+"</td>";
									rawTable += "<td><input type='hidden' name='type[]' value='raw_material'/><input type='hidden' name='cost[]' value='"+this.product_cost+"'/><input type='hidden' class='unit_id' name='unit_id[]' value='"+this.unit_id+"'/>"+this.unit_name+"</td>";
								rawTable += "</tr>";
							});
							$("#rawTable tbody").html(rawTable);
							loadItems();
						}
						if (data.finish_products != false) {
							$.each(data.finish_products, function () {
								finishTable += "<tr>";
									finishTable += "<td><input type='hidden' class='product_id' name='fproduct_id[]' value='"+this.product_id+"'/>"+this.product_code+" - "+this.product_name+"</td>";
									finishTable += "<td><input type='hidden' class='unit_qty' name='funit_qty[]' value='"+this.unit_qty+"'/><input type='hidden' class='quantity' name='fquantity[]' value='"+this.quantity+"'/>"+formatQuantity(this.unit_qty)+"</td>";
									finishTable += "<td><input type='hidden' name='ftype[]' value='finished_good'/><input type='hidden' class='unit_id' name='funit_id[]' value='"+this.unit_id+"'/>"+this.unit_name+"</td>";
								finishTable += "</tr>";
							});
							$("#finishTable tbody").html(finishTable);
						}
						
					}
				});
			}else{
				$("#rawTable tbody").empty();
				$("#finishTable tbody").empty();
			}
		}
		
		function loadItems(){
			$('#add_convert, #edit_convert').attr('disabled', false);
			$('#rawTable tbody .product_id').each(function(index) {
				var row = $(this).closest('tr');
				var quantity = row.find(".quantity").val() - 0;
				var qoh = row.find(".qoh").val() - 0;
				if(quantity > qoh) { 
					row.addClass('danger');
					if(site.settings.overselling != 1) { $('#add_convert, #edit_convert').attr('disabled', true); }
				}
			});
		}
		
		
		
		
		$("#cvbiller").change(biller); biller();
		function biller(){
			var biller = $("#cvbiller").val();
			var project = 0;
			$.ajax({
				url : "<?= admin_url("products/get_project") ?>",
				type : "GET",
				dataType : "JSON",
				data : { biller : biller, project : project },
				success : function(data){
					if(data){
						$(".no-project").html(data.result);
						$("#project").select2();
					}
				}
			})
		}
	});
</script>