<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog  modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_schedule'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('calendar/edit_schedule/'.$schedule->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('start', 'start'); ?>
                        <?= form_input('start',$this->bpas->hrld($schedule->start), 'class="form-control datetime" id="start" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('end', 'end'); ?>
                        <?= form_input('end', $this->bpas->hrld($schedule->end), 'class="form-control datetime" id="end" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('title', 'title'); ?>
                        <?= form_input('title', $schedule->title, 'class="form-control tip" id="title" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?= lang('status', 'status'); ?>
                        <?php $status = [
                            'pending' => lang('pending'),
                            'expired' => lang('expired')
                        ]; ?>
                        <?= form_dropdown('status', $status,$schedule->status, 'class="form-control tip" id="status" required="required"'); ?>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group all">
                        <?= lang('product_image', 'product_image') ?>
                        <input id="product_image" type="file" data-browse-label="<?= lang('browse'); ?>" name="product_image" data-show-upload="false" data-show-preview="true" accept="image/*" class="form-control file">
                    </div>
                </div>

            </div>

            <div class="form-group">
                <?= lang('description', 'description'); ?>
                <textarea class="form-control" id="description" name="description">
                <?= $this->bpas->decode_html($schedule->description);?>
                </textarea>
            </div>
            
        </div>
        <div class="modal-footer">
            <?php echo form_submit('edit_schedule', lang('edit_schedule'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['bpas'] = <?= $dp_lang ?>;
</script>
<?= $modal_js ?>
<script type="text/javascript" charset="UTF-8">

const compressImage = async (file, { quality = 1, type = file.type }) => {
        // Get as image data
        const imageBitmap = await createImageBitmap(file);

        const maxWidth = 400; // Maximum width
        const imageAspectRatio = imageBitmap.width / imageBitmap.height; // Calculate aspect ratio

        // Calculate new height based on the aspect ratio and maximum width
        const newWidth = Math.min(maxWidth, imageBitmap.width);
        const newHeight = Math.floor(newWidth / imageAspectRatio);

        // Draw the resized image to a canvas
        const canvas = document.createElement('canvas');
        canvas.width = newWidth;
        canvas.height = newHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(imageBitmap, 0, 0, newWidth, newHeight);

        // Turn into Blob
        const blob = await new Promise((resolve) =>
            canvas.toBlob(resolve, type, quality)
        );

        // Turn Blob into File
        return new File([blob], file.name, {
            type: blob.type,
        });
    };

    // Get the selected file from the file input
    const input = document.getElementById('product_image');
    input.addEventListener('change', async (e) => {
        // Get the files
        const { files } = e.target;

        // No files selected
        if (!files.length) return;

        // We'll store the files in this data transfer object
        const dataTransfer = new DataTransfer();

        // For every file in the files list
        for (const file of files) {
            // We don't have to compress files that aren't images
            if (!file.type.startsWith('image')) {
                // Ignore this file, but do add it to our result
                dataTransfer.items.add(file);
                continue;
            }

            // We compress the file by 50%
            const compressedFile = await compressImage(file, {
                quality: 1,
                type: 'image/jpeg',
            });

            // Save back the compressed file instead of the original file
            dataTransfer.items.add(compressedFile);
        }

        // Set value of the file input to our new files list
        e.target.files = dataTransfer.files;
    });
    $(document).ready(function() {
        $.fn.datetimepicker.dates['bpas'] = <?= $dp_lang ?>;
        $("#date").datetimepicker({
            format: site.dateFormats.js_ldate,
            fontAwesome: true,
            language: 'bpas',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            forceParse: 0
        }).datetimepicker('update', new Date());
    });
</script>
