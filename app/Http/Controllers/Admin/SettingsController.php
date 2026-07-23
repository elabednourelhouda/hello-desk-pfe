<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Hub page listing every configurable dropdown / list in the
     * application. New entries are added here as they are built
     * (sites, prospect origin, activity sector, ...).
     */
    public function index(): View
    {
        return view('admin.settings.index');
    }
}
