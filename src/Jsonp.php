<?php
namespace Phperf\HighCharts;

class Jsonp
{
    private $callback;
    private $data;

    public function __construct($data = null, $callback = null)
    {
        $this->data = $data;
        $this->callback = $callback;
    }

    public function isEmpty()
    {
        return empty($this->data);
    }

    public function render()
    {
        header('Content-Type: text/javascript; charset=utf8');
        echo $this->__toString();
        return $this;
    }

    public function __toString()
    {
        if (null === $this->callback && !empty($_GET['callback'])) {
            $this->callback = $_GET['callback'];
        }

        $result = $this->callback . '(' . json_encode($this->data) . ');';
        return $result;
    }

}