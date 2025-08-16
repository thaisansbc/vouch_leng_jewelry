<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_warehouse'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('system_settings/add_warehouse', $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <label class="control-label" for="code"><?php echo $this->lang->line('code'); ?></label>
                <?php echo form_input('code', '', 'class="form-control" id="code" required="required"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="name"><?php echo $this->lang->line('name'); ?></label>
                <?php echo form_input('name', '', 'class="form-control" id="name" required="required"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="atten_name"><?php echo $this->lang->line('atten_name'); ?></label>
                <?php echo form_input('atten_name', '', 'class="form-control" id="atten_name"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="price_group"><?php echo $this->lang->line('price_group'); ?></label>
                <?php
                $pgs[''] = lang('select') . ' ' . lang('price_group');
                foreach ($price_groups as $price_group) {
                    $pgs[$price_group->id] = $price_group->name;
                }
                echo form_dropdown('price_group', $pgs, $Settings->price_group, 'class="form-control tip select" id="price_group" style="width:100%;"');
                ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="phone"><?php echo $this->lang->line('phone'); ?></label>
                <?php echo form_input('phone', '', 'class="form-control" id="phone"'); ?>
            </div>
            <div class="form-group">
                <label class="control-label" for="email"><?php echo $this->lang->line('email'); ?></label>
                <?php echo form_input('email', '', 'class="form-control" id="email"'); ?>
            </div>
          
            <div class="form-group">
                <?= lang('saleable', 'saleable'); ?>
                <?php $saleable = ['1' => 'Yes', '0' => 'No']; ?>
                <?= form_dropdown('saleable', $saleable,'', 'class="form-control"'); ?>
            </div>
                
            <div class="form-group">
                <label class="control-label" for="address"><?php echo $this->lang->line('address'); ?></label>
                <?php echo form_textarea('address', '', 'class="form-control" id="address" required="required"'); ?>
            </div>
            <div class="form-group">
                <?= lang('warehouse_map', 'image') ?>
                <input id="image" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false"
                       class="form-control file">
            </div>
            <div class="form-group">
                <?= lang('logo', 'logo') ?>
                <input id="logo" type="file" data-browse-label="<?= lang('browse'); ?>" name="logo" data-show-upload="false" data-show-preview="false" class="form-control file">
            </div>
              <div class="form-group">
                     <label class="control-label" for="currency"><?= lang('default_currency'); ?></label>
                     <div class="controls">
                         <?php
                         foreach ($currencies as $currency) {
                             $cu[$currency->id] = $currency->name;
                         }
                         echo form_dropdown('default_currency', $cu, '' , 'class="form-control tip" id="currency" required="required" style="width:100%;"');
                         ?>
                     </div>
                 </div>
            <?php if($this->Settings->overselling) {?>
            <div class="form-group">
                <label class="control-label"><?= lang('over_selling'); ?></label>
                <div class="controls">
                    <?php
                    $opt = [0 => lang('default'),1 => lang('yes')];
                    echo form_dropdown('warehouse_over_selling', $opt, '', 'class="form-control" style="width:100%;"');
                    ?>
                </div>
            </div>
            <?php }?>
        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_warehouse', lang('add_warehouse'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<?= $modal_js ?>
