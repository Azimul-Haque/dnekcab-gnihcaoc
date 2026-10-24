<?php

namespace App\Imports;

use Rap2hpoutre\FastExcel\FastExcel;
use App\Models\Question;
use App\Models\Questionexplanation;
use App\Models\Examquestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // ডাটাবেজ ট্রানজেকশনের জন্য

public function storeExcelQuestions(Request $request, $exam_id)
{
    // ১. ফাইল ভ্যালিডেশন
    $request->validate([
        'file' => 'required|mimes:xls,xlsx,csv|max:10240',
    ]);

    try {
        // ট্রানজেকশন শুরু (যাতে কোনো এরর হলে অর্ধেক ডাটা সেভ হয়ে না থাকে)
        DB::beginTransaction();

        // ২. FastExcel দিয়ে ফাইল রিড করা
        $questions = (new FastExcel)->import($request->file('file'), function ($row) use ($exam_id) {
            
            // যদি প্রশ্ন বা অপশন ১ ফাঁকা থাকে, তবে সেই রো স্কিপ করবে
            if (empty($row['question']) || empty($row['option1'])) {
                return null;
            }

            // Question টেবিলে ইনসার্ট
            $question = Question::create([
                'topic_id'   => !empty($row['topic_id']) ? $row['topic_id'] : null,
                'question'   => $row['question'],
                'option1'    => $row['option1'],
                'option2'    => $row['option2'],
                'option3'    => $row['option3'],
                'option4'    => $row['option4'],
                'answer'     => $row['answer'],
                'difficulty' => 1, // যেহেতু এক্সেল থেকে বাদ দিয়েছি, ডিফল্ট ১
            ]);

            // Explanation থাকলে ইনসার্ট
            if (!empty($row['explanation'])) {
                Questionexplanation::create([
                    'question_id' => $question->id,
                    'explanation' => $row['explanation'],
                ]);
            }

            // Examquestion পিভট টেবিলে এক্সামের সাথে রিলেশন তৈরি
            Examquestion::create([
                'exam_id'     => $exam_id,
                'question_id' => $question->id,
            ]);

            return $question;
        });

        DB::commit(); // সব ঠিক থাকলে ডাটাবেজে সেভ কনফার্ম করবে

        return redirect()->back()->with('success', 'এক্সেল ফাইল থেকে প্রশ্নসমূহ সফলভাবে আপলোড এবং যুক্ত করা হয়েছে।');
        
    } catch (\Exception $e) {
        DB::rollBack(); // কোনো এরর হলে আগের সব ইনসার্ট বাতিল করে দিবে
        return redirect()->back()->with('error', 'ফাইল আপলোডে সমস্যা হয়েছে। Error: ' . $e->getMessage());
    }
}