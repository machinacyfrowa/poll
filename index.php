<?php
require_once('Route.php'); //klasa do routingu - przetwarza tzw. friendly url
require_once('Poll.class.php'); //podstawowa klasa ankiety
$db = new mysqli('localhost', 'root', '', 'poll');

use Steampixel\Route; //importujemy przestrzeń nazw

Route::add('/poll/([0-9]*)', function($poll_id) { //dodajemy uniwersaną ścieżkę wyświetlającą ankiety
    //wyświetl ankietę o id podanym w url po /poll/
    echo "Wyświetlam ankietę o id: ".$poll_id;
    //utwórz obiekt ankiety przekazując dane
    $p = new Poll($poll_id);
    //wyświetl ankietę
    $p->echoPoll();

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
    //wyciągnij z bazy danych wszystkie odpowiedzi na ankietę o podanym ID zacyznając od tabeli zawierającej nadesłane odpowiedzi (result),
    //poprzez tabele zawierająca odpowiedzi na pytania udzielone w ramach jednej nadesłanej odpowiedzi (resultanswer),
    //dodając do tego treść odpowiedzi i treść pytań z tabel question i result
    $q = $db->prepare("SELECT result.id as result_id, question.id as question_id, 
                        question.content as question_content, answer.id as answer_id, 
                        answer.content as answer_content
                        FROM result 
                        LEFT JOIN resultanswer on resultanswer.result = result.id
                        LEFT JOIN answer on resultanswer.answer = answer.id
                        LEFT JOIN question on answer.question_id = question.id
                        WHERE question.poll_id = ?");
    //podstaw id ankiety
    $q->bind_param("i", $poll_id);
    //wykonajh
    $q->execute();
    $result = $q->get_result();
    //tablica do przechowania treści pytań i udzielonych odpowiedzi
    $questions = array();
    //tablica na treść odpowiedzi
    $answerContent = array();
    while($row = $result->fetch_assoc()) {
        //id pytania
        $questionID = $row['question_id'];
        //treść pytania
        $questionContent = $row['question_content'];
        //id odpowiedzi
        $answerID = $row['answer_id'];
        //tworzymy sobie w tabeli $questions pod indeksem zgodnym z id pytania pole o nazwie content i wstawiamy tam tresć pytania
        $questions[$questionID]['content'] = $questionContent;
        //jeżeli nie zapisano wcześniej żadnej odpowiedzi na to pytanie to utwórz pustą tablicę o nazwie answers wewnątz tablicy $questions 
        //pod id zgodnym z id pytania
        if(!isset($questions[$questionID]['answers']))
            $questions[$questionID]['answers'] = array();
        //wepchnij udzieloną odpowiedź do odpowiedniego pytania (weedług jego id)
        array_push($questions[$questionID]['answers'], $answerID);
        //zachowaj treść odpowiedzi w tablicy $answerContent pod id tej odpowiedzi
        $answerContent[$answerID] = $row['answer_content'];
    }
    foreach($questions as $question) {
        //iteruj przez wszystkie pytania
        //wypisz treść pytania
        echo "Treść pytania: ".$question['content']."<br>";
        //tabela do zliczania ilość odpowiedzi udzielonych na dane pytanie
        $answerCount = array();
        foreach($question['answers'] as $answer) {
            //jeśli nie było wcześniej takiej odpowiedzi to ustaw 1
            if(!isset($answerCount[$answer]))
                $answerCount[$answer] = 1;
            //jesli byłą to dodaj 1
            else 
                $answerCount[$answer] += 1;
        }
        //policz sumę wsyzstkich udzielonych odpowiedzi na dane pytanie
        $questionAnswerSum = array_sum($answerCount);
        foreach($answerCount as $key => $count) {
            //pobierz treść pytania
            $answer = $answerContent[$key];
            //policz procentową ilość tej odpowiedzi
            $percent = round(($count / $questionAnswerSum)*100, 3);
            //wypisz dane
            echo "Odpowiedzi \"$answer\" udzielono $count razy, co stanowi $percent% wszystkich odpowiedzi.<br>";
        }
        echo "<br>";
    }
});
//uruchom routing (musi byc na końcu po deklaracji dostępnych ścieżek)
Route::run('/');

?>