<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
$this->load->admin_model('attendances_model');
?>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script>
    // $(document).ready(function () {
    //     oTable = $('#dmpData').dataTable({
    //         "aaSorting": [[2, "desc"]],
    //         "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
    //         "iDisplayLength": <?= $Settings->rows_per_page ?>,
    //         'bProcessing': true, 'bServerSide': true,
    //        // 'sAjaxSource': '<?= admin_url('attendances/get_Rosters/'); ?>',
    //         'fnServerData': function (sSource, aoData, fnCallback) {
    //             aoData.push({
    //                 "name": "<?= $this->security->get_csrf_token_name() ?>",
    //                 "value": "<?= $this->security->get_csrf_hash() ?>"
    //             });
    //         },
    //         //"aoColumns": [{"bSortable": false, "mRender": checkbox},null, {"mRender": fldt}, {"bSortable": false}],

    //     });
    // });
</script>

<?php if ($Owner || $GP['bulk_actions']) {
        echo admin_form_open('attendances/check_in_out_actions', 'id="action-form"');
    }
?>
<div class="box">
    <div class="box-header">
		<h2 class="#428BCA"><i class="fa-fw fa fa-calendar"></i><?= lang('roster'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"> <?= lang("actions") ?> <span class="fa fa-angle-down"></span></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= admin_url('attendances/add_check_in_out') ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('add_check_in_out') ?>
                            </a>
                        </li>
						<li>
                            <a href="<?= admin_url('attendances/import_check_in_out'); ?>" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal">
                                <i class="fa fa-plus-circle"></i> <?= lang("import_check_in_out"); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        
						<li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" 
                                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                                data-html="true" data-placement="left">
                            <i class="fa fa-trash-o"></i> <?= lang('delete_check_in_outs') ?>
                             </a>
                         </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <table id="dmpData" class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <?php 
                        if($getRoster){
                            $begin      = new DateTime($getRoster[0]->from_date);
                            $end        = new DateTime($getRoster[0]->to_date);
                            $end        = $end->modify( '+1 day' ); 
                            $interval   = DateInterval::createFromDateString('1 day');
                            $period     = new DatePeriod($begin, $interval, $end);
                            $interval   = $begin->diff($end);
                            $duration   = $interval->format('%a'); // Output: +12 days

                        ?>
                        <tr>
                            <th rowspan="2" style="min-width:30px; width: 30px; text-align: center;">N </th>
                            <th rowspan="2"><?= lang("code"); ?></th>
                            <th rowspan="2"><?= lang("employee"); ?></th>
                            <th rowspan="2"><?= lang("gender"); ?></th>
                            <th rowspan="2"><?= lang("department"); ?></th>
                            <th rowspan="2"><?= lang("position"); ?></th>
                            <th colspan="<?= $duration;?>" style="text-align: center;"><?= $getRoster[0]->from_date.' - '.$getRoster[0]->to_date;?></th>
                        </tr>
                        <tr>
                            <?php 


                            foreach ($period as $dt) {
                                
                                echo '<th>'.$dt->format("l").' '.$this->bpas->hrsd($dt->format("Y-m-d")).'</th>';
                            }
                            ?>
                            
                        </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i=1;
                            foreach ($getRoster as $roster) {
                                $rows= $this->attendances_model->getEmployeeRoster($roster->employee_id,$roster->year,$roster->month);
                            ?>
                            <tr>
                                <td><?= $i;?></td>
                                <td><?= $roster->empcode;?></td>
                                <td><?= $roster->firstname.' '.$roster->lastname;?></td>
                                <td><?= $roster->gender;?></td>
                                <td><?= $roster->department;?></td>
                                <td><?= $roster->position;?></td>
                                <?php foreach ($rows as $row) {
                                    echo '<td>'.$row->roster_code.'</td>';
                                }?>
                            </tr>
                            <?php
                                $i++;
                                }
                            
                            ?>
                        </tbody>
                        <?php }?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($Owner || $GP['bulk_actions']) {?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
    </div>
    <?=form_close()?>
<?php }
?>
