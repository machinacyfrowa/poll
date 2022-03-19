<?php
require_once('Route.php'); //klasa do routingu - przetwarza tzw. friendly url
require_once('Poll.class.php'); //podstawowa klasa ankiety

use Steampixel\Route; //importujemy przestrzeń nazw

Route::add('/poll/([0-9]*)', function($poll_id) { //dodajemy uniwersaną ścieżkę wyświetlającą ankiety
    //wyświetl ankietę o id podanym w url po /poll/
    echo "Wyświetlam ankietę o id: ".$poll_id;
    //łączymy się z bazą
    $db = new mysqli('localhost', 'root', '', 'poll');
    //wykonujemy zapytanie przez 3 tabele - ankiety, pytania i odpowiedzi
    $q = $db->prepare("SELECT poll.id AS poll_id, poll.title AS poll_title, 
                        question.id AS question_id, question.content AS question_content, 
                        answer.id AS answer_id, answer.content AS answer_content 
                        FROM poll 
                        LEFT JOIN question on poll.id = question.poll_id 
                        LEFT JOIN answer on question.id = answer.question_id 
                        WHERE poll.id=?");
    // dopisujemy do kwerendy id ankiety która chcemy wczytać
    $q->bind_param("i", $poll_id);
    if($q->execute()) { //jeżeli kwerenda wykonała się poprawnie
        $result = $q->get_result(); //pobierz dane
        if($result->num_rows >= 1) { //jeśli więcej niż zero wierszy 
            //istnieje taka ankieta - wyświetl
            //stwórz tablice na dane
            $data = array();
            while($row = $result->fetch_assoc()) {
                //przepisz wiersz po wierszu do tablicy na dane
                array_push($data, $row);
            }
            //utwórz obiekt ankiety przekazując dane
            $p = new Poll($data);
            //wyświetl ankietę
            $p->echoPoll();
        } else {
            //uruchomi się jeśli podamy id bez odpowiadającej ankiety w bazie danych
            echo "Nie istnieje ankieta o takim ID";
        }
    } else {
        //uruchomi się jeśli nie da się wykonać kwerendy do bazy danych
        echo "Błąd wykonania zapytania do bazy";
    }
});

Route::add('/poll/save', function() {
    echo "Ankieta została zapisana";
    //TODO: faktycznie przyjmij dane i zapisz odpowiedzi do bazy danych
}, 'post');

//uruchom routing (musi byc na końcu po deklaracji dostępnych ścieżek)
Route::run('/');

?>