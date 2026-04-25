<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\SeatUser;
use App\Models\Unit;
use App\Http\Requests\StoreSeatRequest;
use App\Http\Requests\UpdateSeatRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SeatController extends Controller
{
    /**
     * Display a listing of seats.
     */
    public function index(): View
    {
        $seats = Seat::with(['units', 'activeAssignment.user.profilePhoto'])->paginate(10);
        return view('admin.seat_view', compact('seats'));
    }

    /**
     * Show the form for creating a new seat.
     */
    public function create(): View
    {
        $units = Unit::all();
        return view('admin.seat_add', compact('units'));
    }

    /**
     * Store a newly created seat in storage.
     */
    public function store(StoreSeatRequest $request): RedirectResponse
    {
        return DB::transaction(function () use ($request) {
            try {
                $seat = Seat::create($request->only('seat_name', 'is_active'));
                $seat->units()->sync($request->unit_ids);
                return redirect()->route('admin.seats.index')->with('success', 'Seat created successfully.');
            } catch (\Exception $e) {
                return back()->with('error', 'Failed to create seat: ' . $e->getMessage())->withInput();
            }
        });
    }

    /**
     * Show the form for editing the specified seat.
     */
    public function edit(int $id): View
    {
        $seat = Seat::with('units')->findOrFail($id);
        $units = Unit::all();
        return view('admin.seat_edit', compact('seat', 'units'));
    }

    /**
     * Update the specified seat in storage.
     */
    public function update(UpdateSeatRequest $request, int $id): RedirectResponse
    {
        $seat = Seat::findOrFail($id);

        return DB::transaction(function () use ($request, $seat) {
            try {
                $seat->update($request->only('seat_name', 'is_active'));
                $seat->units()->sync($request->unit_ids);
                return redirect()->route('admin.seats.index')->with('success', 'Seat updated successfully.');
            } catch (\Exception $e) {
                return back()->with('error', 'Failed to update seat: ' . $e->getMessage())->withInput();
            }
        });
    }

    /**
     * Display seat statistics.
     */
    public function statistics(Request $request): View
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $from = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : null;
        $to = $dateTo ? Carbon::parse($dateTo)->endOfDay() : null;

        $seats = Seat::with('activeAssignment.user')
                     ->withStatistics($from, $to)
                     ->get();

        return view('admin.seat_statistics', compact('seats', 'dateFrom', 'dateTo'));
    }

    /**
     * Export seat statistics to Excel.
     */
    public function exportStatistics(Request $request): HttpResponse
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $from = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : null;
        $to = $dateTo ? Carbon::parse($dateTo)->endOfDay() : null;

        $seats = Seat::with('activeAssignment.user')
                     ->withStatistics($from, $to)
                     ->get();

        // Filename generation
        $filenameDateText = $dateFrom && $dateTo ? "{$dateFrom}_to_{$dateTo}" : ($dateFrom ? "from_{$dateFrom}" : ($dateTo ? "up_to_{$dateTo}" : "all_time"));
        $filename = "seat_wise_details_" . $filenameDateText . ".xls";
        
        // Heading text
        $dFrom = $dateFrom ? date('d/m/Y', strtotime($dateFrom)) : '';
        $dTo = $dateTo ? date('d/m/Y', strtotime($dateTo)) : '';
        $mainHeading = match (true) {
            (bool)$dateFrom && (bool)$dateTo => "SEAT WISE DETAILS OF PETITIONS from $dFrom to $dTo",
            (bool)$dateFrom => "SEAT WISE DETAILS OF PETITIONS from $dFrom onwards",
            (bool)$dateTo => "SEAT WISE DETAILS OF PETITIONS up to $dTo",
            default => "SEAT WISE DETAILS OF PETITIONS (All Time)",
        };

        $content = view('exports.seat_statistics_export', compact('seats', 'mainHeading'))->render();

        return response($content)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    /**
     * Remove the specified seat from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $seat = Seat::findOrFail($id);

            // Check if seat has active assignment
            if (SeatUser::where('seat_id', $id)->where('is_active', true)->exists()) {
                return back()->with('error', 'Cannot delete seat: An officer is currently assigned.');
            }

            $seat->delete();
            return redirect()->route('admin.seats.index')->with('success', 'Seat deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete seat: ' . $e->getMessage());
        }
    }

    /**
     * Display seat history.
     */
    public function history(int $id): View
    {
        $seat = Seat::findOrFail($id);
        $assignments = SeatUser::with(['user' => fn($q) => $q->withTrashed()->with('profilePhoto')])
            ->where('seat_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.seat_history', compact('seat', 'assignments'));
    }

    /**
     * Revoke active assignment from a seat.
     */
    public function revokeAssignment(int $id): RedirectResponse
    {
        try {
            SeatUser::where('seat_id', $id)
                ->where('is_active', true)
                ->update(['is_active' => false, 'revoked_at' => now()]);
            return redirect()->route('admin.seats.index')->with('success', 'Assignment revoked successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to revoke assignment: ' . $e->getMessage());
        }
    }

    /**
     * Switch current session seat view.
     */
    public function switchSeat(Request $request, int $seatId): RedirectResponse
    {
        $user = auth()->user();
        $assignment = $user->seatUsers()->where('seat_id', $seatId)->where('is_active', true)->first();

        if (!$assignment) {
            return redirect()->back()->with('error', 'Unauthorized or inactive seat selection.');
        }

        session(['current_seat_id' => $seatId]);
        if ($user->role === 'admin') {
            session(['is_impersonating_seat' => true]);
        } else {
            session()->forget('is_impersonating_seat');
        }

        return redirect()->route('user.dashboard')->with('success', "Switched to {$assignment->seat->seat_name} view.");
    }

    /**
     * Switch back to admin view.
     */
    public function switchBack(): RedirectResponse
    {
        $user = auth()->user();

        if ($user->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Only admins can switch back.');
        }

        session()->forget(['current_seat_id', 'is_impersonating_seat']);

        return redirect()->route('admin.dashboard')->with('success', 'Switched back to Admin view.');
    }

    /**
     * Check if seat name exists.
     */
    public function checkSeatName(Request $request)
    {
        $request->validate(['seat_name' => 'required|string|max:255']);
        return response()->json(['exists' => Seat::where('seat_name', $request->seat_name)->exists()]);
    }
}
