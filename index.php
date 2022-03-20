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
    //TODO: faktycznie przyjmij dane i zapisz odpowiedzi do bazy danych
    $db = new mysqli('localhost', 'root', '', 'poll');
    $q = $db->prepare("INSERT INTO result VALUES (NULL, NULL)");
    //utwórz wpis w bazie danych o nowej wypełnionej ankiecie
    $result = $q->execute();
    //pobierz id nowej wypełnionej ankiety
    $resultID = $db->insert_id;
    $answers = array();
    foreach($_POST as $key => $item) {
        if(strpos($key, "question") == 0) {
            //tu są tylko odpowiedzi na pytania
            array_push($answers, $item);
        }
    }
    $q = $db->prepare("INSERT INTO resultanswer VALUES (NULL, ?, ?)");
    
    foreach($answers as $answerID) {
        $q->bind_param("ii", $resultID, $answerID);
        $q->execute();
    }
    echo "Ankieta została zapisana";
    
}, 'post');

Route::add('/poll/([0-9]*)/results', function ($poll_id) {
    echo "Wyniki dla ankiety od id: ".$poll_id."<br>";
    $db = new mysqli('localhost', 'root', '', 'poll');
    $q = $db->prepare("SELECT result.id as result_id, question.id as question_id, 
                        question.content as question_content, answer.id as answer_id, 
                        answer.content as answer_content
                        FROM result 
                        LEFT JOIN resultanswer on resultanswer.result = result.id
                        LEFT JOIN answer on resultanswer.answer = answer.id
                        LEFT JOIN question on answer.question_id = question.id
                        WHERE question.poll_id = ?");
    
    $q->bind_param("i", $poll_id);
    $q->execute();
    $result = $q->get_result();
    $questions = array();
    $answerContent = array();
    while($row = $result->fetch_assoc()) {
        $questionID = $row['question_id'];
        $questionContent = $row['question_content'];
        $answerID = $row['answer_id'];
        
        $questions[$questionID]['content'] = $questionContent;
        if(!isset($questions[$questionID]['answers']))
            $questions[$questionID]['answers'] = array();
        array_push($questions[$questionID]['answers'], $answerID);
        $answerContent[$answerID] = $row['answer_content'];
    }
    foreach($questions as $question) {
        echo "Treść pytania: ".$question['content']."<br>";

        $answerCount = array();
        foreach($question['answers'] as $answer) {
            if(!isset($answerCount[$answer]))
                $answerCount[$answer] = 1;
            else 
                $answerCount[$answer] += 1;
        }
        $questionAnswerSum = array_sum($answerCount);
        foreach($answerCount as $key => $count) {
            $answer = $answerContent[$key];
            $percent = round(($count / $questionAnswerSum)*100, 3);
            echo "Odpowiedzi \"$answer\" udzielono $count razy, co stanowi $percent% wszystkich odpowiedzi.<br>";
        }
        echo "<br>";
    }
});
//uruchom routing (musi byc na końcu po deklaracji dostępnych ścieżek)
Route::run('/');

?>