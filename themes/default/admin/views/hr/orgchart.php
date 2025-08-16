<script>
    $(document).ready(function () {

    });
</script>


<?php 
if ($Owner || $Admin || $GP['bulk_actions']) {
    echo admin_form_open('hr/employees_actions', 'id="action-form"');
}

if ( ! function_exists('getAllDepartmentById'))
{
    function getAllDepartmentById($company_id) {
        $CI =&  get_instance();
        $sql = "select * from bpas_hr_departments where biller_id = '".$company_id."'";
        $query = $CI->db->query($sql);
        $result = $query->result();
        return $result;
    }
}

if ( ! function_exists('getAllgroupByID'))
{
    function getAllgroupByID($department_id) {
        $CI =&  get_instance();
        $sql = "select * from bpas_hr_groups where department_id = '".$department_id."'";
        $query = $CI->db->query($sql);
        $result = $query->result();
        return $result;
    }
}
if ( ! function_exists('getAllPosition'))
{
    function getAllPosition($group_id) {
        $CI =&  get_instance();
        $sql = "select * from bpas_hr_positions where group_id= '".$group_id."' ";
        // $CI->db->group_by("d.designation_id");
        $query = $CI->db->query($sql);
        $result = $query->result();
        return $result;
    }
}
?>

<div class="box">
    <div class="box-header">
		<h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('organization_chart').' ('.($biller ? $biller->name : lang('all_billers')).')'; ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?php echo admin_url('hr/add_employee/'); ?>">
                                <i class="fa fa-plus"></i> <?= lang('add_employee') ?>
                            </a>                           
                        </li>
                        <li>
                            <a href="<?php echo admin_url('hr/import_employee/'); ?>">
                                <i class="fa fa-plus"></i> <?= lang('import_employee') ?>
                            </a>                           
                        </li>
						<li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?=lang('export_to_excel')?>
                            </a>
                        </li>
                        
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo"
								title="<b><?=lang("delete_employees")?></b>"
								data-content="<p><?=lang('r_u_sure')?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?=lang('i_m_sure')?></a> <button class='btn bpo-close'><?=lang('no')?></button>"
								data-html="true" data-placement="left">
								<i class="fa fa-trash-o"></i> <?=lang('delete_employees')?>
							</a>
						</li>
                    </ul>
                </li>
				<?php if (!empty($billers) && $this->config->item('one_biller')==false) { ?>
						<li class="dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-industry tip" data-placement="left" title="<?= lang("billers") ?>"></i></a>
							<ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
								<li><a href="<?= admin_url('hr/index') ?>"><i class="fa fa-industry"></i> <?= lang('all_billers') ?></a></li>
								<li class="divider"></li>
								<?php
								foreach ($billers as $biller) {
									echo '<li><a href="' . admin_url('hr/index/'.$biller->id) . '"><i class="fa fa-home"></i>' . $biller->name . '</a></li>';
								}
								?>
							</ul>
						</liv>
				<?php } ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div id="chart-container"></div>
    </div>
</div>

<link rel="stylesheet" href="<?= base_url();?>themes/default/admin/assets/js/orgchart/css/jquery.orgchart.css">
<style type="text/css">
#chart-container {
  position: relative;
  display: inline-block;
  height: 420px;
  width: calc(100% - 24px);
  border: 1px dashed #aaa;
  border-radius: 0px;
  overflow: auto;
  text-align: center;
}
.orgchart {
    background: #fff;
}
</style>
<script type="text/javascript" src="<?= base_url();?>themes/default/admin/assets/js/orgchart/js/html2canvas.min.js"></script>
<script type="text/javascript" src="<?= base_url();?>themes/default/admin/assets/js/orgchart/js/jquery.orgchart.js"></script>

<script type="text/javascript">
    $(function() {
    var datascource = {
      'name': '<?php echo $this->Settings->site_name;?>',
      'title': '<?php echo lang('Limited Company');?>',
      'children': [
      <?php 
      foreach($main_companies as $cr){ 
        
        $type_name = 'Limited Liability Company'; 
          
       ?>
        { 'name': '<?php echo $cr->name;?>', 'title': '<?php echo $type_name;?>',
            <?php $department_chart = getAllDepartmentById($cr->id);?>
            
        'children': [
            <?php 
            foreach($department_chart as $depchart){ ?>
           
      
            { 'name': '<?php echo $depchart->name;?>', 'title': '<?php echo $depchart->description;?>',
                <?php $groups = getAllgroupByID($depchart->id);?>
                'children': [
                  <?php foreach($groups as $group){ 

                    ?>
                    { 'name': '<?php echo $group->name;?>', 'title': '<?php echo $group->description;?>',
                    <?php $positions = getAllPosition($group->id);?>
                    'children': [
                        <?php foreach($positions as $position){ ?>
                        
                        { 'name': '<?php echo $position->name;?>', 'title': '<?php echo $position->description;?>',
                        },
                        
                        <?php }?>
                    ]
                    },
                    <?php }?>
                  ]
            },
            <?php }?>
            ]
        },
        <?php }?>
      ]
      
    };

    $('#chart-container').orgchart({
      'data' : datascource,
      'visibleLevel': 5,
      'nodeContent': 'title',
      'exportButton': true,
      'exportFilename': 'BPAS HR',
      'pan': true,
      'zoom': true,
      'direction': 't2b'
    });

  });
  </script>
