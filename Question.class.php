<?php
require('Answer.class.php');
class Question {
    //id pytania w bazie danych
    private $id;
    //treść pytanie w bazie danych
    private $content;
    //tablica zawierająca odpowiedzi jako obiekty klasy Answer
    private $answers;

    
    function __construct(int $i = 0, string $c = "", int $poll_id = 0) {
        global $db;
        if($i == 0 && strlen($c) > 0 && $poll_id != 0) {
            //tworzymy nowe pytanie
            $this->content = $c;
            //zapisz je do bazy
            $q = $db->prepare("INSERT INTO question VALUES (NULL, ?, ?)");
            $q->bind_param("is", $poll_id, $this->content);
            $q->execute();
            $this->id = $db->insert_id;
            //uwtorz pusta tablicę na odpowiedzi
            $this->answers = array();
        }
        if($i != 0 && $c == "") {
            //zaciągnij pytanie z bazy danych
            $this->id = $i;
            //zaciągnij content z bazy danych
            $q = $db->prepare("SELECT content FROM question WHERE id = ?");
            $q->bind_param("i", $this->id);
            $q->execute();
            $result = $q->get_result();
            $row = $result->fetch_assoc();
            $this->content = $row['content'];
            $this->getAnswers();
        }
        
    }
    private function getAnswers() {
        global $db;
        $q = $db->prepare("SELECT * FROM answer WHERE question_id = ?");
        $q->bind_param("i", $this->id);
        $q->execute();
        $result = $q->get_result();
        while($row = $result->fetch_assoc()) {
            //iteruj przez odpowiedzi na to pytanie
            $answerID = $row['id'];
            $answerContent = $row['content'];
            $answer = new Answer($answerID, $answerContent);
            array_push($this->answers, $answer);
        }
    }
    //deprecated: do usunięcia po refactoringu
    public function addAnswer(int $id, string $content) {
        //dopisz odpowiedz do pytania pod id zgodnym z id w bazie danych
        $this->answers[$id] = $content;
    }
    public function echoQuestion() {
        //wydrukuj nagłówek - treść pytrania
        echo "<h3>".$this->content."</h3>";
        //dla każdej z zapisanych odpowiedzi
        foreach($this->answers as $answer) {
            //znajdz id dla danej odpowiedzi
            $answerID = array_search($answer, $this->answers);
            //wydrukuj  guzik typu radio i przypisz do niego zarówno
            //name czyli identyfikator pytania
            //oraz value czyli identyfikator udzielonej odpowiedzi
            echo "<input type=\"radio\" name=\"question".$this->id."\"
                    value=\"".$answerID."\">".$answer."<br>";
        }
        
    }
}

?>