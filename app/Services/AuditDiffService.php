<?php

namespace App\Services;

use App\Models\DepartmentList;
use App\Models\DesignationList;
use App\Models\District;
use App\Models\Petition;
use App\Models\Unit;
use App\Models\Upload;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

class AuditDiffService
{
    /**
     * Compute field-level differences between original petition and updated payload.
     *
     * @param Petition $petition Untouched original petition model
     * @param array $newData Validated incoming input data
     * @param array $files Uploaded files
     * @param array $deletedAttachments Deleted attachment IDs
     * @return array Array of diff items with field, field_ml, old, and new
     */
    public function computePetitionDiff(
        Petition $petition,
        array $newData,
        array $files = [],
        array $deletedAttachments = []
    ): array {
        $diffs = [];

        // 1. Receipt No
        if (array_key_exists('receipt_no', $newData) && $newData['receipt_no'] !== null) {
            $oldReceipt = $this->normalizeString($petition->receipt_no);
            $newReceipt = $this->normalizeString($newData['receipt_no']);
            if ($oldReceipt !== $newReceipt) {
                $diffs[] = [
                    'field'     => 'Receipt Number',
                    'field_ml'  => 'രസീത് നമ്പർ',
                    'field_key' => 'receipt_no',
                    'old'       => $oldReceipt ?: '[Blank]',
                    'new'       => $newReceipt ?: '[Blank]',
                ];
            }
        }

        // 2. Date of Receipt
        if (array_key_exists('date_of_petition_received', $newData) && !empty($newData['date_of_petition_received'])) {
            $oldDate = $petition->date_of_petition_received ? Carbon::parse($petition->date_of_petition_received)->format('d-m-Y') : '';
            $newDate = Carbon::parse($newData['date_of_petition_received'])->format('d-m-Y');
            if ($oldDate !== $newDate) {
                $diffs[] = [
                    'field'     => 'Date of Receipt',
                    'field_ml'  => 'ലഭിച്ച തീയതി',
                    'field_key' => 'date_of_petition_received',
                    'old'       => $oldDate ?: '[Blank]',
                    'new'       => $newDate ?: '[Blank]',
                ];
            }
        }

        // 3. Nature of Petition
        if (array_key_exists('nature_of_petition', $newData)) {
            $oldNature = $this->normalizeString($petition->nature_of_petition);
            $newNature = $this->normalizeString($newData['nature_of_petition']);
            if ($oldNature !== $newNature) {
                $diffs[] = [
                    'field'     => 'Nature of Petition',
                    'field_ml'  => 'പരാതിയുടെ സ്വഭാവം',
                    'field_key' => 'nature_of_petition',
                    'old'       => $oldNature ?: '[Blank]',
                    'new'       => $newNature ?: '[Blank]',
                ];
            }
        }

        // 4. Mode of Petition Received
        if (array_key_exists('mode_of_petition_received', $newData)) {
            $oldMode = $this->normalizeString($petition->mode_of_petition_received);
            $newMode = $this->normalizeString($newData['mode_of_petition_received']);
            if (strtolower($oldMode) !== strtolower($newMode)) {
                $diffs[] = [
                    'field'     => 'Mode of Receipt',
                    'field_ml'  => 'സ്വീകരിച്ച രീതി',
                    'field_key' => 'mode_of_petition_received',
                    'old'       => $oldMode ?: '[Blank]',
                    'new'       => $newMode ?: '[Blank]',
                ];
            }
        }

        // 5. Mode Others
        $newModeOthers = $newData['mode_others'] ?? ($newData['mode_of_petition_received_others'] ?? null);
        if ($newModeOthers !== null || $petition->mode_of_petition_received_others !== null) {
            $oldModeOthers = $this->normalizeString($petition->mode_of_petition_received_others);
            $normModeOthers = $this->normalizeString($newModeOthers);
            if ($oldModeOthers !== $normModeOthers) {
                $diffs[] = [
                    'field'     => 'Other Mode Details',
                    'field_ml'  => 'മറ്റു രീതികളുടെ വിവരങ്ങൾ',
                    'field_key' => 'mode_others',
                    'old'       => $oldModeOthers ?: '[None]',
                    'new'       => $normModeOthers ?: '[None]',
                ];
            }
        }

        // 6. Concerned District
        if (array_key_exists('concerned_district_id', $newData)) {
            $oldDistId = $petition->concerned_district_id;
            $newDistId = !empty($newData['concerned_district_id']) ? (int) $newData['concerned_district_id'] : null;
            if ($oldDistId != $newDistId) {
                $diffs[] = [
                    'field'     => 'Concerned District',
                    'field_ml'  => 'ബന്ധപ്പെട്ട ജില്ല',
                    'field_key' => 'concerned_district_id',
                    'old'       => $this->resolveDistrictName($oldDistId),
                    'new'       => $this->resolveDistrictName($newDistId),
                ];
            }
        }

        // 7. Concerned Unit
        $modeIsUnit = strtolower($newData['mode_of_petition_received'] ?? $petition->mode_of_petition_received ?? '') === 'unit';
        $newUnitId = $modeIsUnit ? (!empty($newData['unit_id']) ? (int) $newData['unit_id'] : null) : null;
        $oldUnitId = $petition->unit_id;
        if ($oldUnitId != $newUnitId) {
            $diffs[] = [
                'field'     => 'Concerned Unit',
                'field_ml'  => 'ബന്ധപ്പെട്ട യൂണിറ്റ്',
                'field_key' => 'unit_id',
                'old'       => $this->resolveUnitName($oldUnitId),
                'new'       => $this->resolveUnitName($newUnitId),
            ];
        }

        // 8. Date Received at Unit
        $newUnitDate = $modeIsUnit ? ($newData['date_of_petition_received_at_unit'] ?? null) : null;
        $oldUnitDate = $petition->date_of_petition_received_at_unit ? Carbon::parse($petition->date_of_petition_received_at_unit)->format('d-m-Y') : '';
        $normNewUnitDate = !empty($newUnitDate) ? Carbon::parse($newUnitDate)->format('d-m-Y') : '';
        if ($oldUnitDate !== $normNewUnitDate) {
            $diffs[] = [
                'field'     => 'Date Received at Unit',
                'field_ml'  => 'യൂണിറ്റിൽ ലഭിച്ച തീയതി',
                'field_key' => 'date_of_petition_received_at_unit',
                'old'       => $oldUnitDate ?: '[None]',
                'new'       => $normNewUnitDate ?: '[None]',
            ];
        }

        // 9. Description / Gist of Allegation
        if (array_key_exists('description', $newData)) {
            $oldDesc = $this->normalizeString($petition->description);
            $newDesc = $this->normalizeString($newData['description']);
            if ($oldDesc !== $newDesc) {
                $diffs[] = [
                    'field'     => 'Description / Allegation',
                    'field_ml'  => 'പരാതിയുടെ ചുരുക്കം',
                    'field_key' => 'description',
                    'old'       => $oldDesc ?: '[Blank]',
                    'new'       => $newDesc ?: '[Blank]',
                ];
            }
        }

        // 10. Proposed Action
        if (array_key_exists('proposed_action', $newData)) {
            $oldAction = $this->normalizeString($petition->proposed_action);
            $newAction = $this->normalizeString($newData['proposed_action']);
            if ($oldAction !== $newAction) {
                $diffs[] = [
                    'field'     => 'Proposed Action',
                    'field_ml'  => 'നിർദ്ദേശിച്ച നടപടി',
                    'field_key' => 'proposed_action',
                    'old'       => $oldAction ?: '[None]',
                    'new'       => $newAction ?: '[None]',
                ];
            }
        }

        // 11. Complainants / Petitioners
        if (array_key_exists('complainants', $newData)) {
            $oldComplainants = $this->summarizeExistingComplainants($petition);
            $newComplainants = $this->summarizeNewComplainants($newData['complainants']);
            if ($oldComplainants !== $newComplainants) {
                $diffs[] = [
                    'field'     => 'Complainants / Petitioners',
                    'field_ml'  => 'പരാതിക്കാർ',
                    'field_key' => 'complainants',
                    'old'       => $oldComplainants,
                    'new'       => $newComplainants,
                ];
            }
        }

        // 12. Suspects / Accused
        if (array_key_exists('accused', $newData)) {
            $oldAccused = $this->summarizeExistingAccused($petition);
            $newAccused = $this->summarizeNewAccused($newData['accused']);
            if ($oldAccused !== $newAccused) {
                $diffs[] = [
                    'field'     => 'Suspects / Accused',
                    'field_ml'  => 'എതിർകക്ഷികൾ / പ്രതികൾ',
                    'field_key' => 'accused',
                    'old'       => $oldAccused,
                    'new'       => $newAccused,
                ];
            }
        }

        // 13. Attachments Added / Deleted
        $attachmentDiff = $this->summarizeAttachmentChanges($files, $deletedAttachments);
        if ($attachmentDiff !== null) {
            $diffs[] = $attachmentDiff;
        }

        return $diffs;
    }

