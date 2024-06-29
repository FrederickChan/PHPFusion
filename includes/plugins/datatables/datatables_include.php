<?php

defined('IN_FUSION') || exit;

/**
 * Initiliazes Datatables
 *
 * @param string $table_id
 * @param array $options
 *
 * Options for columns parameters (Example)
 *                          $options["columns"] = array(
 *                          array("data" => "column_1_name", "orderable"=>FALSE, "width"=>200, "class"=>"min"),
 *                          array("data" => "column_1_name")
 *                          )
 *
 *                          'orderable' - boolean (true/false)
 *                          'width' - width of column
 *                          'class' - class name,
 *                          'responsive' - boolean (true/false)
 *                          'className' -   'never' // hide on all devices
 *                                      -   'all' //show on all devices
 *                                      -   'not-mobile' // hide on mobile
 *
 * The response for the item must contains such:
 *  [
 *       "data" => array( 0 => array("column_1" => "data", "column_2" => "data"...), 1 => ... ),
 *       "recordsTotal" => $rows,
 *       "recordsFiltered" => $max_rows,
 *       "responsive" => TRUE
 *  ]
 *
 * Row Sorter
 * $options['columns'] must be defined. data must be as string?
 * $options['remote_file'] must be on string file path
 *
 * editor is - 'editor'
 * @return string
 */
