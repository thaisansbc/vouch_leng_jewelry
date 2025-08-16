<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <script type="text/javascript">
        var base_url = '';
        var url_base = '<?php echo base_url(); ?>'; 
    </script>

    <?php
        $bg_teeth3 = "assets/uploads/mix_tooth.jpg";
        $bg_teeth2 = "assets/uploads/mix_tooth.jpg";
        $bg_teeth1 = "assets/uploads/mix_tooth.jpg";
    ?>
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> -->
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/wpain/lib/jquery.1.10.2.min.js"></script>

    <!-- jQuery UI -->
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/wpain/lib/jquery.ui.core.1.10.3.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/wpain/lib/jquery.ui.widget.1.10.3.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/wpain/lib/jquery.ui.mouse.1.10.3.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/wpain/lib/jquery.ui.draggable.1.10.3.min.js"></script>

    <!-- wColorPicker -->
    <link rel="Stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/wpain/lib/wColorPicker.min.css" />
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/wpain/lib/wColorPicker.min.js"></script>

    <!-- wPaint -->
    <link rel="Stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/wpain/wPaint.min.css"/>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/wpain/wPaint.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/wpain/plugins/main/wPaint.menu.main.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/wpain/plugins/text/wPaint.menu.text.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/wpain/plugins/shapes/wPaint.menu.main.shapes.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/wpain/plugins/file/wPaint.menu.main.file.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function(){ 
            var str = '<?php echo $draw_image->image; ?>';
            var teeth_model = str.toString().split("-")[0];
            if (teeth_model == "Adult") {    
                document.getElementById("optradio1").checked = true;
                document.getElementById("optradio2").checked = false;
                            
                $("#loption1").addClass("label label-primary");
                $("#loption2").removeClass("label label-success");
                $(".obj_img").css({
                    "background-image": "url(<?php echo base_url() . $bg_teeth1 ?>)"
                });
            } else if (teeth_model == "Child") {                    
                document.getElementById("optradio1").checked = false;
                document.getElementById("optradio2").checked = true;
                            
                $("#loption1").removeClass("label label-primary");
                $("#loption2").addClass("label label-success");
                $(".obj_img").css({
                    "background-image": "url(<?php echo base_url() . $bg_teeth2 ?>)"
                });
            } 
        });
    </script>

</head>
<body>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_draw'); ?></h4>
        </div>
        <?php $attrib = ['class' => 'form-horizontal', 'data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('sales/add_draw/' . $inv->id, $attrib); ?>
        <div class="modal-body">
            <div id="content" style="background-color: white;">
                <div class="content-box" id="content-box">
                    <div id="wPaint" class="obj_img img-responsive" style="background-image: url(<?php echo base_url() . $bg_teeth1 ?>); position:relative; width:850px; height:250px; margin:70px auto 20px auto; background-size:contain; background-repeat: no-repeat; vertical-align: middle; line-height: 0; display: block;">
                    </div>
                    <br>
                    <center id="wPaint-img"></center>

                    <input type="hidden" id="siteurl" value="<?php echo base_url(); ?>">
                    <input type="hidden" id="bill_id" value="<?= $inv->id ?>" name="bill_id">
                    <input type="hidden" id="get_img" value="<?= $draw_image->image ?>" name="get_img">
                    <input type="hidden" id="patient_id" value="<?= $inv->customer_id ?>" name="patient_id">
                    <input type="hidden" id="teeth_model" value="teeth_model1" name="teeth_model">
                    <img src="#" id="draw_img" name="draw_img" width="200px;" height="200px;" style="display: none;">
                    <script type="text/javascript" charset="utf-8">
                        $(document).ready(function(){
                            var images = [url_base + 'assets/uploads/wpain/'];
                            function  saveImg(image) {
                          
                                var sale_id = $("#bill_id").val();
                                var teeth_model = $("#teeth_model").val();
                                var customer_id = $("#patient_id").val();

                                $.ajax({
                                    url: "<?= base_url();?>admin/sales/wpain_upload",
                                    type: 'POST',
                                    data: {     
                                        <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>',
                                        image: image,
                                        sale_id:sale_id,
                                        teeth_model:teeth_model,
                                        customer_id:customer_id
                                    },
                                    success: function (data) {
                                        alert(data);
                                        location.reload();
                                    },
                                    error: function (data) {
                                        alert('fails');
                                    }
                                });
                                return false;
                            }

                            function loadImgBg() {
                                var get_img = [images + $("input[name='get_img']").val()];
                                this._showFileModal('bg', get_img);
                            }

                            function loadImgFg() {
                                var get_img = [images + $("input[name='get_img']").val()];
                                this._showFileModal('fg', get_img);
                            }

                            // init wPaint
                            $('#wPaint').wPaint({
                                menuOffsetLeft: -35,
                                menuOffsetTop: -50,
                                saveImg: saveImg,
                                loadImgBg: loadImgBg,
                                loadImgFg: loadImgFg
                            });
                        });
                
                    </script>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <div class="text-left">
                <label onclick="" id="loption1" class="radio-inline" style="font-size: 12px;">
                    <input id="optradio1" type="checkbox" name="optradio1" value="teeth_model1"> Adult Teeth
                </label>
                <label id="loption2" class="radio-inline" style="font-size: 12px;">
                    <input id="optradio2" type="checkbox" name="optradio1" value="teeth_model2"> Child Teeth
                </label>
                <label id="loption3" class="radio-inline" style="font-size: 12px;">
                    <input id="optradio3" type="checkbox" name="optradio3" value="teeth_model3"> Adult&Child Teeth
                </label>

                 <button style="float: right; " type="button" class="btn btn-danger" id="close_draw">Close</button>
            </div>

            <script>
                $(document).ready(function() {
                    var plan_adult = document.getElementById("plan_adult");
                    var plan_child = document.getElementById("plan_child");

                    $("#loption1").click(function() {
                        if (plan_adult.checked == true) {
                            plan_child.checked = false;
                            $("#teeth_model").val("teeth_model1");
                            $("#loption2").removeClass("label label-success");
                            $(this).addClass("label label-primary");
                            $(".obj_img").css({
                                    "background-image": "url(<?php echo base_url() . $bg_teeth1 ?>)"
                            });
                        }
                    });

                    $("#loption2").click(function() {
                        if (plan_child.checked == true) {
                            plan_adult.checked = false;
                                
                            $("#loption1").removeClass("label label-primary");
                            $(this).addClass("label label-success");
                            $("#teeth_model").val("teeth_model2");
                            $(".obj_img").css({
                                    "background-image": "url(<?php echo base_url() . $bg_teeth2 ?>)"
                            });
                        }
                    });

                    // $('.wPaint-menu-icon-img').css('background-image', 'url(http://192.168.0.253:81/erp/assets/wpain/plugins/main/img/icons-menu-main.png)');
                   
                    $("#close_draw").click(function() {
                        location.reload();
                    });
                });
            </script>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
</body>