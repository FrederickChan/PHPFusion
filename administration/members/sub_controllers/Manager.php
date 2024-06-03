<?php

namespace Pro\Admin\Members\Controller;

use Pro\Admin\Members\Helper;

class Manager extends Helper {


    public static function filterOptions()
    : array {

        return
            [
                1 => [
                    'text'     => 'Basic Data',
                    'children' => [
                        'user_name'       => 'Username',
                        'user_firstname'  => 'First name',
                        'user_lastname'   => 'Last name',
                        'user_email'      => 'Email',
                        'user_hide_email' => 'Email is hidden',
                        'user_joined'     => 'Joined',
                        'user_lastvisit'  => 'Last visited',
                        'user_ip'         => 'IP address',
                        'user_groups'     => 'User groups',
                        'user_status'     => 'User status'
                    ],
                ],

            ];
    }

    /**
     * @param int $index
     *
     * @return array
     */
    public static function filterInput(int $index = 1)
    : array {

        $options = [
            'width'       => '100%',
            'inner_width' => '100%',
        ];

        return [
            'user_name'       => form_text('filter_value[user_name]',
                                           '',
                                           '',
                                           $options + [
                                               'input_id' => 'jc' . $index . 'c_username', 'class' => 'filter-input user_name'
                                           ]),
            'user_firstname'  => form_text('filter_value[user_firstname]',
                                           '',
                                           '',
                                           $options + [
                                               'placeholder' => 'First name',
                                               'input_id'    => 'jc' . $index . 'c_firstname',
                                               'class'       => 'filter-input user_firstname'
                                           ]),
            'user_lastname'   => form_text('filter_value[user_lastname]',
                                           '',
                                           '',
                                           $options + [
                                               'placeholder' => 'Last name',
                                               'input_id'    => 'jc' . $index . 'c_lastname',
                                               'class'       => 'filter-input user_lastname'
                                           ]),
            'user_email'      => form_text('filter_value[user_email]',
                                           '',
                                           '',
                                           $options + [
                                               'placeholder' => 'Email',
                                               'input_id'    => 'jc' . $index . 'c_email',
                                               'class'       => 'filter-input user_email'
                                           ]),
            'user_hide_email' => form_select('filter_value[user_hide_email]',
                                             '',
                                             '',
                                             $options + [
                                                 'options'  => [
                                                     '0' => 'Visible',
                                                     '1' => 'Hidden'
                                                 ],
                                                 'input_id' => 'jc' . $index . 'c_hide_email',
                                                 'class'    => 'filter-input user_hide_email'
                                             ]),
            'user_joined'     => form_datepicker('filter_value[user_joined]',
                                                 '',
                                                 '',
                                                 $options + [
                                                     'input_id'    => 'jc' . $index . 'c_joined',
                                                     'class'       => 'filter-input user_joined',
                                                     'placeholder' => "Joined date"
                                                 ]),
            'user_lastvisit'  => form_datepicker('filter_value[user_lastvisit]',
                                                 '',
                                                 '',
                                                 $options + [
                                                     'input_id'    => 'jc' . $index . 'c_lastvisit',
                                                     'class'       => 'filter-input user_lastvisit',
                                                     'placeholder' => 'Last visited'
                                                 ]),
            'user_ip'         => form_text('filter_value[user_ip]',
                                           '',
                                           '',
                                           $options + [
                                               'input_id'    => 'jc' . $index . 'c_ip',
                                               'class'       => 'filter-input user_ip',
                                               'placeholder' => 'IP Address'
                                           ]),
            'user_groups'     => form_select('filter_value[user_groups]',
                                             '',
                                             '',
                                             $options + [
                                                 'options'     => fusion_get_groups(),
                                                 'input_id'    => 'jc' . $index . 'c_groups',
                                                 'class'       => 'filter-input user_groups',
                                                 'placeholder' => 'Groups'
                                             ]),
            'user_status'     => form_select('filter_value[user_status]',
                                             '',
                                             '',
                                             $options + [
                                                 'options'     => [
                                                     self::USER_MEMBER      => 'Member',
                                                     self::USER_BAN         => 'Banned',
                                                     self::USER_ANON        => 'Anonymous',
                                                     self::USER_CANCEL      => 'Cancelled',
                                                     self::USER_UNACTIVATED => 'Unactivated',
                                                     self::USER_SUSPEND     => 'Suspended',
                                                     self::USER_DEACTIVATE  => 'Deactivated',
                                                 ],
                                                 'input_id'    => 'jc' . $index . 'c_status',
                                                 'class'       => 'filter-input user_status',
                                                 'placeholder' => "Status"
                                             ]),


        ];
    }


