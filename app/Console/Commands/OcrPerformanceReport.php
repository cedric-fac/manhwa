<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OcrTrainingData;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\OcrPerformanceReport as OcrReport;

class OcrPerformanceReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocr:report
                          {--days=7 : Number of days to analyze}
                          {--email= : Specific email to send the report to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send OCR performance report';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $email = $this->option('email');

        // Collect statistics
        $stats = $this->collectStatistics($days);

        // Format the report
        $report = $this->formatReport($stats);

        // Display in console
        $this->displayReport($report);

        // Send via email if requested
        if ($email) {
            $this->sendReport($email, $report);
        } else {
            // Send to all admin users
            User::where('is_admin', true)->get()->each(function ($admin) use ($report) {
                $this->sendReport($admin->email, $report);
            });
        }

        $this->info('OCR performance report completed.');
    }

    /**
     * Collect OCR performance statistics.
     */
    private function collectStatistics(int $days): array
    {
        $period = now()->subDays($days);

        $recent = OcrTrainingData::where('created_at', '>=', $period);
        $total = $recent->count();

        if ($total === 0) {
            return [
                'period' => $days,
                'total' => 0,
                'avg_confidence' => 0,
                'verified' => 0,
                'needs_review' => 0,
                'accuracy' => 0,
            ];
        }

        $verified = $recent->where('verified', true)->count();
        $needsReview = $recent->where(function ($query) {
            $query->where('verified', false)
                  ->orWhere('confidence', '<', 80);
        })->count();

        // Calculate accuracy based on verified readings
        $verifiedData = OcrTrainingData::where('created_at', '>=', $period)
            ->where('verified', true)
            ->get();

        $accuracySum = $verifiedData->sum(function ($data) {
            return $data->getImprovementRatio();
        });

        return [
            'period' => $days,
            'total' => $total,
            'avg_confidence' => $recent->avg('confidence'),
            'verified' => $verified,
            'needs_review' => $needsReview,
            'accuracy' => $total > 0 ? ($accuracySum / $total) * 100 : 0,
        ];
    }

    /**
     * Format the report for display and email.
     */
    private function formatReport(array $stats): array
    {
        return [
            'title' => "Rapport de Performance OCR ({$stats['period']} jours)",
            'sections' => [
                [
                    'heading' => 'Volume de Traitement',
                    'items' => [
                        "Total des lectures: {$stats['total']}",
                        "Vérifiés: {$stats['verified']}",
                        "En attente de révision: {$stats['needs_review']}"
                    ]
                ],
                [
                    'heading' => 'Métriques de Performance',
                    'items' => [
                        "Confiance moyenne: " . number_format($stats['avg_confidence'], 2) . "%",
                        "Précision: " . number_format($stats['accuracy'], 2) . "%",
                        "Taux de vérification: " . ($stats['total'] > 0 
                            ? number_format(($stats['verified'] / $stats['total']) * 100, 2)
                            : 0) . "%"
                    ]
                ]
            ]
        ];
    }

    /**
     * Display the report in console.
     */
    private function displayReport(array $report): void
    {
        $this->info("\n" . $report['title'] . "\n");

        foreach ($report['sections'] as $section) {
            $this->line("\n" . $section['heading']);
            $this->line(str_repeat('-', strlen($section['heading'])));
            
            foreach ($section['items'] as $item) {
                $this->line("- " . $item);
            }
        }
    }

    /**
     * Send the report via email.
     */
    private function sendReport(string $email, array $report): void
    {
        Mail::to($email)->queue(new OcrReport($report));
        $this->info("Report sent to: {$email}");
    }
}