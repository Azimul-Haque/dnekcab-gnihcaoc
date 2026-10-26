<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
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

    public function getCourseExams($courseId)
    {
        try {
            $exams = Cache::remember('course_exams_' . $courseId, now()->addDays(10), function () use ($courseId) {
                return Courseexam::query()
                    ->where('course_id', $courseId)
                    ->with(['exam' => function ($query) {
                        $query->select(
                            'id', 
                            'examcategory_id', 
                            'name', 
                            'duration', 
                            'negativepercentage', 
                            'available_from', 
                            'available_to', 
                            'syllabus', 
                            'participation', 
                            'cutmark', 
                            'lecture_url'
                        );
                    }])
                    ->get()
                    ->pluck('exam')
                    ->filter() // ফাঁকা/নাল ডাটা ফিল্টার
                    ->values()
                    ->map(function ($exam) {
                        return [
                            'id'                 => $exam->id,
                            'name'               => $exam->name,
                            'duration'           => $exam->duration,
                            'negativepercentage' => $exam->negativepercentage,
                            'available_from'     => $exam->available_from,
                            'available_to'       => $exam->available_to,
                            'syllabus'           => $exam->syllabus,
                            'participation'      => $exam->participation,
                            'cutmark'            => $exam->cutmark,
                            'lecture_url'        => $exam->lecture_url,
                        ];
                    });
            });

            return response()->json([
                'status'  => true,
                'message' => 'পরীক্ষাসমূহ সফলভাবে পাওয়া গেছে।',
                'exams'   => $exams,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Course Exams API Error: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'সার্ভার এরর: ' . $e->getMessage(),
            ], 500);
        }
    }
}