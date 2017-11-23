<?php

namespace Phperf\HighCharts;

use Swaggest\Json\Json;
use Swaggest\Json\RawJson;

class HighCharts
{
    public function isEmpty()
    {
        return empty($this->series);
    }

    public function __toString()
    {
        ob_start();
        $this->render();
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    private $options;
    /**
     * @var Series[]
     */
    private $series = array();


    public function __construct()
    {
        $this->options = array(
            'title' => false,

            /*
            'chart' => array(
                'resetZoomButton' => array(
                    'position' => array(
                        'align' => 'left', // by default
                        'verticalAlign' => 'bottom', // by default
                        'x' => 0,
                        'y' => -130,
                    )
                )
            ),
            */

            'chart' => array(
                'zoomType' => 'x'
            ),

            'legend' => array(
                'enabled' => true,
                //'layout' => 'vertical',
                'verticalAlign' => 'top'
            ),

            'plotOptions' => array(
                'series' => array(
                    'marker' => array(
                        'enabled' => false
                    )
                )
            ),

            'tooltip' => array(
                'crosshairs' => array(true, true),
                'shared' => false,
            ),

            'credits' => array(
                'enabled' => false
            )

        );
    }


    public function setType($type = Series::TYPE_LINE)
    {
        $this->options['chart']['type'] = $type;
        return $this;
    }


    public function setTitle($title)
    {
        if ($title) {
            $this->options['title']['text'] = $title;
        } else {
            $this->options['title'] = false;
        }
        return $this;
    }

    public function setYTitle($title)
    {
        if ($title) {
            $this->options['yAxis']['title']['text'] = $title;
        } else {
            $this->options['yAxis']['title']['text'] = null;
        }
        return $this;
    }

    public function setXTitle($title)
    {
        if ($title) {
            $this->options['xAxis']['title']['text'] = $title;
        } else {
            $this->options['xAxis']['title']['text'] = null;
        }
        return $this;
    }


    private $literalDateAxis;

    public function withDateAxis($literal = false)
    {
        $this->options['xAxis']['type'] = 'datetime';
        $this->literalDateAxis = $literal;
        return $this;
    }


    public function addOptions($options)
    {
        $this->options = Util::arrayMergeRecursiveDistinct($this->options, $options);
        return $this;
    }


    /**
     * @return $this
     */
    public function addOption()
    {
        $args = func_get_args();
        $value = array_pop($args);
        $t = &$this->options;
        foreach ($args as $arg) {
            $t = &$t[$arg];
        }
        $t = $value;
        return $this;
    }

    /**
     * @var Series
     */
    private $defaultSeries;

    /**
     * @param mixed $defaultSeries
     * @return $this
     */
    public function setDefaultSeries(Series $defaultSeries = null)
    {
        $this->defaultSeries = $defaultSeries;
        return $this;
    }


    public function addRow($x, $y, $id = 'default')
    {
        if ($this->literalDateAxis) {
            $x = 1000 * strtotime($x);
        }

        //echo 'row added';
        if (!$series = &$this->series[$id]) {
            if ($this->defaultSeries) {
                $series = clone $this->defaultSeries;
            } else {
                $series = new Series();
            }
            $series->setId($id);
        }
        if (isset($this->ranges[$id])) {
            $series->addRangeRow($x, $y, $this->ranges[$id]);
        } else {
            $series->addRow($x, $y);
        }
        return $this;
    }

    private $ranges = array();

    public function addSeries(Series $series, $rangeHighId = null)
    {
        $this->series[$rangeLowId = $series->getId()] = $series;
        if (null !== $rangeHighId) {
            $this->series[$rangeHighId] = $series;
            $this->ranges[$rangeLowId] = Series::VALUE_LOW;
            $this->ranges[$rangeHighId] = Series::VALUE_HIGH;
        }
        return $this;
    }


    private $containerSelector;
    private $id;

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setContainerSelector($id)
    {
        $this->containerSelector = $id;
        return $this;
    }

    public $globalOptions = array(
        'global' => array(
            'useUTC' => false
        ),
    );


    public $jsonLoadingText = 'Loading data from server...';
    private $withJsonZoom;
    private $jsonUrl;

    public function withJsonZoom($jsonUrl = null)
    {
        $this->withJsonZoom = true;
        $this->jsonUrl = $jsonUrl;
        return $this;
    }


    private $minX;
    private $maxX;

    private function countExtremes()
    {
        $this->minX = 0;
        $this->maxX = 0;
        foreach ($this->series as $series) {
            $this->minX = $series->minX;
            $this->maxX = $series->maxX;
            break;
        }
        foreach ($this->series as $series) {
            $this->minX = min($this->minX, $series->minX);
            $this->maxX = max($this->maxX, $series->maxX);
        }
    }

    private static $autoContainerId = 0;
    public static $jsLoaded = false;

    public static function getRequiredScriptUrls()
    {
        return [
            '//code.highcharts.com/stock/highstock.js',
            '//code.highcharts.com/highcharts.js',
            '//code.highcharts.com/highcharts-more.js',
        ];
    }

    public function render()
    {
        if ($this->withJsonZoom) {
            $this->addOption('xAxis', 'events', 'afterSetExtremes', new RawJson('loadPoints'));
            $this->addOption('xAxis', 'events', 'setExtremes', new RawJson('setExtremesCallback'));
            if (null === $this->jsonUrl) {
                $this->jsonUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
                if (strpos($this->jsonUrl, '?') !== false) {
                    $this->jsonUrl .= '&';
                } else {
                    $this->jsonUrl .= '?';
                }
                $this->jsonUrl .= 'min=:min&max=:max&callback=?';
            }
            $this->countExtremes();

        }

        $this->options['series'] = array();
        //var_dump($this->series);
        $options = (string)new Json($this->options);

        if (!$this->containerSelector) {
            if (!$this->id) {
                $this->id = 'highcharts-' . self::$autoContainerId++;
            }
            $this->containerSelector = $this->id;
            ?>
<div id="<?php echo $this->containerSelector ?>"></div>
<?php
            $this->containerSelector = '#' . $this->containerSelector;
        }

        if (!self::$jsLoaded) {
            foreach (self::getRequiredScriptUrls() as $url) {
                echo '<script type="text/javascript" src="' . $url . '"></script>', "\n";
            }
            self::$jsLoaded = true;
        }

        ?>
<script type="text/javascript">
(function(){
    <?php
    if ($this->withJsonZoom) {
    ?>
    var isReset = false;

    function setExtremesCallback(e) {
        if (e.max == null || e.min == null) {
            isReset = true;
        }
        else {
            isReset = false;
        }
    }

    function loadPoints(e) {

        var url = '<?php echo $this->jsonUrl?>',
            chart = $('#hc-container-1').highcharts();

        var min = <?php echo $this->minX ?>;
        var max = <?php echo $this->maxX ?>;

        if(!isReset)
        {
            min = e.min;
            max = e.max;
        }
        chart.showLoading('<?php echo $this->jsonLoadingText?>');

        url = url.replace(/:min/g, min).replace(/:max/g, max);

        $.getJSON(url, function (data) {
            var seriesOptions, series;
            for (var i in data) {
                if (data.hasOwnProperty(i)) {
                    seriesOptions = data[i];
                    if (series = chart.get(seriesOptions.id)) {
                        series.setData(seriesOptions.data, false);
                    }
                    else {
                        chart.addSeries(seriesOptions, false);
                    }
                }
            }
            chart.redraw();
            chart.hideLoading();
        });
    }
<?php
    }
    ?>Highcharts.setOptions(<?php echo json_encode($this->globalOptions)?>);

    var chart = $('<?php echo $this->containerSelector ?>').highcharts(<?php echo $options ?>).highcharts();
    <?php
    if ($this->withJsonZoom && empty($this->series)) {
        ?>isReset = true;
    loadPoints();
    <?php
        } else {
            foreach ($this->series as $id => $series) {
                if (isset($this->ranges[$id]) && Series::VALUE_HIGH == $this->ranges[$id]) {
                    continue;
                }
            ?>chart.addSeries(<?php echo json_encode($series->exportOptions())?>, false);
    <?php
        }
    }

?>chart.redraw();
})();
</script><?php
    }


    /**
     * @return array
     */
    public function getData()
    {
        $data = array();
        foreach ($this->series as $series) {
            $data [] = $series->exportOptions();
        }
        return $data;
    }


    /**
     * @return $this
     */
    public function renderJson()
    {
        (new Jsonp($this->getData(), $_GET['callback']))->render();
        return $this;
    }
    
}