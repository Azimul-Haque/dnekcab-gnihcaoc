<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * ক্যাটাগরি আইডি অনুযায়ী চলমান কোর্সসমূহ লোড করা (১০ দিনের ক্যাশ)
     */
    public function getCategoryCourses($categoryId)
    {
        try {
            $courses = Cache::remember('category_courses_' . $categoryId, now()->addDays(10), function () use ($categoryId) {
                return Course::query()
                    ->select('id', 'name', 'category', 'status', 'serial')
                    ->where('category', $categoryId)
                    ->where('status', 1) // এক্টিভ কোর্স
                    ->withCount('courseexams as total_exams')
                    ->orderBy('serial', 'asc') // কোর্সের সিরিয়াল অনুযায়ী সর্ট
                    ->get()
                    ->map(function ($course) {
                        return [
                            'id'          => $course->id,
                            'title'       => $course->name,
                            'total_exams' => $course->total_exams ?? 0,
                        ];
                    });
            });

            return response()->json([
                'status'  => true,
                'message' => 'কোর্সসমূহ সফলভাবে পাওয়া গেছে।',
                'courses' => $courses,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Category Courses API Error: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'সার্ভার এরর: ' . $e->getMessage(),
            ], 500);
        }
    }
}