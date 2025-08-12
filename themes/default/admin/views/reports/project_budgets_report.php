<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php

$v = '';
if ($this->input->post('reference_no')) {
    $v .= '&reference_no=' . $this->input->post('reference_no');
}
if ($this->input->post('category')) {
    $v .= '&category=' . $this->input->post('category');
}
if ($this->input->post('biller')) {
    $v .= '&biller=' . $this->input->post('biller');
}
if ($this->input->post('note')) {
    $v .= '&note=' . $this->input->post('note');
}
if ($this->input->post('user')) {
    $v .= '&user=' . $this->input->post('user');
}
if ($this->input->post('start_date')) {
    $v .= '&start_date=' . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= '&end_date=' . $this->input->post('end_date');
}

?>
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
    });
</script>
<div class="box">
    <div class="box-header">
        <?php 
            $biller_title = "";
            if ($biller_id) {
                $biller_title = $biller->name;
            } elseif ($multi_biller) {
                if (count($multi_biller) > 1) {
                    $biller_title = lang('all_billers');
                } else {
                    $biller_title = $multi_biller[0]->name;
                }
            } else {
                $biller_title = lang('all_billers');
            }
        ?>
        <h2 class="blue"><i class="fa-fw fa fa-dollar"></i><?= lang('project_budgets_report') . ' (' . $biller_title . ')'; ?> <?php
            if ($this->input->post('start_date')) {
                echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
            }
            ?>
        </h2>
        <div class="box-icon">
            <ul class="btn-tasks">
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
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <?php if (($this->Owner || $this->Admin) || !$user_biller_id) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('billers') ?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('reports/projects') ?>"><i class="fa fa-building-o"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            foreach ($billers as $biller) {
                                echo '<li><a href="' . admin_url('reports/projects/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company.'/'.$biller->name . '</a></li>';
                            } ?>
                        </ul>
                    </li>
                <?php } elseif (!empty($billers)){ ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('billers') ?>"></i></a>
                        <ul class="dropdown-menu pull-right" class="tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('reports/projects') ?>"><i class="fa fa-building-o"></i> <?= lang('all_billers') ?></a></li>
                            <li class="divider"></li>
                            <?php
                            $biller_id_ = explode(',', $user_biller_id);
                            foreach ($billers as $biller) {
                                foreach ($biller_id_ as $key => $value) {
                                    if ($biller->id==$value) {
                                        echo '<li><a href="' . admin_url('reports/projects/' . $biller->id) . '"><i class="fa fa-building"></i>' . $biller->company.'/'.$biller->name . '</a></li>';
                                    }
                                }
                                
                            } ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div id="form">
                    <?php echo admin_form_open('reports/projects' . ($biller_id ? '/' . $biller_id : '')); ?>
                    <div class="row">
                        <?php if ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) { ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang('biller'); ?></label>
                                <?php
                                $bl[''] = lang('select') . ' ' . lang('biller');
                                foreach ($billers as $biller) {
                                    $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('biller') . '"');
                                ?>
                            </div>
                        </div>
                        <?php } elseif (!empty($billers) && $this->session->userdata('biller_id')) { ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="biller"><?= lang('biller'); ?></label>
                                <?php
                                $bl['']    = lang('select') . ' ' . lang('biller');
                                $biller_id = explode(',', $this->session->userdata('biller_id'));
                                foreach ($billers as $biller) {
                                    foreach ($biller_id as $key => $value) {
                                        if ($biller->id == $value) {
                                            $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;  
                                        }
                                    }   
                                }
                                echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'class="form-control" id="biller" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('biller') . '"');
                                ?>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="reference_no"><?= lang('reference_no'); ?></label>
                                <?php echo form_input('reference_no', (isset($_POST['reference_no']) ? $_POST['reference_no'] : ''), 'class="form-control tip" id="reference_no"'); ?>

                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang('created_by'); ?></label>
                                <?php
                                $us[''] = lang('select') . ' ' . lang('user');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->first_name . ' ' . $user->last_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ''), 'class="form-control" id="user" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('user') . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('note', 'note'); ?>
                                <?php echo form_input('note', (isset($_POST['note']) ? $_POST['note'] : ''), 'class="form-control" id="note"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('start_date', 'start_date'); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('end_date', 'end_date'); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div
                            class="controls"> <?php echo form_submit('submit_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?> </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>
                <div class="table-responsive">
                    <table id="EXPData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th class="col-xs-2"><?= lang('No'); ?></th>
                            <?php 
                            foreach($projects as $project){
                                echo '<th class="col-xs-2">'.$project->project_name.'</th>';
                            }
                            ?>
                            <th class="col-xs-2"><?= lang('total'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= lang('budget');?></td>
                                <?php 
                                foreach($projects as $project){
                                    echo '<td>'.$this->bpas->formatDecimal(0).'</td>';
                                }
                                echo '<td>'.$this->bpas->formatDecimal(0).'</td>';
                                ?>
                            </tr>
                            <tr>
                                <td><?= lang('influencer');?></td>
                                <?php 
                                foreach($projects as $project){
                                    echo '<td>'.$this->bpas->formatDecimal(0).'</td>';
                                }
                                echo '<td>'.$this->bpas->formatDecimal(0).'</td>';
                                ?>
                            </tr>
                            <tr>
                                <td><?= lang('expenses');?></td>
                                <?php 
                                foreach($projects as $project){
                                    echo '<td>'.$this->bpas->formatDecimal(0).'</td>';
                                }
                                echo '<td>'.$this->bpas->formatDecimal(0).'</td>';
                                ?>
                            </tr>
                            <tr>
                                <td><?= lang('profit_loss');?></td>
                                <?php 
                                foreach($projects as $project){
                                    echo '<td>'.$this->bpas->formatDecimal(0).'</td>';
                                }
                                echo '<td>'.$this->bpas->formatDecimal(0).'</td>';
                                ?>
                            </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th><th></th>
                            <th> </th><th> </th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getProjectsReport/' . ($biller_id ? $biller_id : 0) . '/pdf/?v=1' . $v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=admin_url('reports/getProjectsReport/' . ($biller_id ? $biller_id : 0) . '/0/xls/?v=1' . $v)?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    openImg(canvas.toDataURL());
                }
            });
            return false;
        });
    });
</script>
