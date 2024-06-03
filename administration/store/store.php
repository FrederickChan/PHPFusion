<?php

class Store {

    private static $instance;

    public function __construct() {}

    public static function getInstance()
    : Store {

        if ( ! self::$instance ) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function buttons() {

        return '
    <a href="" class="btn btn-default"><span>Infusions</span></a>
    <a href="" class="btn btn-default"><span>Themes</span></a>
    <a href="" class="btn btn-default"><span>Templates</span></a>
    <a href="" class="btn btn-default"><span>User fields</span></a>
    <a href="" class="btn btn-default"><span>Widgets</span></a>
    ';
    }

    public function navlinks() {
        return [
            [ 'title' => 'Featured', 'link' => BASEDIR ],
            [ 'title' => 'Popular', 'link' => BASEDIR ],
            [ 'title' => 'Recommended', 'link' => BASEDIR ],
            [ 'title' => 'Favorites', 'link' => BASEDIR ],
        ];
    }

    public function latestItems() {
        // central db must go with other db.

        // damn.. i need to have the stats and installed data
    }


}

require_once __DIR__.'/../store/query.php';
