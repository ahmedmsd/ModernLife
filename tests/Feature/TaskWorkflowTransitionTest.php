<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Department;
use App\Models\MaterialRequest;
use App\Models\ProductionRequest;
use App\Models\ProductionTask;
use App\Models\Project;
use App\Models\User;
use App\Services\Tasks\Workflow\AssignmentWorkflowService;
use App\Services\Tasks\Workflow\CompletionWorkflowService;
use App\Services\Tasks\Workflow\InstallationWorkflowService;
use App\Services\Tasks\Workflow\ManufacturingWorkflowService;
use App\Services\Tasks\Workflow\MaterialsWorkflowService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Comprehensive workflow transition tests for the Task Workflow Services.
 * Tests cover all major manufacturing workflow phases:
 * - Assignment
 * - Materials
 * - Manufacturing
 * - QA (after manufacturing)
 * - Installation
 * - QA (after installation)
 * - Completion
 * 
 * Uses DatabaseTransactions for test isolation - each test runs in a transaction
 * that is rolled back after completion, keeping the database clean.
 */
class TaskWorkflowTransitionTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected User $deptManager;
    protected User $purchasingManager;
    protected User $qualityManager;
    protected Department $department;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users with roles
        $this->user = User::factory()->create();
        $this->deptManager = User::factory()->create();
        $this->purchasingManager = User::factory()->create();
        $this->qualityManager = User::factory()->create();

        // Assign roles if spatie/permission is available
        if (method_exists($this->deptManager, 'assignRole')) {
            $this->deptManager->assignRole('department_manager');
            $this->purchasingManager->assignRole('purchasing_manager');
            $this->qualityManager->assignRole('quality_manager');
        }

        // Create department with manager
        $this->department = Department::factory()->create([
            'manager_id' => $this->deptManager->id,
        ]);

        // Create project
        $this->project = Project::factory()->create();

        $this->actingAs($this->deptManager);
    }

    // ==================== ASSIGNMENT WORKFLOW TESTS ====================

    /** @test */
    public function assign_to_dept_manager_sets_pending_status()
    {
        $task = ProductionTask::factory()->create([
            'project_id' => $this->project->id,
            'department_id' => $this->department->dept_id,
            'status' => 'new',
        ]);

        $service = app(AssignmentWorkflowService::class);
        $service->assignToDeptManager($task, 'Test assignment', now()->addWeeks(2)->toDateString());

        $task->refresh();

        $this->assertEquals('pending', $task->status);
        $this->assertEquals('department_manager', $task->current_owner_role);
        $this->assertNotNull($task->assigned_at);
        $this->assertNotNull($task->sent_to_owner_at);
    }

    /** @test */
    public function dept_acknowledge_sets_received_status()
    {
        $task = ProductionTask::factory()->create([
            'project_id' => $this->project->id,
            'department_id' => $this->department->dept_id,
            'status' => 'pending',
            'current_owner_role' => 'department_manager',
            'current_owner_user_id' => $this->deptManager->id,
            'sent_to_owner_at' => now(),
        ]);

        $service = app(AssignmentWorkflowService::class);
        $service->deptAcknowledge($task, 'Acknowledged');

        $task->refresh();

        $this->assertEquals('received', $task->status);
        $this->assertNotNull($task->received_at);
        $this->assertNotNull($task->received_by_owner_at);
    }

    /** @test */
    public function dept_reject_returns_to_factory()
    {
        $task = ProductionTask::factory()->create([
            'project_id' => $this->project->id,
            'department_id' => $this->department->dept_id,
            'status' => 'received',
            'current_owner_role' => 'department_manager',
        ]);

        $service = app(AssignmentWorkflowService::class);
        $service->deptRejectToFactory($task, 'Insufficient resources');

        $task->refresh();

        $this->assertEquals('returned_to_factory', $task->status);
        $this->assertEquals('factory_manager', $task->current_owner_role);
    }

    // ==================== MATERIALS WORKFLOW TESTS ====================

    /** @test */
    public function request_materials_creates_material_request()
    {
        $task = ProductionTask::factory()->create([
            'project_id' => $this->project->id,
            'department_id' => $this->department->dept_id,
            'status' => 'received',
        ]);

        $service = app(MaterialsWorkflowService::class);
        $service->requestMaterials($task, 'Need steel plates', 'po_files/test.pdf');

        $task->refresh();

        $this->assertEquals('materials_wait', $task->status);
        $this->assertDatabaseHas('material_requests', [
            'task_id' => $task->id,
            'status' => 'requested',
        ]);
    }

    /** @test */
    public function materials_provided_sets_materials_done_status()
    {
        $task = ProductionTask::factory()->create([
            'project_id' => $this->project->id,
            'department_id' => $this->department->dept_id,
            'status' => 'materials_prep',
        ]);

        // Create a material request first
        MaterialRequest::create([
            'task_id' => $task->id,
            'department_id' => $task->department_id,
            'requested_by' => $this->user->id,
            'requested_at' => now(),
            'status' => 'approved',
        ]);

        $service = app(MaterialsWorkflowService::class);
        $service->materialsProvided($task, 5000.00, 'Materials delivered');

        $task->refresh();

        $this->assertEquals('materials_done', $task->status);
    }

    /** @test */
    public function materials_received_ok_sets_waiting_production_status()
    {
        $task = ProductionTask::factory()->create([
            'project_id' => $this->project->id,
            'department_id' => $this->department->dept_id,
            'status' => 'materials_done',
            'current_owner_role' => 'purchasing_manager',
        ]);

        $service = app(MaterialsWorkflowService::class);
        $service->materialsReceivedOk(
            $task,
            now()->addDays(1)->toDateString(),
            now()->addDays(7)->toDateString(),
            now()->addDays(14)->toDateString(),
            'All materials received'
        );

        $task->refresh();

        $this->assertEquals('waiting_production', $task->status);
        $this->assertNotNull($task->planned_start_at);
        $this->assertNotNull($task->planned_end_at);
        $this->assertNotNull($task->planned_install_at);
    }

    // ==================== MANUFACTURING WORKFLOW TESTS ====================

    /** @test */
    public function start_production_sets_in_progress_status()
    {
        $task = ProductionTask::factory()->create([
            'project_id' => $this->project->id,
            'department_id' => $this->department->dept_id,
            'status' => 'waiting_production',
            'current_owner_role' => 'department_manager',
        ]);

        $service = app(ManufacturingWorkflowService::class);
        $service->startProduction($task, null, 'Starting production');

        $task->refresh();

        $this->assertEquals('in_progress', $task->status);
        $this->assertNotNull($task->actual_start_at);
    }

    /** @test */
    public function finish_manufacturing_sends_to_qa()
    {
        $task = ProductionTask::factory()->create([
            'project_id' => $this->project->id,
            'department_id' => $this->department->dept_id,
            'status' => 'in_progress',
            'actual_start_at' => now()->subDays(3),
        ]);

        $service = app(ManufacturingWorkflowService::class);
        $service->finishManufacturingAndSendToQA($task, null, 'Manufacturing complete');

        $task->refresh();

        $this->assertEquals('under_review', $task->status);
        $this->assertEquals('quality_manager', $task->current_owner_role);
        $this->assertNotNull($task->actual_end_at);
    }

    /** @test */
    public function approve_manufacturing_qa_sends_to_installation()
    {
        $task = ProductionTask::factory()->create([
            'project_id' => $this->project->id,
            'department_id' => $this->department->dept_id,
            'status' => 'under_review',
            'current_owner_role' => 'quality_manager',
        ]);

        $service = app(ManufacturingWorkflowService::class);
        $service->approveManufacturingQA($task, 'QA approved');

        $task->refresh();

        $this->assertEquals('approved', $task->status);
        $this->assertEquals('installation_manager', $task->current_owner_role);
    }

    /** @test */
    public function reject_manufacturing_qa_returns_to_manufacturing()
    {
        $task = ProductionTask::factory()->create([
            'project_id' => $this->project->id,
            'department_id' => $this->department->dept_id,
            'status' => 'under_review',
            'current_owner_role' => 'quality_manager',
        ]);

        $service = app(ManufacturingWorkflowService::class);
        $service->rejectManufacturingQA($task, 'Quality issues found');

        $task->refresh();

        $this->assertEquals('rework', $task->status);
        $this->assertEquals('department_manager', $task->current_owner_role);
    }

    // ==================== INSTALLATION WORKFLOW TESTS ====================

    /** @test */
    public function start_installation_sets_in_progress_status()
    {
        $task = ProductionTask::factory()->create([
            'project_id' => $this->project->id,
            'department_id' => $this->department->dept_id,
            'status' => 'approved',
            'current_owner_role' => 'installation_manager',
        ]);

        $service = app(InstallationWorkflowService::class);
        $service->startInstallation($task, null, 'Starting installation');

        $task->refresh();

        $this->assertEquals('in_progress', $task->status);
    }

    /** @test */
    public function finish_installation_sends_to_qa()
    {
        $task = ProductionTask::factory()->create([
            'project_id' => $this->project->id,
            'department_id' => $this->department->dept_id,
            'status' => 'in_progress',
            'current_owner_role' => 'installation_manager',
        ]);

        $service = app(InstallationWorkflowService::class);
        $service->finishInstallationToQA($task, null, 'Installation complete');

        $task->refresh();

        $this->assertEquals('under_review', $task->status);
        $this->assertEquals('quality_manager', $task->current_owner_role);
    }

    /** @test */
    public function approve_installation_qa_marks_qa_approved()
    {
        $task = ProductionTask::factory()->create([
            'project_id' => $this->project->id,
            'department_id' => $this->department->dept_id,
            'status' => 'under_review',
            'current_owner_role' => 'quality_manager',
        ]);

        $service = app(InstallationWorkflowService::class);
        $service->approveInstallationQA($task, 'Final QA approved');

        $task->refresh();

        $this->assertEquals('qa_approved', $task->status);
    }

    /** @test */
    public function reject_installation_qa_returns_to_installation()
    {
        $task = ProductionTask::factory()->create([
            'project_id' => $this->project->id,
            'department_id' => $this->department->dept_id,
            'status' => 'under_review',
            'current_owner_role' => 'quality_manager',
        ]);

        $service = app(InstallationWorkflowService::class);
        $service->rejectInstallationQA($task, 'Installation issues found');

        $task->refresh();

        $this->assertEquals('rework', $task->status);
        $this->assertEquals('installation_manager', $task->current_owner_role);
    }

    // ==================== COMPLETION WORKFLOW TESTS ====================

    /** @test */
    public function complete_task_sets_completed_status()
    {
        $task = ProductionTask::factory()->create([
            'project_id' => $this->project->id,
            'department_id' => $this->department->dept_id,
            'status' => 'qa_approved',
        ]);

        $service = app(CompletionWorkflowService::class);
        $service->uploadClientReceiptAndComplete($task, 'receipts/test.pdf', null, 'Client signed');

        $task->refresh();

        $this->assertEquals('completed', $task->status);
        $this->assertNotNull($task->completed_at);
        $this->assertNull($task->current_owner_role);
        $this->assertEquals('receipts/test.pdf', $task->client_receipt);
    }

    /** @test */
    public function complete_last_task_closes_project()
    {
        // Create a project with a single task
        $project = Project::factory()->create(['status' => 'active']);

        $task = ProductionTask::factory()->create([
            'project_id' => $project->id,
            'department_id' => $this->department->dept_id,
            'status' => 'qa_approved',
        ]);

        $service = app(CompletionWorkflowService::class);
        $service->uploadClientReceiptAndComplete($task, null, null, 'Complete');

        $project->refresh();

        $this->assertEquals('completed', $project->status);
    }

    // ==================== LOGGING TESTS ====================

    /** @test */
    public function workflow_transitions_create_log_entries()
    {
        $task = ProductionTask::factory()->create([
            'project_id' => $this->project->id,
            'department_id' => $this->department->dept_id,
            'status' => 'waiting_production',
        ]);

        $service = app(ManufacturingWorkflowService::class);
        $service->startProduction($task, null, 'Test log entry');

        $this->assertDatabaseHas('task_logs', [
            'task_id' => $task->id,
            'type' => 'manufacturing_started',
        ]);
    }
}