    /**
     * Summarize existing complainants from petition relations
     */
    protected function summarizeExistingComplainants(Petition $petition): string
    {
        $addresses = $petition->uniqueOwnAddresses()->where('person_type', 'Complainant');
        if ($addresses->isEmpty()) {
            return 'None';
        }

        $grouped = $addresses->groupBy(function ($addr) {
            return ($addr->person_name ?: 'Anonymous') . '_' .
                ($addr->phone ?: '') . '_' .
                ($addr->email ?: '');
        });

        $items = [];
        $i = 1;
        foreach ($grouped as $group) {
            $first = $group->first();
            $name = trim((string) ($first->person_name ?? '')) ?: 'Anonymous / Unknown';
            $details = [];

            if (!empty($first->phone)) {
                $details[] = 'Phone: ' . $first->phone;
            }
            if (!empty($first->email)) {
                $details[] = 'Email: ' . $first->email;
            }

            $addrParts = [];
            foreach ($group as $addr) {
                $formatted = $addr->formatted_address;
                if ($formatted) {
                    $addrParts[] = $formatted;
                } elseif (!empty($addr->full_address) && strtolower($addr->full_address) !== 'not provided') {
                    $addrParts[] = $addr->full_address;
                }
            }
            if (!empty($addrParts)) {
                $details[] = 'Address: ' . implode('; ', $addrParts);
            }

            $detailStr = !empty($details) ? ' (' . implode(', ', $details) . ')' : '';
            $items[] = "{$i}. {$name}{$detailStr}";
            $i++;
        }

        return !empty($items) ? implode("\n", $items) : 'None';
    }

