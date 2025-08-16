<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang('actions')?>"></i>
                    </a>
                    
                </li>
            </ul>
        </div>
    </div>
    <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
        <i class="fa fa-print"></i> <?= lang('print'); ?>
    </button>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?=lang('list_results');?></p>
                <h4 class="modal-title" id="myModalLabel">View Sale from (<?php echo $start_date;?> To <?php echo $end_date;?>)</h4>
                <div class="table-responsive">
                    <table class="table table-hover table-striped" border="0">
                        <thead>
                        <tr>
                            <th><?= lang('no'); ?></th>
                            <th><?= lang('date'); ?></th>
                            <th><?= lang('invoice'); ?></th>
                            <th><?= lang('total'); ?></th>
                            <th><?= lang('discount'); ?></th>
                            <th><?= lang('net'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $row = 2;
                            $i=1;
                            foreach ($_POST['val'] as $id) {
                                $sale = $this->sales_model->getInvoiceByID($id);
                            ?>
                                <tr>
                                    <td><?= $i;?></td>
                                    <td><?= $this->bpas->hrld($sale->date);?></td>
                                    <td><?= $sale->reference_no;?></td>
                                    <td><?= $sale->total;?></td>
                                    <td><?= $sale->total_discount;?></td>
                                    <td><?= $sale->grand_total;?></td>
                                </tr>
                            <?php
                                $row++;
                                $i++;
                            }
                            ?>
                        </tbody>
                     
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

