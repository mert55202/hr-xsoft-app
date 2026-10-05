<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Notice;
use Illuminate\View\View;

class TodayController extends Controller
{
    public function index(): View
    {
        return view('today', [
            'company' => Company::current(),
            'notices' => Notice::query()->where('user_id', auth()->id())->whereNull('read_at')->latest()->get(),
        ]);
    }
}
