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
