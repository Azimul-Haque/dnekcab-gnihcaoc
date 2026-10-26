<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Examcategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function getHomeData()
    {
        try {
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
                return ExamCategory::select('id', 'name')->get();
            });

            return response()->json([
                'status'         => true,
                'message'        => 'হোম ডাটা সফলভাবে পাওয়া গেছে।',
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
}