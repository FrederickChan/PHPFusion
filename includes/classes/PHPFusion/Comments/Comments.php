<?php
namespace PHPFusion\Comments;
/**
 * This is the comments factory
 * Class Comments
 * @package PHPFusion\Comments
 */
class Comments {

    protected static $instances = NULL;



    /**
     * @var array
     * comment_item_type -
     * comment_db -
     * comment_item_id -
     * clink -
     * comment_allow_reply - enable or disable reply of others comments
     * comment_allow_post - enable or disable posting of comments
     * comment_allow_ratings - enable or disable ratings
     * comment_allow_vote - enable or disable voting
     * comment_once - each user can only comment once (replying a comment is unaffected)
     * comment_echo - to echo the output if true
     * comment_title - display the comment block title
     * comment_count - display the current comment count
     */
    protected static $params = [
        'comment_user' => '',
        'comment_item_type' => '',
        'comment_db' => '',
        'comment_col' => '',
        'comment_item_id' => '',
        'clink' => '',
        'comment_allow_subject' => TRUE,
        'comment_allow_reply' => TRUE,
        'comment_allow_post' => TRUE,
        'comment_allow_ratings' => FALSE,
        'comment_allow_vote' => TRUE,
        'comment_once' => FALSE,
        'comment_echo' => FALSE,
        'comment_title' => '',
        'comment_form_title' => '',
        'comment_count' => TRUE,
        'comment_bbcode' => TRUE,
        'comment_tinymce' => FALSE,
        'comment_tinymce_skin' => 'lightgray',
        'comment_custom_script' => FALSE,
        'comment_post_callback_function' => '', // trigger custom functions during post comment event
        'comment_edit_callback_function' => '',  // trigger custom functions during reply event
        'comment_delete_callback_function' => '', // trigger custom functions during delete event
        "comment_avatar_size" => "32px",
        "comment_form_avatar_size" => "40px",
    ];

    protected $locale;

    public $userdata;

    protected $settings;

    protected $post_link;

    protected $c_arr = [
        'c_con' => [],
        'c_info' => [
            'c_makepagenav' => FALSE,
            'admin_link' => FALSE,
        ],
    ];

    public $comment_params = [];

    public $comment_param_data = "";

    public $comment_data = [];

    protected $cpp;

    /**
     * Removes comment reply
     *
     * @param string $clink
     *
     * @return string
     */
    protected static $clink = [];

    protected static $c_start = 0;

}
