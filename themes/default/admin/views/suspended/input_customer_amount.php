<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<head>
    <meta charset="utf-8">
</head>
<body>
<div class="modal-dialog modal-lg no-modal-header" style="font-size: 11px; width:15%; margin-top: 20px !important;">
    <div class="modal-content">    
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="PeopleQTYModalLabel"><?=lang('How many people?');?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo admin_form_open("table/input_customer_amount", $attrib); ?>
        <div class="modal-body">
            <div class="form-group">
                <!-- <?=lang("people_qty", "people_qty_input");?> -->
                <?php echo form_input('sid', $sid, 'class="form-control hide kb-pad" id="sid"'); ?>
                <?php echo form_input('room', $room, 'class="form-control hide kb-pad" id="room"'); ?>
                <?php echo form_input('room_name', $room_name, 'class="form-control hide kb-pad" id="room_name"'); ?>
                <?php echo form_input('price', $price, 'class="form-control hide kb-pad" id="price"'); ?>
                <?php echo form_input('discount', $discount, 'class="form-control hide kb-pad" id="discount"'); ?>
                <?php echo form_input('people_qty_input', '', 'class="form-control kb-pad" id="people_qty_input"'); ?>
            </div>
        </div>
        <div class="modal-footer">
            <?= form_submit('submit', lang('submit'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>
<script type="text/javascript">
        var cc_key = ('{clear}');
        $(document).ready(function () {
            $('.kb-text').keyboard({
            autoAccept: true,
            alwaysOpen: false,
            openOn: 'focus',
            usePreview: false,
            layout: 'custom',
            //layout: 'qwerty',
            display: {
                'bksp': "\u2190",
                'accept': 'return',
                'default': 'ABC',
                'meta1': '123',
                'meta2': '#+='
            },
            customLayout: {
                'default': [
                    'q w e r t y u i o p {bksp}',
                    'a s d f g h j k l {enter}',
                    '{s} z x c v b n m , . {s}',
                    '{meta1} {space} {cancel} {accept}'
                ],
                'shift': [
                    'Q W E R T Y U I O P {bksp}',
                    'A S D F G H J K L {enter}',
                    '{s} Z X C V B N M / ? {s}',
                    '{meta1} {space} {meta1} {accept}'
                ],
                'meta1': [
                    '1 2 3 4 5 6 7 8 9 0 {bksp}',
                    '- / : ; ( ) \u20ac & @ {enter}',
                    '{meta2} . , ? ! \' " {meta2}',
                    '{default} {space} {default} {accept}'
                ],
                'meta2': [
                    '[ ] { } # % ^ * + = {bksp}',
                    '_ \\ | &lt; &gt; $ \u00a3 \u00a5 {enter}',
                    '{meta1} ~ . , ? ! \' " {meta1}',
                    '{default} {space} {default} {accept}'
                ]
        }
        });
            $('.kb-pad').keyboard({
                restrictInput: true,
                preventPaste: true,
                autoAccept: true,
                alwaysOpen: false,
                openOn: 'click',
                usePreview: false,
                layout: 'custom',
                display: {
                    'b': '\u2190:Backspace',
                },
                customLayout: {
                    'default': [
                        '1 2 3 {b}',
                        '4 5 6 . ' + cc_key,
                        '7 8 9 0',
                        '{accept} {cancel}'
                    ]
                }
        });
            localStorage.clear();
        });
    </script>
</body>