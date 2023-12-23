<?php

namespace App\Http\Controllers\Admin;

use Sdkconsultoria\Core\Controllers\ResourceController;

class EmbroideryStatisticsController extends ResourceController
{
    protected $view = 'back.embroidery-statistics';

    protected $model = \App\Models\EmbroideryStatistics::class;
}
