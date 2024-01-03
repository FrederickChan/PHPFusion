<?php

// Check and limits
if ( !isset( $this->current_folder ) || !preg_check( "/^(inbox|outbox|archive)$/", $this->current_folder ) ) {
    $this->current_folder = 'inbox';
}
