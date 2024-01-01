<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: UserFields.php
| Author: Hans Kristian Flaatten (Starefossen)
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

namespace PHPFusion;

use PHPFusion\Userfields\Accounts\AccountPrivacy;
use PHPFusion\Userfields\Accounts\AccountsForm;
use PHPFusion\Userfields\Accounts\CloseAccount;
use PHPFusion\Userfields\Privacy\PrivacyForm;
use PHPFusion\Userfields\UserFieldsForm;

/**
 * Class UserFields
 *
 * @package PHPFusion
 */
class UserFields extends QuantumFields {

    public $userData = [
        'user_id'             => '',
        'user_name'           => '',
        'user_firstname'      => '',
        'user_lastname'       => '',
        'user_addname'        => '',
        'user_phone'          => '',
        'user_hide_phone'     => 1,
        'user_bio'            => '',
        'user_password'       => '',
        'user_admin_password' => '',
        'user_email'          => '',
        'user_hide_email'     => 1,
        'user_language'       => LANGUAGE,
        'user_timezone'       => 'Europe/London'
    ];

    public $displayTerms = 0;

    public $displayValidation = 0;

    public $postName;

    public $postValue;

    public $showAdminOptions = FALSE;

    public $showAdminPass = TRUE;

    public $showAvatarInput = TRUE;

    public $baseRequest = FALSE; // new in API 1.02 - turn fusion_self to fusion_request - 3rd party pages. Turn this on if you have more than one $_GET pagination str.

    public $skipCurrentPass = FALSE;

    public $registration = FALSE;

    public $system_title = '';

    public $admin_rights = '';

    public $locale_file = '';

    public $category_db = '';

    public $field_db = '';

    public $plugin_folder = '';

    public $plugin_locale_folder = '';

    public $debug = FALSE;

    public $method;

    public $paginate = TRUE;

    /**
     * Sets moderation mode - previously admin_mode
     *
     * @var bool
     */
    public $moderation = 0;

    public $inputInline = TRUE;

    public $options = [];

    public $username_change = TRUE;

    public $info = [
        'terms'               => '',
        'validate'            => '',
        'user_avatar'         => '',
        'user_admin_password' => '',
    ];

    public $defaultInputOptions = [];

    /**
     * @var int|mixed|string
     */
    private $refURI;

    /**
     * @var int|mixed|string
     */
    private $sectionURI;

    /**
     * Display Input Fields
     */
    public function displayProfileInput() {

        $this->method = 'input';

        $this->options += $this->defaultInputOptions;

        $this->sectionURI = get( 'section' );

        $this->refURI = get( 'ref' );

        $this->info = $this->getEmptyInputInfo();

        $this->info['section'] = $this->upInputSections();
        $this->info['ref'] = $this->refURI;

        $this->info['link'] = [
            'details'        => BASEDIR . 'edit_profile.php?ref=details',
            'totp'           => BASEDIR . 'edit_profile.php?ref=authenticator',
            'password'       => BASEDIR . 'edit_profile.php?ref=password',
            'admin_password' => BASEDIR . 'edit_profile.php?ref=admin_password',
            'google'         => BASEDIR . 'edit_profile.php?ref=google',
            // privacy page
            'privacy'        => BASEDIR . 'edit_profile.php?ref=privacy',
            'login'          => BASEDIR . 'edit_profile.php?section=privacy&ref=login',
            'blacklist'          => BASEDIR . 'edit_profile.php?section=privacy&ref=blacklist',
            // Authenticator download links
            'appstore'       => 'https://apps.apple.com/au/app/google-authenticator/id388497605',
            'playstore'      => 'https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en&gl=US',
        ];

        // Seperate template for each
        if ( $this->registration ) {

        } else {

            switch ( $this->sectionURI ) {
                case 'notifications':
                    return $this->showNotificationSection();
                case 'privacy':
                    display_up_privacy( ( $this->info + (new PrivacyForm($this))->displayInputFields()) );
                    break;
                case 'close':
                    display_up_close( ( $this->info + ( new CloseAccount( $this ) )->displayInputFields() ) );
                    break;
                default:
                    display_up_settings( ( $this->info + ( new AccountsForm( $this ) )->displayInputFields() ) );
            }
        }
        /*
         * Template Output
         */
        //$this->registration ? display_register_form( $this->info ) : display_userprofile_home( $this->info );
    }

