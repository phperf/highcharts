<?php

namespace Phperf\HighCharts;

use Phperf\Pipeline\Vector\VectorProcessor;

class SplitSeries
{
    /** @var HighCharts */
    private $hc;

    private $maxInterval;

    private $prevX;

    /** @var Series */
    private $currentSeries;

    /** @var VectorProcessor */
    private $pipeline;

    public function __construct(HighCharts $hc, $maxInterval)
    {
        $this->hc = $hc;
        $this->maxInterval = $maxInterval;
    }

    /**
     * @param VectorProcessor $pipeline
     * @return $this
     */
    public function setPipeline(VectorProcessor $pipeline)
    {
        $this->pipeline = $pipeline;
        return $this;
    }


    private static $seq = 1;
    public function addRow($x, $y)
    {
        if (null === $this->currentSeries || ($this->prevX < $x - $this->maxInterval)) {
            $this->currentSeries = new Series();
            $this->currentSeries->setId('s' . ++self::$seq);
            $this->hc->addSeries($this->currentSeries);
        }

        if ($this->pipeline !== null) {
            $y = $this->pipeline->value($y);
        }
        if ($y !== null) {
            $this->currentSeries->addRow($x, $y);
        }
        $this->prevX = $x;
    }

}