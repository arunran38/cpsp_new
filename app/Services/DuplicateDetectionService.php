<?php

namespace App\Services;

use App\Models\Petition;
use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DuplicateDetectionService
{
    /**
     * Compute string similarity percentage (0 - 100) combining similar_text and normalized Levenshtein
     */
    public static function calculateSimilarity(string $str1, string $str2): float
    {
        $s1 = strtolower(trim($str1));
        $s2 = strtolower(trim($str2));

        if ($s1 === '' || $s2 === '') {
            return 0.0;
        }

        if ($s1 === $s2) {
            return 100.0;
        }

        similar_text($s1, $s2, $simPercent);

        $maxLen = max(strlen($s1), strlen($s2));
        if ($maxLen > 0 && function_exists('levenshtein') && $maxLen <= 255) {
            $lev = levenshtein($s1, $s2);
            $levPercent = max(0, (1 - ($lev / $maxLen)) * 100);
            return round(($simPercent * 0.6) + ($levPercent * 0.4), 1);
        }

        return round($simPercent, 1);
    }

    /**
     * Extract meaningful search tokens/words (>= 3 chars)
     */
    public static function extractTokens(string $name): array
    {
        // Split on spaces, commas, dots, hyphens
        $parts = preg_split('/[\s,\.\-_]+/', trim($name));
        $tokens = [];
        foreach ($parts as $p) {
            $cleaned = trim(preg_replace('/[^a-zA-Z0-9\x{0D00}-\x{0D7F}]/u', '', $p));
            if (mb_strlen($cleaned) >= 3) {
                $tokens[] = $cleaned;
            }
        }
        return array_unique($tokens);
    }

    /**
     * Check for potential duplicate petitions with exact, Soundex, and fuzzy algorithms.
     */
    public static function checkDuplicates(array $complainants, array $accused, ?string $description = null, ?string $nature = null, ?User $user = null): array
    {
        $user = $user ?? Auth::user();

        $c_phones = collect($complainants)->pluck('phone')->filter()->map(fn($p) => trim($p))->toArray();
        $c_names = collect($complainants)->pluck('name')->filter()->map(fn($n) => trim($n))->toArray();

        $a_phones = collect($accused)->pluck('phone')->filter()->map(fn($p) => trim($p))->toArray();
        $a_pens = collect($accused)->pluck('pen_number')->filter()->map(fn($p) => trim($p))->toArray();
        $a_names = collect($accused)->pluck('name')->filter()->map(fn($n) => trim($n))->toArray();

        // Require at least one identifier from either side
        if ((empty($c_names) && empty($c_phones)) || (empty($a_names) && empty($a_phones) && empty($a_pens))) {
            return [];
        }

        $query = Petition::query()
            ->excludeTSectionIfNotAuthorized($user)
            ->with([
                'decision',
                'addresses.district',
                'originalPetition.addresses.district',
            ]);

        $query->where(function ($q) use ($c_phones, $c_names, $a_phones, $a_pens, $a_names, $description, $nature) {
            // Subquery for Person Matches
            $q->orWhere(function ($subQ) use ($c_phones, $c_names, $a_phones, $a_pens, $a_names) {
                // Complainant Match criteria
                if (!empty($c_phones) || !empty($c_names)) {
                    $subQ->whereHas('addresses', function ($cq) use ($c_phones, $c_names) {
                        $cq->where('person_type', 'Complainant');
                        $cq->where(function ($sub) use ($c_phones, $c_names) {
                            if (!empty($c_phones)) {
                                $sub->orWhereIn('phone', $c_phones);
                            }

                            foreach ($c_names as $name) {
                                if (mb_strlen($name) >= 3) {
                                    // 1. Substring LIKE
                                    $sub->orWhere('person_name', 'LIKE', "%{$name}%");

                                    // 2. Exact Soundex
                                    $sub->orWhereRaw("SOUNDEX(person_name) = SOUNDEX(?)", [$name]);
                                    $sub->orWhereRaw("SOUNDEX(SUBSTRING_INDEX(person_name, ' ', 1)) = SOUNDEX(?)", [$name]);
                                    $sub->orWhereRaw("SOUNDEX(SUBSTRING_INDEX(person_name, ' ', -1)) = SOUNDEX(?)", [$name]);

                                    // 3. Token-based Soundex
                                    $tokens = self::extractTokens($name);
                                    foreach ($tokens as $token) {
                                        $sub->orWhereRaw("SOUNDEX(SUBSTRING_INDEX(person_name, ' ', 1)) = SOUNDEX(?)", [$token]);
                                        $sub->orWhereRaw("SOUNDEX(SUBSTRING_INDEX(person_name, ' ', -1)) = SOUNDEX(?)", [$token]);
                                    }
                                }
                            }
                        });
                    });
                } else {
                    $subQ->whereRaw('1 = 0');
                }

                // Accused Match criteria
                if (!empty($a_phones) || !empty($a_pens) || !empty($a_names)) {
                    $subQ->whereHas('addresses', function ($aq) use ($a_phones, $a_pens, $a_names) {
                        $aq->where('person_type', 'Accused');
                        $aq->where(function ($sub) use ($a_phones, $a_pens, $a_names) {
                            if (!empty($a_phones)) {
                                $sub->orWhereIn('phone', $a_phones);
                            }
                            if (!empty($a_pens)) {
                                $sub->orWhereIn('pen_number', $a_pens);
                            }

                            foreach ($a_names as $name) {
                                if (mb_strlen($name) >= 3) {
                                    // 1. Substring LIKE
                                    $sub->orWhere('person_name', 'LIKE', "%{$name}%");

                                    // 2. Exact Soundex
                                    $sub->orWhereRaw("SOUNDEX(person_name) = SOUNDEX(?)", [$name]);
                                    $sub->orWhereRaw("SOUNDEX(SUBSTRING_INDEX(person_name, ' ', 1)) = SOUNDEX(?)", [$name]);
                                    $sub->orWhereRaw("SOUNDEX(SUBSTRING_INDEX(person_name, ' ', -1)) = SOUNDEX(?)", [$name]);

                                    // 3. Token-based Soundex
                                    $tokens = self::extractTokens($name);
                                    foreach ($tokens as $token) {
                                        $sub->orWhereRaw("SOUNDEX(SUBSTRING_INDEX(person_name, ' ', 1)) = SOUNDEX(?)", [$token]);
                                        $sub->orWhereRaw("SOUNDEX(SUBSTRING_INDEX(person_name, ' ', -1)) = SOUNDEX(?)", [$token]);
                                    }
                                }
                            }
                        });
                    });
                } else {
                    $subQ->whereRaw('1 = 0');
                }
            });

            // Subquery for Description Fulltext Match (if provided)
            if (!empty($description) && strlen($description) > 10) {
                $q->orWhere(function ($subQ) use ($description, $nature) {
                    $subQ->whereRaw("MATCH(description) AGAINST(? IN NATURAL LANGUAGE MODE)", [$description]);
                    if (!empty($nature)) {
                        $subQ->where('nature_of_petition', $nature);
                    }
                });
            }
        });

        $candidates = $query->orderBy('date_of_petition_received', 'desc')->take(15)->get();

        $scored = $candidates->map(function (Petition $p) use ($c_phones, $c_names, $a_phones, $a_pens, $a_names) {
            $addresses = $p->displayAddresses();
            $dbComplainants = $addresses->where('person_type', 'Complainant');
            $dbAccused = $addresses->where('person_type', 'Accused');

            $primaryComplainant = $dbComplainants->first();
            $complainantText = $primaryComplainant ? $primaryComplainant->person_name . ($primaryComplainant->district ? ', ' . $primaryComplainant->district->district_name : '') : 'N/A';
            if ($dbComplainants->count() > 1) {
                $complainantText .= ' (+' . ($dbComplainants->count() - 1) . ' others)';
            }

            $primaryAccused = $dbAccused->first();
            $accusedText = $primaryAccused ? $primaryAccused->person_name . ($primaryAccused->district ? ', ' . $primaryAccused->district->district_name : '') : 'N/A';
            if ($dbAccused->count() > 1) {
                $accusedText .= ' (+' . ($dbAccused->count() - 1) . ' others)';
            }

            // Determine match reason and confidence
            $matchType = 'exact';
            $matchConfidence = 60;
            $matchReasonEn = 'Potential Match';
            $badgeColor = 'bg-amber-100 text-amber-800 border-amber-300';

            $exactPhoneMatch = false;
            $exactPenMatch = false;
            $exactNameMatch = false;
            $soundexMatch = false;
            $maxNameSim = 0.0;

            // Check phone & PEN
            $dbCPhones = $dbComplainants->pluck('phone')->filter()->toArray();
            $dbAPhones = $dbAccused->pluck('phone')->filter()->toArray();
            $dbAPens = $dbAccused->pluck('pen_number')->filter()->toArray();

            if (!empty(array_intersect($c_phones, $dbCPhones)) || !empty(array_intersect($a_phones, $dbAPPhones ?? []))) {
                $exactPhoneMatch = true;
            }
            if (!empty(array_intersect($a_pens, $dbAPens))) {
                $exactPenMatch = true;
            }

            // Name checks
            $dbCNames = $dbComplainants->pluck('person_name')->filter()->toArray();
            $dbANames = $dbAccused->pluck('person_name')->filter()->toArray();

            foreach ($c_names as $cn) {
                foreach ($dbCNames as $dbcn) {
                    $sim = self::calculateSimilarity($cn, $dbcn);
                    if ($sim > $maxNameSim) $maxNameSim = $sim;
                    if (strcasecmp($cn, $dbcn) === 0 || stripos($dbcn, $cn) !== false || stripos($cn, $dbcn) !== false) {
                        $exactNameMatch = true;
                    } elseif (soundex($cn) === soundex($dbcn)) {
                        $soundexMatch = true;
                    }
                }
            }

            foreach ($a_names as $an) {
                foreach ($dbANames as $dban) {
                    $sim = self::calculateSimilarity($an, $dban);
                    if ($sim > $maxNameSim) $maxNameSim = $sim;
                    if (strcasecmp($an, $dban) === 0 || stripos($dban, $an) !== false || stripos($an, $dban) !== false) {
                        $exactNameMatch = true;
                    } elseif (soundex($an) === soundex($dban)) {
                        $soundexMatch = true;
                    }
                }
            }

            if ($exactPhoneMatch || $exactPenMatch || ($exactNameMatch && $maxNameSim >= 90)) {
                $matchType = 'exact';
                $matchConfidence = 100;
                $matchReasonEn = 'Exact Match';
                $badgeColor = 'bg-rose-100 text-rose-800 border-rose-300';
            } elseif ($soundexMatch) {
                $matchType = 'soundex';
                $matchConfidence = max(85, (int)$maxNameSim);
                $matchReasonEn = 'Phonetic Soundex Match';
                $badgeColor = 'bg-purple-100 text-purple-800 border-purple-300';
            } elseif ($maxNameSim >= 70) {
                $matchType = 'fuzzy';
                $matchConfidence = (int)$maxNameSim;
                $matchReasonEn = "Fuzzy Match ({$matchConfidence}%)";
                $badgeColor = 'bg-amber-100 text-amber-800 border-amber-300';
            }

            return [
                'petition_id' => $p->petition_id,
                'receipt_no' => $p->receipt_no,
                'file_no' => $p->file_no,
                'date' => date('d-m-Y', strtotime($p->date_of_petition_received)),
                'complainant' => $complainantText,
                'accused' => $accusedText,
                'description' => \Illuminate\Support\Str::limit(strip_tags($p->description), 100),
                'status' => $p->status,
                'decision' => $p->decision ? $p->decision->final_decision : null,
                'match_type' => $matchType,
                'match_confidence' => $matchConfidence,
                'match_reason_en' => $matchReasonEn,
                'badge_color' => $badgeColor,
            ];
        });

        // Sort by confidence descending, take top 10
        return $scored->sortByDesc('match_confidence')->values()->take(10)->toArray();
    }

    /**
     * Search petitions for autocomplete duplicate linking with Soundex support.
     */
    public static function searchDuplicates(string $query, $excludeId = null, ?User $user = null): array
    {
        $user = $user ?? Auth::user();

        if (!$query) {
            return [];
        }

        $petitions = Petition::excludeTSectionIfNotAuthorized($user)
            ->with(['addresses', 'originalPetition.addresses'])
            ->where(function ($q) use ($query) {
                $q->where('receipt_no', 'LIKE', "%{$query}%")
                  ->orWhere('nature_of_petition', 'LIKE', "%{$query}%")
                  ->orWhereHas('addresses', function ($q2) use ($query) {
                      $q2->where('person_name', 'LIKE', "%{$query}%")
                         ->orWhereRaw("SOUNDEX(person_name) = SOUNDEX(?)", [$query])
                         ->orWhereRaw("SOUNDEX(SUBSTRING_INDEX(person_name, ' ', 1)) = SOUNDEX(?)", [$query]);
                  });
            });

        if ($excludeId) {
            $realExclude = HashidService::decode($excludeId) ?? $excludeId;
            $petitions->where('petition_id', '!=', $realExclude);
        }

        return $petitions->take(12)->get()->map(function (Petition $petition) use ($query) {
            $addresses = $petition->displayAddresses();
            $complainant = $addresses->firstWhere('person_type', 'Complainant');
            $accused = $addresses->firstWhere('person_type', 'Accused');

            $complainantName = $complainant ? $complainant->person_name : 'N/A';
            $accusedName = $accused ? $accused->person_name : 'N/A';

            $isSoundex = false;
            if ($complainant && soundex($complainantName) === soundex($query)) {
                $isSoundex = true;
            } elseif ($accused && soundex($accusedName) === soundex($query)) {
                $isSoundex = true;
            }

            $tag = $isSoundex ? ' [Soundex Match]' : '';

            return [
                'id' => $petition->receipt_no,
                'text' => "{$petition->receipt_no} - {$petition->nature_of_petition} ({$complainantName}){$tag}",
                'receipt_no' => $petition->receipt_no,
                'date' => $petition->date_of_petition_received ? date('d-m-Y', strtotime($petition->date_of_petition_received)) : 'N/A',
                'complainant' => $complainantName,
                'accused' => $accusedName,
                'nature' => $petition->nature_of_petition,
                'is_soundex' => $isSoundex,
            ];
        })->toArray();
    }
}

