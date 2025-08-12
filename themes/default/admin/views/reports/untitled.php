
$this->data['projects']         = $this->site->getAllProject();

<div class="col-md-4">
    <div class="form-group">
        <?= lang("project", "poproject"); ?>
        <?php
        $pro[""] = "";
        foreach ($projects as $project) {
            $pro[$project->project_id] = $project->project_name;
        }
        echo form_dropdown('project', $pro, (isset($_POST['project']) ? $_POST['project'] : ''), 'id="poproject" data-placeholder="' . lang("select") . ' ' . lang("project") . '" class="form-control input-tip select" style="width:100%;"');
        ?>
    </div>
</div>