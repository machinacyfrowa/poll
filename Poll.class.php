<?php
require('Question.class.php');
class Poll {
    private $id;
    private $title;
    private $questions;

    function __construct($data)
    {
        $this->questions = array();
        foreach($data as $row) {
            if(!isset($this->id)) {
                $this->id = $row['poll_id'];
                $this->title = $row['poll_title'];
            }
            $questionID = $row['question_id'];
            if(!isset($this->questions[$questionID])) {
                $questionContent = $row['question_content'];
                $this->questions[$questionID] = 
                    new Question($questionID, $questionContent);
            } 
            $this->questions[$questionID]->
                    addAnswer($row['answer_id'], $row['answer_content']);
            
        }
        
    }
    public function echoPoll() {
        echo "<form action=\"/poll/save\" method=\"post\">";
        foreach($this->questions as $question) {
            $question->echoQuestion();
        }
        echo "<input type=\"submit\">";
        echo "</form>";
    }

}
?>