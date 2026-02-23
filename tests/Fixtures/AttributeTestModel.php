<?php

namespace Reno\Dashboard\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Reno\Dashboard\Attributes\Dashboardable;
use Reno\Dashboard\Attributes\DashboardStat;

#[Dashboardable(dateColumn: 'ordered_at', dashboard: 'sales')]
#[DashboardStat(label: 'Total Orders', aggregate: 'count')]
#[DashboardStat(label: 'Revenue', aggregate: 'sum', column: 'total_amount', icon: 'currency-dollar')]
class AttributeTestModel extends Model
{
    protected $table = 'orders';
}
