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
}

?>