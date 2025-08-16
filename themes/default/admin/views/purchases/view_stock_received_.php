<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
@media print {
    .no-print{
        display:none !important;
    }
}
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang('stock_received') . '. ' . $inv->id; ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <?php if ($inv->attachment) {
                                ?>
                            <li>
                                <a href="<?= admin_url('welcome/download/' . $inv->attachment) ?>">
                                    <i class="fa fa-chain"></i> <?= lang('attachment') ?>
                                </a>
                            </li>
                        <?php
                        } ?>
                  
                     
                     
                        <li>
                            <a href="<?= admin_url('purchases/pdf/' . $inv->id) ?>">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('purchases/excel_export/' . $inv->id) ?>">
                                <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
        <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
            <i class="fa fa-print"></i> <?= lang('print'); ?>
        </button>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-xs-3">
                <img src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>"
                     alt="<?= $Settings->site_name; ?>">
            </div>
            <div class="col-xs-6 text-center">
                 <h1><?= $this->Settings->site_name;?></h1>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-3">&nbsp;</div>
            <div class="col-xs-6 text-center">
                 <h1>Stock Receipt</h1>
            </div>
            <div class="col-xs-3">&nbsp;</div>
        </div>
        <div class="row">
            <div class="col-xs-6">
                <div>   <strong><?php echo $this->lang->line("Received_from"); ?> : </strong> 
                        <?= $warehouse->name; ?>
                </div>            
                <div>   <strong><?php echo $this->lang->line("phone"); ?> : </strong> 
                        <?= $warehouse->phone; ?>
                </div>
            </div>
            <div class="col-xs-6">
                <div>   <strong><?php echo $this->lang->line("ref"); ?> : </strong> 
                        <?= $inv->reference_no; ?>
                </div>
                <div>   <strong><?php echo $this->lang->line("date"); ?> : </strong> 
                        <?= $inv->date; ?>
                </div>
            </div>
         </div>
         <br>
        <div class="row">
            <div class="col-lg-12">
                <div class="table-">
                    <table class="table table-hover table-bordered table-striped">
                        
                        <tr>
                            <th><?= lang("No"); ?></th>
                            <th><?= lang("code"); ?></th>
                            <th><?= lang("description"); ?></th>

                            <th><?= lang("unit"); ?></th>
                            <th><?= lang("qty"); ?></th>
                            
                            <!-- <th><?= lang("color"); ?></th>
                            <th><?= lang("size"); ?></th> -->
                            <?php
                                if ($inv->status == 'partial') {
                                    echo '<th>'.lang("received").'</th>';
                                }
                            ?>
                            <th style="padding-right:20px;"><?= lang("serial"); ?></th>
                          
                            <th style="padding-right:20px;"><?= lang("max_serial"); ?></th>
                       
                        </tr>
                        <tbody>
                        <?php $r = 1; 
                        foreach ($rows as $row):
                            ?>
                            <tr>
                                <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                <td style="vertical-align:middle; width: 100px;">
                                    <?= $row->product_code; ?>
                        
                                </td>
                                <td style="vertical-align:middle;">
                                     <?= $row->product_name; ?>
                                                <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                    <?= $row->supplier_part_no ? '<br>'.lang('supplier_part_no').': ' . $row->supplier_part_no : ''; ?>
                                    <?= $row->details ? '<br>' . $row->details : ''; ?> <?= $row->status_change != null ? '<br>' . $row->status_change : ''; ?>
                                    <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' .lang('expiry').': ' . $this->bpas->hrsd($row->expiry) : ''; ?>
                                    <!-- color  -->
                                     <?php
                                    $data= explode("|",$row->variant);
                                    if($row->variant){
                                        
                                        echo $data[0];
                                        if(isset($data[1])){
                                        //  echo $data[1];
                                        }
                                    }
                                    //  ($row->variant ? ' (' . $row->variant . ')' : ''); 
                                    ?>
                                    <!-- size -->
                                    <?php if(isset($data[1])){ echo $data[1];}
                                    if(isset($data[2])){ echo ' | '.$data[2];}
                                    ?>
                        
                                </td>
                                <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->product_unit_code; ?></td>
                                <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->bpas->formatQuantity($row->unit_quantity); ?></td>
                                
                                
                                </td>
                  
                                <?php
                                if ($inv->status == 'partial') {
                                    echo '<td style="text-align:center;vertical-align:middle;width:120px;">'.$this->bpas->formatQuantity($row->quantity_received).' '.$row->product_unit_code.'</td>';
                                }
                                ?>
                                <td style="text-align:right; width:100px; padding-right:10px;">
                                  
                                </td>
                                <td style="text-align:right; width:100px; padding-right:10px;"></td>
                              
                            </tr>
                            <?php
                            $r++;
                        endforeach;
                    
                        ?>
                        </tbody>
                        
                    </table>

                </div>

                <div class="row">
                    
                    <div class="col-xs-3">
                        <table class="table">
                            <tr>
                                <th style="text-align: center;border: none;" height="30"></th>
                            </tr>
                            <tr>
                                <td>
                                    
                                    <h6>Acknowledged by: ...............................................</h6>
                                    <h6>Name:    .................................</h6>
                                    <h6>Date: ........../.........../...........</h6>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-xs-3">
                        <table class="table">
                            <tr>
                                <th style="text-align: center;border: none;" height="30"></th>
                            </tr>
                            <tr>
                                <td>
                                    
                                    <h6>Stock Received by: ...............................................</h6>
                                    <h6>Name:    .....................................................</h6>
                                    <h6>Date: ........../.........../................</h6>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-xs-3">
                        <table class="table">
                            <tr>
                                <th style="text-align: center;border: none;" height="30"></th>
                            </tr>
                            <tr>
                                <td>
                                    
                                    <h6>Quality Checked by: ...............................................</h6>
                                    <h6>Name:    .....................................................</h6>
                                    <h6>Date: ........../.........../................</h6>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-xs-3">
                        <table class="table">
                            <tr>
                                <th style="text-align: center;border: none;" height="30"></th>
                            </tr>
                            <tr>
                                <td>
                                    
                                    <h6>Procurement: ...............................................</h6>
                                    <h6>Name:    .....................................................</h6>
                                    <h6>Date: ........../.........../................</h6>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-xs-5 no-print">

                    <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_purchase ? $inv->product_tax+$return_purchase->product_tax : $inv->product_tax), true) : ''; ?>
                        <?php if ($inv->note || $inv->note != "") { ?>
                            <div class="well well-sm">
                                <p class="bold"><?= lang("note"); ?>:</p>

                                <div><?= $this->bpas->decode_html($inv->note); ?></div>
                            </div>
                        <?php } ?>
                    </div>

                   
                </div>
        </div>



        <?php if (!$Supplier || !$Customer) {
                            ?>
            <div class="buttons">
                <?php if ($inv->attachment) {
                                ?>
                    <div class="btn-group">
                        <a href="<?= admin_url('welcome/download/' . $inv->attachment) ?>" class="tip btn btn-primary" title="<?= lang('attachment') ?>">
                            <i class="fa fa-chain"></i> <span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
                        </a>
                    </div>
                <?php
                            } ?>
                <div class="btn-group btn-group-justified">
                   
                    <div class="btn-group">
                        <a href="<?= admin_url('purchases/email/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('email') ?>">
                            <i class="fa fa-envelope-o"></i> <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= admin_url('purchases/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                            <i class="fa fa-download"></i> <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                        </a>
                    </div>
                    <div class="btn-group hide">
                        <a href="<?= admin_url('purchases/edit/' . $inv->id) ?>" class="tip btn btn-warning tip" title="<?= lang('edit') ?>">
                            <i class="fa fa-edit"></i> <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                        </a>
                    </div>
                    <div class="btn-group hide">
                        <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line('delete_purchase') ?></b>"
                           data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('purchases/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                           data-html="true" data-placement="top">
                            <i class="fa fa-trash-o"></i> <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                        </a>
                    </div>
                </div>
            </div>
        <?php
                        } ?>
    </div>
</div>
