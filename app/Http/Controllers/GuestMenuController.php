<?php

namespace App\Http\Controllers;

use App\Services\GuestMenuService;

class GuestMenuController extends Controller
{
    /**
     * The public menu guests open from a QR code or link.
     */
    public function show(GuestMenuService $menu)
    {
        return view('menu.show', [
            'title' => config('menu.title'),
            'groups' => $menu->groups(),
        ]);
    }
}
