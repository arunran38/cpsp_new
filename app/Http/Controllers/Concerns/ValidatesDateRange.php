<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait ValidatesDateRange
{
    /**
     * Get the current application date string (Y-m-d) in the configured timezone.
     */
    protected function currentAppDateString(): string
    {
        return now()->timezone(config('app.timezone', 'Asia/Kolkata'))->format('Y-m-d');
    }

    /**
     * Validate one or more date-range pairs on the request.
     *
     * @param  array<int, array{0: string, 1: string, 2?: string, 3?: string}>  $pairs
     * @param  array<string, mixed>  $extraRules
     * @param  array<string, string>  $extraMessages
     * @return array<string, mixed>
     */
    protected function validateDateRangePairs(
        Request $request,
        array $pairs = [['date_from', 'date_to']],
        array $extraRules = [],
        array $extraMessages = []
    ): array {
        $today = $this->currentAppDateString();
        $rules = $extraRules;
        $messages = $extraMessages;

        foreach ($pairs as $pair) {
            $fromField = $pair[0];
            $toField = $pair[1];
            $fromLabel = $pair[2] ?? $this->formatDateFieldLabel($fromField);
            $toLabel = $pair[3] ?? $this->formatDateFieldLabel($toField);

            $rules[$fromField] = ['nullable', 'date', 'before_or_equal:' . $today];

            $toRules = ['nullable', 'date', 'before_or_equal:' . $today];
            $fromValue = $request->input($fromField);
            if (is_string($fromValue) && trim($fromValue) !== '' && strtotime($fromValue) !== false) {
                $toRules[] = 'after_or_equal:' . $fromField;
            }
            $rules[$toField] = $toRules;

            $messages["{$fromField}.date"] = "Please enter a valid {$fromLabel}.";
            $messages["{$fromField}.before_or_equal"] = "{$fromLabel} cannot be a future date.";
            $messages["{$toField}.date"] = "Please enter a valid {$toLabel}.";
            $messages["{$toField}.before_or_equal"] = "{$toLabel} cannot be a future date.";
            $messages["{$toField}.after_or_equal"] = "{$toLabel} must be equal to or later than {$fromLabel}.";
        }

        return $request->validate($rules, $messages);
    }

    /**
     * Validate a single date-range pair on the request.
     *
     * @param  array<string, mixed>  $extraRules
     * @param  array<string, string>  $extraMessages
     * @return array<string, mixed>
     */
    protected function validateDateRange(
        Request $request,
        string $fromField = 'date_from',
        string $toField = 'date_to',
        array $extraRules = [],
        array $extraMessages = []
    ): array {
        return $this->validateDateRangePairs($request, [[$fromField, $toField]], $extraRules, $extraMessages);
    }

    /**
     * Convert a date field key into a user-friendly label.
     */
    private function formatDateFieldLabel(string $field): string
    {
        return match ($field) {
            'date_from', 'from_date' => 'From Date',
            'date_to', 'to_date' => 'To Date',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'district_from_date' => 'District From Date',
            'district_to_date' => 'District To Date',
            default => ucwords(str_replace('_', ' ', $field)),
        };
    }
}
