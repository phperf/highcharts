# phperf/highcharts

HighCharts bindings

[![Build Status](https://travis-ci.org/phperf/highcharts.svg?branch=master)](https://travis-ci.org/phperf/highcharts)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/phperf/highcharts/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/phperf/highcharts/?branch=master)
[![Code Climate](https://codeclimate.com/github/phperf/highcharts/badges/gpa.svg)](https://codeclimate.com/github/phperf/highcharts)

## Installation

```
composer require phperf/highcharts
```

## Usage

```php
$hc = new HighCharts();
$hc->addOption('legend', 'enabled', false);
$hc->setTitle($name);
$hc->addOption('plotOptions', 'series', 'point', 'mouseOver', <<<JS
function(){console.log(this.x, this.y)}
JS

$hc->addRow(1, 1);
$hc->addRow(2, 2);
$hc->addRow(3, 3);

$hc->render();
```