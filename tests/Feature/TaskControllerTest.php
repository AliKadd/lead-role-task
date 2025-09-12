<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function user_can_create_a_task()
    {
        $response = $this->actingAs($this->user, 'api')->postJson('/api/tasks', [
            'title' => 'My Test Task',
            'status' => 'pending',
            'priority' => 'medium',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'My Test Task']);
    }

    /** @test */
    public function tasks_can_be_filtered_by_status()
    {
        Task::factory()->count(2)->create(['status' => 'pending']);
        Task::factory()->count(3)->create(['status' => 'completed']);

        $response = $this->actingAs($this->user, 'api')->getJson('/api/tasks?status=pending');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function user_can_soft_delete_and_restore_task()
    {
        $task = Task::factory()->create(['assigned_to' => $this->user->id]);

        $this->actingAs($this->user, 'api')->deleteJson("/api/tasks/{$task->id}")
            ->assertStatus(200);

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);

        $this->actingAs($this->user, 'api')->postJson("/api/tasks/{$task->id}/restore")
            ->assertStatus(200);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'deleted_at' => null]);
    }

    /** @test */
    public function optimistic_locking_prevents_conflicting_updates()
    {
        $task = Task::factory()->create([
            'title' => 'Old title',
            'version' => 1,
            'assigned_to' => $this->user->id,
        ]);

        $payload = ['title' => 'New title', 'version' => 0];

        $this->actingAs($this->user, 'api')->putJson("/api/tasks/{$task->id}", $payload)
            ->assertStatus(409)
            ->assertJsonFragment(['error' => 'OptimisticLockException']);
    }
}
