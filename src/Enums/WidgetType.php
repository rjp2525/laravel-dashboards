<?php

namespace Reno\Dashboard\Enums;

enum WidgetType: string
{
    case STAT = 'stat';
    case LINE = 'line';
    case BAR = 'bar';
    case AREA = 'area';
    case PIE = 'pie';
    case DONUT = 'donut';
    case TABLE = 'table';
    case LISTING = 'list';
    case PROGRESS = 'progress';
    case HEATMAP = 'heatmap';
    case STATUS_TIMELINE = 'status_timeline';
    case CUSTOM = 'custom';
    case SPARKLINE = 'sparkline';
    case PROGRESS_CIRCLE = 'progress_circle';
    case BAR_LIST = 'bar_list';
    case FUNNEL = 'funnel';
    case CATEGORY = 'category';
    case BUDGET = 'budget';
    case GAUGE = 'gauge';
}
