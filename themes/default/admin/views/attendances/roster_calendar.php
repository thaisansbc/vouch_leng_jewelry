<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
        
        $('#ExpDaily').dataTable({
            "aaSorting": [[0, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?=lang('all')?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            "oTableTools": {
                "sSwfPath": "assets/media/swf/copy_csv_xls_pdf.swf",
                "aButtons": ["csv", {"sExtends": "pdf", "sPdfOrientation": "landscape", "sPdfMessage": ""}, "print"]
            }
        });
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('roster'); ?> <?php
            if ($this->input->post('month')) {
                echo "Date " . $this->input->post('month') . ", " . $this->input->post('year');
            }
            ?>
        </h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                
                <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                    <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                </a>
                <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                    <li>
                        <a href="<?= admin_url('sales/add') ?>">
                            <i class="fa fa-plus-circle"></i> <?= lang('add_roster') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= admin_url('sales/add') ?>">
                            <i class="fa-regular fa fa-file-text"></i> <?= lang('import_roster') ?>
                        </a>
                    </li>
                    <li>
                        <a href="#" id="excel" data-action="export_excel">
                            <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                        </a>
                    </li>
                </ul>
            </li>
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" onclick="window.print();" class="tip" title="<?= lang('print') ?>">
                        <i class="icon fa fa-print"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
            <div class="row">
                <div class="col-md-6">
                        <img width="150" height="80" src="<?= base_url('assets/uploads/logos/'.$this->Settings->logo); ?>" alt="<?= $this->Settings->site_name ?> " style="margin-bottom:0px;" id="logo"/>
                    </div>
                    <div class="col-md-6">
                        <div class="well">
                            <div class="row">
                                <?php 
                                foreach ($leave_types as $leave_type) {
                                    echo '<div class="col-lg-6" style="font-size:12px;">'.$leave_type->code.' : '.$leave_type->name.'</div>';
                                }?>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="form">
                    <?php echo admin_form_open("attendances/roster_calendar"); ?>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("biller"); ?></label>
                                <?php
                                $bl[""] = lang('select').' '.lang('biller');
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->name != '-' ? $biller->name : $biller->company;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ""), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
                        <?php if($Settings->project == 1){ ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <?= lang("project", "project"); ?>
                                    <div class="no-project">
                                        <?php
                                        $pj[''] = '';
                                        if (isset($projects) && $projects != false) {
                                            foreach ($projects as $project) {
                                                $pj[$project->id] = $project->name;
                                            }
                                        }
                                        echo form_dropdown('project', $pj, (isset($_POST['project']) ? $_POST['project'] : isset($Settings->project_id)? $Settings->project_id: ''), 'id="project" class="form-control input-tip select" data-placeholder="' . lang("select") . ' ' . lang("project") . '" style="width:100%;" ');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        
                        <div class="col-sm-3 hide">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                <?php
                                $us[""] = lang('select').' '.lang('user');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->last_name . " " . $user->first_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("year", "year"); ?>
                                <?php echo form_input('year', (isset($_POST['year']) ? $_POST['year'] : date("Y")), 'class="form-control year" id="year"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <?= lang("month", "month"); ?>
                                <select name="month" class="form-control">
                                    <?php 
                                        for ($m=1; $m<=12; $m++) {
                                            if(isset($_POST['month']) && $_POST['month'] == $m){
                                                echo '<option value='.$m.' selected>'.$month = date('F', mktime(0,0,0,$m, 1, date('Y'))).'</option>';
                                            }else if(!isset($_POST['month']) && $m == date("m")){
                                                echo '<option value='.$m.' selected>'.$month = date('F', mktime(0,0,0,$m, 1, date('Y'))).'</option>';
                                            }else{
                                                echo '<option value='.$m.'>'.$month = date('F', mktime(0,0,0,$m, 1, date('Y'))).'</option>';
                                            }
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="user">&nbsp;</label>
                                <div class="controls"> <?php echo form_submit('submit', $this->lang->line("search_availble"), 'class="btn btn-primary"'); ?> </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php echo form_close(); ?>
                </div>
                <div class="clearfix"></div>
                <div class="table-responsive">
                    <table id="ExpDaily" style="margin-bottom:3px;" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped" style="white-space:nowrap;">
                        <thead>
                            <tr class="active">
                                <th rowspan="2"><?= lang("no"); ?></th>
                                <th rowspan="2"><?= lang("ID"); ?></th>
                                <th rowspan="2" width="150"><?= lang("full_name"); ?></th> 
                                <?php 
                                    $post = $this->input->post() ? $this->input->post() : $this->input->get();
                                    $year = isset($post['year']) ? $post['year'] : date("Y");
                                    $month = isset($post['month']) ? $post['month'] : date("m");

                                    if($this->Settings->roster_from_day > 1){
                                        $month      = $month-1;
                                        $start_day  = $this->Settings->roster_from_day;

                                        $begin_day  = date("Y-m-d", strtotime($year."-".$month.'-'.($start_day)));
                                        $end_day    = date("Y-m-d", strtotime("+1 month", strtotime($year."-".$month.'-'.($start_day-1))));
                                    }else{
                                        $month      = $month;
                                        $start_day  = 1;
                                        $last_days  = cal_days_in_month(CAL_GREGORIAN,$month,$year);
                                        $begin_day  = date("Y-m-d", strtotime($year."-".$month.'-'.$start_day));
                                        $end_day    = date("Y-m-d", strtotime($year."-".$month.'-'.($last_days)));
                 
                                    }
                                    $begin = new DateTime($begin_day);
                                    $end   = new DateTime($end_day);
                                    $end = $end->modify( '+1 day' ); 
                                    $interval = new DateInterval('P1D');
                                    $period = new DatePeriod($begin, $interval, $end);
                                    if(isset($begin) && $end){
                                        for($dt = $begin; $dt < $end; $dt->modify('+1 day')){
                                             echo '<th>'.$dt->format("D").'</th>';
                                        }
                                    }
                                ?>
                            </tr>
                            <tr class="active">
                                <?php 
                                if(isset($begin) && $end){
                                    foreach ($period as $dt) {
                                         echo '<th>'.$dt->format("d").'</th>';
                                    }
                                }
                                ?>
                            </tr>
                            
                        </thead>

                        <tbody>
                            <?php
                            $tbody = "";
                            if(isset($employees) && $employees){
                                $i=0;
                                foreach($employees as $employee){
                                    $total_category = 0;
                                    $i++;
                                    $tbody .="<tr>
                                                <td>".$i."</td>
                                                <td>".$employee->empcode."</td>
                                                <td>".$employee->firstname.' '.$employee->lastname."</td>";
                                    if(isset($begin) && $end){
                                        foreach ($period as $dt) {
                                            $days = $dt->format("Y-m-d");
                                            $available = $this->site->getEmpRosterWorkingDay($employee->id,$days);
                                            if($available){
                                                $tbody .="<td class='text-right'>
                                                    ".$available->time_in_one.' '.$available->time_out_two."
                                                </td>"; 
                                            }else{
                                                $take_leave = $this->site->getEmpTakeleaveByEmpID_Date($employee->id,$days);
                                                $dayoff = $this->site->getEmpDayoffByEmpID_Date($employee->id,$days);
                                                if($take_leave != false){
                                                    $tbody .="<td class='text-right'>".$take_leave->code."</td>";
                                                } elseif($dayoff != false){
                                                    $tbody .="<td class='text-right' style='background-color:red'>DO</td>";
                                                }else{
                                                    $tbody .="<td class='text-right'></td>";
                                                }

                                               
                                            }

                                        }
                                    }
                                    $tbody .="</tr>";
                                }
                            }
                            echo $tbody;
                            ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        var supplier_id = "<?= isset($_POST['supplier'])?$_POST['supplier']:0 ?>";
        if (supplier_id > 0) {
          $('#supplier_id').val(supplier_id).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
              $.ajax({
                type: "get", async: false,
                url: site.base_url+"suppliers/getSupplier/" + $(element).val(),
                dataType: "json",
                success: function (data) {
                  callback(data[0]);
                }
              });
            },
            ajax: {
              url: site.base_url + "suppliers/suggestions",
              dataType: 'json',
              deietMillis: 15,
              data: function (term, page) {
                return {
                  term: term,
                  limit: 10
                };
              },
              results: function (data, page) {
                if (data.results != null) {
                  return {results: data.results};
                } else {
                  return {results: [{id: '', text: 'No Match Found'}]};
                }
              }
            }
          });
        }else{
          $('#supplier_id').select2({
            minimumInputLength: 1,
            ajax: {
              url: site.base_url + "suppliers/suggestions",
              dataType: 'json',
              quietMillis: 15,
              data: function (term, page) {
                return {
                  term: term,
                  limit: 10
                };
              },
              results: function (data, page) {
                if (data.results != null) {
                  return {results: data.results};
                } else {
                  return {results: [{id: '', text: 'No Match Found'}]};
                }
              }
            }
          });
        }
        
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/daily_expenses_export/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/daily_expenses_export/0/xls/?v=1'.$v)?>";
            return false;
        });
        $("#biller").change(biller); biller();
        function biller(){
            var biller = $("#biller").val();
            var project = "<?= (isset($_POST['project']) ? trim($_POST['project']) : ''); ?>";
            $.ajax({
                url : "<?= site_url("reports/get_project") ?>",
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



