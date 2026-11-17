<?php

namespace App\Http\Controllers;

use App\Services\GuestMenuService;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;

class GuestMenuController extends Controller
{
    /**
     * The public menu guests open from a QR code or link.
     */
    public function show(GuestMenuService $menu)
    {
        $groups = $menu->groups();

        return view('menu.show', [
            'title' => config('menu.title'),
            'groups' => $groups,
            'hasStandInPhotos' => collect($groups)->pluck('sections')->flatten(1)->pluck('items')->flatten(1)->contains(fn (array $item) => $item['credit'] !== null),
        ]);
    }

    /**
     * A printable QR code card for the menu, for staff.
     */
    public function qrCode(Request $request)
    {
        $url = route('menu');

        return view('menu.qr', [
            'title' => config('menu.title'),
            'url' => $url,
            'svg' => $this->qrSvg($url),
            'isLocalAddress' => $this->isLocalAddress($request->getHost()),
        ]);
    }

    /**
     * The QR code alone, as an SVG file for designers or print shops.
     */
    public function qrCodeSvg()
    {
        return response($this->qrSvg(route('menu')), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="menu-qr-code.svg"',
        ]);
    }

    private function qrSvg(string $url): string
    {
        $style = new RendererStyle(
            size: 480,
            margin: 2,
            fill: Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(43, 29, 22)),
        );

        $svg = (new Writer(new ImageRenderer($style, new SvgImageBackEnd)))->writeString($url);

        // Drop the XML prolog so the SVG can be embedded inline in HTML.
        return preg_replace('/^<\?xml[^>]*\?>\s*/', '', $svg);
    }

    /**
     * Addresses that only work on this computer or this local network.
     */
    private function isLocalAddress(string $host): bool
    {
        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true) || str_ends_with($host, '.test') || str_ends_with($host, '.local')) {
            return true;
        }

        return filter_var($host, FILTER_VALIDATE_IP) !== false
            && filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
}
