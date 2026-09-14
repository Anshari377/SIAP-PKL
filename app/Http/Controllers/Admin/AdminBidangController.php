<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class AdminBidangController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Bidang/Index', ['activeNav' => 'admin.bidang']);
    }

    public function create()
    {
        return Inertia::render('Admin/Bidang/Create', ['activeNav' => 'admin.bidang']);
    }

    public function show($bidang)
    {
        return Inertia::render('Admin/Bidang/Show', ['activeNav' => 'admin.bidang']);
    }

    public function edit($bidang)
    {
        return Inertia::render('Admin/Bidang/Edit', ['activeNav' => 'admin.bidang']);
    }
}