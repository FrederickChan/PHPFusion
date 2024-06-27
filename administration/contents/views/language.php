<?php


function lang_view() {

    $locale = fusion_get_locale();
    $settings = fusion_get_settings();

    echo openform('settingsFrm');
    echo form_hidden('old_localeset', '', $settings["locale"]);
    echo form_hidden('old_enabled_languages', '', $settings["enabled_languages"]);

    //$locale['685ML']

    echo '<h6>System Language</h6>';
    openside();
    echo form_lang_checkbox(makefilelist(LOCALE, ".|..", TRUE, "folders"));
    closeside();

    openside($locale['684ML'] . '<small>Selection of allowed languages.</small>', TRUE);
    echo form_select('localeset', $locale['admins_417'], $settings["localeset"], array(
        'options' => fusion_get_enabled_languages(),
        'inner_width' => '100%',
    ));
    closeside();

    openside($locale['668ML'] . '<small>' . $locale['admins_669ML'] . '</small>', TRUE);
    $result = dbquery("SELECT * FROM " . DB_LANGUAGE_TABLES . "");
    while ($data = dbarray($result)) {
        echo '<div class="checkbox-switch form-group m-b-0"><label class="control-label" data-checked="1" for="' . $data['mlt_rights'] . '">' . $data['mlt_title'] . '</label>
        <div class="pull-left m-r-10"><input id="' . $data['mlt_rights'] . '" style="margin:0;" name="multilang_tables[]" value="' . $data['mlt_rights'] . '" type="checkbox"' . ($data['mlt_status'] == 1 ? ' checked' : '') . ' ></div>
        </div>';
    }
    closeside();

    echo '<noscript>';
    echo '<div class="spacer-sm">';
    lang_button();
    echo '</div>';
    echo '</noscript>';
    echo closeform();

    echo openform('cacheFrm') . closeform();
}

function lang_button() {

    $locale = fusion_get_locale();

    echo form_button('savesettings', $locale['admins_750'], $locale['admins_750'], ['class' => 'btn-primary']);
}

/**
 * Create Language Selector Checkboxes.
 *
 * @param string[] $language_list
 *
 * @return string
 */
function form_lang_checkbox(array $language_list) {

    $enabled_languages = fusion_get_enabled_languages();
    $res = "";
    foreach ($language_list as $language) {
        $deactivate = fusion_get_settings("locale") == $language;

        $res .= form_checkbox("enabled_languages[]",
            translate_lang_names($language),
            (isset($enabled_languages[$language])),
            [
                "input_id" => "langcheck-" . $language,
                "value" => $language,
                "class" => "m-b-0",
                "deactivate" => $deactivate,
                'toggle' => TRUE,
            ]);
        if ($deactivate == TRUE) {
            $res .= form_hidden('enabled_languages[]', '', $language);
        }
    }

    return $res;
}
