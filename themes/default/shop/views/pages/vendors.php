<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css">
	
</style>
<section class="page-contents">
    <div class="container">
        <div style="width: 100%;">
            <?php foreach ($vendors as $vendor) { ?>
            <div style="width:250px;height: 200px;background: #fff;float: left;margin-right: 10px;">
                <div style="padding:5px 0 0 10px;height:100px;">
                    <div><?= $vendor->name;?></div>
                    <div class="hide"><?= lang('products')?>: 0</div>
                </div>
                <div class="vendors_profile">
                    <div class="cycle_profile_border">
                        <img src="<?= base_url('assets/uploads/logos/' . $vendor->logo) ?>" class="img_profile_cycle">
                    </div>
                </div>
            </div>
            <?php }?>
        </div>
    </div>
</section>
