<?php
require('Question.class.php');
class Poll
{
    //id ankiety w bazie
    private $id;
    //tytuł (nagłówek) ankiety w bazie
    private $title;
    //tablica z pytaniami w formie obiektów klasy Question
    private $questions;

    function __construct($id = 0, $title = "")
    {
        global $db;
        if ($id == 0) {
            //tworzymy nową ankietę w bazie
            $q = $db->prepare("INSERT INTO poll VALUES (NULL, ?)");
            $q->bind_param("s", $title);
            $q->execute();
            $this->id = $db->insert_id;
            //szykujemy pustą tablicę na pytania
            $this->questions = array();
        } else {
            //zaczytaj ankietę z bazy danych
            $q = $db->prepare("SELECT * FROM poll WHERE id = ?");
            $q->bind_param("i", $id);
            $q->execute();
            $result = $q->get_result();
            $row = $result->fetch_assoc();
            $this->id = $id;
            $this->title = $row['title'];
            //szykujemy pustą tablicę na pytania
            $this->questions = array();
            //łądujemy pytania z bazy danych 
            $q = $db->prepare("SELECT * FROM question WHERE poll_id = ?");
            $q->bind_param("i", $this->id);
            $q->execute();
            $result = $q->get_result();
            while ($row = $result->fetch_assoc()) {
                $questionID = $row['id'];
                $question = new Question($questionID);
                array_push($this->questions, $question);
            }
        }
    }
    public function saveResult(array $answers)
    {
        global $db;
        $q = $db->prepare("INSERT INTO result VALUES (NULL, NULL)");
        //utwórz wpis w bazie danych o nowej wypełnionej ankiecie
        $result = $q->execute();
        //pobierz id nowej wypełnionej ankiety
        $resultID = $db->insert_id;

        $q = $db->prepare("INSERT INTO resultanswer VALUES (NULL, ?, ?)");

        foreach ($answers as $answerID) {
            $q->bind_param("ii", $resultID, $answerID);
            $q->execute();
        }
        echo "Ankieta została zapisana";
    }
    public function getResults()
    {
        global $db;

        
        echo "Wyniki dla ankiety od id: " . $this->id . "<br>";

        
        $q = $db->prepare("SELECT result.id as result_id, question.id as question_id, answer.id as answer_id
            FROM result 
            LEFT JOIN resultanswer on resultanswer.result = result.id
            LEFT JOIN answer on resultanswer.answer = answer.id
            LEFT JOIN question on answer.question_id = question.id
            WHERE question.poll_id = ?");
        //podstaw id ankiety
        $q->bind_param("i", $this->id);
        //wykonajh
        $q->execute();
        $result = $q->get_result();
        //policz odpowiedzi na każde pytanie w ramach ankiety
        while($row = $result->fetch_assoc()) {
            $answerID = $row['answer_id'];
            foreach($this->questions as $question)
            {
                $question->addAnswerCount($answerID);
            }
        }
        //wypisz informacje o uzyskanych odpowiedziach
        foreach($this->questions as $question) {
            echo "Pytanie o id: " . $question->get()['id'] . "<br>";
            echo "Treść pytania: " . $question->get()['content'] . "<br>";
            $answerSum = count($question->get()['answers']);
            foreach($question->get()['answers'] as $answer) 
            {
                $answerID = $answer->get()['id'];
                $answerContent = $answer->get()['content'];
                $answerCount = $answer->get()['count'];
                $answerPercent = round(($answerCount / $answerSum) * 100, 3);
                echo "Odpowiedzi \"$answerContent\" udzielono $answerCount razy ($answerPercent%)<br>";
            }
        }
    }
    public function echoPoll()
    {
        echo "Wyświetlam ankietę o id: ".$this->id;
        //wydrukuj nagłówek formularza
        echo "<form action=\"/poll/save\" method=\"post\">";
        //wydrukuj kolejno wszystkie pytania z ankiety
        foreach ($this->questions as $question) {
            $question->echoQuestion();
        }
        //wydrukuj guzik do wysłania ankiety
        echo "<input type=\"submit\">";
        //zamknij formularz
        echo "</form>";
    }
}
