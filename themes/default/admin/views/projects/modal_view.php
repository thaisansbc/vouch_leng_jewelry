<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
.qrimg{
	width:75px;
}
</style>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
		 <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				<i class="fa fa-2x">&times;</i>
			</button>
			<button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
				<i class="fa fa-print"></i> <?= lang('print'); ?>
			</button>
			<h4 class="modal-title" id="myModalLabel"><?= lang("style_code").' : '.$rows->project_code; ?></h4>
		</div>
        <div class="modal-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped print-table order-table">
                    <tbody>
						
						<tr>
							<td colspan="2" style="font-size:18px;font-weight:bold;text-align:center;background-color:#FFF;">Article Specification</td>
						</tr>
						<tr>
							<td style="width:30%;"><?= lang("barcode_qrcode"); ?></td>
							<td style="width:70%;">
								<img src="<?= admin_url('misc/barcode/'.$rows->project_code.'/'.$rows->barcode_symbology.'/74/0'); ?>" alt="<?= $rows->project_code; ?>" class="bcimg" />
								<?= $this->bpas->qrcode('link', urlencode($rows->project_code), 4); ?>
							</td>
						</tr>
						 <tr>
							<td><?= lang("Year"); ?></td>
							<td><?= lang($rows->year); ?></td>
						</tr>
						<tr>
							<td><?= lang("Season"); ?></td>
							<td><?= $rows->season; ?></td>
						</tr>
						<tr>
							<td><?= lang("Line"); ?></td>
							<td><?= $rows->line; ?></td>
						</tr>
						<tr>
							<td><?= lang("Size Run"); ?></td>
							<td><?= $rows->size_run; ?></td>
						</tr>
						<tr>
							<td><?= lang("simple_size"); ?></td>
							<td><?= $rows->simple_size; ?></td>
						</tr>
						<tr>
							<td><?= lang("working Number"); ?></td>
							<td><?= $rows->working_no; ?></td>
						</tr>
						<tr>
							<td><?= lang("description"); ?></td>
							<td><?= $rows->description; ?></td>
						</tr>
                    </tbody>
                
                </table>
            </div>
        </div>
    </div>
</div>
