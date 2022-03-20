<?php
require('Question.class.php');
class Poll {
    //id ankiety w bazie
    private $id;
    //tytuł (nagłówek) ankiety w bazie
    private $title;
    //tablica z pytaniami w formie obiektów klasy Question
    private $questions;

    function __construct($id = 0, $title = "")
    {
        global $db;
        if($id == 0) {
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
            while($row = $result->fetch_assoc()) {
                $questionID = $row['id'];
                $question = new Question($questionID);
                array_push($this->questions, $question);
            }
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