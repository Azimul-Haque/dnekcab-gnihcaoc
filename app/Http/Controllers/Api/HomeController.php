<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\ExamCategory;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    /**
     * হোম স্ক্রিনের জন্য একটিমাত্র রিকোয়েস্টে গতিশীল ডাটা লোড (ক্যাশেড)
     */
    public function getHomeData()
    {
        // ১. চলমান কোর্সসমূহ (১০ দিনের জন্য ক্যাশেড)
        $activeCourses = Cache::remember('active_courses', now()->addDays(10), function () {
            return Course::query()
                ->select('id', 'title', 'status', 'priority')
                ->where('status', 1) // শুধু এক্টিভ কোর্সসমূহ
                ->withCount(['courseexams as total_exams']) // SQL লেভেলে কাউন্ট (সুপার ফাস্ট)
                ->orderBy('priority', 'asc')
                ->get();
        });

        // ২. ক্যাটাগরি সমূহ (১০ দিনের জন্য ক্যাশেড)
        $categories = Cache::remember('exam_categories', now()->addDays(10), function () {
            return ExamCategory::query()
                ->select('id', 'name', 'icon', 'status', 'priority')
                ->where('status', 1) // শুধু এক্টিভ ক্যাটাগরি
                ->orderBy('priority', 'asc')
                ->get();
        });

        return response()->json([
            'status'         => true,
            'message'        => 'হোম ডাটা সফলভাবে পাওয়া গেছে।',
            'active_courses' => $activeCourses,
            'categories'     => $categories,
        ], 200);
    }
}