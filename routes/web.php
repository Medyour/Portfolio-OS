<?php

use App\Portfolio\InvalidPortfolioConfiguration;
use App\Portfolio\PortfolioComposer;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function (PortfolioComposer $composer) {
    $configuration = config('portfolio');

    try {
        $portfolio = $composer->compose(is_array($configuration) ? $configuration : []);
    } catch (InvalidPortfolioConfiguration) {
        abort(500);
    }

    return Inertia::render('Portfolio', compact('portfolio'));
});
