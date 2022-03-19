<?php
require_once('Route.php');

use Steampixel\Route;

Route::add('/poll/([0-9]*)', function($poll_id) {
    //wyświetl ankietę o id podanym w url po /poll/
    echo "Wyświetlam ankietę o id: ".$poll_id;
});

Route::run('/');

?>