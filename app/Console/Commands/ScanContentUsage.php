<?php

namespace App\Console\Commands;

use App\Services\MediaUsageService;
use Illuminate\Console\Command;

class ScanContentUsage extends Command
{
    protected $signature = 'media:scan-usage';

    protected $description = 'Link media rows referenced inside raw HTML content of pages, services, destinations and packages';

    public function handle(MediaUsageService $usage): int
    {
        $result = $usage->scanContent();

        $this->info("Content usage scan complete: {$result['linked']} references linked.");

        return self::SUCCESS;
    }
}
