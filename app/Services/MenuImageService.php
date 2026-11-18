<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Stores photos for the guest menu (menu items and bar products).
 *
 * Phone photos are large, so every upload is turned upright, shrunk to at
 * most 800px on its long edge and saved as WebP (typically 30-80 KB), which
 * keeps the menu quick to load on mobile data.
 */
class MenuImageService
{
    public const DISK = 'menu_images';

    private const MAX_EDGE = 800;

    private const QUALITY = 78;

    private const NOT_STOCK = ['image_credit' => null, 'image_credit_url' => null, 'image_is_stock' => false];

    /** Validation rules for the upload field. */
    public static function rules(): array
    {
        return [
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'remove_image' => 'nullable|boolean',
        ];
    }

    /**
     * Apply the form's photo changes and return the attributes to save.
     *
     * A staff upload or removal replaces any stand-in photo, so its credit
     * and stand-in flag are cleared too.
     *
     * @return array{image_path: ?string, image_credit?: null, image_credit_url?: null, image_is_stock?: false}
     */
    public function sync(Request $request, ?string $currentPath): array
    {
        if ($request->hasFile('image')) {
            $newPath = $this->store($request->file('image'));
            $this->delete($currentPath);

            return ['image_path' => $newPath, ...self::NOT_STOCK];
        }

        if ($request->boolean('remove_image')) {
            $this->delete($currentPath);

            return ['image_path' => null, ...self::NOT_STOCK];
        }

        return ['image_path' => $currentPath];
    }

    public function store(UploadedFile $file): string
    {
        $image = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if ($image === false) {
            throw ValidationException::withMessages(['image' => 'This photo could not be read. Please upload a JPG, PNG or WebP image.']);
        }

        // WebP needs full colour; indexed-colour PNGs and GIFs are converted first.
        if (! imageistruecolor($image)) {
            imagepalettetotruecolor($image);
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        $image = $this->upright($image, $file);
        $image = $this->shrink($image);

        ob_start();
        imagewebp($image, null, self::QUALITY);
        $webp = (string) ob_get_clean();

        $path = Str::uuid().'.webp';
        Storage::disk(self::DISK)->put($path, $webp);

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    public static function url(?string $path): ?string
    {
        return $path ? asset('uploads/menu/'.$path) : null;
    }

    /** Rotate phone photos according to their EXIF orientation. */
    private function upright(\GdImage $image, UploadedFile $file): \GdImage
    {
        if (! function_exists('exif_read_data') || ! in_array($file->getMimeType(), ['image/jpeg', 'image/jpg'], true)) {
            return $image;
        }

        $orientation = @exif_read_data($file->getRealPath())['Orientation'] ?? 1;
        $angle = [3 => 180, 6 => -90, 8 => 90][$orientation] ?? 0;

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        return $rotated;
    }

    private function shrink(\GdImage $image): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $scale = self::MAX_EDGE / max($width, $height);

        if ($scale >= 1) {
            return $image;
        }

        $resized = imagescale($image, (int) round($width * $scale), (int) round($height * $scale), IMG_BICUBIC);

        return $resized;
    }
}
