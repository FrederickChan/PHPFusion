<?php

defined('IN_FUSION') || exit;


function panel_list() {

    $locale = fusion_get_locale();

    $aidlink = fusion_get_aidlink();

    add_to_footer("<script type='text/javascript' src='" . INCLUDES . "jquery/jquery-ui/jquery-ui.min.js'></script>");

    add_to_jquery(/** @lang JavaScript */ "
        $('.panels-list, .cards-list').sortable({
            handle : '.handle',
            placeholder: 'state-highlight',
            connectWith: '.connected',
            scroll: true,
            axis: 'auto',
            update: function (event, ui) {
            
                let ul = $(this),
                    order = ul.sortable('serialize'),
                    side = $(this).data('side'),
                    i = 0,
                    li = $(ui.item[0]).data('id');
                
                $('#info').load('" . ADMIN . "panels/panels_updater.php" . $aidlink . "&'+order+'&side='+side+'&id='+li);
                
                ul.find('.num').each(function(i) {
                    $(this).text(i+1);
                });
                
                window.setTimeout('closeDiv();',2500);

                if (ul.attr('id') === 'panel-side0') {
                    $(ui.item[0]).addClass('pdisabled');
                } else {
                    $(ui.item[0]).removeClass('pdisabled')
                }
                                
            },
            receive: function (event, ui) {
                
                let ul = $(this),
                    order = ul.sortable('serialize'),
                    pdata = ul.attr('data-side'),
                    li = $(ui.item[0]).data('id');
                      
                    if (pdata == 1) { var psidetext = '" . $locale['420'] . "'; }
                    if (pdata == 2) { var psidetext = '" . $locale['421'] . "'; }
                    if (pdata == 3) { var psidetext = '" . $locale['425'] . "'; }
                    if (pdata == 4) { var psidetext = '" . $locale['422'] . "'; }                        
                
                ul.find('.pside').each(function() {
                    $(this).text(psidetext);                        
                });
                
                $('#info').load('" . ADMIN . "panels/panels_updater.php" . $aidlink . "&'+order+'&side='+pdata+'&id='+li);

                if (ul.attr('id') === 'panel-side0') {
                    $(ui.item[0]).addClass('pdisabled');
                } else {
                    $(ui.item[0]).removeClass('pdisabled')
                }
            }
        });
    ");

    echo '<div class="alert alert-info">' . $locale['410'] . '</div>';
    echo '<div id="info"></div>';

    echo '<div class="row">';
    // Position 5 - Top
    echo '<div class="col-xs-12">';
    panel_reactor(5);
    echo '</div>';

    // Left Center Right
    echo '<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">';
    panel_reactor(1);
    echo '</div>';
    echo '<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">';
    panel_reactor(2);

    echo '<p class="text-center m-b-20 m-t-20">' . $locale['606'] . '</p>';
    panel_reactor(3);
    echo '</div>';
    echo '<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">';
    panel_reactor(4);
    echo '</div>';
    echo '<div class="col-xs-12">';
    panel_reactor(6);
    echo '</div>';

    echo '<div class="col-xs-12 col-sm-12 col-md-6">';
    panel_reactor(7);
    echo '</div>';
    echo '<div class="col-xs-12 col-sm-12 col-md-6">';
    panel_reactor(8);
    echo '</div>';
    echo '<div class="col-xs-12 col-sm-12 col-md-6">';
    panel_reactor(9);
    echo '</div>';
    echo '<div class="col-xs-12 col-sm-12 col-md-6">';
    panel_reactor(10);
    echo '</div>';


    echo '</div>';

    // Unused Panels in the directory
    echo '<div class="row"><div class="col-xs-12">' . panel_reactor(0) . '</div></div>';
    // $string = format_word(count($panel_list), $locale['604']);
    // $title = $locale['602'] . ': ' . $string;
    // echo "<div class='panel panel-default'>";
    // echo "<div class='panel-heading'>" . $title . "</div>";
    // echo "<div class='panel-body text-dark'>";
    //
    // foreach ( $panel_list as $panel ) {
    //     echo "<div style='float:left;'>" . $panel . "</div>";
    //     echo "<div style='float:right; width:250px;'>";
    //     echo "</div>";
    //     echo "<div style='float:right; width:10%;'>" . $locale['607'] . "</div>";
    //     echo "<div style='clear:both;'></div>";
    // }
    // echo "</div></div>";
}


/**
 * The container for each grid positions
 *
 * @param $side
 */
function panel_reactor($side) {

    $locale = fusion_get_locale();

    $grid_opts = [ 0 => $locale['429a'] ] + panel_section();

    $type = $grid_opts[ $side ];

    $k = 0;

    $panel_data = load_panels($side);

    $count = ! $side ? count($panel_data) : dbcount("('panel_id')",
                                                    DB_PANELS,
                                                    "panel_side=:value",
                                                    [ ':value' => $side ]);

    $title = $type . " <span id='side-" . $side . "' class='badge num pull-right'>" . $count . '</span>';

    echo '<h6><i class="fal fa-th-large fa-lg m-r-10"></i>' . $title . '</h6>';

    openside();

    if ($side == 6 || $side == 5) {
        echo '<div class="row">';
        echo '<div class="col-md-offset-3 col-lg-offset-3 col-md-6 col-lg-6">';
    }


    echo '<div class="clearfix"></div>';
    echo "<ul id='panel-side" . $side . "' data-side='" . $side . "' style='list-style: none;min-height:80px;display:block;' class='panels-list connected'>";

    if ( isset($panel_data) ) {

        foreach ( $panel_data as $data ) {

            // $type = $data['panel_type'] == 'file' ? $locale['423'] : $locale['424'];

            echo "<li id='listItem_" . $data['panel_id'] . "' data-id='".$data['panel_id']."' class='pointer dropdown " . ( $data['panel_status'] == 0 ? ' pdisabled' : '' ) . "'>";
            echo "<div class='handle'>";
            echo '<div class="move-handle">';
            echo '<svg viewBox="0 0 32 32"><path d="M9.125 27.438h4.563v4.563H9.125zm9.188 0h4.563v4.563h-4.563zm-9.188-9.125h4.563v4.563H9.125zm9.188 0h4.563v4.563h-4.563zM9.125 9.125h4.563v4.563H9.125zm9.188 0h4.563v4.563h-4.563zM9.125 0h4.563v4.563H9.125zm9.188 0h4.563v4.563h-4.563z"></path></svg>';
            echo '</div>';
            echo "<div class='ui-handle-meta overflow-hide'>";
            echo "<a id='dd" . $data['panel_id'] . "'>";
            echo '<strong>' . $data['panel_name'] . "</strong>";
            echo '<span class="badge pull-right"><i title = "' . getgroupname($data['panel_access']) . ' Access" class="fal fa-eye text-success"></i></span>';
            // echo '<br><small> <i class=\'fa fa-file-o m-r-10 m-t-5\'></i><span class=\'badge\'>' . $type . '</span></small>';
            echo "</a>";
            echo "</div>";
            echo '<div class="btn-group">';
            if ( $data['panel_status'] == 0 ) {
                echo '<a href="' . ADMIN_CURRENT_DIR . '&pg=form&&action=setstatus&panel_status=1&panel_id=' . $data['panel_id'] . '" title="' . $locale['435'] . '" class="btn btn-default"><span><i class="fal fa-check"></i></span></a>';
            }
            else {
                echo '<a href="' . ADMIN_CURRENT_DIR . '&pg=form&&action=setstatus&panel_status=0&panel_id=' . $data['panel_id'] . '" title="' . $locale['436'] . '" class="btn btn-default"><span><i class="fal fa-times"></i></span></a>';
            }
            echo '<a href="' . ADMIN_CURRENT_DIR . '&pg=form&action=edit&panel_id=' . $data['panel_id'] . '"  title="' . $locale['edit'] . '"  class="btn btn-default"><span><i class="fal fa-edit"></i></span></a>';
            echo '<a href="' . ADMIN_CURRENT_DIR . '&pg=form&&action=delete&panel_id=' . $data['panel_id'] . '" title="' . $locale['delete'] . '" onclick="return confirm(\'' . $locale['440'] . '\');" class="btn btn-default"><span><i class="fal fa-trash text-danger"></i></span></a>';
            echo '</div>';
            echo "</div>";
            echo "</li>";
            $k++;
        }
    }
    echo "</ul>";


    if ($side == 6 || $side == 5) {
        echo '</div></div>';
    }

    closeside();

}
