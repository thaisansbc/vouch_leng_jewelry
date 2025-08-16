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
    .modal-center{
        width: 100%;
        height: 100%;
        display:flex;
        justify-content: center;
        align-items:center;
    }
</style>
<div class="breadcrumb-header">
    <h2 class="blue"><i class="fa-solid fa-expand"></i><?= lang('verify_member'); ?></h2>
    <div class="box-icon">
        <ul class="btn-tasks">
            <!-- <li class="dropdown">
                <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                    <i class="icon fa fa-toggle-up"></i>
                </a>
            </li>
            <li class="dropdown">
                <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                    <i class="icon fa fa-toggle-down"></i>
                </a>
            </li> -->
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
                        <a href="<?= admin_url('e_ticket/add_ticket') ?>" data-toggle="modal" data-backdrop="static" data-target="#myModal">
                            <i class="fa fa-plus-circle"></i> <?= lang('add_ticket') ?>
                        </a>
                    </li>
                    <!-- <li>
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
                     </li> -->
            
                </ul>
            </li>
        </ul>
    </div>
</div>
<div class="box" style="background-color:#FFFFFF !important;">
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                    <input type="number" class="form-control" name="verify_card" placeholder="scan here" id="verify_card"  >
                
            </div>
        </div>
    </div>
</div>
<div class="modal fade in" id="msg_modal" tabindex="-1" role="dialog" aria-labelledby="txModalLabel" aria-hidden="true">
     <div class="modal-center">
     <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body">
                    <div style="font-size:24px;display:flex;flex-direction: column;align-items:center;">
                    <!-- <img style="height: 100px !important;" src="<?= base_url() . 'assets/uploads/verified.gif' ?>" >
                        Verified -->
                        <img style="height: 250px !important;" src="<?= base_url() . 'assets/uploads/verify-icon.png' ?>" >
                    </div>
                </div>
            </div>
        </div>
     </div>
    </div>


    <div class="modal fade in" id="notfound_modal" tabindex="-1" role="dialog" aria-labelledby="txModalLabel" aria-hidden="true">
       <div class="modal-center">
       <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div style="font-size:24px;display:flex;flex-direction: column;align-items:center;">
                        <!-- <img style="height: 100px !important;" src="<?= base_url() . 'assets/uploads/notfound.gif' ?>" >
                        <p>Invalid code</p> -->
                        <img style="height: 250px !important;" src="<?= base_url() . 'assets/uploads/invalid-icon.png' ?>" >
                    </div>
                </div>
            </div>
        </div>
       </div>
    </div>


    <div class="modal fade in" id="used_modal" tabindex="-1" role="dialog" aria-labelledby="txModalLabel" aria-hidden="true">
       <div class="modal-center">
       <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body">
                    <div style="font-size:24px;display:flex;flex-direction: column;align-items:center;">
                        <!-- <img style="height: 100px !important;" src="<?= base_url() . 'assets/uploads/notfound.gif' ?>" >
                        <p>This ticket has used.</p> -->
                        <img style="height: 250px !important;" src="<?= base_url() . 'assets/uploads/used-icon.png' ?>" >
                    </div>
                </div>
            </div>
        </div>
       </div>
    </div>

    <div class="modal fade in" id="expired_modal" tabindex="-1" role="dialog" aria-labelledby="txModalLabel" aria-hidden="true">
    <div class="modal-center">
    <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body">
                    <div style="font-size:18px;display:flex;flex-direction: column;align-items:center;">
                        <!-- <img style="height: 100px !important;" src="<?= base_url() . 'assets/uploads/expired.jpg' ?>">
                        <p>  This ticket has expired.</p> -->
                        <img style="height: 250px !important;" src="<?= base_url() . 'assets/uploads/expired-icon.png' ?>" >
                    </div>
                </div>
            </div>
        </div>
</div>
    </div>


    <div class="modal fade in" id="nottimeyet_modal" tabindex="-1" role="dialog" aria-labelledby="txModalLabel" aria-hidden="true">
    <div class="modal-center">
    <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body">
                    <div style="font-size:18px;display:flex;flex-direction: column;align-items:center;">
                        <!-- <img style="height: 100px !important;" src="<?= base_url() . 'assets/uploads/expired.jpg' ?>">
                        <p>  This ticket has expired.</p> -->
                        Can not verify because this schedule has not yet started.
                    </div>
                </div>
            </div>
        </div>
    </div>

        
    </div>

<script type="text/javascript">
    $("#verify_card").focus();
    $("#verify_card").change(function(event) {
        var valueidcard = $("#verify_card").val();
        let today = new Date().toISOString().slice(0, 10); 
        $.ajax({
            type: 'get',
            url:  '<?php echo admin_url("pos/getmember_card/"); ?>' + valueidcard
            site.base_url + 'pos/getmember_card',
            dataType: "json",
            data: {
                idcard : valueidcard,
            },
            success: function(data) {
                if(data){
                    localStorage.setItem('posdiscount', data.discount+"%");
                    posdiscount = localStorage.getItem('posdiscount');
                    if(data.card_no != valueidcard){
                        bootbox.alert('Your memebercard was wrong !!');
                        $("#verify_card").val('');                       
                        return;
                    }else if(today > data.expiry){
                        bootbox.alert('Your membercard was expired!!');
                        $("#verify_card").val('');
                        $("#verify_card").focus().select();
                        return;
                    } 
                    if (is_valid_discount(posdiscount)) {
                        $('#posdiscount').val(posdiscount);
                        localStorage.setItem('verify_card', valueidcard);
                        localStorage.removeItem('posdiscount');
                        localStorage.setItem('posdiscount', posdiscount); 
                    } else {
                        bootbox.alert(lang.unexpected_value);
                    }
                }else{
                    $("#verify_card").val('');                       
                    return;
                }
            }
        }); 
    }); 

    $(document).on('change', '#verify_ticket', function() {
        var code = $(this).val() ? $(this).val() : '';
        var url = '<?php echo admin_url("e_ticket/validate_ticket"); ?>/' + code;
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
                        }, 3000);

                        //bootbox.alert('<?= lang('incorrect_code') ?>');
                    }else {
                       if(data.status=='expired'){
                        $('#expired_modal').modal();
                        setTimeout(function () {
                            $("#expired_modal").modal("hide");
                            $("#verify_ticket").focus();
                            $("#verify_ticket").val('');
                        }, 3000);
                        // bootbox.alert('<?= lang('This ticket has expired.') ?>');
                       }else if(data.status=='attendee'){
                        $('#used_modal').modal();
                        setTimeout(function () {
                            $("#used_modal").modal("hide");
                            $("#verify_ticket").focus();
                            $("#verify_ticket").val('');
                        }, 3000);
                       
                       }else if(data.status=='notimeyet'){
                        $('#nottimeyet_modal').modal();
                        setTimeout(function () {
                            $("#nottimeyet_modal").modal("hide");
                            $("#verify_ticket").focus();
                            $("#verify_ticket").val('');
                        }, 3000);
                       }else{
                        $('#msg_modal').modal();
                        setTimeout(function () {
                            $("#msg_modal").modal("hide");
                            $("#verify_ticket").focus();
                            $("#verify_ticket").val('');
                            location.reload();
                        }, 3000);
                       
                       }

                    }
                   $("#verify_ticket").focus();
                //    $("#verify_ticket").val('');
                }
            });
        }
    });
</script>