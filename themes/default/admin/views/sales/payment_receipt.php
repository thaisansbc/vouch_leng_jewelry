<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function () {
        $(document).on('click', '.sledit', function (e) {
            if (localStorage.getItem('slitems')) {
                e.preventDefault();
                var href = $(this).attr('href');
                bootbox.confirm("<?=lang('you_will_loss_sale_data')?>", function (result) {
                    if (result) {
                        window.location.href = href;
                    }
                });
            }
        });
    });
</script>
<style type="text/css" media="all">
    table {
        font-size: 11px !important;
    }
    @media print {
        #myModal .modal-content {
            display: none !important;
        }
        .modal-content { page-break-after: auto; }
        table {
            font-size: 11px !important;
        }
        .bg-from{background: #ffe6e6 !important;}
    }
</style>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang("sale_no") . ' ' . $inv->reference_no; ?></h2>
                
                <div style="margin: 0 5px 0 10px;">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        <i class="fa fa-2x">&times;</i>
                </button>
                </div>
               
                <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                    <i class="fa fa-print"></i> <?= lang('print'); ?>
                </button>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center font-receiptsize" style="color: #2D2E4D;">
                      
                            <span class="txt-bold" style="font-family: Khmer OS Muol Light">បង្កាន់ដៃបង់ប្រាក់ </span>
                         
                        </div>
                        <br>
                        <div class="well-sm">
                            <div class="row">
                                <div class="col-xs-1"></div>
                                <div class="col-xs-5">
                                    <?php 
                                    if ($logo) { ?>
                                    <div><img style="width: 160px !important;" src="<?= base_url() . 'assets/uploads/logos/'.$biller->logo; ?>" ></div>
                                    <?php } ?>
                                </div>
                                <div class="col-xs-5 text-right">
                                    <div class="rec-date rec-size-form" style="padding-right: 0px;">
                                        <p class="rec-date-border"><span class="rec-date-style">កាលបរិច្ឆេត / DATE </span><span style="padding: 5px;"> <?= $this->bpas->hrld($payment->date); ?></span></p>
                                    </div>
                                    <div class="rec-date rec-size-form" style="padding-right: 0px;">
                                        <p class="rec-date-border"><span class="rec-date-style">លេខ / No </span><span style="padding: 5px;"> <?= $payment->reference_no; ?></span></p>
                                    </div>
                                </div>
                                <div class="col-xs-1"></div>
                            </div>
                        </div>
               
                        <div class="well-sm">
                            <!-- <div class="col-xs-12"> -->
                             
                                <div class="rec-form-desc" style="font-size: 11px;">
                                        <div class="row rec-input-form r-f-inp-w">
                                            <div class="col-xs-2 text-right">
                                                <div>ឈ្មោះអតិថិជន</div>
                                                <div>Client Name</div>
                                            </div>
                                            <div class="col-xs-3 text-left"><input type="text" name="" style="width: 100%;" value="<?= $customer->name; ?>"></div>
                                            <div class="col-xs-2 text-right">

                                                <div>លេខទូរស័ព្ទ</div>
                                                <div>Phone Number</div>
                                            </div>
                                            <div class="col-xs-4 text-left"><input type="text" style="width: 100%;" value="<?= $customer->phone; ?>"></div>
                                            <div class="col-xs-1"></div>
                                        </div>
                                       
                                        <div class="row rec-input-form r-f-inp-w">
                                            <div class="col-xs-2 text-right">
                                                <div>បង់ប្រាក់ចំនួន</div>
                                                <div>Amount</div>
                                            </div>
                                            <div class="col-xs-3 text-left"><input type="text" name="" style="width: 100%;" value="<?= $this->bpas->formatMoney($payment->amount); ?>"></div>
                                            <div class="col-xs-6 text-left"><input type="text" style="width: 100%;" value="<?= $this->bpas->convertNumberToKhWords($payment->amount);?> ដុល្លា"></div>
                                            <div class="col-xs-1"></div>
                                        </div>
                                        <div class="row rec-input-form">
                                            <div class="col-xs-2 text-right">
                                                <div>បរិយាយ</div>
                                                <div>Description</div>
                                            </div>
                                            <div class="col-xs-9 text-left"><textarea name="" style="height: 35px;"><?= $this->bpas->remove_tags($payment->note);?></textarea></div>
                                            <div class="col-xs-1"></div>
                                        </div>
                                        <div class="row rec-input-form">
                                            <div class="col-xs-2 text-right">
                                                <div>សម្កាល់</div>
                                                <div>Remark</div>
                                            </div>
                                            <div class="col-xs-9 text-left"><textarea name="" style="height: 35px;">រាល់ទឹកប្រាក់ដែលលោកអ្នកបង់រួចមិនអាចដកវិញបានទេ សូមពិនិត្យមើលវិក័យប័ត្រមុននឹកចាកចេញ!</textarea></div>
                                            <div class="col-xs-1"></div>
                                        </div>
                                    </div>
                                <div class="col-xs-12 rec-txt-foot">
                                    <div class="col-xs-12">
                                        <div class="row text-center">
                                            <div class="col-xs-4 rec-space">
                                                <span class="txt-bold">
                                                    <div style="font-family: Khmer OS Muol Light">គណនេយ្យករ</div>
                                                    <div>Accountant</div>
                                                </span>
                                                <br><br>
                                                <hr>
                                            </div>
                                            <div class="col-xs-4 rec-space">
                                                <span class="txt-bold">
                                                    <div style="font-family: Khmer OS Muol Light">អ្នកទទួលប្រាក់</div>
                                                    <div>Recieved By</div>
                                                </span>
                                                <br><br>
                                                <hr>
                                            </div>
                                            <div class="col-xs-4 rec-space">
                                                <span class="txt-bold">
                                                    <div style="font-family: Khmer OS Muol Light">អ្នកប្រគល់ប្រាក់</div>
                                                    <div>Paid By</div>
                                                </span>
                                                <br><br>
                                                <?php echo $customer->name; ?>
                                            </div>
                                        </div>
                                        <hr style="border-color: gray;">
                                        <p style="font-size: 12px;text-align: center;">Address <?= $this->bpas->remove_tags($biller->address); ?></p>
                                    </div>
                                </div>
                            <!-- </div>  -->
                        </div>
            
                    </div>
             
                </div>
            </div>
        </div>
    </div>
</div>
