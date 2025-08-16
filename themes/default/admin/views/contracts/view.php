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
<style type="text/css">
    body{
        font-size: 12px;
    }
    td{
        padding-bottom: 5px;
    }
    .table_print{
        width: 100%; 
    }
    .table_print th,.table_print td{
        padding: 5px;
    }
    .upper_case{
        text-transform: uppercase;
    }
    .font_bold{
        font-weight: bold;
    }
    .tap_space{
        margin-left: 20px;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang("contract_no") . ' ' . $inv->id; ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>">
                        </i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= base_url('contracts/edit/' . $inv->id) ?>" class="sledit">
                                <i class="fa fa-edit"></i> <?= lang('edit_sale') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('contracts/email/' . $inv->id) ?>" data-target="#myModal" data-toggle="modal">
                                <i class="fa fa-envelope-o"></i> <?= lang('send_email') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= base_url('contracts/pdf/' . $inv->id) ?>">
                                <i class="fa fa-file-pdf-o"></i> <?= lang('export_to_pdf') ?>
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
            <div class="" style="display: none;">
                <img width="100%" src="<?= base_url() . 'assets/uploads/logos/letterhead.jpg' ?>" alt="<?= $Settings->site_name; ?>">
        
            </div>
            <div class="clearfix"></div>
            <div class="text-center">
                <h2 style="font-size: 22px;">CONTRACT FOR SALE AND PURCHASE</h2>
            </div>
            <br>
            <div class="" style="font-weight: bold;">
                <div class="row">
                    <div class="col-xs-9 text-right">CONTRACT NO</div>
                    <div class="col-xs-3">: <?= $inv->reference_no; ?></div>
                </div>
                <div class="row">
                    <div class="col-xs-9 text-right">DATE</div>
                    <div class="col-xs-3">: <?= $this->bpas->hrld($inv->date); ?></div>
                </div>
            </div><br>
            <div class="well well-sm">
               <div class="row"> 
                <div class="col-xs-6 border-right">
                    <table width="100%">
                        <tr>
                            <td width="100">
                                <span class="title_label"><?= lang("SELLER"); ?></span></td>
                            <td>: <strong><?= $biller->company != '-' ? $biller->company : $biller->name; ?></strong>
                            </td>
                        </tr>
                        <tr>
                            <td width="100"><span class="title_label"><?= lang("date"); ?></span></td>
                            <td>: <?= $this->bpas->hrld($inv->date); ?></td>
                        </tr>
                        <tr>
                            <td width="100"><?= $biller->company ? "" : "Attn: " . $biller->name ?>
                                <span class="title_label"><?= lang("address"); ?>: </span></td>
                            <td>: <?php echo $biller->address . " " . $biller->city . " " . $biller->postal_code . " " . $biller->state . " " . $biller->country;

                                echo "<p>"; ?>
                            </td>
                        </tr>

                        <tr>
                            <td width="100">
                                <span class="title_label"><?= lang("tel"); ?></span></td>
                            <td>: <?php echo $biller->phone ?>
                            </td>
                        </tr>
                        <tr>
                            <td width="100">
                                <span class="title_label"><?= lang("email"); ?></span></td>
                            <td>: <?php echo $biller->email;

                                echo "<p>"; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-xs-6">
                    <table width="100%">
                        <tr>
                            <td width="120">
                                <span class="title_label"><?= lang("BUYER/CONSIGNEE"); ?></span></td>
                            <td>: <strong><?= $customer->company && $customer->company != '-' ? $customer->company : $customer->name; ?></strong>
                            </td>
                        </tr>
                        <tr>
                            <td width="120">
                                <span class="title_label"><?= lang("contact_name"); ?></span></td>
                            <td>: <?= $customer->name; ?></td>

                        </tr>
                     
                        <tr>
                            <td width="120">
                                <span class="title_label"><?= lang("address"); ?> </span></td>
                            <td>: <?php echo $customer->address . " " . $customer->city . " " . $customer->postal_code . " " . $customer->state . " " . $customer->country;
                            ?>
                            </td>
                        </tr>
                     
                        <tr>
                            <td width="120">
                                <span class="title_label"><?= lang("tel"); ?></span></td>
                            <td>: <?= $customer->phone;?>
                            </td>
                        </tr>
                        <tr>
                            <td width="120">
                                <span class="title_label"><?= lang("email"); ?></span></td>
                            <td>: <?= $customer->email;?>
                            </td>
                        </tr>
                    </table>     
                </div>
            </div>
            </div>

            <div class="clearfix"></div>

        </div>
        <div>
            <div class="">
                <div class="">
                    <div class="col-xs-12">
                        <p>REPRESENTATIVE BY MR. YO WEI BIN - DEPUTY GM OF RUBBER DIVISION</p>
                        <p>This contract is made by and between the Buyer and the Seller whereby the Buyer agrees to buy and the Seller agrees to sell the under-mentioned commodity according to the terms and conditions stipulated below:</p>
                    </div>
                    <div class="tap_space">
                        <div class="upper_case">
                            <div class="font_bold">1. COMMODITY </div>
                            <p class="tap_space"><?= $inv->product_name; ?></p>
                        </div>
                        <div class="upper_case">
                            <div class="font_bold">2. QUANTITY</div>
                            <p class="tap_space"><?= $inv->quantity; ?></p>
                        </div>
                        <div class="upper_case">
                            <div class="font_bold">3. UNIT PRICE</div>
                            <p class="tap_space"><?= $inv->unit_price; ?></p>
                        </div>
                        <div class="upper_case">
                            <div class="font_bold">4. TOTAL VALUE</div>
                            <p class="tap_space"><?= $inv->total_amount; ?></p>
                        </div>
                        <div>
                            <div class="font_bold upper_case">5.  PRICE TERMS: (INCOTERMS® 2010)</div>
                            <div  class="tap_space">
                            <div>FOB HO CHI MINH, VIETNAM</div>
                            <p>(THC and Local Charge are seller Account)</p>
                            </div>
                        </div>
                        <div>
                            <div class="font_bold upper_case">6. TERMS OF SHIPMENT</div>
                            <div class="tap_space">
                                <div>•  Shipment date: In <?= $inv->delivery; ?></div>
                                <div>•  Port of loading: Ho Chi Minh City port in Vietnam</div>
                                <div>•  Partial shipment is allowed with a minimum of 210 metric tons per shipment.</div>
                                <div>•  Transshipment is allowed.</div>
                                <div>•  The Buyer has to book the vessel space up to the quantity on the contract before loading and booking confirmation must be released to the Seller at least 7 days before the ETD.</div>
                                <div>•  Shipment date on Certificate of Origin and Bill of Lading could differ and must not be considered as discrepancy.</div>
                                <div>•  All expenses on the delay of the shipment except for FORCE MAJEURE cases caused by the Buyer or carrier/shipping line will be on the Buyer’s account.</div>
                                <div>•  All destination charges are for Buyer’s account.</div><br>
                            </div>
                        </div>
                        <div>
                            <div class="font_bold upper_case">7. PACKING:</div>
                            <p class="tap_space">IN <?= $Settings->bale; ?> KGS LOOSE BALES.</p>
                        </div>
                        <div>
                            <div class="font_bold upper_case">8. COUNTRY OF ORIGIN AND MANUFACTURES:</div>
                            <p class="tap_space">CAMBODIA</p>
                        </div>
                        <div>
                            <div class="font_bold upper_case">9. TERMS OF PAYMENT</div>
                            <div class="tap_space">
                                <p>By an irrevocable Letter of Credit (L/C) at sight covering 100% of the value of the shipment. 
                                The Buyer must submit L/C draft for Seller review before opening. Opening Bank must be accepted by the Seller. The Buyer must submit the L/C to be opened in a format acceptable to Seller within 7 working days from the date of finalizing the L/C draft, and payable at sight thought acceptable bank at least 5 working days before ETD from the date of receipt of the Seller’s instruction. If the Buyer fails to open the L/C on time, the Seller has right to terminate the Contract unilaterally. 
                                </p>
                                <div>Account name: <?= $biller->company; ?></div>
                                <div>Account number: <?= $inv->account_no; ?></div>
                                <div>BANK OF CHINA (HONK KONG) PHNOM PENH (SWIFT CODE: BKCHKHPP)</div>
                                <p>All bank changes and other fees outside Cambodia are for Buyer’s account.</p>
                            </div>
                        </div>
                        <div>
                            <div class="font_bold upper_case">10. INSURANCE</div>
                            <p class="tap_space">Insurance cost will be borne by the buyer.</p>
                        </div>
                        <div>
                            <div class="font_bold upper_case">11. DOCUMENTS</div>
                            <div class="tap_space">
                                <p>Documents provided by seller with the following basic documents as follows:</p>
                                <div>A- Bill of lading in 3 originals and 3 copies marked “freight collect”</div>
                                <div>B- Invoice in 3 signed originals and 3 copies.</div>
                                <div>C- Packing List in 3 signed originals and 3 copies.</div>
                                <div>D- Certificate of Quality issued by manufacturer in 1 original and 1 copies.</div>
                                <div>E- Certificate of Analysis Report issued by manufacturer in 1 original and 3 copies.</div>
                                <div>F- Certificate of Non-Wooden Packing issued by manufacturer in 1 original and 3 copies.</div>
                                <div>G- Certificate of Origin: 1 set and 3 Copies.</div>
                                <div>H- Phytosanitary Certificate issued by government authority: 1 original and 2 Copies.</div><br>
                                <div>Above documents must confirm draft from buyer before issued Original, otherwise reissue will response by Seller.</div>
                                <div>Upon Buyer’s request, any reissue and/or additional document (referred to clause 11) will be provided and the fee will be borne by the Buyer as follows:</div>
                                <div>(A), (B), (C), (D), (E) and (F): USD 50.00 each copy of document.</div>
                                <div>(G): USD 300.00 for Certificate of Origin per set of documents.</div>
                                <div>(H): USD 200.00 for Phytosanitary Certificate per set of documents.</div>
                                <div>(I): Other documents as per the seller’s advice.</div>
                                <div>The seller shall not be responsible if any of additional documents referred above or any visa is unobtainable for the reasons beyond his control. The Seller’s inability to obtain such documents or visas shall not preclude the payment.</div><br>
                            </div>
                        </div>
                        <div>
                            <div class="font_bold upper_case">12. TAXATION</div>
                            <div class="tap_space">
                                <p>Any taxes or levies imposed on this natural rubber by the country of destination shall be for the account of Buyer.</p>
                                <p>Any taxes or levies imposed on this natural rubber by the country of origin shall be for the seller, expect the freight tax on the ocean freight, if any, shall be for the account of Buyer.</p>
                            </div>
                        </div>
                        <div>
                            <div class="font_bold upper_case">13. FORCE MAJEUR</div>
                            <p class="tap_space">Any party of the Contract who fails to execute the Contract due to Force Majeure clause of the International Chamber of Commerce (ICC Publication number 421) accidents, such as flood, fire, storm, snow disasters, earthquake and war shall notify by fax immediately the other party of such occurrence and within 14 days thereafter, shall send by airmail the detailed information of the accident and a certificate issued by the Competent Government Authorities of the place where the accident occurs. The other party shall not claim any penalty for the losses suffered therefrom, but the party who encounters the accident shall still be liable to execute the Contract within the deferred time of the Contract as agreed upon by both parties. In case any delay arising therefrom lasts for more than 3 weeks, the other party shall have the right to cancel the Contract and notify the party who encounters the accident in writing.</p>
                        </div>
                        <div>
                            <div class="font_bold upper_case">14. ARBITRATION</div>
                            <p class="tap_space">All disputes in connection with contract or the execution thereof shall be settled by friendly negotiation. If no settlement can be reached, the case in disputes shall then be submitted for arbitrations per ICC - 500 Rules (Amended as per 1993) or as per International Laws, the place of jurisdiction being in Singapore, in accordance with the Singapore commodity Exchange Limited (SICOM) arbitration rules for physical rubber contract as may be prevailing. The decision made by the Commission shall be accepted as final and binding upon both parties. The fees for arbitration shall be borne by the losing party unless otherwise awarded by the commission.</p>
                        </div>
                        <div>
                            <div class="font_bold upper_case">15. COPY OF SALE CONTRACT AND SIGNATORY AUTHORITY</div>
                            <p class="tap_space">This Sale Contract is made out in two copies, one copy to be held by each party in witness thereof, the Contract come into effective after being signed by both parties and stamped the EXIM CONTRACT SEAL of the Buyers. Signed contract should be submitted by electronically (Email or Fax). Original- printed contract is legally binding.</p>
                        </div>
                        <div>
                            <div class="font_bold upper_case">16. NOTE</div>
                            <div class="tap_space">
                                <div>•  All other conditions which not stated in this sales contract will refer to Incoterms® 2010.</div>
                                <div>•  Any amendments or additions to this sales contract shall only be valid in writing and dully signed by their authorized representative on the first above written.</div><br>
                            </div>
                        </div>
                        <div class="">
                            <div class="font_bold upper_case">INTERNATIONALLY OMITTED</div>
                            <p class="tap_space">IN WITHNESS WHEREOF the parties have read and understood all of the above terms and agreed to be bound by those terms by having their respective authorized persons signed their names on the date first written above.</p>
                        </div>
                    </div>
                </div>
            </div><br>
            <div class="upper_case font_bold">
                <div class="row text-center">
                    <div class="col-xs-6">
                        <div>The seller:</div>
                        <br><br>
                        <div style="padding-top: 5px;border-top: 1px solid black; margin: 40px;"><?= $biller->company; ?> </div>
                    </div>
                    <div class="col-xs-6">
                        <div>The buyer:</div>
                        <br><br>
                        <div style="padding: 5px;border-top: 1px solid black;margin: 40px;"><?= $customer->company; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php if (!$Supplier || !$Customer) { ?>
            <div class="buttons">
                <div class="btn-group btn-group-justified">
                    <!-- <?php if ($inv->attachment) { ?>
                        <div class="btn-group">
                            <a href="<?= base_url('welcome/download/' . $inv->attachment) ?>" class="tip btn btn-primary" title="<?= lang('attachment') ?>">
                                <i class="fa fa-chain"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('attachment') ?></span>
                            </a>
                        </div>
                    <?php } ?> -->
                    <div class="btn-group">
                        <a href="<?= base_url('sales/payments/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('view_payments') ?>">
                            <i class="fa fa-money"></i> <span class="hidden-sm hidden-xs"><?= lang('view_payments') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= base_url('sales/add_payment/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('add_payment') ?>">
                            <i class="fa fa-money"></i> <span class="hidden-sm hidden-xs"><?= lang('add_payment') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= base_url('sales/email/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('email') ?>">
                            <i class="fa fa-envelope-o"></i> <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= base_url('sales/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                            <i class="fa fa-download"></i> <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                        </a>
                    </div>
                    <!-- <?php if ( ! $inv->sale_id) { ?>
                    <div class="btn-group">
                        <a href="<?= base_url('sales/add_delivery/' . $inv->id) ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('add_delivery') ?>">
                            <i class="fa fa-truck"></i> <span class="hidden-sm hidden-xs"><?= lang('add_delivery') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= base_url('sales/edit/' . $inv->id) ?>" class="tip btn btn-warning tip sledit" title="<?= lang('edit') ?>">
                            <i class="fa fa-edit"></i> <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="#" class="tip btn btn-danger bpo"
                            title="<b><?= $this->lang->line("delete_sale") ?></b>"
                            data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= base_url('sales/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                            data-html="true" data-placement="top"><i class="fa fa-trash-o"></i>
                            <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                        </a>
                    </div>
                    <?php } ?> -->
                    <!--<div class="btn-group"><a href="<?= base_url('sales/excel/' . $inv->id) ?>" class="tip btn btn-primary"  title="<?= lang('download_excel') ?>"><i class="fa fa-download"></i> <?= lang('excel') ?></a></div>-->
                </div>
            </div>
        <?php } ?>
    </div>
</div>
