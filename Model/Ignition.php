<?php

namespace Swissup\Ignition\Model;

use Throwable;

class Ignition extends \Spatie\Ignition\Ignition
{
    protected function createReport(Throwable $throwable): Report
    {
        return Report::createFromFlareReport(parent::createReport($throwable));
    }
}
