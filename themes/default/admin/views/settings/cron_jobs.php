<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cog"></i><?= lang('cron_jobs'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
           
            </ul>
        </div>
    </div>
    <div class="box-content">
        <?php if (!DEMO) {?>
		<div class="alert alert-info" role="alert">
			<p>
				<a class="btn btn-primary btn-xs pull-right" target="_blank" href="<?= admin_url('cron/run'); ?>"><?= lang('run_manual_now')?></a>
				<p><strong>Cron Job</strong> (run at 1:00 AM daily):</p>
				<pre>0 1 * * * wget -qO- <?= admin_url('cron/run'); ?> &gt;/dev/null 2&gt;&amp;1</pre>
				OR
				<pre>0 1 * * * <?= (defined('PHP_BINDIR') ? PHP_BINDIR . DIRECTORY_SEPARATOR : '') . 'php ' . FCPATH . SELF . ' admin/cron run'; ?> >/dev/null 2>&1</pre>
				For CLI: <code>schedule path/to/php path/to/index.php controller method</code>
			</p>
		</div>
		<div class="alert alert-info" role="alert">
			<p>
				<a class="btn btn-primary btn-xs pull-right" target="" href="<?= admin_url('cron/alert_to_telegram'); ?>" style="margin-right: 10px;"><?= lang('run_manual_now')?> </a>
				<p><strong>Cron Job alert to telegram</strong> (run at 1:00 AM daily):</p>
				<pre>0 1 * * * wget -qO- <?= admin_url('cron/run'); ?> &gt;/dev/null 2&gt;&amp;1</pre>
				
			</p>
		</div>
		<div class="alert alert-info" role="alert">
			<p>
				<a class="btn btn-primary btn-xs pull-right" target="" href="<?= admin_url('cron/chipmong_daily'); ?>" style="margin-right: 10px;"><?= lang('run_manual_now')?></a>
				<p><strong>Cron Job Chipmong</strong> (run at 1:00 AM daily):</p>
				<pre>0 1 * * * wget -qO- <?= admin_url('cron/chipmong_daily/1'); ?> &gt;/dev/null 2&gt;&amp;1</pre>
				
			</p>
		</div>
        <?php } ?>
    </div>
</div>