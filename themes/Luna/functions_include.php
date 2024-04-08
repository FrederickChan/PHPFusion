<?php


function opentable($title = '', $class = '') {
    echo '<div class="card mb-4' . whitespace($class ?? '') . '">';

    if ($title) {
        echo '<div class="card-header">';
        echo '<h4 class="card-title mb-0">' . $title . '</h4>';
        echo '</div>';
    }

    echo '<div class="card-body">';
}

function tablebreak() {
    echo '</div><div class="card-body">';
}

function closetable() {
    echo '</div></div>';
}

function openside($title = '', $class = '') {
    echo '<div class="card mb-4' . whitespace($class ?? '') . '">';

    if ($title) {
        echo '<div class="card-header pb-0 border-0">';
        echo '<h5 class="card-title mb-0">' . $title . '</h5>';
        echo '</div>';
    }

    echo '<div class="card-body">';
}

function closeside() {
    echo '</div></div>';
}
