<?php

declare(strict_types=1);

namespace Leeto\MoonShine\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;

use Leeto\MoonShine\Dashboard\Dashboard;

use function view;

class DashboardController extends BaseController
{
    public function __invoke(): Factory|View|Application
    {
        return view('moonshine::dashboard', [
            'blocks' => app(Dashboard::class)->getBlocks(),
        ]);
    }
}
