
    <div class="container" id="container">
        <div class="row" id="main-con">
        <table class="lt"><tr><td class="sidebar-con <?= $page_view?'hide':'';?>">
            <div id="sidebar-left">
                <div class="sidebar-nav nav-collapse collapse navbar-collapse" id="sidebar_menu">
                    <ul class="nav main-menu">
                        <div class="text-center" style="border-bottom:1px solid #cccccc;padding-bottom:5px; ">
                            <a href="<?= admin_url() ?>">
                        <?php if ($Settings->logo) {
                            echo '<img width="100%" src="' . base_url('assets/uploads/logos/' . $Settings->logo) . '" alt="' . $Settings->site_name . '" style="margin-bottom:0px;" />';
                        } ?>
                                <li class="mm_welcome">
                                    <span class="title"><?= $Settings->site_name; ?></span>                           
                                </li>
                            </a>
                        </div>
                        <?php
                        $session_module = $this->session->userdata('module');

                        $this->db->select($this->db->dbprefix('menu').".*")->from('menu')
                        ->where(array('status' => 1,'parent_id'=>0));
                        $this->db->order_by('order_number', 'asc');
                       $this->db->where('module', $session_module);
                        $q = $this->db->get();
                        foreach (($q->result()) as $row) {
                            $parent_id = $row->id;

                            if ($Owner || $Admin || $GP[$row->permission]) { 
                            ?>
                            <li class="mm_<?= $row->selected_name;?>">
                                <a class="<?= ($row->slug == '#')? 'dropmenu':''; ?>"  href="<?= admin_url(''.$row->slug.''); ?>">
                                        <i class="<?= $row->icon;?>"></i>
                                        <span class="text"> <?= lang(''.$row->name.''); ?> </span>
                                        <?= ($row->slug == '#')? '<span class="chevron closed"></span>':''; ?>
                                    </a>
                                <ul>
                                    <?php
                                    $this->db->select($this->db->dbprefix('menu').".*")->from('menu')
                                    ->where(array('status' => 1,'parent_id'=>$parent_id));
                                    $this->db->where('module', $session_module);
                                    
                                    $q = $this->db->get();
                                    foreach (($q->result()) as $row) {
                                    ?>
                                    <li id="<?= $row->selected_name;?>">
                                        <a class="submenu" href="<?= admin_url(''.$row->slug.''); ?>" <?php echo ($row->is_modal) ? 'data-toggle="modal" data-backdrop="static" data-target="#myModal"' : '';?>>
                                                <i class="<?= $row->icon;?>"></i>
                                                <span class="text"> <?= lang(''.$row->name.''); ?></span>
                                            </a>
                                    </li>
                                    <?php 
                                    }
                                    ?>
                                </ul>
                            </li>
                        <?php 
                            }
                        }
                        ?>
                    </ul>
                </div>
                <a href="#" id="main-menu-act" class="full visible-md visible-lg">
                    <i class="fa fa-angle-double-left"></i>
                </a>
            </div>
            </td>
            <td class="content-con">
            <div id="content">
                
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($message) {
                                ?>
                            <div class="alert alert-success">
                                <button data-dismiss="alert" class="close" type="button">×</button>
                                <?= $message; ?>
                            </div>
                        <?php
                            } ?>
                        <?php if ($error) {
                                ?>
                            <div class="alert alert-danger">
                                <button data-dismiss="alert" class="close" type="button">×</button>
                                <?= $error; ?>
                            </div>
                        <?php
                            } ?>
                        <?php if ($warning) {
                                ?>
                            <div class="alert alert-warning">
                                <button data-dismiss="alert" class="close" type="button">×</button>
                                <?= $warning; ?>
                            </div>
                        <?php
                            } ?>
                        <?php
                        if ($info) {
                            foreach ($info as $n) {
                                if (!$this->session->userdata('hidden' . $n->id)) {
                                    ?>
                                    <div class="alert alert-info">
                                        <a href="#" id="<?= $n->id ?>" class="close hideComment external"
                                           data-dismiss="alert">&times;</a>
                                        <?= $n->comment; ?>
                                    </div>
                                <?php
                                }
                            }
                        } ?>
                        <div class="alerts-con"></div>
