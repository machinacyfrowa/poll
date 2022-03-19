<?php

class Question {
    private $id;
    private $content;
    private $answers;

    function __construct(int $i, string $c) {
        $this->answers = array();
        $this->id = $i;
        $this->content = $c;

    }
    public function addAnswer(int $id, string $content) {

        $this->answers[$id] = $content;
    }
    public function echoQuestion() {
        echo "<h3>".$this->content."</h3>";
        
        foreach($this->answers as $answer) {
            $answerID = array_search($answer, $this->answers);
            echo "<input type=\"radio\" name=\"question".$this->id."\"
                    value=\"".$answerID."\">".$answer."<br>";
        }
        
    }
}

?>