function fusion_table($table_id, array $options = []) {
    $locale = fusion_get_locale();
    $plugin_dir = INCLUDES . '/plugins/datatables/';

    $table_id = str_replace(["-", " "], "_", $table_id);

    $js_event_function = "";
    $filters = "";
    $js_filter_function = "";

    $default_options = [
        'remote_file' => '',
        'page_length' => 0, // result length 0 for default 10
        'debug' => FALSE,
        'reponse_debug' => FALSE,
        // Documentation required for these.
        'server_side' => '',
        'processing' => '',
        'ajax' => FALSE,
        'ajax_debug' => FALSE,
        'responsive' => TRUE,
        // filter input name on the page if extra filters are used
        'ajax_filters' => [],
        // not functional yet
        'ajax_data' => [],
        'order' => [], // [0, 'desc'] // column 0 order desc - sets default ordering
        'state_save' => TRUE, // utilizes localStorage to store latest state
        // documentation needed for columns
        'columns' => NULL,
        'ordering' => TRUE,
        'pagination' => TRUE, //hides table navigation
        'hide_search_input' => FALSE, // hides search input
        // Ui as aesthetics for maximum user experience
        'row_reorder' => FALSE,
        'row_reorder_url' => '',
        'row_reorder_success' => '',
        'row_reorder_failed' => '',
        'col_resize' => FALSE,
        'col_reorder' => FALSE,
        'fixed_header' => FALSE,
        // custom jsscript append
        'js_script' => '',
    ];

    $options += $default_options;
    fusion_load_script(INCLUDES . 'jquery/jquery-ui/jquery-ui.min.js');
    fusion_load_script(INCLUDES . 'jquery/jquery-ui/jquery-ui.css', 'css');

    // Map for file inclusion
    $plugin_registers = array(
        'BOOTSTRAP5' => array(
            'css' => array(
                $plugin_dir . 'css/dataTables.bootstrap5.css',
            ),
            'js' => array(
                $plugin_dir . 'js/dataTables.min.js',
                $plugin_dir . 'js/dataTables.bootstrap5.min.js',
            ),
        ),
        'BOOTSTRAP4' => array(
            'css' => array(
                $plugin_dir . 'css/dataTables.bootstrap4.min.css',
            ),
            'js' => array(
                $plugin_dir . 'js/jquery.dataTables.min.js',
                $plugin_dir . 'js/dataTables.bootstrap4.min.js',
            ),
        ),
        'BOOTSTRAP' => array(
            'css' => array(
                $plugin_dir . 'css/dataTables.bootstrap.min.css',
            ),
            'js' => array(
                $plugin_dir . 'js/jquery.dataTables.min.js',
                $plugin_dir . 'js/dataTables.bootstrap.min.js',
            ),
        ),
        'default' => array(
            'css' => array(
                $plugin_dir . 'css/jquery.dataTables.min.css',
            ),
            'js' => array(
                $plugin_dir . 'js/jquery.dataTables.min.js',
            ),
        ),
    );

    if ($options['page_length'] && isnum($options['page_length'])) {
        $options['datatable_config']['pageLength'] = (int)$options['page_length'];
    }

    // Build configurations
    $config = '';
    if (!empty($options["order"])) {
        $config .= "'order' : [ " . json_encode($options["order"]) . " ],";
    }

    if ($options['hide_search_input'] === TRUE) {
        $config .= "'dom': '<\"top\">rt<\"bottom\"><\"clear\">',";
    }

    if (!empty($options['disable_column_ordering'])) {
        if (isset($options['disable_column_ordering'])) {
            $config .= "'columnDefs': [
                {'orderable': false, 'targets': " . json_encode($options['disable_column_ordering']) . " } // Disable sorting for columns 0, 1, and 2
            ],";
        }
    }

    if ($options['row_reorder'] === TRUE) {

        $options['pagination'] = FALSE;

        $config .= "
        'info':false,
        'aaSorting': [[1, 'asc']],
        ";

        $options['js_script'] .= "
        let fixHelper = function(e, ui) {
            ui.children().each(function() {
                $(this).width($(this).width());
            });
            return ui;
        };

         $('#" . $table_id . " tbody').sortable({
            helper: fixHelper,
            placeholder: 'state-highlight',
            connectWith: '.connected',
            scroll:true,
            axis: 'y',
            update: function(event, ui) {

                let tableElem = $(this).children('tr');
                let order_array = [];
                tableElem.each(function () {
                    order_array.push($(this).data('id'));
                });

                let formData = new FormData();
                formData.append('fusion_token', '" . fusion_get_token($table_id . "_token", 10) . "');
                formData.append('form_id', '" . $table_id . "_token');
                formData.append('order', order_array);
                $(this).find('.num').each(function (i) {
                    $(this).text(i + 1);
                });

                fetch('" . $options['row_reorder_url'] . "', {
                    method: 'POST',
                    mode: 'same-origin',
                    cache: 'force-cache',
                    //headers: {
                        //'Content-Type': 'application/json',
                    //},
                    referrerPolicy: 'origin',
                    body: formData
                }).then(function (response) {
                    console.log(response);
                    if (response.status === 200) {
                        add_notice('success', '" . $options['row_reorder_success'] . "');
                    }
                }).catch(function (error) {
                    add_notice('danger', '" . $options['row_reorder_failed'] . "');
                });

            }
        }).disableSelection();";

    }

    if ($options['pagination'] === FALSE) {
        $config .= "'paging' : false,";
    }

    $config .= "'language': {
        'processing': '" . $locale['processing_locale'] . "',
        'lengthMenu': '" . $locale['menu_locale'] . "',
        'zeroRecords': '" . $locale['zero_locale'] . "',
        'info': '" . $locale['result_locale'] . "',
        'infoEmpty': '" . $locale['empty_locale'] . "',
        'infoFiltered': '" . $locale['filter_locale'] . "',
        'searchPlaceholder': '" . $locale['search_input_locale'] . "',
        'search': '" . $locale['search'] . "',
        'paginate': {
            'next': '" . $locale['next'] . "',
            'previous': '" . $locale['previous'] . "',
        },
    },";

    // Javascript Init
    $js_config_script = "
    {
        'responsive' :" . ($options["responsive"] ? "true" : "false") . ",
        'searching' : true,
        'ordering' : " . ($options["ordering"] ? "true" : "false") . ",
        'stateSave' : " . ($options["state_save"] ? "true" : "false") . ",
        'autoWidth' : true,
        $config
    }";

    $options['js_script'] .= $table_id . 'Table.on("draw.dt", function() {
        var hoverable_elem = $("div[data-toggle=\"table-tr-hover\"]");
        hoverable_elem.hide();
        hoverable_elem.closest("tr").on("mouseenter", function(e) {
            $(this).find("div[data-toggle=\"table-tr-hover\"]").show();
        }).on("mouseleave", function(e) {
            $(this).find("div[data-toggle=\"table-tr-hover\"]").hide();
        });
    });';

    // Ajax handling script
    if ($options['remote_file']) {

        if (empty($options["columns"]) && preg_match("@^http(s)?://@i", $options["remote_file"])) {
            $file_output = fusion_get_contents($options['remote_file']);
            if (!empty($file_output)) {
                if (is_json($file_output)) {
                    $output_array = json_decode($file_output, TRUE);
                    //print_P($output_array);
                    if ($options['reponse_debug']) {
                        print_p($output_array);
                    }
                    // Column
                    if (!empty($output_array['data'])) {
                        $output_data = $output_array["data"];
                        $output_reset = reset($output_data);
                        if (is_array($output_reset)) {
                            $column_key = array_keys($output_reset);
                        }
                        if (!empty($column_key)) {
                            foreach ($column_key as $column) {
                                $options["columns"][] = ['data' => $column];
                            }
                        }
                    }
                }
            } else {
                addnotice("danger", "Table columns could not be loaded automatically.");
            }
        }

        $js_config_script = "
        {
            'responsive' :" . ($options["responsive"] ? "true" : "false") . ",
            'processing' : " . ($options["processing"] ? "true" : "false") . ",
            'serverSide' : " . ($options["server_side"] ? "true" : "false") . ",
            'serverMethod' : 'POST',
            'searching' : true,
            'ordering' : " . ($options["ordering"] ? "true" : "false") . ",
            'stateSave' : " . ($options["state_save"] ? "true" : "false") . ",
            'autoWidth' : true,
            'ajax' : {
                url : '" . $options['remote_file'] . "',
                <data_filters>
            },
            $config
            'columns' : " . json_encode($options['columns']) . "
        }";

        $fields_doms = [];
        if (!empty($options["ajax_filters"])) {

            foreach ($options["ajax_filters"] as $field_id) {
                $fields_doms[] = "#" . $field_id;
                $filters .= "data." . $field_id . "= $('#" . $field_id . "').val();";
            }
            $js_filter_function = "data: function(data) { $filters }";
            $js_event_function = "$('body').on('keyup change', '" . implode(', ', $fields_doms) . "', function(e) {
            " . $table_id . "Table.draw();
            });";
        }

        $js_config_script = str_replace("<data_filters>", $js_filter_function, $js_config_script);
    }

    /**
     * Datatable Plugins
     * Column Resizing     Toggle with $options['col_resize']
     * Column Reordering    Toggle with $options['col_reorder']
     * Enables Fixed header Toggle with $options['fixed_header']
     * Enable Table Responsive Toggle with $options['responsive']
     */

    // Enable column resizing
    if ($options['col_resize']) {
        $_plugin_folder = $plugin_dir . 'extensions/colresize/';
        $files = array(
            'all' => array(
                'css' => array($_plugin_folder . 'css/datatables.colresize.min.css'),
                'js' => array($_plugin_folder . 'js/datatables.colresize.min.js'),
            ),
        );

        $plugin_registers = array_merge_recursive($files, $plugin_registers);

        $options['js_script'] .= 'new $.fn.dataTable.ColResize(' . $table_id . 'Table, {
            isEnabled: true,
            hoverClass: \'dt-colresizable-hover\',
            hasBoundCheck: true,
            minBoundClass: \'dt-colresizable-bound-min\',
            maxBoundClass: \'dt-colresizable-bound-max\',
            isResizable: function(column) { return true; },
            onResize: function(column) {},
            onResizeEnd: function(column, columns) {},
            getMinWidthOf: function($thNode) {}
        });';
    }

    // Enable column reordering
    if ($options['col_reorder']) {
        $_plugin_folder = $plugin_dir . 'extensions/colreorder/';
        $files = array(
            'BOOTSTRAP5' => array(
                'css' => array($_plugin_folder . 'css/colReorder.bootstrap5.min.css'),
                'js' => array($_plugin_folder . 'js/colReorder.bootstrap5.min.js'),
            ),
            'BOOTSTRAP4' => array(
                'css' => array($_plugin_folder . 'css/colReorder.bootstrap4.min.css'),
                'js' => array($_plugin_folder . 'js/colReorder.bootstrap4.min.js'),
            ),
            'BOOTSTRAP' => array(
                'css' => array($_plugin_folder . 'css/colReorder.bootstrap.min.css'),
                'js' => array($_plugin_folder . 'js/colReorder.bootstrap.min.js'),
            ),
            'default' => array(
                'css' => array($_plugin_folder . 'css/colReorder.dataTables.min.css'),
            ),
            'all' => array(
                'js' => array($_plugin_folder . 'js/dataTables.colReorder.min.js'),
            ),
        );
        $plugin_registers = array_merge_recursive($plugin_registers, $files);
        $options['js_script'] .= 'new $.fn.dataTable.ColReorder(' . $table_id . 'Table, {} );';
    }

    // Enable responsive design
    if ($options['responsive']) {
        $plugin_ext = $plugin_dir . 'extensions/responsive/';
        $files = array(
            'BOOTSTRAP5' => array(
                'css' => array(
                    $plugin_ext . 'css/responsive.dataTables.min.css',
                    $plugin_ext . 'css/responsive.bootstrap5.min.css',
                ),
                'js' => array(
                    $plugin_ext . 'js/dataTables.responsive.js',
                    $plugin_ext . 'js/responsive.bootstrap5.js'
                ),
            ),
            'BOOTSTRAP4' => array(
                'css' => array($plugin_ext . 'css/responsive.bootstrap4.min.css', $plugin_ext . 'css/responsive.dataTables.min.css'),
                'js' => array($plugin_ext . 'js/dataTables.responsive.min.js', $plugin_ext . 'js/responsive.bootstrap4.min.js'),
            ),
            'BOOTSTRAP' => array(
                'css' => array($plugin_ext . 'css/responsive.bootstrap.min.css', $plugin_ext . 'css/responsive.dataTables.min.css'),
                'js' => array($plugin_ext . 'js/dataTables.responsive.min.js', $plugin_ext . 'js/responsive.bootstrap.min.js'),
            ),
            'default' => array(
                'css' => array($plugin_ext . 'css/responsive.dataTables.min.css'),
                'js' => array($plugin_ext . 'js/dataTables.responsive.min.js', $plugin_ext . 'js/dataTables.responsive.min.js'),
            ),
        );

        $plugin_registers = array_merge_recursive($plugin_registers, $files);

        $options['js_script'] .= 'new $.fn.dataTable.Responsive(' . $table_id . 'Table);';
    }

    // Fixed header
    if ($options['fixed_header']) {
        $_plugin_folder = $plugin_dir . 'extensions/fixed_header/';
        $files = array(
            'BOOTSTRAP5' => array(
                'css' => array($_plugin_folder . 'css/fixedHeader.bootstrap5.min.css'),
                'js' => array($_plugin_folder . 'js/dataTables.fixedHeader.min.js',
                    $_plugin_folder . 'js/fixedHeader.bootstrap5.min.js'),
            ),
            'BOOTSTRAP4' => array(
                'css' => array($_plugin_folder . 'css/fixedHeader.bootstrap4.min.css'),
                'js' => array($_plugin_folder . 'js/dataTables.fixedHeader.min.js',
                    $_plugin_folder . 'js/fixedHeader.bootstrap4.min.js'),
            ),
            'BOOTSTRAP' => array(
                'css' => array($_plugin_folder . 'css/fixedHeader.bootstrap.min.css'),
                'js' => array($_plugin_folder . 'js/dataTables.fixedHeader.min.js', $_plugin_folder . 'js/fixedHeader.bootstrap.min.js'),
            ),
            'default' => array(
                'css' => array($_plugin_folder . 'css/fixedHeader.dataTables.min.css'),
                'js' => array($_plugin_folder . 'js/dataTables.fixedHeader.min.js', $_plugin_folder . 'js/fixedHeader.dataTables.min.js'),
            ),
        );
        $plugin_registers = array_merge_recursive($plugin_registers, $files);
        $options['js_script'] .= 'new $.fn.dataTable.FixedHeader(' . $table_id . 'Table);';
    }

    // Load file into cache and auto include them
    if ($template = fusion_theme_framework()) {

        if (isset($plugin_registers[$template])) {
            if (isset($plugin_registers[$template]['css'])) {
                foreach ($plugin_registers[$template]['css'] as $css_file) {
                    fusion_load_script($css_file, 'css');
                }
            }
            if (isset($plugin_registers[$template]['js'])) {
                foreach ($plugin_registers[$template]['js'] as $js_file) {
                    fusion_load_script($js_file);
                }
            }
        } else {
            foreach ($plugin_registers['default']['css'] as $css_file) {
                fusion_load_script($css_file, 'css');
            }
            foreach ($plugin_registers['default']['js'] as $js_file) {
                fusion_load_script($js_file);
            }
        }

        if (isset($plugin_registers['all'])) {
            if (isset($plugin_registers['all']['css'])) {
                foreach ($plugin_registers['all']['css'] as $css_file) {
                    fusion_load_script($css_file, 'css');
                }
            }
            if (isset($plugin_registers['all']['js'])) {
                foreach ($plugin_registers['all']['js'] as $js_file) {
                    fusion_load_script($js_file);
                }
            }
        }
    }

    $javascript = "let " . $table_id . "Table = $('#$table_id').DataTable($js_config_script);" . $options['js_script'] . "$js_event_function";

    if ($options['debug']) {
        print_p($javascript);
    }

    add_to_jquery($javascript);

    return $table_id;
}
