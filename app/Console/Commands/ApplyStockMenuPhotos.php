<?php

namespace App\Console\Commands;

use App\Models\MenuItem;
use App\Models\Product;
use App\Services\MenuImageService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Throwable;

/**
 * Applies approved stand-in photos from database/data/stock-photos.json.
 *
 * Each entry is an openly licensed image (commercial use and modification
 * allowed) with the credit its licence requires. Photos uploaded by staff are
 * never replaced, and re-running the command skips entries already applied.
 */
class ApplyStockMenuPhotos extends Command
{
    protected $signature = 'menu:stock-photos
        {--file=database/data/stock-photos.json : Approved list of stand-in photos}
        {--dry-run : Show what would change without downloading anything}';

    protected $description = 'Download approved stand-in photos for guest menu items that have no photo yet';

    public function handle(MenuImageService $images): int
    {
        $path = base_path($this->option('file'));

        if (! is_file($path)) {
            $this->error("No stock photo list at {$path}.");

            return self::FAILURE;
        }

        $entries = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $counts = ['applied' => 0, 'kept staff photo' => 0, 'already applied' => 0, 'not found' => 0, 'failed' => 0];

        foreach ($entries as $entry) {
            $model = match ($entry['type']) {
                'menu_item' => MenuItem::where('name', $entry['name'])->first(),
                'product' => Product::where('name', $entry['name'])->first(),
                default => null,
            };

            $status = match (true) {
                $model === null => 'not found',
                $model->image_path && ! $model->image_is_stock => 'kept staff photo',
                $model->image_is_stock && $model->image_credit_url === $entry['credit_url'] => 'already applied',
                default => null,
            };

            if ($status === null && ! $this->option('dry-run')) {
                $status = $this->apply($model, $entry, $images);
                // Be polite to the image hosts (Wikimedia rate-limits fast clients).
                Sleep::for(1)->second();
            }

            $status ??= 'applied';
            $counts[$status]++;

            if ($status !== 'applied' && $status !== 'already applied') {
                $this->line("  {$entry['name']}: {$status}");
            }
        }

        $this->info(($this->option('dry-run') ? '[dry run] ' : '').collect($counts)->map(fn ($n, $label) => "{$label}: {$n}")->implode(', '));

        return self::SUCCESS;
    }

    private function apply(MenuItem|Product $model, array $entry, MenuImageService $images): string
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders(['User-Agent' => 'GuestMenuStockPhotos/1.0 (Laravel hotel menu; '.config('app.url').')'])
                ->retry(
                    4,
                    fn (int $attempt) => $attempt * 5000,
                    fn (Throwable $e) => $e instanceof RequestException && in_array($e->response->status(), [429, 500, 502, 503, 504], true),
                    throw: false,
                )
                ->get($entry['url']);

            if (! $response->successful() || ! str_starts_with((string) $response->header('Content-Type'), 'image/') || strlen($response->body()) > 15 * 1024 * 1024) {
                return 'failed';
            }

            $tmp = tempnam(sys_get_temp_dir(), 'stock');
            file_put_contents($tmp, $response->body());
            $newPath = $images->store(new UploadedFile($tmp, basename(parse_url($entry['url'], PHP_URL_PATH) ?: 'photo'), $response->header('Content-Type'), null, true));
            @unlink($tmp);
        } catch (Throwable $e) {
            report($e);

            return 'failed';
        }

        $images->delete($model->image_path);
        $model->update([
            'image_path' => $newPath,
            'image_credit' => $entry['credit'],
            'image_credit_url' => $entry['credit_url'],
            'image_is_stock' => true,
        ]);

        return 'applied';
    }
}
