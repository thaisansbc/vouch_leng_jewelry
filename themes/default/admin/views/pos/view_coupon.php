<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css">
    .boder_coupon{
        width: 35px;
        height: 35px;
        border-radius: 25%;
        -webkit-border-radius: 25%;
        -moz-border-radius: 25%;
        border: 1px solid #ffffff;
        float: left;
        margin-right: 15px;
        padding: 5px;
        margin-left: 20px;
        margin-bottom: 10px;
    }
</style>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header no-print">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('view_coupon'); ?></h4>
        </div>
        <div class="modal-body">
            <?php if ($gift_card->expiry && $gift_card->expiry < date('Y-m-d')) { ?>
                <div class="alert alert-danger">
                    <?= lang('member_card_expired') ?>
                </div>
            <?php }?>
            <div class="card">
                <div class="front">
                    <img src="<?=$assets;?>images/card.png" alt="" class="card_img">
                    <div class="card-content white-text">
                        <svg xmlns="http://www.w3.org/2000/svg" width="353px" height="206px" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <text x="5"  y="20" style="font-size:16;fill:#FFF;">
                                <?= lang('coupon'); ?>
                            </text>
                            <text x="175"  y="20" style="font-size:16;fill:#FFF;">
                                <?= wordwrap($gift_card->card_no, 4, ' ', true); ?>
                            </text>
                            <div style="position:absolute;left:0px;top:50px;width:353px;">
                                <div class="text-center">
                                    <?php 
                                    for ($i=1;$i <=$gift_card->value; $i++){
                                        echo '<div class="boder_coupon">'.$i.'</div>';
                                    }
                                    ?>
                                    
                                </div>
                            </div>
                          
                        
                
                            <div style="position:absolute;left:0px;bottom:12px;width:353px;background:#FFF;">
                                <div class="text-center">
                                <img src="<?= admin_url('misc/barcode/' . $gift_card->card_no . '/code128/20'); ?>" alt="<?= $gift_card->card_no; ?>" class="bcimg" />
                                </div>
                            </div>
                        </svg>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
                <div class="back">
                    <img src="<?=$assets;?>images/card2.png" alt="" class="card_img">
                    <div class="card-content">
                        <div class="middle">
                            <?= '<img src="' . base_url('assets/uploads/logos/' . $Settings->logo2) . '" alt="' . $Settings->site_name . '" />'; ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <div class="clearfix"></div>
            <?php if (!$gift_card->expiry || $gift_card->expiry > date('Y-m-d')) { ?>
            <button type="button" class="btn btn-primary btn-block no-print" onClick="window.print();"><?= lang('print'); ?></button>
            <?php } ?>
        </div>
    </div>
</div>
