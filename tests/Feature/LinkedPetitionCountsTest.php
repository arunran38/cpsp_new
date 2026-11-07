<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Department;
use App\Models\Petition;
use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LinkedPetitionCountsTest extends TestCase
{
    use RefreshDatabase;

    protected User $cpspUser;
    protected Seat $cpspSeat;
    protected Petition $originalPetition;
    protected Petition $duplicatePetition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $cpspRole = Role::findByName('CPSP', 'web');

        $this->cpspSeat = Seat::factory()->create([
            'seat_name' => 'CPSP Desk 1',
            'is_active' => true,
        ]);
        $this->cpspSeat->assignRole($cpspRole);

        $this->cpspUser = User::factory()->create(['name' => 'CPSP Officer']);
        $this->cpspUser->assignRole($cpspRole);
        SeatUser::factory()->create([
            'user_id' => $this->cpspUser->user_id,
            'seat_id' => $this->cpspSeat->seat_id,
            'is_active' => true,
        ]);

        $this->originalPetition = Petition::factory()->create([
            'receipt_no' => 'ORIG-2026/001',
            'seat_id' => $this->cpspSeat->seat_id,
            'status' => Petition::STATUS_RECEIVED,
            'is_cpsp_processed' => true,
        ]);

        $this->duplicatePetition = Petition::factory()->create([
            'receipt_no' => 'DUP-2026/002',
            'seat_id' => $this->cpspSeat->seat_id,
            'status' => Petition::STATUS_RECEIVED,
            'is_cpsp_processed' => false,
            'is_returned_to_inward' => false,
        ]);
    }

    public function test_linking_petition_marks_it_as_processed_and_sets_audit_fields(): void
    {
        $response = $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->post(route('petitions.linkDuplicate', $this->duplicatePetition->hashid), [
                'original_receipt_no' => $this->originalPetition->receipt_no,
            ]);

        $response->assertRedirect(route('petitions.show', $this->duplicatePetition->hashid));

        $this->duplicatePetition->refresh();
        $this->assertSame($this->originalPetition->petition_id, $this->duplicatePetition->linked_petition_id);
        $this->assertSame(Petition::STATUS_DUPLICATE, $this->duplicatePetition->status);
        $this->assertTrue((bool)$this->duplicatePetition->is_cpsp_processed);
        $this->assertNotNull($this->duplicatePetition->processed_at);
        $this->assertSame($this->cpspUser->user_id, $this->duplicatePetition->processed_by_user_id);
        $this->assertSame($this->cpspSeat->seat_id, $this->duplicatePetition->processed_by_seat_id);
        $this->assertSame('Duplicate', $this->duplicatePetition->workflow_status['label']);
        $this->assertSame('Linked to ' . $this->originalPetition->receipt_no, $this->duplicatePetition->workflow_status['description']);
    }

    public function test_linked_petition_is_excluded_from_pending_counts_and_included_in_processed_counts(): void
    {
        // 1. Before linking: duplicate is pending (is_cpsp_processed = false)
        $inwardPendingBefore = Petition::filterByTab('inward')->count();
        $this->assertSame(1, $inwardPendingBefore);

        // 2. Link duplicate to original
        $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->post(route('petitions.linkDuplicate', $this->duplicatePetition->hashid), [
                'original_receipt_no' => $this->originalPetition->receipt_no,
            ]);

        $this->duplicatePetition->refresh();

        // 3. After linking: duplicate is NO LONGER in pending/inward tab
        $inwardPendingAfter = Petition::filterByTab('inward')->count();
        $this->assertSame(0, $inwardPendingAfter);

        // 4. Inward statistics: Total = 2 (original + linked), Processed = 2, Pending = 0
        $adminUser = User::factory()->create();
        $adminRole = Role::findByName('admin', 'web');
        $adminUser->assignRole($adminRole);

        $statsResponse = $this->actingAs($adminUser)
            ->get(route('inward.statistics'));

        $statsResponse->assertOk();
        $seatStats = $statsResponse->viewData('seatStats');
        $targetSeatStat = collect($seatStats)->firstWhere('seat_id', $this->cpspSeat->seat_id);

        $this->assertNotNull($targetSeatStat);
        $this->assertSame(2, $targetSeatStat['total']);
        $this->assertSame(2, $targetSeatStat['processed']);
        $this->assertSame(0, $targetSeatStat['pending']);
    }

    public function test_unlinking_petition_restores_previous_status_and_processing_state(): void
    {
        // Link first
        $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->post(route('petitions.linkDuplicate', $this->duplicatePetition->hashid), [
                'original_receipt_no' => $this->originalPetition->receipt_no,
            ]);

        $this->duplicatePetition->refresh();
        $this->assertTrue((bool)$this->duplicatePetition->is_cpsp_processed);

        // Unlink
        $unlinkResponse = $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->post(route('petitions.unlinkDuplicate', $this->duplicatePetition->hashid));

        $unlinkResponse->assertRedirect(route('petitions.show', $this->originalPetition->hashid));

        $this->duplicatePetition->refresh();
        $this->assertNull($this->duplicatePetition->linked_petition_id);
        $this->assertSame(Petition::STATUS_RECEIVED, $this->duplicatePetition->status);
        $this->assertFalse((bool)$this->duplicatePetition->is_cpsp_processed);
        $this->assertNull($this->duplicatePetition->processed_at);
    }

    public function test_excel_export_includes_linked_duplicate_petitions_with_appropriate_status_and_recommendation(): void
    {
        // 1. Link duplicate
        $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->post(route('petitions.linkDuplicate', $this->duplicatePetition->hashid), [
                'original_receipt_no' => $this->originalPetition->receipt_no,
            ]);

        $this->duplicatePetition->refresh();

        // 2. Admin user with export permissions exports
        $adminUser = User::factory()->create();
        $adminRole = Role::findByName('admin', 'web');
        $adminUser->assignRole($adminRole);

        $response = $this->actingAs($adminUser)->get(route('petitions.export', ['tab' => 'all']));
        $response->assertOk();

        $content = $response->getContent();

        // Both original and linked duplicate must be present
        $this->assertStringContainsString($this->originalPetition->receipt_no, $content);
        $this->assertStringContainsString($this->duplicatePetition->receipt_no, $content);

        // Status and Recommendation for duplicate
        $this->assertStringContainsString('Duplicate (Linked to ' . $this->originalPetition->receipt_no . ')', $content);
        $this->assertStringContainsString('Linked as Duplicate', $content);
    }

    public function test_reports_table_shows_linked_duplicate_badge_on_original_petition(): void
    {
        // 1. Link duplicate
        $this->actingAs($this->cpspUser)
            ->withSession(['current_seat_id' => $this->cpspSeat->seat_id])
            ->post(route('petitions.linkDuplicate', $this->duplicatePetition->hashid), [
                'original_receipt_no' => $this->originalPetition->receipt_no,
            ]);

        // 2. Access petitions index
        $adminUser = User::factory()->create();
        $adminRole = Role::findByName('admin', 'web');
        $adminUser->assignRole($adminRole);

        $response = $this->actingAs($adminUser)->get(route('petitions.index', ['tab' => 'all']));
        $response->assertOk();

        // Original petition row displays badge with duplicate's receipt number
        $response->assertSee($this->originalPetition->receipt_no);
        $response->assertSee($this->duplicatePetition->receipt_no);
    }
}

