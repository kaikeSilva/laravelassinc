<?php

namespace App\Jobs;

use App\Models\Color;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use SplFileObject;

class ProcessImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $cacheKey;

    /**
     * Create a new job instance.
     *
     * @param string $cacheKey
     * @return void
     */
    public function __construct(string $cacheKey)
    {
        $this->cacheKey = $cacheKey;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Log::info('Process import started at: ' . now());

        // Retrieve the full file path from the cache.
        $fullPath = Cache::get($this->cacheKey);
        Log::info('CSV file path: ' . $fullPath);

        if (!$fullPath || !file_exists($fullPath)) {
            Log::error('CSV file not found for cache key: ' . $this->cacheKey);
            Cache::put('import_status_' . $this->cacheKey, 'failed');
            return;
        }

        Log::info('CSV file found for cache key: ' . $this->cacheKey);

        try {
            Log::info('Processing CSV file: ' . $fullPath);
            $file = new SplFileObject($fullPath);
            $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

            // Read the header row
            $header = $file->fgetcsv();
            if (!$header) {
                Log::error('Failed to read header from CSV file.');
                Cache::put('import_status_' . $this->cacheKey, 'failed');
                return;
            }

            Log::info('CSV Header: ' . json_encode($header));

            // Step 1: Count total valid data rows
            Log::info('Starting to count total rows.');
            $totalRows = 0;
            while (!$file->eof()) {
                $row = $file->fgetcsv();

                // Skip empty rows or rows with no data
                if ($this->isRowEmpty($row)) {
                    continue;
                }

                $totalRows++;
            }

            Log::info('Total rows (excluding header): ' . $totalRows);

            // Step 2: Rewind the file to start processing
            $file->rewind();
            $header = $file->fgetcsv(); // Skip header again after rewinding
            Log::info('Rewound and skipped header.');

            // Initialize counters and storage
            $processedRows = 0;
            $chunkSize = 1; // Adjust based on your performance needs
            $rows = [];
            $startTime = now();

            Log::info('Starting to process CSV rows.');

            // Step 3: Process and insert rows in chunks
            while (!$file->eof()) {
                $row = $file->fgetcsv();
                Log::info('Processing row: ' . json_encode($row));
                // Skip empty or invalid rows
                if ($this->isRowEmpty($row)) {
                    continue;
                }

                $data = array_combine($header, $row);
                if ($data === false) {
                    Log::warning('Row does not match header format: ' . json_encode($row));
                    continue;
                }

                $rows[] = [
                    'name'       => $data['name'] ?? null,
                    'hex_code'   => $data['hex_code'] ?? null,
                    'rgb_code'   => isset($data['rgb_code']) ? str_replace('|', ',', $data['rgb_code']) : null,
                    'cmyk_code'  => isset($data['cmyk_code']) ? str_replace('|', ',', $data['cmyk_code']) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $processedRows++;

                // Insert rows when chunk size is reached
                if (count($rows) >= $chunkSize) {
                    Color::insert($rows);
                    $rows = [];

                    // Calculate progress percentage
                    $progress = ($processedRows / $totalRows) * 100;
                    $progress = min(round($progress, 2), 100); // Ensure it doesn't exceed 100%

                    // Update progress in cache
                    Cache::put('import_progress_' . $this->cacheKey, $progress);
                    Cache::put('import_status_' . $this->cacheKey, 'processing');
                    Log::info("Inserted {$processedRows} rows so far. Progress: {$progress}%");
                    sleep(1); // Optional: slow down the process to avoid overwhelming the database
                }
            }

            // Insert any remaining rows after the loop
            if (!empty($rows)) {
                Color::insert($rows);
                $processedRows += count($rows);

                // Final progress update
                Cache::put('import_progress_' . $this->cacheKey, 100);
                Log::info("Inserted remaining " . count($rows) . " rows. Progress: 100%");
            }

            // Update final status
            Cache::put('import_status_' . $this->cacheKey, 'completed');

            // Clean up: delete the CSV file and remove cache key
            @unlink($fullPath);
            Cache::forget($this->cacheKey);

            $timeTaken = now()->diffInSeconds($startTime);

            Log::info("Process import completed at: " . now() . ". Total rows processed: {$processedRows}. Time taken: {$timeTaken} seconds.");

        } catch (\Exception $e) {
            Log::error('Import failed: ' . $e->getMessage());
            Cache::put('import_status_' . $this->cacheKey, 'failed');
            throw $e;
        }
    }

    /**
     * Helper function to determine if a CSV row is empty.
     *
     * @param array|null $row
     * @return bool
     */
    protected function isRowEmpty(?array $row): bool
    {
        if (empty($row)) {
            return true;
        }

        // Check if all fields are null or empty strings
        foreach ($row as $field) {
            if ($field !== null && $field !== '') {
                return false;
            }
        }

        return true;
    }
}