<!DOCTYPE html>
<html lang="en">

<head>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
    
    <link href="<?php echo base_url(); ?>themes/default/admin/assets/kanban/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="<?php echo base_url(); ?>themes/default/admin/assets/css/bootstrap_limitless.min.css" rel="stylesheet" type="text/css"> 
</head>

<body>
    <div class="row">
        <div class="col-md-12">
        <div class="col-md-4"> &nbsp;</div>
        <div class="col-md-3">
            <div class="text-center">
                <img class="mt-3" src="<?= base_url('assets/uploads/logos/' . $Settings->logo)  ?>" height="100">
                <img src="<?= base_url() . 'assets/qr_code/' . $file_name; ?>">
                
            </div> 
        </div>
        </div>
    </div>
</body>

</html>