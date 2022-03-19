<?php

class Question {
    //id pytania w bazie danych
    private $id;
    //treść pytanie w bazie danych
    private $content;
    //tablica zawierająca odpowiedzi jako pary id => tresc odpowiedzi
    private $answers;

    
    function __construct(int $i, string $c) {
        //uwtorz pusta tablicę na odpowiedzi
        $this->answers = array();
        //zapisz id i trec pytania
        $this->id = $i;
        $this->content = $c;

    }
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