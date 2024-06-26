<?php

function misc_view() {

    $locale = fusion_get_locale();
    $settings = fusion_get_settings();

    echo openform('settingsFrm', 'POST');

    openside();
    echo form_checkbox('tinymce_enabled', $locale['admins_662'], $settings['tinymce_enabled'], ['toggle' => TRUE]);
    closeside();

    echo '<h6>Email Server</h6>';
    openside('Email Settings<small>Server email configurations</small>', TRUE);
    echo form_text('smtp_host', $locale['admins_664'], $settings['smtp_host'], [
        'max_length' => 200,
    ]);
    echo form_text('smtp_port', $locale['admins_674'], $settings['smtp_port'], [
        'max_length' => 10,
    ]);

    echo form_checkbox('smtp_auth', $locale['admins_698'], $settings['smtp_auth'],
//        'ext_tip' => $locale['admins_665'],
        array('toggle' => TRUE)
    );

    echo '<div id="a_smtp_auth" style="display:' . ($settings['smtp_auth'] ? 'block' : 'none') . ';">';
    echo form_text('smtp_username', $locale['admins_666'], $settings['smtp_username'], [
        'max_length' => 100,
    ]);
    echo form_text('smtp_password', $locale['admins_667'], $settings['smtp_password'], [
        'max_length' => 100,
    ]);
    echo '</div>';

    closeside();

    echo '<h6>Post, Comments & Ratings</h6>';
    openside();
    echo form_checkbox('guestposts', $locale['admins_655'], $settings['guestposts'], array('toggle' => TRUE));
    closeside();

    openside();
    echo form_checkbox('comments_enabled', $locale['admins_671'], $settings['comments_enabled'], ['toggle' => TRUE]);
    echo '<div id="a_comments_enabled" style="display:' . ($settings['comments_enabled'] ? 'block' : 'none') . ';">';
    echo form_text('comments_per_page', $locale['admins_913'], $settings['comments_per_page'], [
        'error_text' => $locale['error_value'],
        'type' => 'number',
        'inner_width' => '150px',
    ]);
    echo form_checkbox('comments_sorting', $locale['admins_684'], $settings['comments_sorting'], [
        //'inline'  => TRUE,
        'options' => ['ASC' => $locale['admins_685'], 'DESC' => $locale['admins_686']],
        'type' => 'radio',
    ]);
    echo form_checkbox('comments_avatar', $locale['admins_656'], $settings['comments_avatar'], ['toggle' => TRUE]);
    echo '</div>';
    closeside();

    openside();
    echo form_checkbox('ratings_enabled', $locale['admins_672'], $settings['ratings_enabled'], ['toggle' => TRUE]);
    closeside();

    openside('Image Handling<small>Image compression configurations</small>', TRUE);
    echo form_select('thumb_compression', $locale['admins_606'], $settings['thumb_compression'], [
        'options' => ['gd1' => $locale['admins_607'], 'gd2' => $locale['admins_608']],
        'width' => '100%',
    ]);
    closeside();

    openside();
    echo form_checkbox('index_url_bbcode', $locale['admins_1031'], $settings['index_url_bbcode'], ['toggle' => TRUE]);
    closeside();

    openside();
    echo form_checkbox('index_url_userweb', $locale['admins_1032'], $settings['index_url_userweb'], ['toggle' => TRUE]);
    closeside();

    openside();
    echo form_checkbox('create_og_tags', $locale['admins_1030'], $settings['create_og_tags'], ['toggle' => TRUE]);
    closeside();

    openside();
    echo form_checkbox('rendertime_enabled', $locale['admins_688'], $settings['rendertime_enabled'], [
        'options' => ['0' => $locale['no'], '1' => $locale['admins_689'], '2' => $locale['admins_690']],
        'type' => 'radio',
    ]);
    closeside();

    openside();
    echo form_checkbox('visitorcounter_enabled', $locale['admins_679'], $settings['visitorcounter_enabled'], ['toggle' => TRUE]);
    closeside();

    openside();
    echo form_checkbox('devmode', $locale['admins_609'], $settings['devmode'], ['toggle' => TRUE]);
    closeside();

    openside();
    echo form_checkbox('update_checker', $locale['admins_610'], $settings['update_checker'], ['toggle' => TRUE]);
    closeside();
    openside('');
    $options = [
        '.' => '.',
        ',' => ',',
    ];
    echo form_select('number_delimiter', $locale['admins_611'], $settings['number_delimiter'], [
        'options' => $options,
        'width' => '100%',
    ]);
    echo form_select('thousands_separator', $locale['admins_612'], $settings['thousands_separator'], [
        'options' => $options,
        'width' => '100%',
    ]);
    closeside();

    echo '<noscript>';
    echo '<div class="spacer-sm">';
    misc_button();
    echo '</div>';
    echo '</noscript>';

    echo closeform();
}

function misc_button() {

    $locale = fusion_get_locale();

    echo form_button('savesettings', $locale['admins_750'], $locale['admins_750'], ['class' => 'btn-primary']);
}

function pf_js() {
    return "checkboxToggle('comments_enabled', 'a_comments_enabled');
    checkboxToggle('smtp_auth', 'a_smtp_auth');
    ";
}