    /**
     * Notification input
     *
     * @return array
     */
    private function showNotificationSection() {

        $locale = fusion_get_locale();

        return [
            'user_comments_notify'   => form_checkbox( 'n_comments', $locale['u502'], $this->userData['user_comments_notify'], ['toggle' => TRUE, 'ext_tip' => $locale['u503']] ),
            'user_tag_notify'        => form_checkbox( 'n_tags', $locale['u504'], $this->userData['user_tag_notify'], ['toggle' => TRUE, 'ext_tip' => $locale['u505']] ),
            'user_newsletter_notify' => form_checkbox( 'n_newsletter', $locale['u506'], $this->userData['user_newsletter_notify'], ['toggle' => TRUE, 'ext_tip' => $locale['u507']] ),
            'user_follow_notify'     => form_checkbox( 'n_follow', $locale['u508'], $this->userData['user_follow_notify'], ['toggle' => TRUE, 'ext_tip' => $locale['u509']] ),
            'user_pm_notify'         => form_checkbox( 'n_pm', $locale['u510'], $this->userData['user_pm_notify'], ['toggle' => TRUE, 'ext_tip' => $locale['u511']] ),
            'user_pm_email'          => form_checkbox( 'e_pm', $locale['u510'], $this->userData['user_pm_email'] ),
            'user_follow_email'      => form_checkbox( 'e_follow', $locale['u514'], $this->userData['user_follow_email'] ),
            'user_feedback_email'    => form_checkbox( 'e_feedback', $locale['u515'], $this->userData['user_feedback_email'] ),
            'user_email_duration'    => form_checkbox( 'e_duration', $locale['u516'], $this->userData['user_email_duration'], [
                'type'    => 'radio',
                'options' => [
                    '1' => $locale['u517'],
                    '2' => $locale['u518'],
                    '3' => $locale['u519'],
                    '0' => $locale['u520'],
                ]] ),
            'notify_button'          => form_button( 'save_notify', $locale['save_changes'], 'save_notify', ['class' => 'btn-primary'] ),
        ];
    }

    private function getEmptyInputInfo() {
        return [
            //'section'   => $this->getProfileSections(),
            'userdata' => $this->userData,
        ];
    }

    /**
     * Empty inputs vars
     */
    private function setEmptyInput() {
        // I do not think we need this.
        return [
            'user_id'             => form_hidden( 'user_id', '', $this->userData["user_id"] ),
            'user_hash'           => form_hidden( 'user_hash', '', $this->userData['user_password'] ),
            'show_avatar'         => $this->showAvatarInput,
            'user_name'           => '',
            'user_firstname'      => '',
            'user_lastname'       => '',
            'user_addname'        => '',
            'user_phone'          => '',
            'user_bio'            => '',
            'user_password'       => '', //form_hidden( 'user_hash', '', $this->userData['user_hash'] ),
            'user_admin_password' => '',
            'user_email'          => '',
            'user_hide_email'     => '',
            'user_avatar'         => '',
            'validate'            => '',
            'terms'               => '',
        ];
    }


