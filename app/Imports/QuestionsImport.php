<?php

namespace App\Imports;

use App\Models\Question;
use App\Models\Questionexplanation;
use App\Models\Examquestion;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // এটি প্রথম রো-কে হেডার হিসেবে ধরবে

class QuestionsImport implements ToModel, WithHeadingRow
{
    protected $examId;

    // কন্ট্রোলার থেকে Exam ID রিসিভ করার জন্য
    public function __construct($examId)
    {
        $this->examId = $examId;
    }

    public function model(array $row)
    {
        // ১. যদি প্রশ্ন বা অপশন ১ ফাঁকা থাকে, তবে সেই রো স্কিপ করবে
        if (empty($row['question']) || empty($row['option1'])) {
            return null; 
        }

        // ২. Question টেবিলে ডাটা ইনসার্ট (এক্সেল শিটের হেডারের নাম অনুযায়ী)
        $question = Question::create([
            'topic_id'   => !empty($row['topic_id']) ? $row['topic_id'] : null,
            'question'   => $row['question'],
            'option1'    => $row['option1'],
            'option2'    => $row['option2'],
            'option3'    => $row['option3'],
            'option4'    => $row['option4'],
            'answer'     => $row['answer'],
            'difficulty' => 1, // ডিফল্ট ভ্যালু দিয়ে দিলাম যেহেতু এক্সেল থেকে বাদ দিয়েছি
        ]);

        // ৩. যদি ব্যাখ্যা (Explanation) থাকে, তবে Questionexplanation টেবিলে সেভ করবে
        if (!empty($row['explanation'])) {
            Questionexplanation::create([
                'question_id' => $question->id,
                'explanation' => $row['explanation'],
            ]);
        }

        // ৪. Examquestion পিভট টেবিলে এক্সামের সাথে রিলেশন তৈরি
        Examquestion::create([
            'exam_id'     => $this->examId,
            'question_id' => $question->id,
        ]);

        return $question;
    }
}