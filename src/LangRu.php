<?php

namespace Phperf\HighCharts;


class LangRu
{
    public static function apply(HighCharts $hc)
    {
        $hc->globalOptions['lang'] = array(
            'shortMonths' => array('Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'),
            'rangeSelectorFrom' => 'C',
            'rangeSelectorTo' => 'по',
            'rangeSelectorZoom' => 'Период',
            'thousandsSep' => '',
            'resetZoom' => 'Сбросить масштаб',
            'resetZoomTitle' => 'Установить масштаб 1:1',
            'weekdays' => array('Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота')
        );
        $hc->jsonLoadingText = 'Загрузка...';

        return $hc;
    }
}