    /**
     * Fetch User Fields Array to templates
     * Toggle with class string method - input or display
     * output to array
     */
    public function getUserFields() {
        $fields = [];
        $category = [];
        $item = [];

        $this->callback_data = $this->userData;

        switch ( $this->method ) {
            case 'input':
                if ( $this->registration == FALSE ) {
                    if ( isset( $this->info['user_field'][0]['fields']['user_name'] ) ) {
                        $this->info['user_field'][0]['fields']['user_name'] = form_hidden( 'user_name', '', $this->callback_data['user_name'] );
                    }
                }
                break;
            case 'display':
                $info['user_field'] = [];
        }

        $index_page_id = isset( $_GET['section'] ) && isnum( $_GET['section'] ) && isset( $this->upInputSections()[$_GET['section']] ) ? intval( $_GET['section'] ) : 1;

        $registration_cond = ( $this->registration == TRUE ? ' AND field.field_registration=:field_register' : '' );
        $registration_bind = ( $this->registration == TRUE ? [':field_register' => 1] : [] );

        $query = "SELECT field.*, cat.field_cat_id, cat.field_cat_name, cat.field_parent, root.field_cat_id as page_id, root.field_cat_name as page_name, root.field_cat_db, root.field_cat_index
                  FROM " . DB_USER_FIELDS . " field
                  INNER JOIN " . DB_USER_FIELD_CATS . " cat ON (cat.field_cat_id = field.field_cat)
                  INNER JOIN " . DB_USER_FIELD_CATS . " root on (cat.field_parent = root.field_cat_id)
                  WHERE (cat.field_cat_id=:index00 OR root.field_cat_id=:index01) $registration_cond
                  ORDER BY root.field_cat_order, cat.field_cat_order, field.field_order
                  ";
        $bind = [
            ':index00' => $index_page_id,
            ':index01' => $index_page_id,
        ];
        $bind = $bind + $registration_bind;
        $result = dbquery( $query, $bind );
        $rows = dbrows( $result );
        if ( $rows != '0' ) {
            while ( $data = dbarray( $result ) ) {
                if ( $data['field_cat_id'] ) {
                    $category[$data['field_parent']][$data['field_cat_id']] = self::parseLabel( $data['field_cat_name'] );
                }
                if ( $data['field_cat'] ) {
                    $item[$data['field_cat']][] = $data;
                }
            }
            if ( isset( $category[$index_page_id] ) ) {

                foreach ( $category[$index_page_id] as $cat_id => $cat ) {

                    if ( $this->registration || $this->method == 'input' ) {

                        if ( isset( $item[$cat_id] ) ) {

                            $fields['user_field'][$cat_id]['title'] = $cat;

                            foreach ( $item[$cat_id] as $field ) {
                                $options = [
                                    'show_title' => TRUE,
                                    'inline'     => $this->inputInline,
                                    'required'   => (bool)$field['field_required']
                                ];
                                if ( $field['field_type'] == 'file' ) {
                                    $options += [
                                        'plugin_folder'        => [INCLUDES . "user_fields/", INFUSIONS],
                                        'plugin_locale_folder' => LOCALE . LOCALESET . "user_fields/"
                                    ];
                                }

                                $field_output = $this->displayFields( $field, $this->callback_data, $this->method, $options );

                                $fields['user_field'][$cat_id]['fields'][$field['field_id']] = $field_output;
                                $fields['extended_field'][$field['field_name']] = $field_output; // for the gets
                            }
                        }
                    } else {

                        // Display User Fields

                        if ( isset( $item[$cat_id] ) ) {

                            $fields['user_field'][$cat_id]['title'] = $cat;

                            foreach ( $item[$cat_id] as $field ) {

                                // Outputs array
                                $field_output = $this->displayFields( $field, $this->callback_data, $this->userFields->method );

                                //$fields['user_field'][$cat_id]['fields'][$field['field_id']] = $field_output; // relational to the category
                                $fields['extended_field'][$field['field_name']] = $field_output; // for the gets

                                if ( !empty( $field_output ) ) {
                                    $fields['user_field'][$cat_id]['fields'][$field['field_id']] = array_merge( $field, $field_output );
                                }
                            }
                        }
                    }
                }
            }
        }

        return $fields;
    }


    /**
     * @return array
     */
    private function upInputSections() {

        $link_prefix = BASEDIR . 'edit_profile.php?section=';
        if ( $this->moderation ) {
            $link_prefix = ADMIN . 'members.php?lookup=' . $this->userData['user_id'] . '&action=edit&';
        }

        return [
            'account'        => ['link' => $link_prefix . 'account', 'title' => 'Account'],
            'notifications'  => ['link' => $link_prefix . 'notifications', 'title' => 'Notifications'],
            'privacy'        => ['link' => $link_prefix . 'privacy', 'title' => 'Privacy and safety'],
            'communications' => ['link' => $link_prefix . 'communications', 'title' => 'Communications'],
            'message'        => ['link' => $link_prefix . 'message', 'title' => 'Messaging'],
            'close'          => ['link' => $link_prefix . 'close', 'title' => 'Close account']
        ];

    }


