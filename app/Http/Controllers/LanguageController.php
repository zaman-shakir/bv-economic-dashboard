<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class LanguageController extends Controller
{
    /**
     * Switch application language
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        // Validate locale
        if (!in_array($locale, ['en', 'da'])) {
            abort(400, 'Invalid language');
        }

        // Store in session
        session(['locale' => $locale]);

        // Redirect back
        return redirect()->back();
    }
}
