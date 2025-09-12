<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_a_default_status_and_priority()
    {
        $task = Task::factory()->create();
        $this->assertEquals('pending', $task->status);
        $this->assertEquals('medium', $task->priority);
    }

    /** @test */
    public function it_logs_audit_trails_on_create_update_delete()
    {
        $user = User::factory()->create();
        $this->be($user);

        $task = Task::factory()->create();
        $this->assertDatabaseHas('task_logs', [
            'task_id' => $task->id,
            'operation_type' => 'create',
        ]);

        $task->update(['title' => 'Updated title']);
        $this->assertDatabaseHas('task_logs', [
            'task_id' => $task->id,
            'operation_type' => 'update',
        ]);

        $task->delete();
        $this->assertDatabaseHas('task_logs', [
            'task_id' => $task->id,
            'operation_type' => 'delete',
        ]);
    }
}
