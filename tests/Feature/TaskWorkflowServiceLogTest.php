<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Tasks\Workflow\Concerns\HasTaskWorkflowHelpers;
use App\Models\ProductionTask;
use Mockery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tests for the HasTaskWorkflowHelpers trait's log() method.
 * Previously tested TaskWorkflowService which has been removed as it was
 * duplicate code - all workflow logic now lives in specialized services.
 */
class TaskWorkflowServiceLogTest extends TestCase
{
    /**
     * Test that log() creates a log entry with the correct structure.
     */
    public function test_log_creates_log_entry()
    {
        Auth::shouldReceive('id')->andReturn(123);

        $task = Mockery::mock(ProductionTask::class);
        
        $logsRelation = Mockery::mock(HasMany::class);
        $logsRelation->shouldReceive('create')->once()->with([
            'type' => 'test_action',
            'data' => ['some' => 'data', 'note' => 'test note'],
            'causer_id' => 123,
        ]);

        $task->shouldReceive('logs')->once()->andReturn($logsRelation);

        // Create anonymous class using the trait
        $service = new class {
            use HasTaskWorkflowHelpers;
            
            public function publicLog($task, $type, $data = []) {
                return $this->log($task, $type, $data);
            }
        };

        $service->publicLog($task, 'test_action', ['some' => 'data', 'note' => 'test note']);
    }

    /**
     * Test that log() uses Auth::id() for causer_id.
     */
    public function test_log_uses_authenticated_user_id()
    {
        Auth::shouldReceive('id')->andReturn(999);

        $task = Mockery::mock(ProductionTask::class);
        
        $logsRelation = Mockery::mock(HasMany::class);
        $logsRelation->shouldReceive('create')->once()->with([
            'type' => 'test_action',
            'data' => [],
            'causer_id' => 999,
        ]);

        $task->shouldReceive('logs')->once()->andReturn($logsRelation);

        $service = new class {
            use HasTaskWorkflowHelpers;
            
            public function publicLog($task, $type, $data = []) {
                return $this->log($task, $type, $data);
            }
        };

        $service->publicLog($task, 'test_action', []);
    }

    /**
     * Test that log() falls back to taskLogs() if logs() doesn't exist.
     */
    public function test_log_falls_back_to_task_logs_relation()
    {
        Auth::shouldReceive('id')->andReturn(123);

        $task = Mockery::mock(ProductionTask::class);
        
        $logsRelation = Mockery::mock(HasMany::class);
        $logsRelation->shouldReceive('create')->once()->with([
            'type' => 'fallback_action',
            'data' => ['key' => 'value'],
            'causer_id' => 123,
        ]);

        // Simulate logs() not existing, taskLogs() exists
        $task->shouldReceive('logs')->once()->andReturn($logsRelation);

        $service = new class {
            use HasTaskWorkflowHelpers;
            
            public function publicLog($task, $type, $data = []) {
                return $this->log($task, $type, $data);
            }
        };

        $service->publicLog($task, 'fallback_action', ['key' => 'value']);
    }
}
