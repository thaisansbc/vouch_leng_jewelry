<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('create_user'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?php echo lang('create_user'); ?></p>
                <?php $attrib = ['class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open('auth/create_user', $attrib);
                ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-5">
                            <div class="form-group">
                                <?php echo lang('first_name', 'first_name'); ?>
                                <div class="controls">
                                    <?php echo form_input('first_name', (isset($_POST['first_name']) ? $_POST['first_name'] : ''), 'class="form-control" id="first_name" required="required" pattern=".{3,10}"'); ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('last_name', 'last_name'); ?>
                                <div class="controls">
                                    <?php echo form_input('last_name', (isset($_POST['last_name']) ? $_POST['last_name'] : ''), 'class="form-control" id="last_name" required="required"'); ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?= lang('gender', 'gender'); ?>
                                <?php
                                $ge[''] = ['male' => lang('male'), 'female' => lang('female')];
                                echo form_dropdown('gender', $ge, (isset($_POST['gender']) ? $_POST['gender'] : ''), 'class="tip form-control" id="gender" data-placeholder="' . lang('select') . ' ' . lang('gender') . '" required="required"');
                                ?>
                            </div>
                            <div class="form-group">
                                <?php echo lang('company', 'company'); ?>
                                <div class="controls">
                                    <?php echo form_input('company',  (isset($_POST['company']) ? $_POST['company'] : ''), ' class="form-control" id="company" '); ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('phone', 'phone'); ?>
                                <div class="controls">
                                    <?php echo form_input('phone',  (isset($_POST['phone']) ? $_POST['phone'] : ''), 'class="form-control" id="phone"'); ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('email', 'email'); ?>
                                <div class="controls">
                                    <?= form_input('email',  (isset($_POST['email']) ? $_POST['email'] : ''), 'class="form-control" id="email" '); ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('username', 'username'); ?>
                                <div class="controls">
                                    <input type="text" id="username" value="<?= (isset($_POST['username']) ? $_POST['username'] : '');?>" name="username" class="form-control" pattern=".{4,20}" />
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('password', 'password'); ?>
                                <div class="controls">
                                    <?php echo form_password('password', '', 'class="form-control tip" id="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" data-bv-regexp-message="' . lang('pasword_hint') . '"'); ?>
                                    <span class="help-block"><?= lang('pasword_hint') ?></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo lang('confirm_password', 'confirm_password'); ?>
                                <div class="controls">
                                    <?php echo form_password('confirm_password', '', 'class="form-control" id="confirm_password" data-bv-identical="true" data-bv-identical-field="password" data-bv-identical-message="' . lang('pw_not_same') . '"'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 col-md-offset-1">
                            <div class="form-group">
                                <?= lang('status', 'status'); ?>
                                <?php
                                $opt = [1 => lang('active'), 0 => lang('inactive')];
                                echo form_dropdown('status', $opt, (isset($_POST['status']) ? $_POST['status'] : ''), 'id="status" required="required" class="form-control select" style="width:100%;"');
                                ?>
                            </div>
                            <div class="form-group">
                                <?= lang('group', 'group'); ?>
                                <?php
                                foreach ($groups as $group) {
                                    if ($Store) {
                                        if ($group['name'] != 'developer' && $group['name'] != 'customer' && $group['name'] != 'supplier' && $group['name'] != 'admin' && $group['name'] != 'owner') {
                                            $gp[$group['id']] = lang($group['name']);
                                        }
                                    } else {
                                        if ($group['name'] != 'developer' && $group['name'] != 'customer' && $group['name'] != 'supplier') {
                                            $gp[$group['id']] = lang($group['name']);
                                        }
                                    }
                                }
                                echo form_dropdown('group', $gp, (isset($_POST['group']) ? $_POST['group'] : ''), 'id="group" required="required" class="form-control select" style="width:100%;"');
                                ?>
                            </div>
                            <div class="form-group hide">
                                <?php echo lang('commission' . " (%)", 'commission'); ?>
                                <div class="controls">
                                    <?php echo form_input('commission', (isset($_POST['commission']) ? $_POST['commission'] : 0), 'class="form-control" id="commission"'); ?>
                                </div>
                            </div>
                            <div class="form-group hide">
                                <?php echo lang('choose_options', 'choose_options'); ?>
                                <?php
                                    $copt[''] = ['' => lang('select'), '1' => lang('Salary') . ' + ' . lang('Commissions'), '2' => 'Maximum Between (Salary and Commissions)'];
                                    echo form_dropdown('choose_options', $copt, (isset($_POST['choose_options']) ? $_POST['choose_options'] : ''), 'id="choose_options" class="form-control select"');
                                ?>
                            </div>
                            <div class="form-group hide">
                                <?= lang('basic_salary', 'basic_salary'); ?>
                                <div class="controls">
                                    <?php echo form_input('basic_salary', 0, 'class="form-control" id="basic_salary"'); ?>
                                </div>
                            </div>
                            <?php if($Settings->zone){?>
                            <div class="form-group">
                                <?= lang('zone', 'zone'); ?>
                                <?php if($zones){
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
                                echo form_dropdown('multi_zone[]', $zns, (isset($_POST['multi_zone']) ? $_POST['multi_zone'] : ''), 'id="multi_zone" class="form-control select" data-placeholder="' . lang('select') . ' ' . lang('zone') . '" style="width:100%;" multiple="multiple"');
                                // foreach ($zones as $zone) {
                                //     $zns[$zone->id] = $zone->zone_name && $zone->zone_name != '-' ? $zone->zone_name : $zone->zone_name;
                                // }
                                // echo form_dropdown('multi_zone[]', $zns, (isset($_POST['multi_zone']) ? $_POST['multi_zone'] : ''), 'id="multi_zone" class="form-control select" data-placeholder="' . lang('select') . ' ' . lang('zone') . '" style="width:100%;" multiple="multiple"');
                                ?>
                            </div>
                            <?php }?>
                            <div class="form-group">
                                <?= lang('save_point', 'save_point'); ?>
                                <?php $sp = ['0' => lang('no'), '1' => lang('yes')]; ?>
                                <?php echo form_dropdown('save_point', $sp, (isset($_POST['save_point']) ? $_POST['save_point'] : '0'), 'class="form-control input-tip select" required="required" id="save_point" style="width:100%;"'); ?> 
                            </div>
                            <div class="clearfix"></div>
                            <div class="no">
                                <?php if (!empty($billers) && count($billers) > 1) { ?>
                                    <?php if (!$this->Settings->multi_biller) { ?>
                                    <div class="form-group">
                                        <?= lang('biller', 'biller'); ?>
                                        <?php
                                        $bl[''] = lang('select') . ' ' . lang('biller');
                                        foreach ($billers as $biller) {
                                            $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;
                                        }
                                        echo form_dropdown('biller', $bl, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'id="biller" class="form-control select" style="width:100%;" ' . ($Store ? ' required="required" ' : ''));
                                        ?>
                                    </div>
                                    <?php } else { ?>
                                    <div class="form-group">
                                        <?= lang('multi_Biller', 'multi_Biller'); ?>
                                        <?php
                                        foreach ($billers as $biller) {
                                            $bls[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;
                                        }
                                        echo form_dropdown('multi_biller[]', $bls, (isset($_POST['biller']) ? $_POST['biller'] : ''), 'id="multi_biller" class="form-control select" data-placeholder="' . lang('select') . ' ' . lang('biller') . '" style="width:100%;" multiple="multiple" ' . ($Store ? ' required="required" ' : ''));
                                        ?>
                                    </div>
                                    <?php } ?>
                                <?php } else {
                                    $biller_input = array(
                                        'type'  => 'hidden',
                                        'name'  => ($this->Settings->multi_biller ? 'multi_biller[]' : 'biller'),
                                        'id'    => 'slbiller',
                                        'value' => $billers[0]->id,
                                    );
                                    echo form_input($biller_input);
                                } ?>
                                <?php if (!empty($warehouses) && count($warehouses) > 1) { ?>
                                <div class="form-group">
                                    <?= lang('warehouse', 'warehouse'); ?>
                                    <?php
                                    if (!$this->Settings->multi_warehouse) {
                                        $wh[''] = lang('select') . ' ' . lang('warehouse');
                                        foreach ($warehouses as $warehouse) {
                                            $wh[$warehouse->id] = $warehouse->name;
                                        }
                                        echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'id="warehouse" class="form-control select" style="width:100%;" ' . ($Store ? ' required="required" ' : ''));
                                    } else {
                                        foreach ($warehouses as $warehouse) {
                                            $wh[$warehouse->id] = $warehouse->name;
                                        }
                                        echo form_dropdown('warehouse[]', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'id="warehouse" class="form-control select" placeholder="' . lang('select') . ' ' . lang('warehouse') . '" style="width:100%;" multiple="multiple" required="required" ');
                                    } ?>
                                </div>
                                <?php } else {
                                    $warehouse_input = array(
                                        'type'  => 'hidden',
                                        'name'  => ($this->Settings->multi_warehouse ? 'warehouse[]' : 'warehouse'),
                                        'id'    => 'warehouse',
                                        'value' => $warehouses[0]->id,
                                    );
                                    echo form_input($warehouse_input);
                                } ?>
                                <div class="form-group">
                                    <?= lang('view_right', 'view_right'); ?>
                                    <?php
                                    $vropts = [1 => lang('all_records'), 0 => lang('own_records')];
                                    echo form_dropdown('view_right', $vropts, (isset($_POST['view_right']) ? $_POST['view_right'] : 1), 'id="view_right" class="form-control select" style="width:100%;"');
                                    ?>
                                </div>
                                <div class="form-group">
                                    <?= lang('edit_right', 'edit_right'); ?>
                                    <?php
                                    $opts = [1 => lang('yes'), 0 => lang('no')];
                                    echo form_dropdown('edit_right', $opts, (isset($_POST['edit_right']) ? $_POST['edit_right'] : 0), 'id="edit_right" class="form-control select" style="width:100%;"');
                                    ?>
                                </div>
                                <div class="form-group">
                                    <?= lang('allow_discount', 'allow_discount'); ?>
                                    <?= form_dropdown('allow_discount', $opts, (isset($_POST['allow_discount']) ? $_POST['allow_discount'] : 0), 'id="allow_discount" class="form-control select" style="width:100%;"'); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <label class="checkbox" for="notify">
                                        <input type="checkbox" name="notify" value="1" id="notify" />
                                        <?= lang('notify_user_by_email') ?>
                                    </label>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <p><?php echo form_submit('add_user', lang('add_user'), 'class="btn btn-primary"'); ?></p>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" charset="utf-8">
    $(document).ready(function() {
        if ($('#group').val() != 1) {
            $('.no').slideDown();
        } else {
            $('.no').slideUp();
        }
        $('#group').change(function(event) {
            var group = $(this).val();
            if (group == 1) {
                $('.no').slideUp();
            } else {
                $('.no').slideDown();
            }
        });
    });
</script>