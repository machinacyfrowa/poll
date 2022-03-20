<?php
class Answer {
    private $id;
    private $content;
    
    function __construct(int $i, string $c)
    {
        $this->id = $i;
        $this->content = $c;
    }

    function get() : array {
        return array('id'       => $this->id,
                     'content'  => $this->content);
    }
}


?>