    /**
     * Summarize incoming complainants payload
     */
    protected function summarizeNewComplainants(?array $complainants): string
    {
        if (empty($complainants) || !is_array($complainants)) {
            return 'None';
        }

        $items = [];
        $i = 1;
        foreach ($complainants as $comp) {
            if (!is_array($comp)) {
                continue;
            }
            $name = trim((string) ($comp['name'] ?? '')) ?: 'Anonymous / Unknown';
            $phone = trim((string) ($comp['phone'] ?? ''));
            $email = trim((string) ($comp['email'] ?? ''));

            $details = [];
            if ($phone !== '') {
                $details[] = 'Phone: ' . $phone;
            }
            if ($email !== '') {
                $details[] = 'Email: ' . $email;
            }

            if (!empty($comp['addresses']) && is_array($comp['addresses'])) {
                $addrParts = [];
                foreach ($comp['addresses'] as $addr) {
                    $a = trim((string) ($addr['address'] ?? ''));
                    $pin = trim((string) ($addr['pincode'] ?? ''));
                    $distId = $addr['district_id'] ?? null;
                    $dist = $distId ? District::find($distId) : null;

                    $parts = [];
                    if ($a !== '') $parts[] = $a;
                    if ($dist) $parts[] = $dist->district_name;
                    if ($pin !== '') $parts[] = 'PIN: ' . $pin;

                    if (!empty($parts)) {
                        $addrParts[] = implode(', ', $parts);
                    }
                }
                if (!empty($addrParts)) {
                    $details[] = 'Address: ' . implode('; ', $addrParts);
                }
            }

            $detailStr = !empty($details) ? ' (' . implode(', ', $details) . ')' : '';
            $items[] = "{$i}. {$name}{$detailStr}";
            $i++;
        }

        return !empty($items) ? implode("\n", $items) : 'None';
    }

