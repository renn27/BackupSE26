<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessStatus;
use Illuminate\Http\Request;

class UserBusinessController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $officers = collect();
        
        if ($user->isSuperAdmin()) {
            $assignedVillages = \App\Models\Village::orderBy('nmkec')
                ->orderBy('nmdesa')
                ->get();
            
            $officers = \App\Models\User::where('role', 'petugas')
                ->orderBy('name')
                ->get(['id', 'name', 'email']);

            $officerId = $request->user_id;
            $selectedStatus = $request->status;

            $businesses = Business::with(['village', 'status.updatedBy'])
                ->when($officerId, function ($query, $officerId) {
                    $officerVillageIds = \App\Models\UserVillageAssignment::where('user_id', $officerId)->pluck('village_id');
                    $query->whereIn('village_id', $officerVillageIds);
                })
                ->when($request->village_id, function ($query, $villageId) {
                    $query->where('village_id', $villageId);
                })
                ->when($selectedStatus, function ($query, $selectedStatus) {
                    if ($selectedStatus === 'belum_dicatat') {
                        $query->whereDoesntHave('status');
                    } elseif ($selectedStatus === 'pindah') {
                        $query->whereHas('status', function ($q) {
                            $q->where('status', 'tidak_ditemukan');
                        });
                    } else {
                        $query->whereHas('status', function ($q) use ($selectedStatus) {
                            $q->where('status', $selectedStatus);
                        });
                    }
                })
                ->when($request->search, function ($query, $search) {
                    $query->where(function ($inner) use ($search) {
                        $inner->where('nama_usaha', 'like', "%{$search}%")
                            ->orWhere('idsbr', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate(20)
                ->withQueryString();
        } else {
            $assignedVillages = $user->villages()
                ->orderBy('nmkec')
                ->orderBy('nmdesa')
                ->get();

            $villageIds = $assignedVillages->pluck('id');
            $selectedStatus = $request->status;

            $businesses = Business::with(['village', 'status.updatedBy'])
                ->whereIn('village_id', $villageIds)
                ->when($request->village_id, function ($query, $villageId) use ($villageIds) {
                    if ($villageIds->contains($villageId)) {
                        $query->where('village_id', $villageId);
                    }
                })
                ->when($selectedStatus, function ($query, $selectedStatus) {
                    if ($selectedStatus === 'belum_dicatat') {
                        $query->whereDoesntHave('status');
                    } elseif ($selectedStatus === 'pindah') {
                        $query->whereHas('status', function ($q) {
                            $q->where('status', 'tidak_ditemukan');
                        });
                    } else {
                        $query->whereHas('status', function ($q) use ($selectedStatus) {
                            $q->where('status', $selectedStatus);
                        });
                    }
                })
                ->when($request->search, function ($query, $search) {
                    $query->where(function ($inner) use ($search) {
                        $inner->where('nama_usaha', 'like', "%{$search}%")
                            ->orWhere('idsbr', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate(20)
                ->withQueryString();
        }

        $payload = [
            'businesses' => $businesses,
            'assignedVillages' => $assignedVillages,
            'officers' => $officers,
            'selectedVillageId' => $request->village_id,
            'selectedUserId' => $user->isSuperAdmin() ? $request->user_id : null,
            'selectedStatus' => $request->status,
            'search' => $request->search,
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('businesses.partials.table', $payload)->render(),
                'total' => $businesses->total(),
            ]);
        }

        return view('businesses.index', $payload);
    }

    public function updateStatus(Request $request, Business $business)
    {
        $user = $request->user();

        if (!$user->isSuperAdmin()) {
            $assignedVillageIds = $user->villages()->pluck('villages.id');
            abort_unless($assignedVillageIds->contains($business->village_id), 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:tidak_ditemukan,ditemukan,baru,tutup,ganda'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $existingStatus = $business->status;

        if ($existingStatus && !$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Status usaha ini sudah diperbarui dan terkunci. Hanya superadmin yang dapat mengubah status ini.',
            ], 423);
        }

        $status = BusinessStatus::updateOrCreate(
            ['business_id' => $business->id],
            [
                'status' => $validated['status'],
                'updated_by_user_id' => $user->id,
                'updated_by_name' => $user->name,
                'catatan' => $validated['catatan'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'status' => $status->status,
            'status_label' => $this->statusLabel($status->status),
            'updated_by' => $status->updated_by_name,
            'updated_at' => $status->updated_at->format('d M Y H:i'),
            'catatan' => $status->catatan,
        ]);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'tidak_ditemukan' => 'Tidak Ditemukan',
            'ditemukan' => 'Ditemukan',
            'baru' => 'Baru',
            'tutup' => 'Tutup',
            'ganda' => 'Ganda',
        };
    }
}
