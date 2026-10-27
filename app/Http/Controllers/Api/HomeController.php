<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Examcategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function getHomeData(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'অননুমোদিত অ্যাক্সেস (User not authenticated)',
                ], 401);
            }

            // ০. ইউজারের লাইভ এপ্রুভাল স্ট্যাটাস (ক্যাশ বাইপাস)
            $isApproved = Cache::remember('user_approval_status_' . $user->id, 864000, function () use ($user) {
                return (int) $user->is_approved;
            });

            // ১. চলমান কোর্সসমূহ ( priority অনুযায়ী সর্টেড এবং ১০ দিনের ক্যাশ )
            $activeCourses = Cache::remember('active_courses', now()->addDays(10), function () {
                return Course::query()
                    ->select('id', 'name', 'priority')
                    ->where('status', 1)
                    ->withCount('courseexams as total_exams')
                    ->orderBy('priority', 'asc')
                    ->get()
                    ->map(function ($course) {
                        return [
                            'id'          => $course->id,
                            'title'       => $course->name,
                            'total_exams' => $course->total_exams ?? 0,
                        ];
                    });
            });

            // ২. ক্যাটাগরি সমূহ ( ১০ দিনের ক্যাশ )
            $categories = Cache::remember('exam_categories', now()->addDays(10), function () {
                return Examcategory::select('id', 'name', 'thumbnail')->get();
            });

            return response()->json([
                'status'         => true,
                'message'        => 'হোম ডাটা সফলভাবে পাওয়া গেছে।',
                'user_name'      => $user->name,
                'is_approved'    => $isApproved, // লাইভ স্ট্যাটাস (1 অথবা 0)
                'active_courses' => $activeCourses,
                'categories'     => $categories,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Home Data API Error: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'সার্ভার এরর: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function checkUserApprovalStatus(Request $request)
    {
        $userId = $request->user()->id; // Sanctum Token থেকে Auth User

        // ১০ দিনের ক্যাশ (এডমিন থেকে স্টেটাস চেঞ্জ করলে ক্যাশ ফ্লাশ করা হবে)
        $isApproved = Cache::remember('user_approval_status_' . $userId, 864000, function () use ($userId) {
            $user = User::select('id', 'is_approved')->find($userId);
            
            return $user ? (int) $user->is_approved : null;
        });

        if ($isApproved === null) {
            return response()->json([
                'status'  => false,
                'message' => 'ব্যবহারকারী পাওয়া যায়নি',
            ], 444);
        }

        return response()->json([
            'status'      => true,
            'is_approved' => $isApproved, // 1 অথবা 0
        ], 200);
    }
}