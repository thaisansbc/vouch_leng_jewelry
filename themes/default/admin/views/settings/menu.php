<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?= admin_form_open('system_settings/category_actions', 'id="action-form"') ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-folder-open"></i><?= lang('menu');?> - <?= lang($active_module);?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?php echo admin_url('system_settings/add_menu'); ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                                <i class="fa fa-plus"></i> <?= lang('add_menu') ?>
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
                <div class="form-group">
                <?php 
                foreach ($modules as $mod) {
                    echo '<a href="'.admin_url('system_settings/menu/').$mod->name.'" class="btn btn-info" role="button">'.lang($mod->name).'</a>';
                }
                ?>
                </div>
            </div>
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <div class="table-responsive">
                    <table id="CategoryTable" class="table table-bordered table-hover table-striped reports-table">
                        <thead>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <th style="min-width:40px; width: 40px; text-align: center;">
                                    <?= lang('image'); ?>
                                </th>
                                <th><?= lang('name'); ?></th>
                                <th><?= lang('slug'); ?></th>
                                <th><?= lang('module'); ?></th>
                                <th><?= lang('permissions'); ?></th>
                                <th><?= lang('parent_category'); ?></th>
                                <th><?= lang('active_name'); ?></th>
                                <th><?= lang('status'); ?></th>
                                <th><?= lang('order'); ?></th>
                                <th><?= lang('modal'); ?></th>
                                <th style="width:100px;"><?= lang('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($menus as $row){
                            ?>
                            <tr>
                                <th style="min-width:30px; width: 30px; text-align: center;">
                                    <input class="checkbox checkth" type="checkbox" name="check"/>
                                </th>
                                <td> <i class="<?= $row->icon;?>"></i></td>
                                <td> <?= ($row->parent_id >0)? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;':''; ?><?= $row->name;?></td>
                                <td> <?= $row->slug;?></td>
                                <td> <?= $row->module;?></td>
                                <td> <?= $row->permission;?></td>
                                <td> <?= $row->parent;?></td>
                                <td> <?= $row->selected_name;?></td>
                                <td> <?= $row->status;?></td>
                                <td> <?= $row->order_number;?></td>
                                <td> <?= $row->is_modal;?></td>
                                <td> <?php
                                    echo "<a href='" . admin_url('system_settings/edit_menu/'.$row->id.'') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_menu') . "'><i class=\"fa fa-edit\"></i></a>";

                                    echo "<a href='" . admin_url('system_settings/delete_menu--/'.$row->id.'') . "'><i class=\"fa fa-trash-o\"></i></a>"; ?></td>
                            </tr>
                            <?php }?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action"/>
    <?= form_submit('submit', 'submit', 'id="action-form-submit"') ?>
</div>
<?= form_close() ?>
<script language="javascript">
    $(document).ready(function () {

        $('#delete').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

        $('#excel').click(function (e) {
            e.preventDefault();
            $('#form_action').val($(this).attr('data-action'));
            $('#action-form-submit').trigger('click');
        });

    });
</script>

