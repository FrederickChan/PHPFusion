<?php


/**
 * Select2 hierarchy
 * Returns a full hierarchy nested dropdown.
 * @deprecated Please use form_select instead
 *
 * @param        $input_name
 * @param string $label
 * @param string $input_value
 * @param array $options
 * @param        $db       - your db
 * @param        $name_col - the option text to show
 * @param        $id_col   - unique id
 * @param        $cat_col  - parent id
 *                         ## The rest of the Params are used by the function itself -- no need to handle ##
 * @param bool $self_id    - not required -- import this to form_seelct
 * @param bool $id         - not required
 * @param bool $level      - not required
 * @param bool $index      - not required
 * @param bool $data       - not required
 *
 * @return string
 * @deprecated and will be removed
 * @todo       : Select 2 is able to do this now, and this function should be deprecated.
 *
 */
function form_select_tree($input_name, $label, $input_value, array $options, $db, $name_col, $id_col, $cat_col, $self_id = FALSE, $id = FALSE, $level = FALSE, $index = [], $data = FALSE) {

    if (defined('DEVELOPER_MODE')) {
        // Adds developer notice for community to change implementation
        set_error(E_NOTICE, 'Deprecation notice: The input ' . $input_name . ' on this page need to be altered to form_select function which also supports hierarchy and DB. This function will be deprecated and removed by the developer team soon.', FUSION_SELF, '0');
    }

    $html = '';
    $locale = fusion_get_locale();
    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    $options += array(
        'required' => FALSE,
        'regex' => '',
        'input_id' => $input_name,
        'placeholder' => $locale['choose'],
        'deactivate' => FALSE,
        'safemode' => FALSE,
        'allowclear' => FALSE,
        'multiple' => FALSE,
        'width' => '',
        'inner_width' => '250px',
        'flex' => FALSE,
        'keyflip' => FALSE,
        'tags' => FALSE,
        'jsonmode' => FALSE,
        'chainable' => FALSE,
        'max_select' => FALSE,
        'error_text' => $locale['error_input_default'],
        'class' => '',
        'inline' => FALSE,
        'tip' => '',
        'delimiter' => ',',
        'callback_check' => '',
        'file' => '',
        'parent_value' => $locale['root'],
        'add_parent_opts' => FALSE,
        'disable_opts' => '',
        'hide_disabled' => FALSE,
        'no_root' => FALSE,
        'show_current' => FALSE,
        'query' => '',
        'full_query' => '',
    );

    $options['input_id'] = trim($options['input_id'], "[]");

    if ($options['flex'] == TRUE) {
        $options['inner_width'] = "100%";
    }

    if ($options['multiple']) {
        if ($input_value) {
            $input_value = explode('|', $input_value);
        } else {
            $input_value = [];
        }
    }

    $allowclear = ($options['placeholder'] && $options['multiple'] || $options['allowclear']) ? "allowClear:true" : '';
    $disable_opts = '';
    if ($options['disable_opts']) {
        $disable_opts = is_array($options['disable_opts']) ? $options['disable_opts'] : explode(',', $options['disable_opts']);
    }

    /* Child patern */
    $opt_pattern = str_repeat("&#8212;", $level);

    if (!$level) {
        $level = 0;
        if (!isset($index[$id])) {
            $index[$id] = ['0' => $locale['no_opts']];
        }

        $error_class = '';
        if (Defender::inputHasError($input_name)) {
            $error_class = "has-error ";
            if (!empty($options['error_text'])) {
                $new_error_text = Defender::getErrorText($input_name);
                if (!empty($new_error_text)) {
                    $options['error_text'] = $new_error_text;
                }
                addnotice("danger", $options['error_text']);
            }
        }

        $html = "<div id='" . $options['input_id'] . "-field' class='form-group " . ($options['inline'] && $label ? 'row ' : '') . $error_class . $options['class'] . "' " . ($options['inline'] && $options['width'] && !$label ? "style='width: " . $options['width'] . "'" : '') . ">\n";
        $html .= ($label) ? "<label class='control-label " . ($options['inline'] ? 'col-xs-12 col-sm-12 col-md-3 col-lg-3' : '') . "' for='" . $options['input_id'] . "'>" . $label . ($options['required'] == TRUE ? "<span class='required'>&nbsp;*</span>" : '') . " " . ($options['tip'] ? "<i class='pointer fa fa-question-circle' title=" . $options['tip'] . "></i>" : '') . "</label>\n" : '';
        $html .= $options['inline'] && $label ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";
    }

    if ($level == 0) {

        add_to_jquery("
        $('#" . $options['input_id'] . "').select2({
        " . (defined('BOOTSTRAP') && BOOTSTRAP == 5 ? "theme: 'bootstrap-5'," : '') . "  
        placeholder: '" . $options['placeholder'] . "',
        $allowclear
        });
        ");

        if (is_array($input_value) && $options['multiple']) { // stores as value;
            $vals = '';
            foreach ($input_value as $arr => $val) {
                $vals .= ($arr == count($input_value) - 1) ? "'$val'" : "'$val',";
            }
            add_to_jquery("$('#" . $options['input_id'] . "').select2('val', [$vals]);");
        }

        $html .= "<select name='$input_name' id='" . $options['input_id'] . "' style='width: " . (!empty($options['inner_width']) ? $options['inner_width'] : $default_options['inner_width']) . "'" . ($options['deactivate'] ? " disabled" : "") . ($options['multiple'] ? " multiple" : "") . ">";
        $html .= $options['allowclear'] ? "<option value=''></option>\n" : '';
        if ($options['no_root'] == FALSE) { // api options to remove root from selector. used in items creation.
            $this_select = '';
            if ($input_value !== NULL) {
                if ($input_value !== '') {
                    $this_select = 'selected';
                }
            }
            $html .= ($options['add_parent_opts'] == TRUE) ? "<option value='0' " . $this_select . ">$opt_pattern " . $locale['parent'] . "</option>\n" : "<option value='0' " . $this_select . " >$opt_pattern " . $options['parent_value'] . "</option>\n";
        }

        $index = dbquery_tree($db, $id_col, $cat_col, $options['query'], $options['full_query']);
        if (!empty($index)) {
            $data = dropdown_select($db, $id_col, $name_col, $cat_col, implode(',', flatten_array($index)), $options['query'], $options['full_query']);
        }
    }

    if (!$id) {
        $id = 0;
    }

    if (isset($index[$id]) && !empty($data)) {
        foreach ($index[$id] as $value) {
            // value is the array
            //$hide = $disable_branch && $value == $self_id ? 1 : 0;
            $name = $data[$value][$name_col];

            $name = PHPFusion\QuantumFields::parseLabel($name);
            $select = ($input_value !== "" && ($input_value == $value)) ? 'selected' : '';
            $disabled = $disable_opts && in_array($value, $disable_opts);
            $hide = $disabled && $options['hide_disabled'];
            // do a disable for filter_opts item.
            $html .= (!$hide) ? "<option value='$value' " . $select . " " . ($disable_opts && in_array($value, $disable_opts) ? 'disabled' : '') . " >$opt_pattern $name " . ($options['show_current'] && $self_id == $value ? '(Current Item)' : '') . "</option>\n" : '';
            if (isset($index[$value]) && (!$hide)) {
                //                $html .= form_select_tree( $input_name, $label, $input_value, $options, $db, $name_col, $id_col, $cat_col, $self_id, $value, $level + TRUE, $index, $data );
            }
        }
    }
    if (!$level) {
        $html .= "</select>\n";
        $html .= (($options['required'] == 1 && Defender::inputHasError($input_name)) || Defender::inputHasError($input_name)) ? "<div id='" . $options['input_id'] . "-help' class='label label-danger p-5 display-inline-block'>" . $options['error_text'] . "</div>" : "";
        $html .= $options['inline'] && $label ? "</div>\n" : '';
        $html .= "</div>\n";
        if ($options['required']) {
            $html .= "<input class='req' id='dummy-" . $options['input_id'] . "' type='hidden'>\n"; // for jscheck
        }
        $input_name = ($options['multiple']) ? str_replace("[]", "", $input_name) : $input_name;

        set_field_config([
                'input_name' => $input_name,
                'title' => trim($title, '[]'),
                'id' => $options['input_id'],
                'type' => 'dropdown',
                'regex' => $options['regex'],
                'required' => $options['required'],
                'safemode' => $options['safemode'],
                'error_text' => $options['error_text'],
                'callback_check' => $options['callback_check'],
                'delimiter' => $options['delimiter'],
            ]
        );
    }

    load_select2_script();

    return $html;
}

/*
 * Optimized performance by adding a self param to implode to fetch only certain rows
 */
function dropdown_select($db, $id_col, $name_col, $cat_col, $index_values, $filter = '', $query_replace = '') {
    $data = [];
    $query = "SELECT $id_col, $name_col, $cat_col FROM " . $db . " " . ($filter ? $filter . " AND " : 'WHERE') . " $id_col IN ($index_values) ORDER BY $name_col ASC";
    if (!empty($query_replace)) {
        $query = $query_replace;
    }
    $result = dbquery($query);
    while ($row = dbarray($result)) {
        $id = $row[$id_col];
        $data[$id] = $row;
    }

    return $data;
}
