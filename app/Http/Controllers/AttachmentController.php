<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Http\Resources\AttachmentResource;
use App\Models\Attachment;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AttachmentController extends Controller
{
    public function index(Task $task)
    {
        $attachments = $task->attachments()
            ->with('user')
            ->latest()
            ->get();

        return AttachmentResource::collection($attachments);
    }

    public function store(StoreAttachmentRequest $request, Task $task)
    {
        $user = Auth::user();

        $file = $request->file('file');

        $path = $file->store('attachments');

        $attachment = Attachment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return new AttachmentResource($attachment->load('user'));
    }

    public function download(Attachment $attachment)
    {
        if (! Storage::exists($attachment->file_path)) {
            abort(404);
        }

        return Storage::download(
            $attachment->file_path,
            $attachment->file_name
        );
    }

    public function destroy(Attachment $attachment): JsonResponse
    {
        Storage::delete($attachment->file_path);

        $attachment->delete();

        return response()->json(null, 204);
    }
}