    /***
     * Fetch profile output data
     * Display Profile (View)
     */
    public function displayProfileOutput() {

        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();
        $lookup = get( 'lookup', FILTER_VALIDATE_INT );

        // Add User to Groups
        if ( iADMIN && checkrights( "UG" ) && get( 'lookup', FILTER_VALIDATE_INT ) !== fusion_get_userdata( 'user_id' ) ) {

            if ( check_post( 'add_to_group' ) && $user_group = post( 'user_group', FILTER_VALIDATE_INT ) ) {

                if ( !preg_match( "(^\.$user_group$|\.$user_group\.|\.$user_group$)", $this->userData['user_groups'] ) ) {
                    $userdata = [
                        'user_groups' => $this->userData['user_groups'] . "." . $user_group,
                        'user_id'     => $lookup
                    ];
                    dbquery_insert( DB_USERS, $userdata, 'update' );
                }

                if ( defined( 'ADMIN_PANEL' ) && get( 'step' ) === 'view' ) {
                    redirect( ADMIN . "members.php" . fusion_get_aidlink() . "&amp;step=view&amp;user_id=" . $this->userData['user_id'] );
                } else {
                    redirect( BASEDIR . "profile.php?lookup=" . $lookup );
                }

            }
        }

        $this->info['section'] = $this->upInputSections();

        $this->info['user_id'] = $this->userData['user_id'];

        $this->info['user_name'] = $this->userData['user_name'];

        $current_section = ['id' => 1];
        if ( !empty( $this->info['section'] ) ) {
            $current_section = current( $this->info['section'] );
        }

        $_GET['section'] = isset( $_GET['section'] ) && isset( $this->info['section'][$_GET['section']] ) ? $_GET['section'] : $current_section['id'];

        if ( empty( $this->userData['user_avatar'] ) && !file_exists( IMAGES . "avatars/" . $this->userData['user_avatar'] ) ) {
            $this->userData['user_avatar'] = get_image( 'noavatar' );
        }

        $this->info['core_field']['profile_user_avatar'] = [
            'title'  => $locale['u186'],
            'value'  => $this->userData['user_avatar'],
            'status' => $this->userData['user_status']
        ];

        // username
        $this->info['core_field']['profile_user_name'] = [
            'title' => $locale['u068'],
            'value' => $this->userData['user_name']
        ];

        // user level
        $this->info['core_field']['profile_user_level'] = [
            'title' => $locale['u063'],
            'value' => getgroupname( $this->userData['user_level'] )
        ];

        // user email
        if ( iADMIN || $this->userData['user_hide_email'] == 0 ) {
            $this->info['core_field']['profile_user_email'] = [
                'title' => $locale['u064'],
                'value' => hide_email( $this->userData['user_email'], fusion_get_locale( "UM061a" ) )
            ];
        }

        // user joined
        $this->info['core_field']['profile_user_joined'] = [
            'title' => $locale['u066'],
            'value' => showdate( "longdate", $this->userData['user_joined'] )
        ];

        // Last seen
        $this->info['core_field']['profile_user_visit'] = [
            'title' => $locale['u067'],
            'value' => $this->userData['user_lastvisit'] ? showdate( "longdate", $this->userData['user_lastvisit'] ) : $locale['u042']
        ];

        // user status
        if ( iADMIN && $this->userData['user_status'] > 0 ) {
            $this->info['core_field']['profile_user_status'] = [
                'title' => $locale['u055'],
                'value' => getuserstatus( $this->userData['user_status'], $this->userData['user_lastvisit'] )
            ];

            if ( $this->userData['user_status'] == 3 ) {
                $this->info['core_field']['profile_user_reason'] = [
                    'title' => $locale['u056'],
                    'value' => $this->userData['suspend_reason']
                ];
            }
        }

        // IP
        //$this->info['core_field']['profile_user_ip'] = [];
        if ( iADMIN && checkrights( "M" ) ) {
            $this->info['core_field']['profile_user_ip'] = [
                'title' => $locale['u049'],
                'value' => $this->userData['user_ip']
            ];
        }

        // Groups - need translating.
        $this->info['core_field']['profile_user_group']['title'] = $locale['u057'];
        $this->info['core_field']['profile_user_group']['value'] = '';
        $user_groups = strpos( $this->userData['user_groups'], "." ) == 0 ? substr( $this->userData['user_groups'], 1 ) : $this->userData['user_groups'];
        $user_groups = explode( ".", $user_groups );
        $user_groups = (array)array_filter( $user_groups );

        $group_info = [];
        if ( !empty( $user_groups ) ) {
            for ( $i = 0; $i < count( $user_groups ); $i++ ) {
                if ( $group_name = getgroupname( $user_groups[$i] ) ) {
                    $group_info[] = [
                        'group_url'  => BASEDIR . "profile.php?group_id=" . $user_groups[$i],
                        'group_name' => $group_name
                    ];
                }
            }
            $this->info['core_field']['profile_user_group']['value'] = $group_info;
        }

        $this->info = $this->info + $this->getUserFields();

        if ( iMEMBER && fusion_get_userdata( 'user_id' ) != $this->userData['user_id'] ) {

            $this->info['buttons'] = [
                'user_pm_title' => $locale['u043'],
                'user_pm_link'  => BASEDIR . "messages.php?msg_send=" . $this->userData['user_id']
            ];

            if ( checkrights( 'M' ) && fusion_get_userdata( 'user_level' ) <= USER_LEVEL_ADMIN && $this->userData['user_id'] != '1' ) {
                $groups_cache = cache_groups();
                $user_groups_opts = [];
                $this->info['user_admin'] = [
                    'user_edit_title'     => $locale['edit'],
                    'user_edit_link'      => ADMIN . "members.php" . $aidlink . "&amp;ref=edit&amp;lookup=" . $this->userData['user_id'],
                    'user_ban_title'      => $this->userData['user_status'] == 1 ? $locale['u074'] : $locale['u070'],
                    'user_ban_link'       => ADMIN . "members.php" . $aidlink . "&amp;action=" . ( $this->userData['user_status'] == 1 ? 2 : 1 ) . "&amp;lookup=" . $this->userData['user_id'],
                    'user_suspend_title'  => $locale['u071'],
                    'user_suspend_link'   => ADMIN . "members.php" . $aidlink . "&amp;action=3&amp;lookup=" . $this->userData['user_id'],
                    'user_delete_title'   => $locale['delete'],
                    'user_delete_link'    => ADMIN . "members.php" . $aidlink . "&amp;ref=delete&amp;lookup=" . $this->userData['user_id'],
                    'user_delete_onclick' => "onclick=\"return confirm('" . $locale['delete'] . "');\"",
                    'user_susp_title'     => $locale['u054'],
                    'user_susp_link'      => ADMIN . "members.php" . $aidlink . "&amp;ref=log&amp;lookup=" . $this->userData['user_id']
                ];
                if ( count( $groups_cache ) > 0 ) {
                    foreach ( $groups_cache as $group ) {
                        if ( !preg_match( "(^{$group['group_id']}|\.{$group['group_id']}\.|\.{$group['group_id']}$)", $this->userData['user_groups'] ) ) {
                            $user_groups_opts[$group['group_id']] = $group['group_name'];
                        }
                    }
                    if ( iADMIN && checkrights( "UG" ) && !empty( $user_groups_opts ) ) {
                        $submit_link = BASEDIR . "profile.php?lookup=" . $this->userData['user_id'];
                        if ( defined( 'ADMIN_PANEL' ) && isset( $_GET['step'] ) && $_GET['step'] == "view" ) {
                            $submit_link = ADMIN . "members.php" . $aidlink . "&amp;step=view&amp;user_id=" . $this->userData['user_id'] . "&amp;lookup=" . $this->userData['user_id'];
                        }
                        $this->info['group_admin']['ug_openform'] = openform( "admin_grp_form", "post", $submit_link );
                        $this->info['group_admin']['ug_closeform'] = closeform();
                        $this->info['group_admin']['ug_title'] = $locale['u061'];
                        $this->info['group_admin']['ug_dropdown_input'] = form_select( "user_group", '', "", ["options" => $user_groups_opts, "width" => "100%", "inner_width" => "100%", "inline" => FALSE, 'class' => 'm-0'] );
                        $this->info['group_admin']['ug_button'] = form_button( "add_to_group", $locale['u059'], $locale['u059'] );
                    }
                }
            }
        }

        // Display Template
        display_user_profile( $this->info );
    }

}
