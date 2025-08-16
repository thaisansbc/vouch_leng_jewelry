<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css">
    .desc_title{
        color: #d2bfbf;
        font-size: 11px;
    }
    .left_title{
        width:85px;float: left;
    }
    .right_title{
        width:195px;float: left;text-align: left;
    }
</style>
    <div class="contain_module">
        <div class="box-header">
            <h2 class="text-center"><?= lang('reset') ?></h2>
        </div>
        <div>
            <button id="reset">RESET</button>
        </div>
    </div>

<script type="text/javascript" src="<?=$assets?>pos/js/plugins.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $("#reset").click(function(){

            var boxd = bootbox.dialog({
                title: "<i class='fa fa-key'></i> Pin Code ?",
                message: '<input id="pos_pin" name="pos_pin" type="password" placeholder="Pin Code" class="form-control"> ',
                buttons: {
                    success: {
                        label: "<i class='fa fa-tick'></i> Submit",
                        className: "btn-success verify_pin",
                        callback: function () {
                            var pos_pin = $('#pos_pin').val();
                            if (pos_pin == 12345) {
                                alert('success');
                                return false;
                            } else {
                                alert('Wrong Pin Code');
                            }
                        }
                    }
                }
            });
        });
    });
</script>