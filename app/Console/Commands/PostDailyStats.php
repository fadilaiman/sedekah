<?php

namespace App\Console\Commands;

use App\Jobs\PostToDaun;
use App\Models\DailyAnalytic;
use App\Models\Institution;
use App\Services\DaunService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PostDailyStats extends Command
{
    protected $signature = 'daun:post-stats
                            {--dry-run : Preview post content without sending}';

    protected $description = 'Post daily statistics to Daun.me social platform';

    public function handle(DaunService $daun): int
    {
        $dryRun = $this->option('dry-run');
        $today = Carbon::today();

        // Check if Daun is enabled
        if (!$daun->isEnabled()) {
            $this->info('Daun integration is disabled. Set DAUN_ENABLED=true to enable.');
            return self::SUCCESS;
        }

        // Gather statistics
        $dailyAnalytic = DailyAnalytic::today()->first();
        $pageVisits = $dailyAnalytic?->page_visits ?? 0;
        $qrDownloads = $dailyAnalytic?->qr_downloads ?? 0;

        // Count active verified institutions
        $activeTotal = Institution::verified()->count();

        // Count new institutions created today
        $newToday = Institution::whereDate('created_at', $today)->count();

        // Count verified institutions from today
        $verifiedToday = Institution::whereDate('verified_at', $today)->count();

        // Skip posting if all stats are 0 (prevent empty posts)
        if ($pageVisits === 0 && $qrDownloads === 0 && $newToday === 0 && $verifiedToday === 0) {
            $this->info('No activity today. Skipping post.');
            return self::SUCCESS;
        }

        // Format date for post
        $dateFormatted = $today->format('d F Y');
        $dateFormatted = $this->convertDateToMalay($dateFormatted);

        // Prepare payload
        $payload = [
            'date' => $dateFormatted,
            'page_visits' => $pageVisits,
            'qr_downloads' => $qrDownloads,
            'new_today' => $newToday,
            'verified_today' => $verifiedToday,
            'active_total' => $activeTotal,
        ];

        // Build post content
        $job = new PostToDaun('daily_stats', $payload);
        $content = $job->handle(app(DaunService::class));

        if ($dryRun) {
            $this->info('=== DRY RUN ===');
            $this->info('Post content:');
            $this->line('');
            $this->line($this->buildPostContent($payload));
            $this->line('');
            $this->info('(No post sent)');
            return self::SUCCESS;
        }

        // Dispatch job to queue
        PostToDaun::dispatch('daily_stats', $payload);
        $this->info('Daily stats posted to Daun.me');

        return self::SUCCESS;
    }

    /**
     * Convert English month names to Malay.
     */
    private function convertDateToMalay(string $date): string
    {
        $months = [
            'January' => 'Januari',
            'February' => 'Februari',
            'March' => 'Mac',
            'April' => 'April',
            'May' => 'Mei',
            'June' => 'Jun',
            'July' => 'Julai',
            'August' => 'Ogos',
            'September' => 'September',
            'October' => 'Oktober',
            'November' => 'November',
            'December' => 'Disember',
        ];

        return strtr($date, $months);
    }

    /**
     * Build post content for display.
     */
    private function buildPostContent(array $payload): string
    {
        return "📊 Kemas Kini Harian Sedekah.online\n"
            . "{$payload['date']}\n\n"
            . "Hari ini:\n"
            . "• {$payload['page_visits']} lawatan\n"
            . "• {$payload['qr_downloads']} muat turun QR\n"
            . "• {$payload['new_today']} institusi baharu\n"
            . "• {$payload['verified_today']} institusi disahkan\n\n"
            . "Jumlah keseluruhan: {$payload['active_total']} institusi aktif\n\n"
            . "Terima kasih kerana menyokong ekosistem sedekah yang telus 🤲";
    }
}
