<?php
require_once('Route.php');
require_once('Poll.class.php');

use Steampixel\Route;

Route::add('/poll/([0-9]*)', function($poll_id) {
    //wyświetl ankietę o id podanym w url po /poll/
    echo "Wyświetlam ankietę o id: ".$poll_id;
    $db = new mysqli('localhost', 'root', '', 'poll');
    $q = $db->prepare("SELECT poll.id AS poll_id, poll.title AS poll_title, 
                        question.id AS question_id, question.content AS question_content, 
                        answer.id AS answer_id, answer.content AS answer_content 
                        FROM poll 
                        LEFT JOIN question on poll.id = question.poll_id 
                        LEFT JOIN answer on question.id = answer.question_id 
                        WHERE poll.id=?");
    $q->bind_param("i", $poll_id);
    if($q->execute()) {
        $result = $q->get_result();
        if($result->num_rows >= 1) {
            //istnieje taka ankieta - wyświetl
            $poll = array();
            while($row = $result->fetch_assoc()) {
                array_push($poll, $row);
                /*
                $questionID = $row['question_id'];
                $questionContent = $row['question_content'];
                $answerID = $row['answer_id'];
                $answer = $row['answer_content'];
                $poll[$questionID]['content'] = $questionContent;
                $poll[$questionID][$answerID];
                */
            }
            $p = new Poll($poll);
            
        } else {
            echo "Nie istnieje ankieta o takim ID";
        }
    } else {
        echo "Błąd wykonania zapytania do bazy";
    }
});

Route::run('/');

?>