<?php

namespace Tests\Unit;

use App\Services\PermissionCatalog;
use PHPUnit\Framework\TestCase;

class PermissionCatalogTest extends TestCase
{
    public function test_permission_labels_describe_special_capabilities(): void
    {
        $this->assertSame(
            'View Inward Statistics and Pendency Details',
            PermissionCatalog::label('inward statistics')
        );
        $this->assertSame(
            'View T-Section Pending Petition Details',
            PermissionCatalog::label('view t-section pending details')
        );
        $this->assertSame(
            'Update Office Seats',
            PermissionCatalog::label('update seats')
        );
    }

    public function test_permissions_are_assigned_to_explicit_categories(): void
    {
        $this->assertSame('users', PermissionCatalog::categoryFor('access admin dashboard'));
        $this->assertSame('inward', PermissionCatalog::categoryFor('filter transfer'));
        $this->assertSame('analytics', PermissionCatalog::categoryFor('view seat diagnostics'));
        $this->assertSame('t_section', PermissionCatalog::categoryFor('view t-section diagnostics'));
        $this->assertSame('maintenance', PermissionCatalog::categoryFor('restore recycle bin'));
    }

    public function test_catalog_describes_all_supported_permission_categories(): void
    {
        foreach (PermissionCatalog::categories() as $category => $metadata) {
            $this->assertNotSame('', $metadata['title'], "Missing title for {$category}.");
            $this->assertNotSame('', $metadata['icon'], "Missing icon for {$category}.");
            $this->assertNotSame('', $metadata['badge'], "Missing style for {$category}.");
        }
    }
}
