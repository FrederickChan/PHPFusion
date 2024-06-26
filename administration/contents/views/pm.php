<?php

function pm_view() {

    $locale = fusion_get_locale();
    $settings = fusion_get_settings();

    echo openform('delFrm', 'POST') . closeform();

    echo openform('settingsFrm', 'POST');
    echo '<h6>Private Messages</h6>';
    openside('Private Message System<small>Private messaging system configurations</small>', TRUE);
    echo form_text('pm_inbox_limit',
        $locale['admins_701'] . '<small>' . $locale['admins_704'] . '</small>',
        $settings['pm_inbox_limit'],
        [
            'type' => 'number',
            'max_length' => 2,
            'inner_width' => '100px',
        ]);
    echo form_text('pm_outbox_limit',
        $locale['admins_702'] . '<small>' . $locale['704'] . '</small>',
        $settings['pm_outbox_limit'],
        [
            'type' => 'number',
            'max_length' => 2,
            'inner_width' => '100px',
        ]);
    echo form_text('pm_archive_limit',
        $locale['admins_703'] . '<small>' . $locale['admins_704'] . '</small>',
        $settings['pm_archive_limit'],
        [
            'type' => 'number',
            'max_length' => 2,
            'inner_width' => '100px',
        ]);
    closeside();
    openside('Messaging Notification<small>Notification configurations for private messages</small>', TRUE);
    echo form_select('pm_email_notify', $locale['admins_709'], $settings['pm_email_notify'], [
        'options' => ['1' => $locale['no'], '2' => $locale['yes']],
        'inner_width' => '100%',
    ]);
    echo form_select('pm_save_sent', $locale['admins_710'], $settings['pm_save_sent'], [
        'options' => ['1' => $locale['no'], '2' => $locale['yes']],
        'inner_width' => '100%',
    ]);
    closeside();

    echo '<noscript>';
    echo '<div class="spacer-sm">';
    pm_button();
    echo '</div>';
    echo '</noscript>';
    echo closeform();

}

function pm_button() {
    $locale = fusion_get_locale();

    echo form_button('deletepm', $locale['admins_714'], $locale['admins_714'], ['class' => 'btn-danger', 'icon' => 'fad fa-trash']) .
        form_button('savesettings', $locale['admins_750'], $locale['admins_750'], ['class' => 'btn-primary']);
}

function pm_js() {

    $locale = fusion_get_locale();

    return "$('#deletepm').bind('click', function() { return confirm('" . $locale['admins_713'] . "'); });";
}
