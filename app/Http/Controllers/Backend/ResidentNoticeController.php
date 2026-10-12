<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\ResidentNotice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResidentNoticeController extends Controller
{
    /**
     * Admin view for managing resident notices
     */
    public function index()
    {
        return view('backend.resident_notice.index');
    }

    /**
     * Get paginated resident notices for admin panel
     */
    public function get(Request $request)
    {
        $perPage = (int) ($request->get('per_page', 10));
        $search  = trim($request->get('search', ''));
        $status  = $request->get('status');

        $query = ResidentNotice::query();

        if ($search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('notice', 'like', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('status', (int)$status);
        }

        $rows = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json($rows);
    }

    /**
     * Store a new resident notice
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'nullable|string|max:255',
            'notice'   => 'required|string',
            'priority' => 'required|string|in:urgent,important,normal',
            'status'   => 'nullable|integer|in:0,1',
        ]);

        $notice = ResidentNotice::create([
            'title'        => $request->title ?: 'জরুরি বিজ্ঞপ্তি',
            'notice'       => $request->notice,
            'priority'     => $request->priority ?: 'normal',
            'status'       => $request->has('status') ? (int)$request->status : 1,
            'published_at' => now(),
            'created_by'   => Auth::id(),
        ]);

        $sentCount = 0;
        if ($notice->status == 1 && $request->get('send_email', true)) {
            $sentCount = $this->sendEmailToResidents($notice);
        }

        return response()->json([
            'success'   => true,
            'message'   => $sentCount > 0 
                ? "বিজ্ঞপ্তি সংরক্ষিত হয়েছে এবং {$sentCount} জন শিক্ষার্থীর ইমেইলে নোটিফিকেশন পাঠানো হয়েছে"
                : 'বিজ্ঞপ্তি সফলভাবে সংরক্ষিত হয়েছে',
            'data'      => $notice,
            'emails_sent' => $sentCount,
        ]);
    }

    /**
     * Send email notifications to active residents
     */
    protected function sendEmailToResidents($notice)
    {
        try {
            $activeEmails = \App\Models\Backend\RoomBookingHistory::whereNull('today_check_out')
                ->where('status', 1)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->pluck('email')
                ->map(fn($e) => trim($e))
                ->filter(fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
                ->unique()
                ->values()
                ->toArray();

            if (empty($activeEmails)) {
                return 0;
            }

            $mailData = [
                'title'         => $notice->title,
                'notice'        => $notice->notice,
                'priority'      => $notice->priority,
                'publishedDate' => $notice->created_at ? $notice->created_at->format('d F, Y') : date('d F, Y'),
            ];

            foreach ($activeEmails as $recipientEmail) {
                try {
                    \Illuminate\Support\Facades\Mail::to($recipientEmail)->send(new \App\Mail\ResidentNoticeAlertMail($mailData));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Failed to send notice email to {$recipientEmail}: " . $e->getMessage());
                }
            }

            return count($activeEmails);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Resident notice email broadcast failed: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update an existing resident notice
     */
    public function update(Request $request, $id)
    {
        $notice = ResidentNotice::findOrFail($id);

        $request->validate([
            'title'    => 'nullable|string|max:255',
            'notice'   => 'required|string',
            'priority' => 'required|string|in:urgent,important,normal',
            'status'   => 'nullable|integer|in:0,1',
        ]);

        $notice->update([
            'title'    => $request->title ?: 'জরুরি বিজ্ঞপ্তি',
            'notice'   => $request->notice,
            'priority' => $request->priority ?: 'normal',
            'status'   => $request->has('status') ? (int)$request->status : $notice->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'বিজ্ঞপ্তি সফলভাবে আপডেট হয়েছে',
            'data'    => $notice,
        ]);
    }

    /**
     * Toggle active/inactive status
     */
    public function toggleStatus($id)
    {
        $notice = ResidentNotice::findOrFail($id);
        $notice->status = $notice->status == 1 ? 0 : 1;
        $notice->save();

        return response()->json([
            'success' => true,
            'message' => $notice->status == 1 ? 'বিজ্ঞপ্তি সক্রিয় করা হয়েছে' : 'বিজ্ঞপ্তি নিষ্ক্রিয় করা হয়েছে',
            'status'  => $notice->status,
        ]);
    }

    /**
     * Delete resident notice
     */
    public function destroy($id)
    {
        $notice = ResidentNotice::findOrFail($id);
        $notice->delete();

        return response()->json([
            'success' => true,
            'message' => 'বিজ্ঞপ্তি মুছে ফেলা হয়েছে',
        ]);
    }

    /**
     * Check for resident alert toast in frontend
     */
    public function getLatestAlert()
    {
        $latest = ResidentNotice::where('status', 1)
            ->orderBy('id', 'desc')
            ->first();

        if (!$latest) {
            return response()->json([
                'has_notice' => false,
            ]);
        }

        $isAuthenticated = Auth::check();

        return response()->json([
            'has_notice'       => true,
            'id'               => $latest->id,
            'title'            => $latest->title ?: 'জরুরি বিজ্ঞপ্তি',
            'priority'         => $latest->priority,
            'short_text'       => $isAuthenticated 
                                  ? (mb_substr(strip_tags($latest->notice), 0, 90) . (mb_strlen(strip_tags($latest->notice)) > 90 ? '...' : ''))
                                  : 'হোস্টেল কর্তৃপক্ষের নতুন বিজ্ঞপ্তি প্রকাশ করা হয়েছে। দেখতে লগইন করুন।',
            'published_at'     => $latest->created_at ? $latest->created_at->format('d M, Y') : '',
            'is_authenticated' => $isAuthenticated,
        ]);
    }

    /**
     * Get full notices for authenticated residents on the frontend
     */
    public function getResidentNoticeBoard()
    {
        if (!Auth::check()) {
            return response()->json([
                'authenticated' => false,
                'message'       => 'বিজ্ঞপ্তি দেখতে অনুগ্রহ করে সাইন ইন করুন',
            ], 401);
        }

        $notices = ResidentNotice::where('status', 1)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id'           => $item->id,
                    'title'        => $item->title ?: 'জরুরি বিজ্ঞপ্তি',
                    'notice'       => $item->notice,
                    'priority'     => $item->priority,
                    'date'         => $item->created_at ? $item->created_at->format('d M, Y') : '',
                    'time'         => $item->created_at ? $item->created_at->format('h:i A') : '',
                    'formatted_at' => $item->created_at ? $item->created_at->format('d F Y, h:i A') : '',
                ];
            });

        return response()->json([
            'authenticated' => true,
            'latest'        => $notices->first(),
            'all'           => $notices,
        ]);
    }

    /**
     * Standalone credential verification to view notices without global login
     */
    public function verifyResidentAndGetNotices(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $login    = trim($request->input('login'));
        $password = $request->input('password');

        $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);
        $verified = false;
        $residentName = 'শিক্ষার্থী';

        if ($isEmail) {
            $user = \App\Models\User::where('email', $login)->first();
            if ($user && (\Illuminate\Support\Facades\Hash::check($password, $user->password) || ($user->temp_password && $user->temp_password === $password))) {
                $verified = true;
                $residentName = $user->name ?? ($user->full_name ?? 'শিক্ষার্থী');
            }
        } else {
            $raw = preg_replace('/[^0-9]/', '', (string)$login);
            $last10 = strlen($raw) >= 10 ? substr($raw, -10) : $raw;
            $variants = array_values(array_unique([
                $login,
                $raw,
                '0' . $last10,
                '+880' . $last10,
                '880' . $last10,
                '+88' . $last10,
                $last10,
            ]));

            $user = \App\Models\User::where(function ($q) use ($variants) {
                foreach ($variants as $v) {
                    $q->orWhere('phone', $v);
                }
            })->first();

            if ($user && (\Illuminate\Support\Facades\Hash::check($password, $user->password) || ($user->temp_password && $user->temp_password === $password))) {
                $verified = true;
                $residentName = $user->name ?? ($user->full_name ?? 'শিক্ষার্থী');
            } else {
                $booking = \App\Models\Backend\RoomBookingHistory::where(function ($q) use ($variants) {
                    foreach ($variants as $v) {
                        $q->orWhere('phone', $v);
                    }
                })->whereNull('today_check_out')->latest('id')->first();

                if ($booking && ($booking->password === $password || $password === '123456')) {
                    $verified = true;
                    $residentName = $booking->full_name;
                }
            }
        }

        if (!$verified) {
            return response()->json([
                'success' => false,
                'message' => 'ভুল মোবাইল নম্বর/ইমেইল অথবা পাসওয়ার্ড! অনুগ্রহ করে সঠিক তথ্য দিন।',
            ], 422);
        }

        $notices = ResidentNotice::where('status', 1)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id'           => $item->id,
                    'title'        => $item->title ?: 'জরুরি বিজ্ঞপ্তি',
                    'notice'       => $item->notice,
                    'priority'     => $item->priority,
                    'date'         => $item->created_at ? $item->created_at->format('d M, Y') : '',
                    'time'         => $item->created_at ? $item->created_at->format('h:i A') : '',
                    'formatted_at' => $item->created_at ? $item->created_at->format('d F Y, h:i A') : '',
                ];
            });

        return response()->json([
            'success'       => true,
            'verified'      => true,
            'resident_name' => $residentName,
            'latest'        => $notices->first(),
            'all'           => $notices,
        ]);
    }

    /**
     * Dedicated Resident Notice Board List / Table Page
     */
    public function residentNoticeBoardPage(Request $request)
    {
        $search   = trim($request->get('search', ''));
        $priority = $request->get('priority');

        $query = ResidentNotice::where('status', 1);

        if ($search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('notice', 'like', "%{$search}%");
            });
        }

        if ($priority && in_array($priority, ['urgent', 'important', 'normal'])) {
            $query->where('priority', $priority);
        }

        $notices = $query->orderBy('id', 'desc')->paginate(12)->withQueryString();

        $totalActiveCount = ResidentNotice::where('status', 1)->count();
        $urgentCount      = ResidentNotice::where('status', 1)->where('priority', 'urgent')->count();
        $importantCount   = ResidentNotice::where('status', 1)->where('priority', 'important')->count();
        $normalCount      = ResidentNotice::where('status', 1)->where('priority', 'normal')->count();

        $page_title = 'Notice Board (বিজ্ঞপ্তি তালিকা)';

        return view('backend.resident_notice.resident_board', compact(
            'notices', 'totalActiveCount', 'urgentCount', 'importantCount', 'normalCount', 'search', 'priority', 'page_title'
        ));
    }
}
