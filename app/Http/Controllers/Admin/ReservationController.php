<?php

namespace App\Http\Controllers\Admin;

use App\Events\TableStatusChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    public function index()
    {
        // Explicit branch filter (default: all). The old session-scope hid
        // storefront bookings made for other branches.
        $branchFilter = request()->get('branch_id');

        $reservations = Reservation::self()
            ->when($branchFilter === 'unassigned', fn ($q) => $q->whereNull('branch_id'))
            ->when($branchFilter && $branchFilter !== 'unassigned', fn ($q) => $q->where('branch_id', (int) $branchFilter))
            ->with(['table', 'branch'])
            ->orderByDesc('reservation_time')
            ->paginate(20)
            ->withQueryString();

        $tables = DiningTable::self()->forActiveBranch()->orderBy('name')->get();
        $statuses = Reservation::statuses();
        $branches = seller_branches();

        return view('admin.reservations.index', compact('reservations', 'tables', 'statuses', 'branches', 'branchFilter'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'table_id' => [
                'required',
                Rule::exists('dining_tables', 'id')->where(fn ($q) => $q->where('seller_id', panel_owner_id())),
            ],
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'guest_count' => 'nullable|integer|min:1|max:100',
            'reservation_time' => 'required|date|after_or_equal:now',
            'notes' => 'nullable|string|max:1000',
            'status' => 'nullable|in:'.implode(',', Reservation::statuses()),
            'branch_id' => [
                'nullable',
                Rule::exists('branches', 'id')->where(fn ($q) => $q->where('seller_id', panel_owner_id())),
            ],
        ]);

        $status = $data['status'] ?? Reservation::CONFIRMED;

        if ($status !== Reservation::CANCELLED
            && $conflict = Reservation::conflictingBooking((int) $data['table_id'], $data['reservation_time'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', Reservation::conflictMessage($conflict));
        }

        DB::transaction(function () use ($data, $status) {
            $table = DiningTable::self()->whereKey($data['table_id'])->firstOrFail();

            $reservation = Reservation::create([
                'seller_id' => panel_owner_id(),
                'branch_id' => $data['branch_id'] ?? $table->branch_id ?? active_branch_id(),
                'table_id' => $data['table_id'],
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'guest_count' => $data['guest_count'] ?? 1,
                'reservation_time' => $data['reservation_time'],
                'status' => $status,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncTableStatus($reservation);
        });

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', 'Reservation created.');
    }

    public function update(Request $request, Reservation $reservation)
    {
        abort_unless((int) $reservation->seller_id === (int) panel_owner_id(), 403);

        $data = $request->validate([
            'table_id' => [
                'required',
                Rule::exists('dining_tables', 'id')->where(fn ($q) => $q->where('seller_id', panel_owner_id())),
            ],
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'guest_count' => 'nullable|integer|min:1|max:100',
            'reservation_time' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:'.implode(',', Reservation::statuses()),
        ]);

        if ($data['status'] !== Reservation::CANCELLED
            && $conflict = Reservation::conflictingBooking((int) $data['table_id'], $data['reservation_time'], (int) $reservation->id)) {
            return redirect()->back()
                ->withInput()
                ->with('error', Reservation::conflictMessage($conflict));
        }

        DB::transaction(function () use ($reservation, $data) {
            $previousTableId = $reservation->table_id;

            $reservation->update([
                'table_id' => $data['table_id'],
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'guest_count' => $data['guest_count'] ?? 1,
                'reservation_time' => $data['reservation_time'],
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);

            if ((int) $previousTableId !== (int) $data['table_id']) {
                $this->releaseTableIfIdle((int) $previousTableId);
            }

            $this->syncTableStatus($reservation->fresh());
        });

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', 'Reservation updated.');
    }

    public function destroy(Reservation $reservation)
    {
        abort_unless((int) $reservation->seller_id === (int) panel_owner_id(), 403);

        DB::transaction(function () use ($reservation) {
            $tableId = (int) $reservation->table_id;
            $reservation->delete();
            $this->releaseTableIfIdle($tableId);
        });

        return redirect()
            ->route('admin.reservations.index')
            ->with('success', 'Reservation cancelled.');
    }

    private function syncTableStatus(Reservation $reservation): void
    {
        $table = DiningTable::self()
            ->whereKey($reservation->table_id)
            ->lockForUpdate()
            ->first();

        if (! $table) {
            return;
        }

        // Never override an occupied table from a reservation update.
        if ($table->status === DiningTable::OCCUPIED) {
            return;
        }

        $nextStatus = match ($reservation->status) {
            Reservation::CONFIRMED, Reservation::PENDING => DiningTable::RESERVED,
            Reservation::SEATED => DiningTable::OCCUPIED,
            Reservation::CANCELLED => DiningTable::FREE,
            default => $table->status,
        };

        if ($table->status !== $nextStatus) {
            $table->update(['status' => $nextStatus]);
            event(new TableStatusChangedEvent($table->fresh()));
        }
    }

    private function releaseTableIfIdle(int $tableId): void
    {
        $table = DiningTable::self()->whereKey($tableId)->lockForUpdate()->first();
        if (! $table || $table->status === DiningTable::OCCUPIED) {
            return;
        }

        $hasActive = Reservation::self()
            ->where('table_id', $tableId)
            ->whereIn('status', [Reservation::PENDING, Reservation::CONFIRMED, Reservation::SEATED])
            ->exists();

        if (! $hasActive && $table->status === DiningTable::RESERVED) {
            $table->update(['status' => DiningTable::FREE]);
            event(new TableStatusChangedEvent($table->fresh()));
        }
    }
}



