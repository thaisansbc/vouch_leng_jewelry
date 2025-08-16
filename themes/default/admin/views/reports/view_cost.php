<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>

            <div class="text-center"><h2><?= lang('cost_report').' '.lang('details'); ?></h2></div>
        </div>
        <div class="modal-body">
            <!-- <?php if ($logo) { ?>
                <div class="text-center" style="margin-bottom:20px;">
                    <img src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>"
                         alt="<?= $Settings->site_name; ?>">
                </div>
            <?php } ?> -->
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped print-table order-table">

                            <thead>
                            <tr>
                                <th><?= lang('no'); ?></th>
                                <th><?= lang('date'); ?></th>
                                <th><?= lang('quantity'); ?></th>
                                <th><?= lang('cost'); ?></th>
                                <th><?= lang('total').' '.lang('cost'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php 
                            $avCost = 0;
                            if (!empty($result)) {
                                $i = 1; $totalCost = 0; $countQty = 0;
                                foreach ($result as $row) { ?>
                                <tr>
                                    <td class="text-center"><?= $i; ?></td>
                                    <td><?= $this->bpas->hrld($row->date); ?></td>
                                    <td><?= $row->quantity; ?></td>
                                    <td class="text-center"><?= $this->bpas->formatMoney($row->real_unit_cost); ?></td>
                                    <td class="text-center"><?= $this->bpas->formatMoney($row->cost); ?></td>
                                </tr>   
                            <?php $i++;
                                    $totalCost += $row->total_cost;
                                    $countQty += $row->quantity;
                                } 
                                $avCost = $totalCost/($countQty); 
                            } ?>
                            </tbody>
                            <tfoot class="dtFilter">
                                <tr class="active">
                                    <th colspan="4" class="text-right">AVERAGE</th>
                                    <th class="text-center"><?= $this->bpas->formatQuantityDecimal($avCost); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
    $('.change_img').click(function(event) {
        event.preventDefault();
        var img_src = $(this).attr('href');
        $('#pr-image').attr('src', img_src);
        return false;
    });
});
</script>
