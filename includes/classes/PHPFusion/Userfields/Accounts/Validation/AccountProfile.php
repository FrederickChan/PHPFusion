<?php
namespace PHPFusion\Userfields\Accounts\Validation;

use PHPFusion\QuantumFields;
use PHPFusion\Userfields\UserFieldsValidate;

class AccountProfile extends UserFieldsValidate {

    private $_quantum;

    public function setAccountProfile() {

        $locale = fusion_get_locale();
        $settings = fusion_get_settings();

        $this->_data['user_id'] = $this->userFieldsInput->userData['user_id'];
        $this->_data['user_name'] = $this->accountUsername()->verifyUserName();
        $this->_data['user_firstname'] = sanitizer( 'user_firstname', '', 'user_firstname' );
        $this->_data['user_lastname'] = sanitizer( 'user_lastname', '', 'user_lastname' );
        $this->_data['user_addname'] = sanitizer( 'user_addname', '', 'user_addname' );
        $this->_data['user_displayname'] = sanitizer( 'user_displayname', '0', 'user_displayname' );
        $this->_data['user_phonecode'] = sanitizer( 'user_phonecode', '', 'user_phonecode' );
        $this->_data['user_phone'] = sanitizer( 'user_phone', '', 'user_phone' );
        $this->_data['user_bio'] = sanitizer( 'user_bio', '', 'user_bio' );

        if ( $_input = $this->setCustomUserFields() ) {
            foreach ( $_input as $input_values ) {
                $this->_data += $input_values;
            }
        }

        // id request spoofing request
        if ( $this->userFieldsInput->checkUpdateAccess() ) {

            // Log username change
            if ( $settings['username_change'] && $this->_data['user_name'] !== $this->userFieldsInput->userData['user_name'] ) {
                save_user_log( $this->userFieldsInput->userData['user_id'], 'user_name', $this->_data['user_name'], $this->userFieldsInput->userData['user_name'] );
            }

            // Log all custom field changes
            $this->_quantum->logUserAction( DB_USERS, 'user_id' );

            // Update Table
            dbquery_insert( DB_USERS, $this->_data, 'update' );

            //dbquery_insert( DB_USER_SETTINGS, $this->data, 'update', ['primary_key' => 'user_id'] );
            addnotice( 'success', $locale['u163'] . "\nYour personal information has been successfully updated." );

            redirect( BASEDIR . 'edit_profile.php' );

        } else {

            fusion_stop();
            addnotice( 'danger', $locale['error_request'] );
        }
    }


    /**
     * @return array
     */
    public function setCustomUserFields() {

        $this->_quantum = new QuantumFields();
        $this->_quantum->setFieldDb( DB_USER_FIELDS );
        $this->_quantum->loadFields();
        $this->_quantum->loadFieldCats();
        $this->_quantum->setCallbackData( $this->_data );

        return $this->_quantum->returnFieldsInput( DB_USERS, 'user_id' );
    }

}
