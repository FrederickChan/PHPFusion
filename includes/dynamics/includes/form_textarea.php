<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: form_textarea.php
| Author: Frederick MC Chan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

/**
 * @param string $input_name
 * @param string $label
 * @param string $input_value
 * @param array $options
 *
 * @return string
 */
function form_textarea($input_name, $label = '', $input_value = '', array $options = []) {

    $locale = fusion_get_locale('',
        [
            LOCALE . LOCALESET . "admin/html_buttons.php",
            LOCALE . LOCALESET . "error.php",
        ]);

    require_once INCLUDES . "bbcode_include.php";
    require_once INCLUDES . "html_buttons_include.php";

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    if (!empty($options['bbcode'])) {
        $options['type'] = "bbcode";
    } else if (!empty($options['html'])) {
        $options['type'] = "html";
    }

    $options += [
        "input_id" => clean_input_id($input_name),
        "input_name" => clean_input_name($input_name),
        "type" => "",
        "inline_editing" => FALSE,
        "required" => FALSE,
        "tinymce_forced_root" => TRUE,
        "placeholder" => "",
        "deactivate" => FALSE,
        "width" => "",
        "inner_width" => "100%",
        "height" => "200px",
        "class" => "",
        "inner_class" => "",
        "inline" => FALSE,
        "length" => 200,
        "error_text" => $locale["error_input_default"],
        'add_error_notice' => FALSE,
        'error_text_notice' => '',
        "safemode" => FALSE,
        "form_name" => "input_form",
        "tinymce" => "simple",
        "tinymce_css" => "",
        "tinymce_image" => TRUE, // Turns on or off the image selection feature in TinyMCE
        "no_resize" => FALSE,
        "autosize" => FALSE,
        "bbcode" => FALSE,
        "html" => FALSE,
        "preview" => FALSE,
        "path" => IMAGES,
        "maxlength" => "",
        "tip" => "",
        "ext_tip" => "",
        "input_bbcode" => "",
        "char_count" => TRUE, // it is a character counter, not a word counter
        "file_filter" => [".png", ".PNG", ".svg", ".SVG", ".bmp", ".BMP", ".jpg", ".JPG", ".jpeg", ".gif", ".GIF", ".tiff", ".TIFF"],
        "tinymce_theme" => "silver", // silver|mobile
        "tinymce_skin" => "oxide", // oxide|oxide-dark
        "tinymce_spellcheck" => TRUE,
        "rows" => 5,
        "censor_words" => TRUE,
        "descript" => TRUE,
        "floating_label" => FALSE,
        "template" => "form_inputs",
    ];

    $input_value = clean_input_value($input_value, $input_name);
    if ($input_value) {
        $input_value = htmlspecialchars_decode(html_entity_decode(stripslashes($input_value), ENT_QUOTES, $locale['charset']));
    }

    $options['template_type'] = 'textarea';

    if ($options['type'] == "tinymce") {

        $options['tinymce'] = !empty($options['tinymce']) && in_array($options['tinymce'], [TRUE, 'simple', 'advanced']) ? $options['tinymce'] : "simple";
        $options['tinymce_css'] = (!empty($options['tinymce_css']) && file_exists($options['tinymce_css']) ? $options['tinymce_css'] : '');
        $options['tinymce_spellcheck'] = $options['tinymce_spellcheck'] == TRUE ? 'true' : 'false';

        $tinymce_list = [];
        if (!empty($options['path']) && $options['tinymce_image'] == TRUE) {
            $image_list = [];
            if (is_array($options['path'])) {
                foreach ($options['path'] as $dir) {
                    if (is_file($dir) && is_dir($dir)) {
                        $image_list[$dir] = makefilelist($dir, ".|..|");
                    }
                }
            } else {
                if (is_file($options['path']) && is_dir($options['path'])) {
                    $image_list[$options['path']] = makefilelist($options['path'], '.|..|');
                }
            }
            foreach ($image_list as $key => $images) {
                foreach ($images as $image_name) {
                    $image_1 = explode('.', $image_name);
                    $last_str = count($image_1) - 1;
                    if (in_array("." . $image_1[$last_str], $options['file_filter'])) {
                        $tinymce_list[] = ['title' => $image_name, 'value' => $key . $image_name];
                    }
                }
            }
        }

        $tinymce_list = json_encode($tinymce_list);
        $tinymce_smiley_vars = "";
        if (!defined('tinymce')) {
            add_to_head('<script src="' . INCLUDES . 'jquery/jquery-ui/jquery-ui.min.js"></script>');
            add_to_head('<link rel="stylesheet" href="' . INCLUDES . 'jquery/jquery-ui/jquery-ui.min.css">');
            add_to_head('<script src="' . INCLUDES . 'elFinder/js/elfinder.min.js"></script>');
            add_to_head('<link rel="stylesheet" href="' . INCLUDES . 'elFinder/css/elfinder.min.css">');
            add_to_head('<link rel="stylesheet" href="' . INCLUDES . 'elFinder/css/theme.css">');
            add_to_head("<script src='" . INCLUDES . "jscripts/tinymce5/tinymce.min.js'></script>");
            add_to_head("<script src='" . INCLUDES . "elFinder/js/tinymceElfinder.min.js'></script>");

            add_to_jquery('
                const mceElf = new tinymceElfinder({
                    // connector URL (Set your connector)
                    url: "' . fusion_get_settings('siteurl') . 'includes/elFinder/php/connector.php' . fusion_get_aidlink() . '",
                    // upload target folder hash for this tinyMCE
                    uploadTargetHash: "l1_lw", // Hash value on elFinder of writable folder
                    // elFinder dialog node id
                    nodeId: "elfinder", // Any ID you decide
                        ui: ["toolbar", "tree", "path", "stat"],
                        uiOptions: {
                            toolbar: [
                                ["home", "back", "forward", "up", "reload"],
                                ["mkdir", "mkfile", "upload"],
                                ["open"],
                                ["copy", "cut", "paste", "rm", "empty"],
                                ["duplicate", "rename", "edit", "resize", "chmod"],
                                ["quicklook", "info"],
                                ["extract", "archive"],
                                ["search"],
                                ["view", "sort"],
                                ["preference", "help"]
                            ]
                        }
                });
            ');

            define('tinymce', TRUE);
            // PHPFusion Parse Cache Smileys
            $smileys = cache_smileys();
            $tinymce_smiley_vars = "";
            if (!empty($smileys)) {
                $tinymce_smiley_vars = "var shortcuts = {\n";
                foreach ($smileys as $params) {
                    $tinymce_smiley_vars .= "'" . strtolower($params['smiley_code']) . "' : '<img alt=\"" . $params['smiley_text'] . "\" src=\"" . IMAGES . "smiley/" . $params['smiley_image'] . "\"/>',\n";
                }
                $tinymce_smiley_vars .= "};\n";
                $tinymce_smiley_vars .= "
                ed.on('keyup', function(e){
                    var marker = tinymce.activeEditor.selection.getBookmark();
                    // Store editor contents
                    var content = tinymce.activeEditor.getContent({'format':'raw'});
                    // Loop through all shortcuts
                    for(var key in shortcuts){
                        // Check if the editor html contains the looped shortcut
                        if(content.toLowerCase().indexOf(key) != -1) {
                            // Escaping special characters to be able to use the shortcuts in regular expression
                            var k = key.replace(/[<>*()?']/ig, \"\\$&\");
                            tinymce.activeEditor.setContent(content.replace(k, shortcuts[key]));
                        }
                    }
                    // Now put cursor back where it was
                    tinymce.activeEditor.selection.moveToBookmark(marker);
                });
                ";
            }
        }

        $images = '';
        if ($options['tinymce_image']) {
            $images = "file_picker_callback : mceElf.browser,";
        }

        $tinymce_lang = '';
        if (file_exists(LOCALE . LOCALESET . "includes/jscripts/tinymce/langs/" . $locale['tinymce'] . ".js")) {
            $tinymce_lang = "language:'" . $locale['tinymce'] . "',
            language_url: '" . LOCALE . LOCALESET . "includes/jscripts/tinymce/langs/" . $locale['tinymce'] . ".js',";
        }

        // Mode switching for TinyMCE
        switch ($options['tinymce']) {
            case 'advanced':
                add_to_jquery("
                tinymce.init({
                    " . $images . "
                    relative_urls: false,
                    remove_script_host: false,
                    selector: '#" . $options['input_id'] . "',
                    inline: " . ($options['inline_editing'] == TRUE ? "true" : "false") . ",
                    theme: '" . $options['tinymce_theme'] . "',
                    skin: '" . (defined('TINYMCE_SKIN') ? TINYMCE_SKIN : $options['tinymce_skin']) . "',
                    " . (defined('TINYMCE_SKIN_PATH') ? "skin_url: '" . TINYMCE_SKIN_PATH . "', " : '') . "
                    browser_spellcheck: " . $options['tinymce_spellcheck'] . ",
                    entity_encoding: 'raw',
                    " . $tinymce_lang . "
                    directionality : '" . $locale['text-direction'] . "',
                    " . ($options['tinymce_forced_root'] ? "forced_root_block: ''," : '') . "
                    width: '100%',
                    height: 300,
                    plugins: [
                        'advlist autolink " . ($options['autosize'] ? " autoresize " : "") . " link image lists charmap print preview hr anchor pagebreak',
                        'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
                        'save table directionality template paste " . ($options['inline_editing'] ? " save " : "") . "'
                    ],
                    image_list: $tinymce_list,
                    " . (!empty($options['tinymce_css'] ? "content_css: '" . $options['tinymce_css'] . "'," : '')) . "
                    toolbar1: '" . ($options['inline_editing'] ? " save " : "") . " insertfile undo redo | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | newdocument fullscreen preview cut copy paste pastetext searchreplace code',
                    toolbar2: 'styleselect formatselect removeformat | fontselect fontsizeselect bold italic underline strikethrough subscript superscript blockquote | forecolor backcolor',
                    toolbar3: 'hr pagebreak insertdatetime | link unlink anchor | image media | table charmap visualchars visualblocks emoticons',
                    image_advtab: true,
                    style_formats: [
                        {title: 'Bold text', inline: 'b'},
                        {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
                        {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
                        {title: 'Example 1', inline: 'span', classes: 'example1'},
                        {title: 'Example 2', inline: 'span', classes: 'example2'},
                        {title: 'Table styles'},
                        {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
                    ],
                    setup: function(ed) {
                        // add tabkey listener
                        ed.on('keydown', function(event) {
                            if (event.keyCode == 9) { // tab pressed
                                if (event.shiftKey) { ed.execCommand('Outdent'); } else { ed.execCommand('Indent'); }
                                event.preventDefault();
                                return false;
                            }
                        });
                        // auto smileys parsing
                        " . $tinymce_smiley_vars . "
                    }
                });
                ");
                break;
            case 'simple':
                add_to_jquery("
                tinymce.init({
                    " . $images . "
                    relative_urls: false,
                    remove_script_host: false,
                    selector: '#" . $options['input_id'] . "',
                    inline: " . ($options['inline_editing'] == TRUE ? "true" : "false") . ",
                    theme: '" . $options['tinymce_theme'] . "',
                    skin: '" . (defined('TINYMCE_SKIN') ? TINYMCE_SKIN : $options['tinymce_skin']) . "',
                    " . (defined('TINYMCE_SKIN_PATH') ? "skin_url: '" . TINYMCE_SKIN_PATH . "', " : '') . "
                    browser_spellcheck: " . $options['tinymce_spellcheck'] . ",
                    entity_encoding: 'raw',
                    menubar: false,
                    statusbar: false,
                    content_css: '" . $options['tinymce_css'] . "',
                    image_list: $tinymce_list,
                    plugins: [
                        'advlist autolink " . ($options['autosize'] ? " autoresize " : "") . " link image lists charmap print preview hr anchor pagebreak',
                        'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
                        'directionality template paste" . ($options['bbcode'] ? " bbcode " : "") . ($options['autosize'] ? " autoresize " : "") . ($options['inline_editing'] ? " save " : "") . "'
                    ],
                    width: '100%',
                    height: 100,
                    image_advtab: true,
                    toolbar1: 'undo redo | bold italic underline | emoticons | visualblocks | bullist numlist blockquote | hr " . ($options['tinymce_image'] ? " image " : "") . " | fullscreen " . ($options['inline_editing'] ? " save " : "") . " | code',
                    language: '" . $locale['tinymce'] . "',
                    directionality : '" . $locale['text-direction'] . "',
                    " . ($options['tinymce_forced_root'] ? "forced_root_block: ''," : '') . "
                    object_resizing: " . ($options['autosize'] ? "false" : "true") . ",
                    resize: " . ($options['autosize'] ? "false" : "true") . ",
                    setup: function(ed) {
                        // add tabkey listener
                        ed.on('keydown', function(event) {
                            if (event.keyCode == 9) { // tab pressed
                                if (event.shiftKey) { ed.execCommand('Outdent'); } else { ed.execCommand('Indent'); }
                                event.preventDefault();
                                return false;
                            }
                        });
                        // auto smileys parsing
                        " . $tinymce_smiley_vars . "
                    }
                });

                $('#inject').bind('click', function () {
                    tinyMCE.activeEditor.execCommand(\"mceInsertContent\", true, '[b]I am injecting in stuff..[/b]');
                });
                ");
                break;
            case 'default':
                add_to_jquery("
                tinymce.init({
                    " . $images . "
                    relative_urls: false,
                    remove_script_host: false,
                    selector: '#" . $options['input_id'] . "',
                    inline: " . ($options['inline_editing'] == TRUE ? "true" : "false") . ",
                    content_css: '" . $options['tinymce_css'] . "',
                    theme: '" . $options['tinymce_theme'] . "',
                    skin: '" . (defined('TINYMCE_SKIN') ? TINYMCE_SKIN : $options['tinymce_skin']) . "',
                    " . (defined('TINYMCE_SKIN_PATH') ? "skin_url: '" . TINYMCE_SKIN_PATH . "', " : '') . "
                    browser_spellcheck: " . $options['tinymce_spellcheck'] . ",
                    entity_encoding: 'raw',
                    " . $tinymce_lang . "
                    directionality : '" . $locale['text-direction'] . "',
                    " . ($options['tinymce_forced_root'] ? "forced_root_block: ''," : '') . "
                    setup: function(ed) {
                        // add tabkey listener
                        ed.on('keydown', function(event) {
                            if (event.keyCode == 9) { // tab pressed
                                if (event.shiftKey) { ed.execCommand('Outdent'); } else { ed.execCommand('Indent'); }
                                event.preventDefault();
                                return false;
                            }
                        });
                        // auto smileys parsing
                        " . $tinymce_smiley_vars . "
                    }
                });
                ");
                break;
        }

    } else {

        if ($options['type'] == 'bbcode' || $options['bbcode']) {
            fusion_load_script(INCLUDES . 'jquery/texteditor.min.js');

            if (!defined('TT_BBCODE')) {
                define('TT_BBCODE', TRUE);
                add_to_footer('<script src="' . INCLUDES . 'jscripts/bbcode.min.js" defer></script>');
            }
        }

        if ($options['bbcode']) {
            $options['type'] = 'bbcode';
        } else if ($options['html']) {
            $options['type'] = 'html';
        }

        if ($options['autosize'] || defined('AUTOSIZE')) {
            fusion_load_script(DYNAMICS . 'assets/autosize/autosize.js');
            add_to_jquery("autosize($('#" . $options['input_id'] . "'));");
        }
    }

    [$options['error_class'], $options['error_text']] = form_errors($options);

    if ($options['type'] == 'html' or $options['type'] == 'bbcode') {

        if ($options['preview']) {
            add_to_jquery("
            $(document).on('click', '[data-action=\"preview\"]', function(e) {
            e.preventDefault();                
            let preview_tab = $('#" . $options['input_id'] . "_preview'),
            editor_tab = $('#" . $options['input_id'] . "_write'),
            placeholder = $(this).find('.preview-response');

            let text = $('#" . $options['input_id'] . "').val(),
            format = '" . ($options['type'] == "bbcode" ? 'bbcode' : 'html') . "',
            data = {
                " . (defined('ADMIN_PANEL') ? "'mode': 'admin', " : "") . "
                'text' : text,
                'editor' : format,
                'url' : '" . $_SERVER['REQUEST_URI'] . "',
                'form_id' : 'prw-" . $options['form_name'] . "',
                'fusion_token' : '" . fusion_get_token("prw-" . $options['form_name'], 30) . "'
            },

            sendData = $(this).closest('form').serialize() + '&' + $.param(data);
            
            $.post('" . FUSION_ROOT . INCLUDES . "api/?api=preview-text', sendData).
            done(function(e) {
                preview_tab.html(e);
            }).error(function(e) {
                alert('" . $locale['error_preview'] . "' + '\\n" . $locale['error_preview_text'] . "');
            });
        });
        ");
        }
    }

    // we do not need form_name any longer, because we can use plugin and Id
    if ($options['type'] == "bbcode" && $options['form_name']) {

        display_smiley_options();

        $options['toolbar'] = '<div class="bbcode_input">' . display_bbcodes('100%', $options['input_id'], $options['form_name'], $options['input_bbcode']) . '</div>';
        $options['toolbar_1'] = ($preview_button ?? '');

    } else if ($options['type'] == "html" && $options['form_name']) {

        // @todo: Develop a new set of wysiwyg html editor.
        $options['toolbar'] = display_html($options['form_name'], $options['input_id'], TRUE, TRUE, TRUE, $options['path']);
        $options['toolbar_1'] = ($preview_button ?? '');
    }

    //    if ($options['inline_editing'] == TRUE) {
    //        $html .= "<div id='" . $options['input_id'] . "' " . ($options['width'] ? "style='display:block; width: " . $options['width'] . ";'" : '') . ">" . $input_value . "</div>\n";
    //    } else {
    //        $html .= "<textarea name='$input_name' style='width: " . $options['inner_width'] . "; height:" . $options['height'] . ";" . ($options['no_resize'] ? ' resize: none;' : '') . "' rows='" . $options['rows'] . "' cols='' class='form-control m-0 " . ($options['inner_class'] ? " " . $options['inner_class'] . " " : '') . ($options['autosize'] ? 'animated-height' : '') . " " . (($options['type'] == "html" || $options['type'] == "bbcode") ? "no-shadow no-border bbr-0" : '') . " textbox'" . ($options['placeholder'] ? " placeholder='" . $options['placeholder'] . "' " : '') . " id='" . $options['input_id'] . "'" . ($options['deactivate'] ? ' readonly' : '') . " " . ($options['maxlength'] ? " maxlength='" . $options['maxlength'] . "'" : '') . ">" . $input_value . "</textarea>\n";
    //    }

    if (($options['type'] == "html" || $options['type'] == "bbcode") && $options['char_count'] != FALSE) {
        // depends on texteditor.js
        add_to_jquery("$('#" . $options['input_id'] . "').charCounter();");
    }

    //    if ((!$options['type'] == "bbcode" && !$options['type'] == "html")) {
    //        $html .= $options['ext_tip'] ? "<span class='tip'><i>" . $options['ext_tip'] . "</i></span>" : "";
    //    }

    set_field_config([
        'input_name' => $input_name,
        'type' => 'textarea',
        'title' => $title,
        'id' => $options['input_id'],
        'required' => $options['required'],
        'safemode' => $options['safemode'],
        'error_text' => $options['error_text'],
        'censor_words' => $options['censor_words'],
        'descript' => $options['descript'],
    ]);

    ksort($options);

    return fusion_get_template($options['template'], [
        "input_name" => $input_name,
        "input_label" => $label,
        "input_value" => $options['priority_value'] ?? $input_value,
        "input_options" => $options,
    ]);

}
