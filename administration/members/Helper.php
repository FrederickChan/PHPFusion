<?php

namespace Pro\Admin\Members;

/**
 * Helper Class for Member Administration
 */
class Helper {

    const USER_MEMBER = 0;

    const USER_BAN = 1;

    const USER_REINSTATE = 2;

    const USER_SUSPEND = 3;

    const USER_SECURITY_BAN = 4;

    const USER_CANCEL = 5;

    const USER_ANON = 6;

    const USER_DEACTIVATE = 7;

    const USER_UNACTIVATED = 2;

    /**
     * @var int
     */
    private static $status;

    /**
     * @return int
     */
    public static function getStatus(): int {
        return self::$status;
    }

    /**
     * @return string
     */
    public static function getUsrMysqlStatus() {
        return self::$usr_mysql_status;
    }

    /**
     * @return string|string[]|null
     */
    public function getSettings() {
        return $this->settings;
    }

    /**
     * @return float|int
     */
    public function getTimeOverdue() {
        return $this->time_overdue;
    }

    /**
     * @return mixed|string
     */
    public function getDeactivationPeriod() {
        return $this->deactivation_period;
    }

    /**
     * @return float|int
     */
    public function getResponseRequired() {
        return $this->response_required;
    }

    /**
     * @return string
     */
    public function getAidlink(): string {
        return $this->aidlink;
    }

    /**
     * @return array|string
     */
    public function getLocale() {
        return $this->locale;
    }

    /**
     * @return int|mixed
     */
    public function getUserId() {
        return $this->user_id;
    }

    /**
     * @return bool
     */
    public function getIsAdmin(): bool {
        return $this->is_admin;
    }

    /**
     * @var string
     */
    private static $usr_mysql_status;
    /**
     * @var string|string[]|null
     */
    private $settings;
    /**
     * @var float|int
     */
    private $time_overdue;
    /**
     * @var mixed|string
     */
    private $deactivation_period;
    /**
     * @var float|int
     */
    private $response_required;
    /**
     * @var string
     */
    private $aidlink;
    /**
     * @var array|string
     */
    private $locale;
    /**
     * @var int|mixed
     */
    private $user_id;
    /**
     * @var bool
     */
    private $is_admin;

    /**
     * Helper constructor
     */
    public function __construct() {

        $this->settings = fusion_get_settings();

        $this->time_overdue = time() - (86400 * $this->settings['deactivation_period']);

        $this->deactivation_period = $this->settings['deactivation_period'];

        $this->response_required = time() + (86400 * $this->settings['deactivation_response']);

        $this->aidlink = fusion_get_aidlink();

        $this->locale = fusion_get_locale('', [
            LOCALE.LOCALESET."admin/members.php",
            LOCALE.LOCALESET.'admin/members_include.php',
            LOCALE.LOCALESET.'admin/members_email.php',
            LOCALE.LOCALESET."user_fields.php"
        ]);

        //self::$rowstart = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) ? $_GET['rowstart'] : 0);

        //self::$sortby = (isset($_GET['sortby']) ? stripinput($_GET['sortby']) : "all");

        self::$status = $this->setStatus();

        self::$usr_mysql_status = (isset($_GET['usr_mysql_status']) && isnum($_GET['usr_mysql_status'] && $_GET['usr_mysql_status'] < 9) ? $_GET['usr_mysql_status'] : 0);

        if (self::$status == 0 && fusion_get_settings('enable_deactivation') == 1) {

            self::$usr_mysql_status = "0' AND user_lastvisit>'".$this->time_overdue."' AND user_actiontime='0";

        } else if (self::$status == 8 && fusion_get_settings('enable_deactivation') == 1) {

            self::$usr_mysql_status = "0' AND user_lastvisit<'".$this->time_overdue."' AND user_actiontime='0";
        }


        //self::$status_uri = [
        //    self::USER_MEMBER       => $base_url."&amp;status=".self::USER_MEMBER,
        //    self::USER_UNACTIVATED  => $base_url."&amp;status=".self::USER_UNACTIVATED,
        //    self::USER_BAN          => $base_url."&amp;status=".self::USER_BAN,
        //    self::USER_SUSPEND      => $base_url."&amp;status=".self::USER_SUSPEND,
        //    self::USER_SECURITY_BAN => $base_url."&amp;status=".self::USER_SECURITY_BAN,
        //    self::USER_CANCEL       => $base_url."&amp;status=".self::USER_CANCEL,
        //    self::USER_ANON         => $base_url."&amp;status=".self::USER_ANON,
        //    self::USER_DEACTIVATE   => $base_url."&amp;status=".self::USER_DEACTIVATE,
        //    'add_user'              => $base_url.'&amp;ref=add',
        //    'view'                  => $base_url.'&amp;ref=view&amp;lookup=',
        //    'edit'                  => $base_url.'&amp;ref=edit&amp;lookup=',
        //    'delete'                => $base_url.'&amp;ref=delete&amp;lookup=',
        //    'login_as'              => $base_url."&amp;ref=login&amp;lookup=",
        //    'inactive'              => $base_url.'&amp;ref=inactive',
        //    'resend'                => $base_url.'&amp;ref=resend&amp;lookup=',
        //    'activate'              => $base_url.'&amp;ref=activate&amp;lookup=',
        //];

        $this->user_id = $this->setUserID();

        $this->is_admin = $this->checkIsAdmin();

    }

    /**
     * @return int
     */
    private function setStatus(): int {
        $status = get('status', FILTER_VALIDATE_INT);

        return ($status && $status < 9 ? $status : 0);
    }

    /**
     * @return int
     */
    private function setUserID(): int {
        $lookup = get('id', FILTER_VALIDATE_INT);

        return ($lookup && dbcount('(user_id)', DB_USERS, 'user_id=:user_id', [':user_id' => $lookup]) ? $lookup : 0);
    }

    /**
     * @return bool
     */
    private function checkIsAdmin(): bool {

        if (dbcount("(user_id)", DB_USERS, "user_id=:user_id AND user_level<:user_level", [
                ':user_id'    => $this->user_id,
                ':user_level' => USER_LEVEL_MEMBER,
            ]) > 0
        ) {
            return TRUE;

        }
        return FALSE;
    }


}
