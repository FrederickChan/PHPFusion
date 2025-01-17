<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Introduction.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion\Installer\Steps;

use PHPFusion\Installer\InstallCore;
use PHPFusion\Installer\Requirements;

/**
 * Class Introduction
 *
 * @package PHPFusion\Steps
 */
class Introduction extends InstallCore {

    /**
     * @return string
     */
    public function view() {

        if ($mode = $this->recovery()) {

            return $mode;
        } else if ($mode = $this->index()) {

            return $mode;
        }
        return "";
    }

    /**
     * @return string
     */
    public function recovery() {

        // Reset connection session if any during the initialization step.
        session_remove( "db_config_connection" );

        if (self::$connection = self::fusionGetConfig( BASEDIR . 'config_temp.php' )) {

            $validation = Requirements::getSystemValidation();

            if ($current_version = fusion_get_settings( 'version' )) {

                if (isset( $validation[3] )) {

                    if (version_compare( self::BUILD_VERSION, $current_version, ">" )) {

                        return $this->stepUpgrade();

                    } else {

                        return $this->recoveryConsole();
                    }
                }
                die( "Not a valid Super Administrator" );

            } else {
                die( "No table to upgrade or recover from" );
            }
        }

        return FALSE;

    }

    /**
     * @return string
     */
    private function stepUpgrade() {
        /*
         * Here we already have a working database, but config is not done so there will be errors.
         * Now I've already cured the config_temp.php to PF9 standard config_temp.php
         * All we need to do left is checking on the system, so we'll send to start with STEP2
         */
        $_GET['upgrade'] = TRUE;
        $_POST['license'] = TRUE;
        $this->installerStep( self::STEP_INTRO );
        return $this->index();
    }

    /**
     * @return string
     */
    private function index() {

        if (isset( $_POST['step'] ) && $_POST['step'] == 1) {
            if (isset( $_POST['license'] )) {
                $_SESSION['step'] = self::STEP_PERMISSIONS;
                redirect( FUSION_SELF . "?localeset=" . LANGUAGE );
            } else {
                redirect( FUSION_SELF . "?error=license&localeset=" . LANGUAGE );
            }
        }

        $content = "<h2 class='title'>" . (isset( $_GET['upgrade'] ) ? self::$locale['setup_0022'] : self::$locale['setup_0002']) . "</h2>\n";
        $content .= "<p>" . (isset( $_GET['upgrade'] ) ? self::$locale['setup_0023'] : self::$locale['setup_0003']) . "</p>\n";
        $content .= "<p>" . self::$locale['setup_1001'] . "</p>\n";
        $content .= "<hr/>";

        $content .= "<h3>" . self::$locale['setup_1000'] . "</h3>\n";
        $content .= form_select( 'localeset', '', LANGUAGE,
            [
                'options' => self::$locale_files,
            ]
        );
        if (isset( $_GET['error'] ) && $_GET['error'] == 'license') {
            $content .= "<div class='alert alert-danger'>" . self::$locale['setup_5000'] . "</div>\n";
        }

        $content .= form_checkbox( 'license', self::$locale['setup_0005'], '',
            [
                'required'      => TRUE,
                'error_text'    => self::$locale['setup_5000']
            ]
        );

        add_to_jquery( '
            $("#step").attr("disabled", true);
            $("#license").on("click", function() {
                if ($(this).is(":checked")) {
                    $("#step").attr("disabled", false);
                } else {
                    $("#step").attr("disabled", true);
                }
            });
        ' );

        add_to_jquery( "
        $('#localeset').bind('change', function() {
            var value = $(this).val();
            document.location.href='" . FUSION_SELF . "?localeset='+value;
        });
        " );

        self::$step = [
            1 => [
                'name'  => 'step',
                'label' => self::$locale['setup_0121'],
                'value' => self::STEP_INTRO
            ]
        ];

        return $content;
    }

    /**
     * @return string
     */
    private function recoveryConsole() {

        $content = "<h4 class='title'>" . self::$locale['setup_1002'] . "</h4>\n";

        if (check_post( "htaccess" )) {

            require_once(INCLUDES . 'htaccess_include.php');
            write_htaccess();
            addnotice( 'success', self::$locale['setup_1020'] );
            $this->installerStep( self::STEP_INTRO );
            redirect( FUSION_SELF . "?localeset=" . LANGUAGE );

        }

        if (check_post( "uninstall" )) {
            $coretables = \PHPFusion\Installer\Lib\CoreTables::get_core_tables( self::$localeset );
            $i = 0;
            foreach (array_keys( $coretables ) as $table) {
                $result = dbquery( "DROP TABLE IF EXISTS " . self::$connection['db_prefix'] . $table );
                if ($result) {
                    $i++;
                    usleep( 600 );
                    //continue;
                }
            }
            @unlink( BASEDIR . 'config_temp.php' );
            @unlink( BASEDIR . 'config.php' );
            @unlink( BASEDIR . '.htaccess' );
            // go back to the installer
            $_SESSION['step'] = self::STEP_INTRO;
            addnotice( 'danger', "<strong>" . self::$locale['setup_0125'] . "</strong>" );
            $content .= rendernotices( getnotices() );
            if ($i == count( $coretables )) {
                redirect( filter_input( INPUT_SERVER, 'REQUEST_URI' ), 6 );
            }
        } else {
            // Exit Installer
            $content .= "<span class='display-block m-t-20 m-b-20'>" . self::$locale['setup_1003'] . "</span>\n";
            $content .= "<hr/>\n";
            $content .= rendernotices( getnotices() );
            $content .= form_hidden( 'localeset', '', LANGUAGE );
            $content .= "<h5 class='title'>" . self::$locale['setup_1017'] . "</h5>\n";
            $content .= "<p>" . self::$locale['setup_1018'] . "</p>\n";
            $content .= form_button( 'step', self::$locale['setup_1019'], self::STEP_EXIT, ['class' => 'btn-success'] );
            $content .= "<hr/>\n";

            // Change Primary Admin Details
            $content .= "<h5 class='title'>" . self::$locale['setup_1011'] . "</h5>\n";
            $content .= "<p>" . self::$locale['setup_1012'] . "</p>\n";
            $content .= form_button( 'step', self::$locale['setup_1013'], self::STEP_TRANSFER, ['class' => 'btn-primary'] );
            $content .= "<hr/>\n";

            // Infusions Installer
            $content .= "<h5 class='title'>" . self::$locale['setup_1008'] . "</h5>\n";
            $content .= "<p>" . self::$locale['setup_1009'] . "</p>\n";
            $content .= form_button( 'step', self::$locale['setup_1010'], self::STEP_INFUSIONS, ['class' => 'btn-primary'] );
            $content .= "<hr/>\n";

            // Build htaccess
            if (isset( self::$connection['db_prefix'] )) {
                $content .= "<h5 class='title'>" . self::$locale['setup_1014'] . "</h5>\n";
                $content .= "<p>" . self::$locale['setup_1015'] . "</p>\n";
                $content .= form_button( 'htaccess', self::$locale['setup_1014'], 'htaccess', ['class' => 'btn-default'] );
                $content .= "<hr/>\n";
            }

            $content .= "<h5 class='title'>" . self::$locale['setup_1004'] . "</h5>\n";
            $content .= "<p>" . self::$locale['setup_1005'] . "</p><p class='text-danger strong'>" . self::$locale['setup_1006'] . "</p>\n";
            $content .= form_button( 'uninstall', self::$locale['setup_1007'], 'uninstall', ['class' => 'btn-danger'] );
        }

        return $content;
    }
}