    /**
     * Summarize existing suspects from petition relations
     */
    protected function summarizeExistingAccused(Petition $petition): string
    {
        $addresses = $petition->uniqueOwnAddresses()->where('person_type', 'Accused');
        if ($addresses->isEmpty()) {
            return 'None';
        }

        $grouped = $addresses->groupBy(function ($addr) {
            return ($addr->person_name ?: 'Unknown') . '_' .
                ($addr->department_id ?: '') . '_' .
                ($addr->designation_id ?: '') . '_' .
                ($addr->pen_number ?: '') . '_' .
                ($addr->phone ?: '') . '_' .
                ($addr->entity_type ?: '');
        });

        $items = [];
        $i = 1;
        foreach ($grouped as $group) {
            $first = $group->first();
            $name = trim((string) ($first->person_name ?? '')) ?: 'Unknown';
            $details = [];

            if (!empty($first->entity_type) && strtolower($first->entity_type) !== 'person') {
                $details[] = 'Type: ' . $first->entity_type;
            }

            $dept = $first->department?->department_name
                ?? ($first->department_id ? DepartmentList::find($first->department_id)?->department_name : null);
            if ($dept) {
                $details[] = 'Dept: ' . $dept;
            }

            $desig = $first->designation?->designation_name
                ?? ($first->designation_id ? DesignationList::find($first->designation_id)?->designation_name : null);
            if ($desig) {
                $details[] = 'Desig: ' . $desig;
            }

            if (!empty($first->pen_number)) {
                $details[] = 'PEN: ' . $first->pen_number;
            }
            if (!empty($first->phone)) {
                $details[] = 'Phone: ' . $first->phone;
            }

            $addrParts = [];
            foreach ($group as $addr) {
                $formatted = $addr->formatted_address;
                if ($formatted) {
                    $addrParts[] = $formatted;
                } elseif (!empty($addr->full_address) && strtolower($addr->full_address) !== 'not provided') {
                    $addrParts[] = $addr->full_address;
                }
            }
            if (!empty($addrParts)) {
                $details[] = 'Address: ' . implode('; ', $addrParts);
            }

            $detailStr = !empty($details) ? ' (' . implode(' | ', $details) . ')' : '';
            $items[] = "{$i}. {$name}{$detailStr}";
            $i++;
        }

        return !empty($items) ? implode("\n", $items) : 'None';
    }

