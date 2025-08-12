<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css">
    /* .box{
          background-color: black;
          z-index: 1001;
          -moz-opacity: 0.5;
          opacity: .50;
          filter: alpha(opacity=80);
    }
    .msg_alert{
        display: none;
        background: #ffffff;
        width: 300px;
        height: 200px;
        margin: 0 auto;
        border: 1PX solid #CCCCCC;
    } */
</style>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-solid fa-expand"></i><?= lang('Verify_Ticket'); ?></h2>
    <div class="box-icon">
        <ul class="btn-tasks">
            <li class="dropdown">
                <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                    <i class="icon fa fa-toggle-up"></i>
                </a>
            </li>
            <li class="dropdown">
                <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                    <i class="icon fa fa-toggle-down"></i>
                </a>
            </li>
        </ul>
    </div>
     <div class="box-icon">
        <ul class="btn-tasks">
            <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                    <i class="icon fa fa-tasks tip" data-placement="left" title="<?=lang("actions")?>"></i>
                </a>
                <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                   <li>
                        <a href="<?= admin_url('calendar/add_ticket') ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                            <i class="fa fa-plus-circle"></i> <?= lang('add_ticket') ?>
                        </a>
                    </li>
                    <li>
                        <a href="#" id="excel" data-action="export_excel"><i class="fa fa-file-excel-o"></i> 
                            <?= lang('export_to_excel') ?>
                        </a>
                    </li>   
                    <li class="divider"></li>
                    <li>
                        <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_calendars") ?></b>"
                            data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                            data-html="true" data-placement="left">
                        <i class="fa fa-trash-o"></i> <?= lang('delete_template') ?>
                         </a>
                     </li>
            
                </ul>
            </li>
        </ul>
    </div>
</div>
<div class="box" style="background-color:#FFFFFF !important;">
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                    <input type="number" class="form-control" name="verify_ticket" placeholder="scan here" id="verify_ticket"  >
                
            </div>
        </div>
    </div>
</div>
<div class="modal fade in" id="msg_modal" tabindex="-1" role="dialog" aria-labelledby="txModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body">
                    <div style="font-size:24px;display:flex;flex-direction: column;align-items:center;">
                        <img style="height: 100px !important;" src="<?= base_url() . 'assets/uploads/verified.gif' ?>" >
                        Verified
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade in" id="notfound_modal" tabindex="-1" role="dialog" aria-labelledby="txModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body">
                    <div style="font-size:24px;display:flex;flex-direction: column;align-items:center;">
                        <img style="height: 100px !important;" src="<?= base_url() . 'assets/uploads/notfound.gif' ?>" >
                        <p>Invalid code</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade in" id="expired_modal" tabindex="-1" role="dialog" aria-labelledby="txModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body">
                    <div style="font-size:18px;display:flex;flex-direction: column;align-items:center;">
                        <img style="height: 100px !important;" src="<?= base_url() . 'assets/uploads/expired.jpg' ?>">
                        <p>  This ticket has expired.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script type="text/javascript">
    $("#verify_ticket").focus();
    $(document).on('keyup', '#verify_ticket', function() {
        var code = $(this).val() ? $(this).val() : '';
        var url = '<?php echo admin_url("calendar/validate_ticket"); ?>/' + code;
        $("#verify_ticket").focus();
        if (code != '') {
            $.ajax({
                type: "get",
                async: false,
                url:  url,
                dataType: "json",
                success: function(data) {
                    console.log(data);
                    if (data === false) {
                        $('#notfound_modal').modal();
                        setTimeout(function () {
                            $("#notfound_modal").modal("hide");
                            $("#verify_ticket").focus();
                            $("#verify_ticket").val('');
                        }, 1000);

                        //bootbox.alert('<?= lang('incorrect_code') ?>');
                    } else if (data.customer_id == null) {
                        bootbox.alert('<?= lang('gift_card_not_for_customer') ?>');
                    }else {
                       if(data.status=='expired'){
                        $('#expired_modal').modal();
                        setTimeout(function () {
                            $("#expired_modal").modal("hide");
                            $("#verify_ticket").focus();
                            $("#verify_ticket").val('');
                        }, 1500);
                        // bootbox.alert('<?= lang('This ticket has expired.') ?>');
                       }else{
                        $('#msg_modal').modal();
                        setTimeout(function () {
                            $("#msg_modal").modal("hide");
                            $("#verify_ticket").focus();
                            $("#verify_ticket").val('');
                        }, 1500);
                       
                       }

                    }
                   $("#verify_ticket").focus();
                //    $("#verify_ticket").val('');
                }
            });
        }
    });
</script>