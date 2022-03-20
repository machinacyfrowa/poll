<?php
class Answer {
    private $id;
    private $content;
    private $count;
    
    function __construct(int $i, string $c)
    {
        $this->id = $i;
        $this->content = $c;
    }

    function get() : array {
        return array('id'       => $this->id,
                     'content'  => $this->content,
                     'count'    => $this->count);
    }
    function addCount() {
        $this->count++;
    }
}


?>