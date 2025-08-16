<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css">
	.pricing_border{
		border:1px solid #333333;
		border-radius:5px;
	}
	.pricing .price_head{
		background: #C4435F;
		padding: 5px;
		text-align: center;
		font-weight: bold;
		font-size: 16px;
		color: #fff;
	}
	.price_content{
		padding:10px;
	}
	.pricing .price_footer{
		text-align: center;
		padding: 10px;
	}
</style>
<section class="page-contents">
    <div class="container">
        <div class="row pricing">
			
			<div class="col-sm-4">
				<div class="pricing_border">
					<div class="price_head">
						Family
					</div>
					<div class="price_content">
						<p>- Standard </p>
						<p>- Auto Backup Every Week </p>
						<p>&nbsp;</p>
						<p>&nbsp;</p>
						<p>&nbsp;</p>
					</div>
					<div class="price_footer">
						<button type="button" class="btn btn-primary">1 <?= lang('this_package') ?></button>
					</div>
				</div>
			</div>
			<div class="col-sm-4">
				<div class="pricing_border">
					<div class="price_head">
						Medium
					</div>
					<div class="price_content">
						<p>- Standard </p>
						<p>- Add-On Modules </p>
						<p>- Multi Warehouse </p>
						<p>- Multi Branch </p>
						<p>- Auto Backup Every Week </p>
					</div>
					<div class="price_footer">
						<button type="button" class="btn btn-primary">1 <?= lang('this_package') ?></button>
					</div>
				</div>
			</div>
			<div class="col-sm-4">
				<div class="pricing_border">
					<div class="price_head">
						Custom
					</div>
					<div class="price_content">
						<p>- Custom to requirments </p>
						<p>- Add-On Modules </p>
						<p>- Multi Warehouse </p>
						<p>- Multi Branch </p>
						<p>- Auto Backup Every Week </p>
					</div>
					<div class="price_footer">
						<button type="button" class="btn btn-primary">1 <?= lang('this_package') ?></button>
					</div>
				</div>
			</div>
            
            
        </div>
    </div>
</section>
