<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditLogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_logs_table_shows_ip_address_and_no_method_badge(): void
    {
        Permission::firstOrCreate(['name' => 'view log', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->givePermissionTo('view log');

        AuditLog::create([
            'user_id' => $admin->user_id,
            'action' => 'UPDATE',
            'module' => 'Roles',
            'description' => 'Updated Roles record',
            'method' => 'PUT',
            'url' => 'http://127.0.0.1:8000/admin/roles/5',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.audit_logs.index'));

        $response->assertOk();
        $response->assertSee('IP Address');
        $response->assertSee('Excel Export');
        $response->assertSee('127.0.0.1');
        // Method badge should not be in the table
        $response->assertDontSee('<span class="px-1.5 py-0.5 text-[10px] font-bold rounded bg-slate-100 text-slate-700 uppercase">', false);
    }

    public function test_audit_logs_excel_export_returns_xlsx_file(): void
    {
        Permission::firstOrCreate(['name' => 'view log', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->givePermissionTo('view log');

        AuditLog::create([
            'user_id' => $admin->user_id,
            'action' => 'UPDATE',
            'module' => 'Roles',
            'description' => 'Updated Roles record',
            'method' => 'PUT',
            'url' => 'http://127.0.0.1:8000/admin/roles/5',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.audit_logs.export'));

        $response->assertOk();
        $this->assertStringContainsString('filename=cpsp_audit_logs_', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }
}

