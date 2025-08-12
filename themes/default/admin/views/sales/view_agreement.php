<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', '.sledit', function(e) {
            if (localStorage.getItem('slitems')) {
                e.preventDefault();
                var href = $(this).attr('href');
                bootbox.confirm("<?= lang('you_will_loss_sale_data') ?>", function(result) {
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
        table {
            font-size: 11px !important;
        }
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang("agreement") . ' ' . $inv->id; ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>">
                        </i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <?php if ($inv->attachment) { ?>
                            <li>
                                <a href="<?= admin_url('welcome/download/' . $inv->attachment) ?>">
                                    <i class="fa fa-chain"></i> <?= lang('attachment') ?>
                                </a>
                            </li>
                        <?php } ?>                       
                        <li>
                            <a href="<?= admin_url('sales/email/' . $inv->id) ?>" data-target="#myModal" data-toggle="modal">
                                <i class="fa fa-envelope-o"></i> <?= lang('send_email') ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?= admin_url('sales/pdf/' . $inv->id) ?>">
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
            <div class="col-lg-12">
                <div class="col-xs-12 text-center">
                    <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>" alt="<?= $biller->company != '-' ? $biller->company : $biller->name; ?>">
                </div>
                <div class="well-sm">
                    <div class="hide no-print">
                        <?php $agreement_temp = file_get_contents('themes/' . $this->Settings->theme . '/admin/views/email_templates/service_agreement.html',true);?>
                    </div>
                    <?php 
                    $this->load->library('parser');
                    $parse_data = [
                        'reference_number' => $inv->reference_no,
                        'contact_person'   => $customer->name,
                        'company'          => $customer->company && $customer->company != '-' ? '(' . $customer->company . ')' : '',
                        'order_link'       => $inv->shop ? shop_url('orders/' . $inv->id . '/' . ($this->loggedIn ? '' : $inv->hash)) : base_url(),
                        'site_link'        => base_url(),
                        'site_name'        => $this->Settings->site_name,
                        'logo'             => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company && $biller->company != '-' ? $biller->company : $biller->name) . '"/>',
                    ];
                    $message =  $this->parser->parse_string($agreement_temp, $parse_data, true);
                    echo $message;
                    ?>
                    <div class="clearfix"></div>
                </div>
                <div class="row">
                    <div class="col-xs-6 pull-right">
                        <div class="well-sm">
                            <p>
                                <?= lang("seller"); ?>:<br>
                                <?= lang("date"); ?>: ............./............./...................
                            </p>
                        </div>
                    </div>
                    <div class="col-xs-6 pull-right">
                        <div class="well-sm">
                            <p>
                                <?= lang("buyer"); ?>:<br>
                                <?= lang("date"); ?>: ............./............./...................
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>