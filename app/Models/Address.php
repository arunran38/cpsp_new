<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes, \Illuminate\Database\Eloquent\Factories\HasFactory;
    protected $primaryKey = 'address_id';
    protected $guarded = [];

    public function petition()
    {
        return $this->belongsTo(Petition::class, 'petition_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function designation()
    {
        return $this->belongsTo(DesignationList::class, 'designation_id');
    }

    public function department()
    {
        return $this->belongsTo(DepartmentList::class, 'department_id');
    }

    /**
     * Convert person name to Title Case consistently across the application
     * while properly handling initials, hyphens, apostrophes, and special characters.
     */
    public static function formatPersonName(?string $name, ?string $personType = null): ?string
    {
        if ($name === null) {
            return null;
        }

        $trimmed = trim($name);
        if ($trimmed === '') {
            return '';
        }

        // Handle known special placeholders
        $lower = strtolower($trimmed);
        if ($lower === 'unknown' || $lower === 'anonymous / unknown' || $lower === 'anonymous/unknown') {
            return $personType === 'Accused' ? 'Unknown Suspect' : 'Anonymous / Unknown';
        }
        if ($lower === 'unknown suspect') {
            return 'Unknown Suspect';
        }
        if (in_array($lower, ['not provided', 'not_provided', 'n/a', 'none', 'null', 'nil', '-'], true)) {
            return $trimmed;
        }

        // Convert to Title Case using UTF-8 multibyte conversion
        $title = mb_convert_case($trimmed, MB_CASE_TITLE, 'UTF-8');

        // Handle letters following dots (e.g. A.k. -> A.K., A.k.p. -> A.K.P., Dr.john -> Dr.John)
        $title = preg_replace_callback('/(?<=\.)[a-z]/u', function ($m) {
            return mb_strtoupper($m[0], 'UTF-8');
        }, $title);

        // Handle letters following apostrophes (e.g. O'brien -> O'Brien, D'souza -> D'Souza)
        $title = preg_replace_callback("/(?<=\b[OD]')([a-z])/u", function ($m) {
            return mb_strtoupper($m[1], 'UTF-8');
        }, $title);

        // Handle letters following hyphens (e.g. Mary-jane -> Mary-Jane)
        $title = preg_replace_callback('/(?<=-)[a-z]/u', function ($m) {
            return mb_strtoupper($m[0], 'UTF-8');
        }, $title);

        // Handle letters following opening parentheses e.g. (director) -> (Director)
        $title = preg_replace_callback('/(?<=\()[a-z]/u', function ($m) {
            return mb_strtoupper($m[0], 'UTF-8');
        }, $title);

        // Handle letters following slash e.g. Firm/project -> Firm/Project, M/s -> M/S
        $title = preg_replace_callback('/(?<=\/)[a-z]/u', function ($m) {
            return mb_strtoupper($m[0], 'UTF-8');
        }, $title);

        return $title;
    }

    /**
     * Dynamically format person_name in Title Case for display while preserving raw DB value.
     */
    public function getPersonNameAttribute($value): ?string
    {
        return self::formatPersonName($value, $this->attributes['person_type'] ?? null);
    }

    /**
     * Dynamically format contact_person in Title Case for display while preserving raw DB value.
     */
    public function getContactPersonAttribute($value): ?string
    {
        return self::formatPersonName($value);
    }

    /**
     * Get the clean, unified formatted address with proper commas and spacing.
     * e.g., "Software Division, Malappuram - 695033"
     */
    public function getFormattedAddressAttribute(): string
    {
        $cleanText = function (?string $val): ?string {
            if ($val === null) return null;
            $str = trim((string) $val);
            if ($str === '') return null;

            $placeholders = ['not provided', 'not_provided', 'n/a', 'none', 'null', 'nil', '-'];
            if (in_array(strtolower($str), $placeholders, true)) {
                return null;
            }

            // Remove multiple consecutive commas (e.g. ", ," or ",,")
            $str = preg_replace('/,\s*,+/', ',', $str);
            // Ensure single space after commas
            $str = preg_replace('/\s*,\s*/', ', ', $str);
            // Trim leading/trailing commas and whitespace
            $str = trim($str, ", \t\n\r");

            return $str === '' ? null : $str;
        };

        $addr = $cleanText($this->full_address);
        $districtName = $this->district?->district_name ?? (is_string($this->district) ? $this->district : null);
        $dist = $cleanText($districtName);
        $pin = $cleanText($this->pincode);

        $locationParts = array_filter([$addr, $dist]);
        $location = implode(', ', $locationParts);

        if ($location !== '' && $pin !== null) {
            return $location . ' - ' . $pin;
        }

        if ($location !== '') {
            return $location;
        }

        if ($pin !== null) {
            return $pin;
        }

        return 'Not provided';
    }
}
