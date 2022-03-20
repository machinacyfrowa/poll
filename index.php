<?php
require_once('Route.php'); //klasa do routingu - przetwarza tzw. friendly url
require_once('Poll.class.php'); //podstawowa klasa ankiety
$db = new mysqli('localhost', 'root', '', 'poll');

use Steampixel\Route; //importujemy przestrzeń nazw

Route::add('/poll/([0-9]*)', function($poll_id) { //dodajemy uniwersaną ścieżkę wyświetlającą ankiety
    //wyświetl ankietę o id podanym w url po /poll/
    //utwórz obiekt ankiety przekazując dane
    $p = new Poll($poll_id);
    //wyświetl ankietę
    $p->echoPoll();

});

Route::add('/poll/save', function() {
    //TODO: faktycznie przyjmij dane i zapisz odpowiedzi do bazy danych
    $p = new Poll();
    $answers = array();
    foreach ($_POST as $key => $item) {
        if (strpos($key, "question") == 0) {
            //tu są tylko odpowiedzi na pytania
            array_push($answers, $item);
        }
    }
    $p->saveResult($answers);
}, 'post');

Route::add('/poll/([0-9]*)/results', function ($poll_id) {
    $p = new Poll($poll_id);
    $p->getResults();
    
});
//uruchom routing (musi byc na końcu po deklaracji dostępnych ścieżek)
Route::run('/');

?>