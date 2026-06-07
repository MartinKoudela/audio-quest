<?php

namespace App\Jobs;

use App\Enums\ScanStatus;
use App\Models\Scan;
use App\Services\MusicRecommendationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateMusicRecommendations implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [5, 15];

    public function __construct(public int $scanId) {}

    public function handle(MusicRecommendationService $recommendations): void
    {
        $scan = Scan::findOrFail($this->scanId);

        $scan->update(['status' => ScanStatus::Processing]);

        $recommendation = $recommendations->recommend($scan);

        $scan->update([
            'ai_prompt' => $recommendation['prompt'],
            'result' => $recommendation['structured'],
            'result_source' => $recommendation['result_source'],
            'status' => ScanStatus::Done,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Music recommendation generation failed.', [
            'scan_id' => $this->scanId,
            'exception' => $exception->getMessage(),
        ]);

        Scan::whereKey($this->scanId)->update(['status' => ScanStatus::Failed]);
    }
}
