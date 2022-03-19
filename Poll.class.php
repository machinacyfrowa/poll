<?php
require('Question.class.php');
class Poll {
    //id ankiety w bazie
    private $id;
    //tytuł (nagłówek) ankiety w bazie
    private $title;
    //tablica z pytaniami w formie obiektów klasy Question
    private $questions;

    function __construct($data)
    {
        //szykujemy pustą tablicę na pytania
        $this->questions = array();
        foreach($data as $row) {
            //jeśli nie mamy jeszcze id i nazwy ankiety to pobieramy z danych
            if(!isset($this->id)) {
                $this->id = $row['poll_id'];
                $this->title = $row['poll_title'];
            }
            //pobierz id pytania
            $questionID = $row['question_id'];
            //jeśli nie ma tego pytania to stwórz "puste" - sam id i treść - bez odpowiedzi
            if(!isset($this->questions[$questionID])) {
                $questionContent = $row['question_content'];
                $this->questions[$questionID] = 
                    new Question($questionID, $questionContent);
            } 
            //dopisz odpowiedz do pytania
            $this->questions[$questionID]->
                    addAnswer($row['answer_id'], $row['answer_content']);
            
        }
        
    }
    public function echoPoll() {
        //wydrukuj nagłówek formularza
        echo "<form action=\"/poll/save\" method=\"post\">";
        //wydrukuj kolejno wszystkie pytania z ankiety
        foreach($this->questions as $question) {
            $question->echoQuestion();
        }
        //wydrukuj guzik do wysłania ankiety
        echo "<input type=\"submit\">";
        //zamknij formularz
        echo "</form>";
    }

}
?>