<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
	var count = 1, an = 1;
    $(document).ready(function () {
        if (localStorage.getItem('remove_kpls')) {
            if (localStorage.getItem('kpitems')) {
                localStorage.removeItem('kpitems');
            }
            if (localStorage.getItem('kpmnote')) {
                localStorage.removeItem('kpmnote');
            }
			if (localStorage.getItem('kpenote')) {
                localStorage.removeItem('kpenote');
            }
            if (localStorage.getItem('kpdate')) {
                localStorage.removeItem('kpdate');
            }
			if (localStorage.getItem('kpmonth')) {
                localStorage.removeItem('kpmonth');
            }
			if (localStorage.getItem('kpemployee')) {
                localStorage.removeItem('kpemployee');
            }
			if (localStorage.getItem('kpkpi_type')) {
                localStorage.removeItem('kpkpi_type');
            }
            localStorage.removeItem('remove_kpls');
        }
		$(document).on('change', '#kpmonth', function (e) {
            localStorage.setItem('kpmonth', $(this).val());
        });
        $(document).on('change', '#kpdate', function (e) {
            localStorage.setItem('kpdate', $(this).val());
        });
		$(document).on('change', '#kpemployee', function (e) {
            localStorage.setItem('kpemployee', $(this).val());
        });
		$(document).on('change', '#kpkpi_type', function (e) {
            localStorage.setItem('kpkpi_type', $(this).val());
        });
		
		if (kpmonth = localStorage.getItem('kpmonth')) {
            $('#kpmonth').val(kpmonth);
        }

		if (kpdate = localStorage.getItem('kpdate')) {
            $('#kpdate').val(kpdate);
        }
		if (kpemployee = localStorage.getItem('kpemployee')) {
            $('#kpemployee').val(kpemployee);
        }
		if (kpkpi_type = localStorage.getItem('kpkpi_type')) {
            $('#kpkpi_type').val(kpkpi_type);
        }



    });
</script>


<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add_kpi'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'stForm');
                echo admin_form_open_multipart("hr/add_kpi", $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12">
						<input id="result" type="hidden" name="result"/>
						<div class="col-md-4">
							<div class="form-group">
                                <div class="form-group">
                                    <?= lang("date", "kpdate"); ?>
                                    <?php echo form_input('date', (isset($_POST['date']) ? $_POST['date'] : date('d/m/Y')), 'class="form-control input-tip date" id="kpdate" required="required"'); ?>
                                </div>
                            </div>	
						</div>
						
						
						<div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("month", "kpmonth"); ?>
                                <?php echo form_input('month', (isset($_POST['kpi_month']) ? $_POST['kpi_month'] : date("m/Y")), 'class="form-control month" id="kpmonth"  required="required"'); ?>
                            </div>
                        </div>
						
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("employee", "kpemployee"); ?>
								<?php
								$emp[""] = "";
								if($employees){
									foreach ($employees as $employee) {
										$emp[$employee->id] = $employee->empcode.' - '.$employee->lastname.' '.$employee->firstname;
									}
								}
								echo form_dropdown('employee', $emp, (isset($_POST['employee']) ? $_POST['employee'] : ''), 'id="kpemployee" data-placeholder="' . lang("select") . ' ' . lang("employee") . '" required="required" class="form-control input-tip select" style="width:100%;"');
								?>
							</div>
						</div>
						
						<div class="col-md-4">
							<div class="form-group">
								<?= lang("kpi_type", "kpkpi_type"); ?>
								<?php
								$kpi["0"] = lang("select") . ' ' . lang("kpi_type");
								if($kpi_types){
									foreach ($kpi_types as $kpi_type) {
										$kpi[$kpi_type->id] = $kpi_type->name;
									}
								}
								echo form_dropdown('kpi_type', $kpi, (isset($_POST['kpi_type']) ? $_POST['kpi_type'] : ''), 'id="kpkpi_type" data-placeholder="' . lang("select") . ' ' . lang("kpi_type") . '" required="required" class="form-control input-tip select" style="width:100%;"');
								?>
							</div>
						</div>
						<div class="col-md-4">
                            <div class="form-group">
                                <?= lang("document", "document") ?>
                                <input id="document" type="file" data-browse-label="<?= lang('browse'); ?>" name="document" data-show-upload="false"
                                       data-show-preview="false" class="form-control file">
                            </div>
                        </div>
						<div class="col-md-12" id="sticker"></div>
                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang("questions"); ?> *</label>

                                <div class="controls table-controls">
                                    <table id="kpTable" class="table items table-striped table-bordered table-condensed table-hover">
                                        <thead>
                                        <tr>
											<th><?= lang("question")  ?></th>  
                                            <th><?= lang("question_kh")  ?></th>  
											<th><?= lang("comment")  ?></th>	
											<th><?= lang("rate"); ?></th>
                                            <th style="max-width: 30px !important; text-align: center;">
                                                <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="clearfix"></div>
							<div class="col-md-12">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("manager_note", "kpmnote"); ?>
                                        <?php echo form_textarea('manager_note', (isset($_POST['manager_note']) ? $_POST['manager_note'] : ""), 'class="form-control" id="kpmnote" style="margin-top: 10px; height: 100px;"'); ?>

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <?= lang("employee_note", "kpenote"); ?>
                                        <?php echo form_textarea('employee_note', (isset($_POST['employee_note']) ? $_POST['employee_note'] : ""), 'class="form-control" id="kpenote" style="margin-top: 10px; height: 100px;"'); ?>																				
									</div>
                                </div>
                            </div>
                        <div class="col-md-12">
                            <div
                                class="fprom-group"><?php echo form_submit('add_kpi', lang("submit"), 'id="add_cost_adjustment" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                        </div>
                    </div>
                </div>
				<div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
					<table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
						<tr class="warning">
							<td><?= lang('question') ?> : <span class="totals_val pull" id="t_question">0</span></td>
							<td><?= lang('rate') ?> : <span class="totals_val pull" id="t_rate">0.00</span></td>
							<td><?= lang('result') ?> : <span class="totals_val pull" id="t_result">0.00%</span></td>
						</tr>
					</table>
				</div>
                <?php echo form_close(); ?>

            </div>

        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
		ItemnTotals();
		
		function getQuestions(kpi_type){
			$.ajax({
				url : site.base_url + "hr/get_kpi_questions",
				dataType : "JSON",
				type : "GET",
				data : { kpi_type : kpi_type },
				success : function(data){
					localStorage.setItem('kpitems', JSON.stringify(data));
					loadItems();
				}
		   });
		}
		
		$('#kpkpi_type').on('change',function(){
			var kpi_type = $(this).val();
			if(kpi_type > 0){
				getQuestions(kpi_type);
			}else{
				localStorage.setItem('kpitems', JSON.stringify(false));
				loadItems();
			}
		});
		
		$('#kpemployee').on('change',function(){
			var employee_id = $(this).val();
			$.ajax({
				url : site.base_url + "hr/get_kpi",
				dataType : "JSON",
				type : "GET",
				data : { employee_id : employee_id },
				success : function(kpi_type){
					$("#kpkpi_type").val(kpi_type);
					$("#kpkpi_type").select2();
					localStorage.setItem('kpkpi_type', kpi_type);
					if(kpi_type > 0){
						getQuestions(kpi_type);
					}else{
						localStorage.setItem('kpitems', JSON.stringify(false));
						loadItems();
					}
				}
		   });

		});
    });
</script>













