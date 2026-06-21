<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Organization;
use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Models\TaskStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_task_attachments(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $status->id,
            'created_by' => $user->id,
        ]);

        Attachment::factory()->create([
            'organization_id' => $organization->id,
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->getJson("/api/tasks/{$task->id}/attachments");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.task_id', $task->id);
    }

    public function test_authenticated_user_can_upload_attachment(): void
    {
        Storage::fake('local');

        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $status->id,
            'created_by' => $user->id,
        ]);

        $file = UploadedFile::fake()->create('sample.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)->postJson("/api/tasks/{$task->id}/attachments", [
            'file' => $file,
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('attachments', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'file_name' => 'sample.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $attachment = Attachment::first();

        Storage::disk('local')->assertExists($attachment->file_path);
    }

    public function test_user_cannot_upload_attachment_to_other_organization_task(): void
    {
        Storage::fake('local');

        $organization = Organization::factory()->create();
        $otherOrganization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);
        $otherUser = User::factory()->create([
            'current_org_id' => $otherOrganization->id,
        ]);

        $otherProject = Project::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);

        $otherStatus = TaskStatus::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);

        $otherTask = Task::factory()->create([
            'organization_id' => $otherOrganization->id,
            'project_id' => $otherProject->id,
            'status_id' => $otherStatus->id,
            'created_by' => $otherUser->id,
        ]);

        $file = UploadedFile::fake()->create('sample.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)->postJson("/api/tasks/{$otherTask->id}/attachments", [
            'file' => $file,
        ]);

        $response->assertNotFound();

        $this->assertDatabaseMissing('attachments', [
            'task_id' => $otherTask->id,
            'file_name' => 'sample.pdf',
        ]);
    }

    public function test_authenticated_user_can_delete_attachment(): void
    {
        Storage::fake('local');

        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $status->id,
            'created_by' => $user->id,
        ]);

        $path = UploadedFile::fake()
            ->create('sample.pdf', 100, 'application/pdf')
            ->store('attachments');

        $attachment = Attachment::factory()->create([
            'organization_id' => $organization->id,
            'task_id' => $task->id,
            'user_id' => $user->id,
            'file_name' => 'sample.pdf',
            'file_path' => $path,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/attachments/{$attachment->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('attachments', [
            'id' => $attachment->id,
        ]);

        Storage::disk('local')->assertMissing($path);
    }

    public function test_authenticated_user_can_download_attachment(): void
    {
        Storage::fake('local');

        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $status->id,
            'created_by' => $user->id,
        ]);

        Storage::disk('local')->put('attachments/sample.pdf', 'test content');

        $attachment = Attachment::factory()->create([
            'organization_id' => $organization->id,
            'task_id' => $task->id,
            'user_id' => $user->id,
            'file_name' => 'sample.pdf',
            'file_path' => 'attachments/sample.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $response = $this
            ->actingAs($user)
            ->get("/api/attachments/{$attachment->id}/download");

        $response->assertOk();
    }

    public function test_guest_cannot_access_attachment_api(): void
    {
        Storage::fake('local');

        $organization = Organization::factory()->create();

        $user = User::factory()->create([
            'current_org_id' => $organization->id,
        ]);

        $project = Project::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $status = TaskStatus::factory()->create([
            'organization_id' => $organization->id,
        ]);

        $task = Task::factory()->create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'status_id' => $status->id,
            'created_by' => $user->id,
        ]);

        $attachment = Attachment::factory()->create([
            'organization_id' => $organization->id,
            'task_id' => $task->id,
            'user_id' => $user->id,
            'file_name' => 'sample.pdf',
            'file_path' => 'attachments/sample.pdf',
        ]);

        $file = UploadedFile::fake()->create('sample.pdf', 100, 'application/pdf');

        $this->getJson("/api/tasks/{$task->id}/attachments")
            ->assertUnauthorized();

        $this->postJson("/api/tasks/{$task->id}/attachments", [
            'file' => $file,
        ])->assertUnauthorized();

        $this->getJson("/api/attachments/{$attachment->id}/download")
            ->assertUnauthorized();

        $this->deleteJson("/api/attachments/{$attachment->id}")
            ->assertUnauthorized();
    }
}
