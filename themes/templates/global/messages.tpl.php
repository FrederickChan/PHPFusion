<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: messages.tpl.php
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

use PHPFusion\Panels;

defined( 'IN_FUSION' ) || exit;

if ( !function_exists( 'display_inbox' ) ) {
    /**
     * Message Reader Functions for Inbox, Outbox, Archive
     *
     * @param $info
     */
    function display_inbox( $info ) {
        $locale = fusion_get_locale();

        Panels::getInstance()->hideAll();

        ?>
        <h4><?php echo $locale['400'] ?></h4>
        <hr>
        <div class="row">
            <div class="col-xs-12 col-sm-5 col-md-4 col-lg-2">
                <ul class="nav flex-column">
                    <?php
                    $i = 1;
                    foreach ( $info['folders'] as $key => $folder ) :
                        if ( $i < count( $info['folders'] ) ) :
                            $total_key = $key . "_total";
                            $total = $info[$total_key];
                            ?>
                            <li class="nav-item">
                                <a class="nav-link<?php echo whitespace( $folder['active'] ? 'active' : '' ) ?>" href="<?php echo $folder['link'] ?>">
                                    <span>
                                    <?php echo $folder['icon'] ?>
                                    <?php echo $folder['title'] ?>
                                    </span>
                                    <span class="<?php echo whitespace( $folder['active'] ? 'active' : '' ) ?> ms-auto"><?php echo $total ?></span>
                                </a>
                            </li>
                            <?php
                            $i++;
                        endif;
                    endforeach; ?>
                </ul>
            </div>
            <div class="col-xs-12 col-sm-7 col-md-8 col-lg-10">
                <div class="toolbar d-flex">
                    <?php

                    $msg_read = get( 'msg_read' );

                    if ( check_get( 'msg_read' ) && ( !empty( $info['actions_form'] ) || check_get( 'msg_send' ) ) ) :

                        echo $info['actions_form']['openform'];

                        if ( check_get( 'msg_read' ) ) :

                            echo '<a class="btn btn-sm btn-secondary me-3" href="' . $info['button']['back']['link'] . '" title="' . $info['button']['back']['title'] . '">
                            ' . get_image( 'left', '', '', '', 'class="ms-1"' ) . ' Back</a>';
                        endif;

                        if ( check_get( 'msg_read' ) && isset( $info['items'][$msg_read] ) ) :

                            ?>
                            <div class="btn-group me-2">
                                <?php
                                if ( $_GET['folder'] == 'archive' ) {
                                    echo $info['actions_form']['unlockbtn'];
                                } else if ( $_GET['folder'] == 'inbox' ) {
                                    echo $info['actions_form']['lockbtn'];
                                }
                                ?>
                            </div>
                            <?php
                            echo $info['actions_form']['deletebtn'];

                        else:
                            ?>
                            <div class="dropdown display-inline-block m-r-10">';
                                <a id="ddactions" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-default btn-sm dropdown-toggle">
                                    <i id="chkv" class="fa fa-square-o"></i><span class="caret m-l-5"></span></a>
                                <ul class="dropdown-menu" aria-labelledby="ddactions">
                                    <?php
                                    foreach ( $info['actions_form']['check'] as $id => $title ) :
                                        ?>
                                        <li class="dropdown-item">
                                            <a id="<?php echo $id ?>" data-action="check" class="pointer"><?php echo $title ?></a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="btn-group display-inline-block m-r-10">
                                <?php switch ( $info['current_folder'] ) :
                                    case 'archive':
                                        echo $info['actions_form']['unlockbtn'];
                                        break;
                                    case 'outbox':
                                        echo $info['actions_form']['lockbtn'];
                                        break;
                                    default:
                                        echo $info['actions_form']['deletebtn'];
                                endswitch; ?>
                            </div>
                            <div class="dropdown display-inline-block m-r-10">';
                                <a id="ddactions2" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-default btn-sm dropdown-toggle"><?php echo $locale['444'] ?>&hellip;
                                    <span class="caret"></span></a>
                                <ul class="dropdown-menu" aria-labelledby="ddactions2">
                                    <li class="dropdown-item"><?php echo $info['actions_form']['mark_all'] ?></li>
                                    <li class="dropdown-item"><?php echo $info['actions_form']['mark_read'] ?></li>
                                    <li class="dropdown-item"><?php echo $info['actions_form']['mark_unread'] ?></li>
                                    <li class="dropdown-item"><?php echo $info['actions_form']['unmark_all'] ?></li>
                                </ul>
                            </div>
                        <?php
                        endif;

                        echo $info['actions_form']['closeform'];

                    endif;
                    ?>
                    <a class="btn btn-secondary ms-auto" href="<?php echo $info['button']['new']['link'] ?>">
                        <?php echo get_image('add', '', '', '', 'class="me"') ?>
                        <?php echo $locale['401'] ?></a>
                </div>
                <?php if ( !empty( $info['pagenav'] ) ) : ?>
                    <div class="text-right m-t-10 m-b-10"><?php echo $info['pagenav'] ?></div>
                <?php endif; ?>
                <?php
                switch ( $info['current_folder'] ) {
                    case 'inbox':
                        display_pm_inbox( $info );
                        break;
                    default:
                        display_pm_inbox( $info );
                }
                ?>
            </div>
        </div>
        <?php

    }
}

