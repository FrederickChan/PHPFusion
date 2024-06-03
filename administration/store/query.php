<?php

use PHPFusion\Installer\InstallCore;

class Query {

    public function __construct() {
        $config = InstallCore::getInstance()::fusionGetConfig(BASEDIR.'config.php');
        // connect to the main database.
        $connection = custom_dbconnect('localhost', $config['db_user'], $config['db_pass'], 'store', 3306, 'storequery');
        // we need to start a webstore in another phpfusion.
        //        $connection->query($query);
    }

    public function latestItems() {

    }

}
