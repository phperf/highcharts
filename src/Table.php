<?php

namespace Phperf\HighCharts;

use Closure;
use Phperf\Pipeline\Rows\Processor;

class Table
{
    const DATA_TYPE_REGULAR = 'regular';
    const DATA_TYPE_NAMED = 'named';

    public $dataType = self::DATA_TYPE_REGULAR;
    protected $tag = 'div';
    protected $content = '';

    /**
     * @var HighCharts
     */
    private $highCharts;

    private $rows;

    public function __construct($rows = null)
    {
        $this->rows = $rows;
        $this->highCharts = new HighCharts();
    }

    static $uniqueId = 0;

    /**
     * @return $this
     * @deprecated see withNamedSeries
     */
    public function withRegularSeries()
    {
        $this->dataType = self::DATA_TYPE_REGULAR;
        return $this;
    }

    /**
     * @return $this
     * @deprecated use Processor::create($rows)->combineOffset(2, 1) instead of rows
     */
    public function withNamedSeries()
    {
        $this->dataType = self::DATA_TYPE_NAMED;
        return $this;
    }

    private $filled = false;
    private function seriesFill()
    {
        if ($this->filled) {
            return;
        }

        if (self::DATA_TYPE_NAMED === $this->dataType) {
            $this->rows = Processor::create($this->rows)->combineOffset(2, 1);
        }

        foreach ($this->rows as $row) {

            $xValue = array_shift($row);
            foreach ($row as $key => $value) {
                $this->highCharts->addRow($xValue, $value, $key);
            }
        }
        $this->filled = true;
    }

    public function withChartDo(Closure $closure)
    {
        $closure($this->highCharts);
        return $this;
    }


    /**
     * @return HighCharts
     */
    public function getHighCharts()
    {
        return $this->highCharts;
    }

    public function render()
    {
        $this->seriesFill();
        $this->highCharts->render();
    }

    public function __toString()
    {
        ob_start();
        $this->render();
        return ob_get_clean();
    }


    public function getData()
    {
        $this->seriesFill();
        return $this->highCharts->getData();
    }

    public function renderJson()
    {
        $this->seriesFill();
        $this->highCharts->renderJson();
    }

    public function setId($id)
    {
        $this->getHighCharts()->setId($id);
        return $this;
    }

}