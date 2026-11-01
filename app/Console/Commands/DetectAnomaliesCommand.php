<?php

namespace App\Console\Commands;

use App\Services\AnomalyDetector;
use Illuminate\Console\Command;

class DetectAnomaliesCommand extends Command
{
    protected $signature = 'mesures:detect-anomalies';

    protected $description = 'Détecter les anomalies et proposer des incidents sans les ouvrir automatiquement';

    public function handle(AnomalyDetector $detector): int
    {
        $this->info($detector->scan().' nouvelle(s) suggestion(s).');

        return self::SUCCESS;
    }
}
