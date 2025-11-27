<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Tasks\TaskWorkflowService;
use App\Models\ProductionTask;
use Mockery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskWorkflowServiceLogTest extends TestCase
{
    public function test_log_extracts_note_from_data()
    {
        Auth::shouldReceive('id')->andReturn(123);

        $task = Mockery::mock(ProductionTask::class);
        $task->shouldReceive('getAttribute')->with('id')->andReturn(1);
        
        $logsRelation = Mockery::mock(HasMany::class);
        $logsRelation->shouldReceive('create')->once()->with([
            'type' => 'test_action',
            'data' => ['other_key' => 'value'],
            'causer_id' => 123,
            'note' => 'test note',
        ]);

        $task->shouldReceive('logs')->once()->andReturn($logsRelation);

        $service = new class extends TaskWorkflowService {
            public function publicLog($task, $type, $data = [], $note = null, $causerId = null) {
                return $this->log($task, $type, $data, $note, $causerId);
            }
        };

        $service->publicLog($task, 'test_action', ['note' => 'test note', 'other_key' => 'value']);
    }

    public function test_log_uses_explicit_note()
    {
        Auth::shouldReceive('id')->andReturn(123);

        $task = Mockery::mock(ProductionTask::class);
        
        $logsRelation = Mockery::mock(HasMany::class);
        $logsRelation->shouldReceive('create')->once()->with([
            'type' => 'test_action',
            'data' => ['some' => 'data'],
            'causer_id' => 123,
            'note' => 'explicit note',
        ]);

        $task->shouldReceive('logs')->once()->andReturn($logsRelation);

        $service = new class extends TaskWorkflowService {
            public function publicLog($task, $type, $data = [], $note = null, $causerId = null) {
                return $this->log($task, $type, $data, $note, $causerId);
            }
        };

        $service->publicLog($task, 'test_action', ['some' => 'data'], 'explicit note');
    }

    public function test_log_uses_explicit_causer_id()
    {
        Auth::shouldReceive('id')->andReturn(123);

        $task = Mockery::mock(ProductionTask::class);
        
        $logsRelation = Mockery::mock(HasMany::class);
        $logsRelation->shouldReceive('create')->once()->with([
            'type' => 'test_action',
            'data' => [],
            'causer_id' => 999,
            'note' => null,
        ]);

        $task->shouldReceive('logs')->once()->andReturn($logsRelation);

        $service = new class extends TaskWorkflowService {
            public function publicLog($task, $type, $data = [], $note = null, $causerId = null) {
                return $this->log($task, $type, $data, $note, $causerId);
            }
        };

        $service->publicLog($task, 'test_action', [], null, 999);
    }
}