    public function view() {

        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();

        if ( check_get('relog') ) {
            add_notice('info', fusion_get_locale('global_185'));
        }

        // if ($resend_id = post('resend_mail', FILTER_VALIDATE_INT)) {
        //     // now i need to send an activation code
        //     $userdata = fusion_get_user($resend_id);
        //     if ($userdata['user_joined'] === 0 && !empty($userdata['user_email'])) {
        //         // now we can resend it.
        //         fusion_sendmail($template_key, $toname, $toemail)
        //     }
        // }

        echo openform('resendmailFrm', 'POST') .
             form_hidden('resend_mail', '') .
             closeform();


        echo '<div id="view-filter" style="display:none;">
        <h5>Filters</h5>
        <div class="well">
        <div id="member-query-filter">
        <div class="filter-row">
        ' . form_select('filter_sel[]', '', '', [
                'width'         => '100%',
                'inner_width'   => '100%',
                'placeholder'   => 'Dataset',
                'allowclear'    => TRUE,
                'options'       => self::filterOptions(),
                'disable_opts'  => [ 1, 2 ],
                'hide_disabled' => TRUE,
                'optgroup'      => TRUE,
                'input_id'      => 'jc1a',
            ]) .
             form_select('filter_operator[]', '', '', [
                 'width'       => '100%',
                 'inner_width' => '100%',
                 'options'     => [
                     'is'    => 'is',
                     'isnot' => 'is not'
                 ],
                 'input_id'    => 'jc1b',
             ]) .
             '<div class="filter-val-container" id="filter-val-1">' .
             implode('', self::filterInput()) .
             '</div>' .
             // '<a href="#" data-action="removefilter" class="btn btn-default"><span><i class="far fa-times"></i></span></a>' .
             '</div>
        </div>
        <a class="btn btn-default text-success" data-action="addfilter" href="#"><i class="far fa-plus m-r-10"></i>Add filter condition</a>
        </div>
        <div class="flex gap-10">
        <button name="reset" data-action="resetfilter" class="btn btn-inverse"><span>Reset</span></button>
        <button name="apply" data-action="dofilter" class="btn btn-success"><span>Apply</span></button>
        </div>';
        echo '</div>';

        // go with custom data source
        fusion_load_script(ADMIN . 'members/js/members.js');

        openside();

        $table_settings = fusion_table('memberTable', [
            'hide_search_input' => TRUE,
            'pagination'        => FALSE,
            'col_resize'        => FALSE,
            'col_reorder'       => FALSE,
            'row_reorder'       => FALSE
        ]);

        $list = '';

        $result = dbquery("SELECT user_id, user_name, user_email, user_avatar, user_status, user_level, user_location, user_joined 
        FROM " . DB_USERS . " ORDER BY user_joined ASC");

        if ( $member_count = dbrows($result) ) {

            while ( $data = dbarray($result) ) {

                $link = '<div>';
                $endlink = '</div>';
                if ($data['user_id'] == 1 && $userdata['user_id'] == $data['user_id'] || $data['user_id'] > 1 && $data['user_level'] >= $userdata['user_level']) {
                    $link = '<a class="single-cell" title="'.$locale['edit'].' '.$data['user_name'].'" href="' . ADMIN_CURRENT_DIR . '&action=edit&id=' . $data['user_id'] . '">';
                    $endlink = '</a>';
                }

                $list .= '<tr><td>'.$link.'
                <div class="flex gap-10 ac">'. display_avatar($data, '40px', '', FALSE, 'img-circle') . '
                <div>' . $data['user_name'] . '<br><span><small>' . $data['user_email'] . '</small></span></div>
                </div>
                '.$endlink.'
                </td>
                <td>
                '.$link.'
                <div>' . getuserlevel($data['user_level']) . '
                <div><small><span>' . getuserstatus($data['user_status'], $data['user_joined']) . '</span></small></div>
                </div>'.$endlink.'
                </td>
                <td>'.$link.'<small><span>' . $data['user_location'] . '</span></small>'.$endlink.'</td>
                <td>
                ' . ( $data['user_joined'] ?
                $link.'
                <small><span class="emphasis">' . showdate($data['user_joined'], 'j M Y', FALSE) . '</span><br><span>' . timer($data['user_joined']) . '
                </span></small>
                '.$endlink : '-').'
               </td>
                </tr>';
            }
        }

        echo '<table class="table" id="' . $table_settings . '"><thead><tr><th>' . format_word($member_count,
                                                                                               'member|members') . '</th><th>Rank</th><th>Location</th><th>Joined</th></tr></thead><tbody>' . $list . '</tbody></table>';
        closeside();

    }

}