if ( !function_exists( 'display_pm_inbox' ) ) {
    function display_pm_inbox( $info ) {
        $locale = fusion_get_locale();

        if ( isset( $_GET['msg_read'] ) && isset( $info['items'][$_GET['msg_read']] ) ) {
            $data = $info['items'][$_GET['msg_read']];
            echo '<h4>' . $data['message']['message_header'] . '</h4>';
            echo '<div class="m-b-20">';
            if ( !empty( $data['user_id'] ) ) {
                echo display_avatar( $data, '40px', '', FALSE, 'img-rounded pull-left m-t-5 m-r-10' );
                echo profile_link( $data['user_id'], $data['user_name'], $data['user_status'], 'display-block' );
            } else {
                echo $data['contact_user']['user_name'] . ': ';
            }
            echo '<span>' . showdate( '%d %b', $data['message_datestamp'] ) . ', ' . timer( $data['message_datestamp'] ) . '</span>';
            echo '</div>';

            echo $data['message']['message_text'];
            echo '<hr/>';
            echo $info['reply_form'];
        } else if ( isset( $_GET['msg_send'] ) ) {
            echo $info['reply_form'];
        } else {
            if ( !empty( $info['items'] ) ) {
                $unread = [];
                $read = [];

                foreach ( $info['items'] as $message_id => $data ) {
                    if ( $data['message_read'] ) {
                        $read[$message_id] = $data;
                    } else {
                        $unread[$message_id] = $data;
                    }
                }

                echo '<h4><a data-target="#unread_inbox" class="pointer text-dark" data-toggle="collapse" aria-expanded="false" aria-controls="unread_inbox">' . $locale['446'] . ' <span class="caret"></span></a></h4>';
                echo '<div id="unread_inbox" class="collapse in">';
                if ( !empty( $unread ) ) {
                    echo '<div class="table-responsive"><table id="unread_tbl" class="table table-hover table-striped">';
                    foreach ( $unread as $id => $message_data ) {
                        echo '<tr>';
                        echo '<td class="col-xs-12 col-sm-1 align-middle">' . form_checkbox( 'pmID', '', '', [
                                'input_id' => 'pmID-' . $id,
                                'value'    => $id,
                                'class'    => 'm-b-0'
                            ] ) . '</td>';
                        echo '<td class="col-xs-12 col-sm-2 align-middle"><b>' . $message_data['contact_user']['user_name'] . '</b></td>';
                        echo '<td class="col-xs-12 col-sm-6">';
                        echo '<a class="display-block" href="' . $message_data['message']['link'] . '"><b>' . $message_data['message']['name'] . '</b></a>';
                        echo '</td>';
                        echo '<td class="col-xs-12 col-sm-3 align-middle">' . timer( $message_data['message_datestamp'] ) . '</td>';
                        echo '</tr>';
                    }
                    echo '</table></div>';
                } else {
                    echo '<div class="well text-center">' . $locale['471'] . '</div>';
                }
                echo '</div>';

                echo '<h4><a data-target="#read_inbox" class="pointer text-dark" data-toggle="collapse" aria-expanded="false" aria-controls="read_inbox">' . $locale['447'] . ' <span class="caret"></span></a></h4>';
                echo '<div id="read_inbox" class="collapse in">';
                if ( !empty( $read ) ) {
                    echo '<div class="table-responsive"><table id="read_tbl" class="table table-hover table-striped">';
                    foreach ( $read as $id => $message_data ) {
                        echo '<tr>';
                        echo '<td class="col-xs-12 col-sm-1">' . form_checkbox( 'pmID', '', '', [
                                'input_id' => 'pmID-' . $id,
                                'value'    => $id,
                                'class'    => 'm-b-0'
                            ] ) . '</td>';
                        echo '<td class="col-xs-12 col-sm-2">' . $message_data['contact_user']['user_name'] . '</td>';
                        echo '<td class="col-xs-12 col-sm-6"><a href="' . $message_data['message']['link'] . '">' . $message_data['message']['name'] . '</a></td>';
                        echo '<td class="col-xs-12 col-sm-3">' . timer( $message_data['message_datestamp'] ) . '</td>';
                        echo '</tr>';
                    }
                    echo '</table></div>';
                } else {
                    echo '<div class="well text-center">' . $locale['471'] . '</div>';
                }
                echo '</div>';
            } else {
                echo '<div class="well text-center">' . $info['no_item'] . '</div>';
            }
        }
    }
}
