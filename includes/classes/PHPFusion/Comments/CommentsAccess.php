<?php

namespace PHPFusion\Comments;
/**
 * Class CommentsAccess
 * @package PHPFusion\Comments
 */
class CommentsAccess {

    private static $parent;
    private static $locale;

    /**
     * CommentsAccess constructor.
     * @param \PHPFusion\Comments $comments
     */
    public function __construct(\PHPFusion\Comments $comments) {
        self::$parent = $comments;
        self::$locale = fusion_get_locale();
    }
    /**
     * Check permissions
     */
    public function checkPermissions() {

        $my_id = fusion_get_userdata("user_id");

        if (dbcount("(rating_id)", DB_RATINGS, "rating_user=:name AND rating_item_id=:item_id AND rating_type=:type", [
            ":name" => $my_id,
            ":item_id" => self::$parent->getParams("comment_item_id"),
            ":type" => self::$parent->getParams("comment_item_type"),
        ])) {
            self::$parent->replaceParam('comment_allow_vote', TRUE); // allow post
        }

        if (dbcount("(comment_id)", DB_COMMENTS, "comment_name=:name AND comment_cat=:cat_id AND comment_item_id=:item_id AND comment_type=:type", [
                    ":name" => $my_id,
                    ":cat_id" => 0,
                    ":item_id" => self::$parent->getParams("comment_item_id"),
                    ":type" => self::$parent->getParams("comment_item_type"),
                ]
            ) && self::$parent->getParams('comment_once')) {

            self::$parent->replaceParam('comment_allow_post', FALSE); // allow post
        }
    }



}
