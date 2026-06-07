<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskStatusResource;
use App\Models\TaskStatus;

class TaskStatusController extends Controller
{
    public function index()
    {
        $statuses = TaskStatus::query()
            ->orderBy('sort_order')
            ->get();

        return TaskStatusResource::collection($statuses);
    }
}