    /**
     * Summarize incoming accused payload
     */
    protected function summarizeNewAccused(?array $accused): string
    {
        if (empty($accused) || !is_array($accused)) {
            return 'None';
        }

        $items = [];
        $i = 1;
        foreach ($accused as $acc) {
            if (!is_array($acc)) {
                continue;
            }
            $name = trim((string) ($acc['name'] ?? '')) ?: 'Unknown';
            $details = [];

            $entityType = $acc['entity_type'] ?? 'Person';
            if (strtolower($entityType) !== 'person') {
                $details[] = 'Type: ' . $entityType;
            }

            $deptId = $acc['department_id'] ?? null;
            if ($deptId) {
                $dept = DepartmentList::find($deptId);
                if ($dept) {
                    $details[] = 'Dept: ' . $dept->department_name;
                }
            }

            $desigId = $acc['designation_id'] ?? null;
            if ($desigId) {
                $desig = DesignationList::find($desigId);
                if ($desig) {
                    $details[] = 'Desig: ' . $desig->designation_name;
                }
            }

            $pen = trim((string) ($acc['pen_number'] ?? ''));
            if ($pen !== '') {
                $details[] = 'PEN: ' . $pen;
            }

            $phone = trim((string) ($acc['phone'] ?? ''));
            if ($phone !== '') {
                $details[] = 'Phone: ' . $phone;
            }

            if (!empty($acc['addresses']) && is_array($acc['addresses'])) {
                $addrParts = [];
                foreach ($acc['addresses'] as $addr) {
                    $a = trim((string) ($addr['address'] ?? ''));
                    $pin = trim((string) ($addr['pincode'] ?? ''));
                    $distId = $addr['district_id'] ?? null;
                    $dist = $distId ? District::find($distId) : null;

                    $parts = [];
                    if ($a !== '') $parts[] = $a;
                    if ($dist) $parts[] = $dist->district_name;
                    if ($pin !== '') $parts[] = 'PIN: ' . $pin;

                    if (!empty($parts)) {
                        $addrParts[] = implode(', ', $parts);
                    }
                }
                if (!empty($addrParts)) {
                    $details[] = 'Address: ' . implode('; ', $addrParts);
                }
            }

            $detailStr = !empty($details) ? ' (' . implode(' | ', $details) . ')' : '';
            $items[] = "{$i}. {$name}{$detailStr}";
            $i++;
        }

        return !empty($items) ? implode("\n", $items) : 'None';
    }

    /**
     * Summarize file additions and deletions
     */
    protected function summarizeAttachmentChanges(array $files, array $deletedAttachments): ?array
    {
        $addedNames = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $addedNames[] = $file->getClientOriginalName();
            }
        }

        $deletedNames = [];
        if (!empty($deletedAttachments)) {
            $deletedNames = Upload::whereIn('upload_id', $deletedAttachments)
                ->pluck('file_name')
                ->map(fn($f) => basename($f))
                ->toArray();
        }

        if (empty($addedNames) && empty($deletedNames)) {
            return null;
        }

        $oldDesc = !empty($deletedNames)
            ? 'Deleted: ' . implode(', ', $deletedNames)
            : '[No deletions]';

        $newDesc = !empty($addedNames)
            ? 'Added: ' . implode(', ', $addedNames)
            : '[No new uploads]';

        return [
            'field'     => 'Attachments / Files',
            'field_ml'  => 'അനുബന്ധ രേഖകൾ',
            'field_key' => 'attachments',
            'old'       => $oldDesc,
            'new'       => $newDesc,
        ];
    }

    /**
     * Resolve district ID to formatted name
     */
    protected function resolveDistrictName(?int $districtId): string
    {
        if (!$districtId) {
            return 'None';
        }
        $district = District::find($districtId);
        if (!$district) {
            return "District #{$districtId}";
        }
        return $district->district_code
            ? "{$district->district_name} ({$district->district_code})"
            : $district->district_name;
    }

    /**
     * Resolve unit ID to formatted name
     */
    protected function resolveUnitName(?int $unitId): string
    {
        if (!$unitId) {
            return 'None';
        }
        $unit = Unit::find($unitId);
        if (!$unit) {
            return "Unit #{$unitId}";
        }
        return $unit->unit_code
            ? "{$unit->unit_name} ({$unit->unit_code})"
            : $unit->unit_name;
    }

    /**
     * Normalize string for comparison
     */
    protected function normalizeString(?string $val): string
    {
        if ($val === null) {
            return '';
        }
        return trim((string) preg_replace('/\s+/', ' ', $val));
    }
}

