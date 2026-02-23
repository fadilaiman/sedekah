<?php

namespace App\Jobs;

use App\Services\DaunService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PostToDaun implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param string $type Type of post: 'institution_verified' or 'daily_stats'
     * @param array $payload Post content data (institution_name, state, etc.)
     */
    public function __construct(
        public string $type,
        public array $payload,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(DaunService $daun): void
    {
        $content = match ($this->type) {
            'institution_verified' => $this->buildInstitutionVerifiedPost(),
            'daily_stats' => $this->buildDailyStatsPost(),
            default => null,
        };

        if (!$content) {
            Log::warning('PostToDaun job received unknown type: ' . $this->type);
            return;
        }

        $daun->post($content);
    }

    /**
     * Build institution verified post content.
     */
    private function buildInstitutionVerifiedPost(): string
    {
        $institutionName = $this->payload['institution_name'] ?? 'Unknown';
        $state = $this->payload['state'] ?? '';

        return "✅ Institusi Disahkan\n\n"
            . "{$institutionName}" . ($state ? " ({$state})" : '') . " kini telah disahkan dan boleh menerima sumbangan melalui Sedekah.info.\n\n"
            . "Terima kasih kerana bersama membina ekosistem sedekah yang telus 🤲";
    }

    /**
     * Build daily stats post content.
     */
    private function buildDailyStatsPost(): string
    {
        $date = $this->payload['date'] ?? '';
        $pageVisits = $this->payload['page_visits'] ?? 0;
        $qrDownloads = $this->payload['qr_downloads'] ?? 0;
        $newToday = $this->payload['new_today'] ?? 0;
        $verifiedToday = $this->payload['verified_today'] ?? 0;
        $activeTotal = $this->payload['active_total'] ?? 0;

        return "📊 Kemas Kini Harian Sedekah.info\n"
            . "{$date}\n\n"
            . "Hari ini:\n"
            . "• {$pageVisits} lawatan\n"
            . "• {$qrDownloads} muat turun QR\n"
            . "• {$newToday} institusi baharu\n"
            . "• {$verifiedToday} institusi disahkan\n\n"
            . "Jumlah keseluruhan: {$activeTotal} institusi aktif\n\n"
            . "Terima kasih kerana menyokong ekosistem sedekah yang telus 🤲";
    }

    /**
     * Handle a failed job (log and don't re-throw).
     */
    public function failed(\Exception $exception): void
    {
        Log::error('PostToDaun job failed: ' . $exception->getMessage(), [
            'type' => $this->type,
            'payload' => $this->payload,
        ]);
    }
}
