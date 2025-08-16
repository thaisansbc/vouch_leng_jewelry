<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css">
    body{
        background: #000000 !important;
    }
    .erp_title{
        color: #fff;
    }
    .desc_title{
        color: #d2bfbf;
        font-size: 11px;
    }
    .left_title{
        width:65px;float: left;
    }
    .right_title{
        width:195px;float: left;
        text-align: left;
    }
    .blur{
        filter: grayscale(2) blur(1px);
    }
</style>
    <div class="contain_module">
        <div class="box-header">
            <h2 class="text-center erp_title"><?= lang('BPAS ERP') ?> : All in one Software</h2>
        </div>
        <div>
            <?php
            $modules = $this->site->getAllModules();
            foreach ($modules as $row) {
            ?>
            <div class="icon_module <?= $row->style ?> <?= $this->site->activeModule($row->module)?'':'blur'?>">
                <?php 
                if($this->site->activeModule($row->module)){
                    echo '<a class="white module" href="#" id="'.$row->id.'">';
                }else{
                    echo '<a class="white buy" href="#" id="#">';
                }
                ?>
                    <div class="left_title">
                        <div>
                            <span class="<?= $row->favicon;?>" style="font-size:40px;"></span>
                        </div>
                        <!-- <img alt="" src="<?= base_url('assets/images/icon/'.$row->image.''); ?>" class="img-thumbnail"> -->
                    </div>
                    <div class="right_title">
                        <span class="text-left"><strong><?= lang(''.$row->name.'') ?></strong></span>
                        <p class="desc_title"><?= $row->description ?></p>
                        <?php 
                        if($this->site->activeModule($row->module)){
                            echo '<span class="label label-success"><i class="fa fa-check"></i>'.lang("actived").'</span>';
                        }else{
                            echo '<span class="label label-danger"><i class="fa fa-times"></i> '.lang("setup").'</span>';
                        }
                        ?>
                    </div>
                    <div class="clearfix"></div>
                </a>
            </div>
            <?php
            }
            ?>
            <div class="clearfix"></div>
        </div>
    </div>

<script type="text/javascript">
    $(".buy").click(function() {
        alert('We are sorry, This module is not available. Please buy it from SBC Solutions to install this module');
    });
    $('.module').click(function() {
        var modules = $(this).attr('id');
        $.ajax({
            type: "get",
            url: "<?= admin_url('welcome/checking_module/'); ?>" + modules,
            data: {
                modules: modules
            },
            success: function(data) {
                if(data =='ecommerce'){
                    window.location.href = site.base_url;
                }else{
                    window.location.href = site.base_url + data;
                }
            }
        });
        return false;
    });
</script>


<?php if (($Owner || $Admin) && $chatData) { ?>
    <style type="text/css" media="screen">
        .tooltip-inner {
            max-width: 500px;
        }
    </style>
<?php } ?>
