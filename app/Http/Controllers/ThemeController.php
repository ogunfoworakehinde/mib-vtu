<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

class ThemeController extends Controller {
    public function update(Request $request) {
        $theme = $request->theme;
        if (in_array($theme, ['light','dark','blue'])) {
            auth()->user()->update(['theme' => $theme]);
            return response()->json(['success' => true]);
        }
        return response()->json(['error'=>'Invalid theme'], 400);
    }
}
