<?php

namespace Tests\Feature;

use App\Enums\PhaseStatus;
use App\Enums\ProductionRequestPhase;
use App\Enums\RequestType;
use App\Models\Client;
use App\Models\ProductionRequest;
use App\Models\Project;
use App\Models\User;
use App\Services\ProductionRequestWorkflow;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductionRequestWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected ProductionRequestWorkflow $workflow;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workflow = app(ProductionRequestWorkflow::class);
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_start_a_direct_production_request()
    {
        $client = Client::factory()->create();
        $request = ProductionRequest::factory()->create([
            'client_id' => $client->client_id,
            'request_type' => RequestType::Direct->value,
        ]);

        $result = $this->workflow->start($request);

        $this->assertEquals(ProductionRequestPhase::FactoryIntake->value, $result->current_phase);
        $this->assertEquals(PhaseStatus::Pending->value, $result->phase_status);
        $this->assertEquals('factory_manager', $result->current_owner_role);
    }

    /** @test */
    public function it_can_start_an_indirect_production_request()
    {
        // Skip observer to test workflow directly
        ProductionRequest::withoutEvents(function () use (&$request) {
            $client = Client::factory()->create();
            $request = ProductionRequest::factory()->create([
                'client_id' => $client->client_id,
                'request_type' => RequestType::Indirect->value,
            ]);
        });

        $result = $this->workflow->start($request);

        $this->assertEquals(ProductionRequestPhase::ShowroomReview->value, $result->current_phase);
        $this->assertEquals(PhaseStatus::Pending->value, $result->phase_status);
        $this->assertEquals('showroom_manager', $result->current_owner_role);
    }

    /** @test */
    public function it_can_move_a_production_request_to_new_phase()
    {
        $request = ProductionRequest::factory()->create([
            'current_phase' => ProductionRequestPhase::FactoryIntake->value,
            'phase_status' => PhaseStatus::Pending->value,
        ]);

        $result = $this->workflow->move(
            $request,
            ProductionRequestPhase::DepartmentAssignment,
            PhaseStatus::Pending
        );

        $this->assertEquals(ProductionRequestPhase::DepartmentAssignment->value, $result->current_phase);
        $this->assertNotNull($result->sent_to_owner_at);
    }

    /** @test */
    public function it_can_mark_a_request_as_received()
    {
        $request = ProductionRequest::factory()->create([
            'current_phase' => ProductionRequestPhase::FactoryIntake->value,
            'phase_status' => PhaseStatus::Pending->value,
            'sent_to_owner_at' => now()->subHour(),
        ]);

        $result = $this->workflow->markReceived($request);

        $this->assertEquals(PhaseStatus::Received->value, $result->phase_status);
        $this->assertNotNull($result->received_by_owner_at);
        $this->assertEquals($this->user->id, $result->current_owner_user_id);
    }

    /** @test */
    public function it_can_approve_a_showroom_review_and_move_to_factory_intake()
    {
        $request = ProductionRequest::factory()->create([
            'current_phase' => ProductionRequestPhase::ShowroomReview->value,
            'phase_status' => PhaseStatus::Received->value,
        ]);

        $result = $this->workflow->approve($request);

        $this->assertEquals(ProductionRequestPhase::FactoryIntake->value, $result->current_phase);
        $this->assertEquals(PhaseStatus::Pending->value, $result->phase_status);
    }

    /** @test */
    public function it_can_approve_factory_intake_and_create_project()
    {
        $request = ProductionRequest::factory()->create([
            'current_phase' => ProductionRequestPhase::FactoryIntake->value,
            'phase_status' => PhaseStatus::Received->value,
        ]);

        $result = $this->workflow->approve($request);

        $this->assertEquals(ProductionRequestPhase::DepartmentAssignment->value, $result->current_phase);
        $this->assertTrue($result->project()->exists());
    }

    /** @test */
    public function it_can_reject_a_production_request()
    {
        $request = ProductionRequest::factory()->create([
            'current_phase' => ProductionRequestPhase::ShowroomReview->value,
            'phase_status' => PhaseStatus::Received->value,
        ]);

        $result = $this->workflow->reject($request, 'Incomplete information');

        $this->assertEquals(PhaseStatus::Rejected->value, $result->phase_status);
        $this->assertDatabaseHas('production_request_logs', [
            'production_request_id' => $request->id,
            'type' => 'rejected',
        ]);
    }

    /** @test */
    public function it_creates_log_entry_on_phase_transition()
    {
        $request = ProductionRequest::factory()->create([
            'current_phase' => ProductionRequestPhase::FactoryIntake->value,
            'phase_status' => PhaseStatus::Pending->value,
        ]);

        $this->workflow->move(
            $request,
            ProductionRequestPhase::DepartmentAssignment,
            PhaseStatus::Pending
        );

        $this->assertDatabaseHas('production_request_logs', [
            'production_request_id' => $request->id,
            'type' => 'transition',
        ]);
    }
}

