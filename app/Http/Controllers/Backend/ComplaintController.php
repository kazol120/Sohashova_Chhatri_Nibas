<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Backend\Complaint;
use App\Models\Backend\RoomBookingHistory;
use Auth;
use Carbon\Carbon;

class ComplaintController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $data['page_title'] = "Complaints Management (অভিযোগ তালিকা)";

        if ($user->hasRole('admin') || $user->hasRole('staffs')) {
            $query = Complaint::with(['user', 'booking'])->latest();
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('complaint_text', 'like', "%{$search}%")
                      ->orWhereHas('user', function($uq) use ($search) {
                          $uq->where('name', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%");
                      });
                });
            }

            $data['complaints'] = $query->paginate(15);
            return view('backend.complaints.index', $data);
        } else {
            // Resident / Student user
            $data['complaints'] = Complaint::with(['booking'])
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(10);

            $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
            $last11Digits = !empty($cleanPhone) ? substr($cleanPhone, -11) : '';
            $data['userBooking'] = RoomBookingHistory::where('status', 0)
                ->where(function ($q) use ($user, $last11Digits) {
                    if (!empty($user->email)) $q->orWhere('email', $user->email);
                    if (!empty($last11Digits)) $q->orWhere('phone', 'like', "%{$last11Digits}%");
                })->latest()->first();

            return view('backend.complaints.user_index', $data);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'complaint_text' => 'required|string|min:5|max:1000',
        ]);

        $user = Auth::user();
        $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
        $last11Digits = !empty($cleanPhone) ? substr($cleanPhone, -11) : '';

        $userBooking = RoomBookingHistory::where('status', 0)
            ->where(function ($q) use ($user, $last11Digits) {
                if (!empty($user->email)) $q->orWhere('email', $user->email);
                if (!empty($last11Digits)) $q->orWhere('phone', 'like', "%{$last11Digits}%");
            })->latest()->first();

        Complaint::create([
            'user_id' => $user->id,
            'room_booking_history_id' => $userBooking ? $userBooking->id : null,
            'complaint_text' => $request->complaint_text,
            'status' => 0, // Pending
        ]);

        return redirect()->back()->with('success', 'আপনার অভিযোগটি সফলভাবে জমা হয়েছে। এডমিন প্যানেল থেকে অতি শীঘ্রই সমাধান করা হবে।');
    }

    public function updateStatus(Request $request, $id)
    {
        $complaint = Complaint::findOrFail($id);
        $complaint->status = $request->status ?? 1; // 1 = Accepted / Resolved
        if ($request->filled('admin_note')) {
            $complaint->admin_note = $request->admin_note;
        }
        $complaint->save();

        return redirect()->back()->with('success', 'অভিযোগটি সফলভাবে Accepted / Resolved চিহ্নিত করা হয়েছে।');
    }

    public function checkPendingComplaints()
    {
        $pendingCount = Complaint::where('status', 0)->count();
        $latestComplaints = Complaint::with(['user'])
            ->where('status', 0)
            ->latest()
            ->take(5)
            ->get()
            ->map(function($c) {
                return [
                    'id' => $c->id,
                    'user_name' => $c->user->name ?? 'Resident',
                    'complaint_text' => $c->complaint_text,
                    'time_ago' => $c->created_at->diffForHumans(),
                    'initial' => strtoupper(substr($c->user->name ?? 'R', 0, 1)),
                ];
            });

        return response()->json([
            'count' => $pendingCount,
            'complaints' => $latestComplaints,
        ]);
    }
}
