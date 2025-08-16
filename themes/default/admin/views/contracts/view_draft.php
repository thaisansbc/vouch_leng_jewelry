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
        <div class="row text-center font_bold">
        <div style="border-bottom: 3px solid black;">    
            <h1 class="upper_case">HUAR LONG LEANG HAK CO., LTD</h1>
            <div class=""><?php echo $biller->address . " " . $biller->city . " " . $biller->postal_code . " " . $biller->state . " " . $biller->country; ?> </div>
            <div>Tel: <?php echo $biller->phone ?></div>
            <div>Email: <?php echo $biller->email; ?></div><br>
        </div>
        </div><br>
        <div class="row">
            <div class="text-center" style="">
                <strong style="font-size: 22px; border-bottom: 1px solid black; margin: auto;">SALE AND PURCHASE CONTRACT</strong>
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
                <div class="col-xs-6" style="display: none;">
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
            <div class="row">
                <div class="col-xs-12">
                    <p>After discussion the two parties agree to sign this contract for the goods to be sold by the seller and purchased by the Buyer with term and condition as follows:</p>

                    <table border="1" style="width: 100%;">
                        <tr>
                            <td>Commodity</td>
                            <td>: <?= $inv->product_name; ?></td>
                        </tr>
                        <tr>
                            <td>Quantity</td>
                            <td>: <?= $inv->quantity; ?></td>
                        </tr>
                        <tr>
                            <td>Price</td>
                            <td>: <?= $inv->unit_price; ?></td>
                        </tr>
                        <tr>
                            <td>Total Amount</td>
                            <td>: <?= $inv->total_amount; ?></td>
                        </tr>
                        <tr>
                            <td>Delivery</td>
                            <td>: <?= $inv->delivery; ?></td>
                        </tr>
                        <tr>
                            <td>Origin</td>
                            <td>: KINGDOM OF CAMBODIA</td>
                        </tr>
                        <tr>
                            <td>Payment Term</td>
                            <td>: <?= $inv->payment_term; ?></td>
                        </tr>
                        <tr>
                            <td>For Account Name</td>
                            <td>: HUAR LONG LEANG HAK CO., LTD</td>
                        </tr>
                        <tr>
                            <td>Banker</td>
                            <td>: CIMB BANK PLC
                              Phnom Penh, Cambodia.
                            </td>
                        </tr>
                        <tr>
                            <td>Account Nº</td>
                            <td>: <?= $inv->account_no; ?></td>
                        </tr>
                        <tr>
                            <td>Intermediary Bank</td>
                            <td>: Bank of New York Mellon New York, US (SWIFT CODE: IRVTUS33N)</td>
                        </tr>
                    </table>
                    <p>Please chop and sign and return one copy of this contract as confirmation.</p>
                </div>
            </div>

        </div><br>
        <div class="row upper_case text-center">
            <div class="col-xs-6">
                <div>BUYER</div>
                <p><?= $inv->customer; ?></p>
                <br><br>
                <div style="border-top: 1px solid black; margin: 40px;">Ms. Alice</div>
            </div>
            <div class="col-xs-6">
                <div>SELLER</div>
                <p><?= $inv->biller; ?></p>
                <br><br>
                <div style="border-top: 1px solid black; margin: 40px;"></div>
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
