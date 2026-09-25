<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Client;
use App\Models\Complaint;
use App\Models\Contract;
use App\Models\Floor;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Space;
use App\Models\StaffAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CriticalWorkflowRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_activating_a_contract_does_not_reactivate_a_cancelled_reservation(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'secret',
            'role' => 'admin',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $campus = Campus::create(['name' => 'Campus', 'code' => 'C1', 'city' => 'Rabat', 'is_active' => true]);
        $floor = Floor::create(['campus_id' => $campus->id, 'name' => 'Floor', 'code' => 'F1']);
        $space = Space::create([
            'campus_id' => $campus->id,
            'floor_id' => $floor->id,
            'name' => 'Space',
            'code' => 'S1',
            'status' => 'available',
            'is_active' => true,
        ]);
        $clientUser = User::create([
            'name' => 'Client',
            'email' => 'client@example.com',
            'password' => 'secret',
            'role' => 'client',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $client = Client::create([
            'user_id' => $clientUser->id,
            'full_name' => 'Client',
            'email' => 'client@example.com',
            'status' => 'active',
        ]);
        $reservation = Reservation::create([
            'client_id' => $client->id,
            'space_id' => $space->id,
            'campus_id' => $campus->id,
            'floor_id' => $floor->id,
            'starts_at' => '2026-09-25 09:00:00',
            'ends_at' => '2026-09-26 17:00:00',
            'status' => 'cancelled',
        ]);
        $contract = Contract::create([
            'client_id' => $client->id,
            'reservation_id' => $reservation->id,
            'title' => 'Contract',
            'start_date' => '2026-09-25',
            'end_date' => '2026-09-26',
            'status' => 'draft',
            'pdf_path' => 'contracts/signed.pdf',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.contracts.update', $contract), [
                'title' => 'Contract',
                'start_date' => '2026-09-25',
                'end_date' => '2026-09-26',
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertSame('cancelled', $reservation->fresh()->status);
    }

    public function test_commercial_cannot_access_a_linked_complaint_outside_the_reservation_space_scope(): void
    {
        $commercial = User::create([
            'name' => 'Commercial',
            'email' => 'commercial@example.com',
            'password' => 'secret',
            'role' => 'commercial',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $assignedCampus = Campus::create(['name' => 'Assigned', 'code' => 'A1', 'city' => 'Rabat', 'is_active' => true]);
        $actualCampus = Campus::create(['name' => 'Actual', 'code' => 'A2', 'city' => 'Rabat', 'is_active' => true]);
        $assignedFloor = Floor::create(['campus_id' => $assignedCampus->id, 'name' => 'Assigned Floor', 'code' => 'AF']);
        $actualFloor = Floor::create(['campus_id' => $actualCampus->id, 'name' => 'Actual Floor', 'code' => 'XF']);
        StaffAssignment::create([
            'commercial_id' => $commercial->id,
            'campus_id' => $assignedCampus->id,
            'floor_id' => $assignedFloor->id,
        ]);
        $space = Space::create([
            'campus_id' => $actualCampus->id,
            'floor_id' => $actualFloor->id,
            'name' => 'Actual Space',
            'code' => 'S2',
            'status' => 'available',
            'is_active' => true,
        ]);
        $clientUser = User::create([
            'name' => 'Client',
            'email' => 'client2@example.com',
            'password' => 'secret',
            'role' => 'client',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $client = Client::create([
            'user_id' => $clientUser->id,
            'full_name' => 'Client',
            'email' => 'client2@example.com',
            'main_campus_id' => $assignedCampus->id,
            'status' => 'active',
        ]);
        $reservation = Reservation::create([
            'client_id' => $client->id,
            'space_id' => $space->id,
            'campus_id' => $actualCampus->id,
            'floor_id' => $actualFloor->id,
            'starts_at' => '2026-09-25 09:00:00',
            'ends_at' => '2026-09-26 17:00:00',
            'status' => 'pending',
        ]);
        $complaint = Complaint::create([
            'client_id' => $client->id,
            'user_id' => $clientUser->id,
            'reservation_id' => $reservation->id,
            'subject' => 'Issue',
            'description' => 'Issue description',
            'status' => 'new',
            'priority' => 'normal',
        ]);

        $this->actingAs($commercial)
            ->get(route('commercial.complaints.show', $complaint))
            ->assertForbidden();
    }

    public function test_final_payment_closes_the_contract_and_reservation_but_partial_payment_does_not(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'payments-admin@example.com',
            'password' => 'secret',
            'role' => 'admin',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $campus = Campus::create(['name' => 'Payment Campus', 'code' => 'PC1', 'city' => 'Rabat', 'is_active' => true]);
        $floor = Floor::create(['campus_id' => $campus->id, 'name' => 'Payment Floor', 'code' => 'PF1']);
        $space = Space::create([
            'campus_id' => $campus->id,
            'floor_id' => $floor->id,
            'name' => 'Payment Space',
            'code' => 'PS1',
            'status' => 'available',
            'is_active' => true,
        ]);
        $clientUser = User::create([
            'name' => 'Payment Client',
            'email' => 'payments-client@example.com',
            'password' => 'secret',
            'role' => 'client',
            'must_change_password' => false,
            'is_active' => true,
        ]);
        $client = Client::create([
            'user_id' => $clientUser->id,
            'full_name' => 'Payment Client',
            'email' => 'payments-client@example.com',
            'status' => 'active',
        ]);
        $reservation = Reservation::create([
            'client_id' => $client->id,
            'space_id' => $space->id,
            'campus_id' => $campus->id,
            'floor_id' => $floor->id,
            'starts_at' => '2026-09-25 09:00:00',
            'ends_at' => '2026-09-26 17:00:00',
            'status' => 'confirmed',
        ]);
        $contract = Contract::create([
            'client_id' => $client->id,
            'reservation_id' => $reservation->id,
            'title' => 'Payment Contract',
            'start_date' => '2026-09-25',
            'end_date' => '2026-09-26',
            'status' => 'active',
        ]);
        $firstPayment = Payment::create([
            'client_id' => $client->id,
            'contract_id' => $contract->id,
            'reservation_id' => $reservation->id,
            'due_date' => '2026-09-25',
            'amount_due' => 100,
            'status' => 'due',
        ]);
        $finalPayment = Payment::create([
            'client_id' => $client->id,
            'contract_id' => $contract->id,
            'reservation_id' => $reservation->id,
            'due_date' => '2026-09-26',
            'amount_due' => 100,
            'status' => 'due',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.payments.markAsPaid', $firstPayment))
            ->assertRedirect();

        $this->assertSame('active', $contract->fresh()->status);
        $this->assertSame('confirmed', $reservation->fresh()->status);

        $this->actingAs($admin)
            ->patch(route('admin.payments.markAsPaid', $finalPayment))
            ->assertRedirect();

        $this->assertSame('expired', $contract->fresh()->status);
        $this->assertSame('completed', $reservation->fresh()->status);

        $this->actingAs($admin)
            ->patch(route('admin.payments.markAsPaid', $finalPayment))
            ->assertRedirect();

        $this->assertSame('expired', $contract->fresh()->status);
        $this->assertSame('completed', $reservation->fresh()->status);
    }
}
