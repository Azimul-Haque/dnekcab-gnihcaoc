<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * ওটিপি সেন্ড মেথড (অ্যান্টি-স্প্যাম ফিল্টার সহ)
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:11|max:14',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'সঠিক মোবাইল নম্বর প্রদান করুন।',
                'errors'  => $validator->errors()
            ], 422);
        }

        $phone = preg_replace('/[^0-9]/', '', $request->phone); // শুধুমাত্র ডিজিটাল ক্যারেক্টার রাখা
        $ip = $request->ip();

        // ১. IP ভিত্তিক লিমিট (১ ঘণ্টায় সর্বোচ্চ ১০ টি)
        // ১. IP ভিত্তিক লিমিট (১ ঘণ্টায় সর্বোচ্চ ১০ টি)
        // ১. IP ভিত্তিক লিমিট (১ ঘণ্টায় সর্বোচ্চ ১০ টি)
        // ১. IP ভিত্তিক লিমিট (১ ঘণ্টায় সর্বোচ্চ ১০ টি)
        // ১. IP ভিত্তিক লিমিট (১ ঘণ্টায় সর্বোচ্চ ১০ টি)
        // if (RateLimiter::tooManyAttempts('ip-otp:'.$ip, 10)) {
        //     $seconds = RateLimiter::availableIn('ip-otp:'.$ip);
        //     return response()->json([
        //         'status'  => false,
        //         'message' => "অতিরিক্ত চেষ্টার কারণে আপনার IP সাময়িকভাবে ব্লক করা হয়েছে। অনুগ্রহ করে {$seconds} সেকেন্ড পর চেষ্টা করুন।"
        //     ], 429);
        // }

        // // ২. মোবাইল নম্বর ভিত্তিক দৈনিক লিমিট (২৪ ঘণ্টায় সর্বোচ্চ ৫ টি)
        // // ২. মোবাইল নম্বর ভিত্তিক দৈনিক লিমিট (২৪ ঘণ্টায় সর্বোচ্চ ৫ টি)
        // // ২. মোবাইল নম্বর ভিত্তিক দৈনিক লিমিট (২৪ ঘণ্টায় সর্বোচ্চ ৫ টি)
        // // ২. মোবাইল নম্বর ভিত্তিক দৈনিক লিমিট (২৪ ঘণ্টায় সর্বোচ্চ ৫ টি)
        // // ২. মোবাইল নম্বর ভিত্তিক দৈনিক লিমিট (২৪ ঘণ্টায় সর্বোচ্চ ৫ টি)
        // if (RateLimiter::tooManyAttempts('daily-otp:'.$phone, 5)) {
        //     return response()->json([
        //         'status'  => false,
        //         'message' => 'আজকের দিনের ওটিপি লিমিট শেষ। আগামীকাল আবার চেষ্টা করুন।'
        //     ], 429);
        // }

        // ৩. ১ মিনিটের কুলডাউন চেক
        if (Cache::has('otp_cooldown_'.$phone)) {
            $ttl = Cache::get('otp_cooldown_'.$phone) - time();
            return response()->json([
                'status'  => false,
                'message' => "অনুগ্রহ করে {$ttl} সেকেন্ড পর পুনরায় ওটিপি রিকোয়েস্ট করুন।"
            ], 429);
        }

        // ৪. চার ডিজিটের ওটিপি জেনারেট
        $otp = rand(1000, 9999);

        // টেস্ট এনভায়রনমেন্টের জন্য ফিক্সড ওটিপি (প্রোডাকশনে সরাতে পারেন)
        if ($phone === '01751398392') {
            $otp = 4321;
        }

        // ৫. ক্যাশে ওটিপি সেভ (মেয়াদ ৫ মিনিট)
        Cache::put('otp_'.$phone, [
            'code' => $otp,
            'attempts' => 0
        ], now()->addMinutes(5));

        // ১ মিনিটের কুলডাউন ফ্ল্যাগ সেভ
        Cache::put('otp_cooldown_'.$phone, time() + 60, 60);

        // হিট কাউন্ট বৃদ্ধি
        // হিট কাউন্ট বৃদ্ধি
        // হিট কাউন্ট বৃদ্ধি
        // হিট কাউন্ট বৃদ্ধি
        // RateLimiter::hit('ip-otp:'.$ip, 3600); // ১ ঘণ্টার মেয়াদে হিট
        // RateLimiter::hit('daily-otp:'.$phone, 86400); // ২৪ ঘণ্টার মেয়াদে হিট

        // ৬. এসএমএস গেটওয়ে Integration (এখানে আপনার SMS Gateway API হিট করবেন)
        $this->sendSmsViaGateway($phone, "{$otp} is your pin for Coaching App.");

        return response()->json([
            'status'  => true,
            'message' => 'আপনার মোবাইলে ৪ ডিজিটের ওটিপি পাঠানো হয়েছে।',
            // 'debug_otp' => $otp // ডেভেলপমেন্টের জন্য এটি অন রাখতে পারেন
        ]);
    }

    /**
     * ওটিপি ভেরিফাই ও ৯০ দিনের লং-লিভড সেশন টোকেন তৈরি
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp'   => 'required|string|size:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => '৪ ডিজিটের ওটিপি লিখুন।'], 422);
        }

        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        $cachedData = Cache::get('otp_'.$phone);

        if (!$cachedData) {
            return response()->json([
                'status'  => false,
                'message' => 'ওটিপি এর মেয়াদ শেষ হয়ে গেছে। পুনরায় নতুন ওটিপি পাঠান।'
            ], 400);
        }

        // ব্রুট ফোর্স প্রটেকশন (সর্বোচ্চ ৫ বার ভুল চেষ্টার পর ব্লক)
        if ($cachedData['attempts'] >= 5) {
            Cache::forget('otp_'.$phone);
            return response()->json([
                'status'  => false,
                'message' => 'অতিরিক্ত ভুল চেষ্টার কারণে ওটিপিটি বাতিল করা হয়েছে। নতুন ওটিপি নিন।'
            ], 400);
        }

        // ওটিপি ম্যাচ চেক
        if ($cachedData['code'] != $request->otp) {
            $cachedData['attempts']++;
            Cache::put('otp_'.$phone, $cachedData, now()->addMinutes(5));

            return response()->json([
                'status'  => false,
                'message' => 'ভুল ওটিপি কোড। পুনরায় চেষ্টা করুন।'
            ], 400);
        }

        // ওটিপি সঠিক হলে ক্যাশ থেকে মুছে ফেলা
        Cache::forget('otp_'.$phone);
        Cache::forget('otp_cooldown_'.$phone);

        // ইউজার না থাকলে নতুন অ্যাকাউন্ট তৈরি করা, থাকলে অ্যাকাউন্টটি খুঁজে নেওয়া
        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => 'User_' . Str::random(5),
                'status' => 1
            ]
        );

        // Sanctum লং-লিভড টোকেন জেনারেট (৩ মাস মেয়াদ)
        $token = $user->createToken('coacing_mobile_app')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'সফলভাবে লগইন হয়েছে।',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'phone' => $user->phone,
            ]
        ]);
    }

    /**
     * এসএমএস পাঠানোর কাস্টম হেল্পার
     */
    private function sendSmsViaGateway($phone, $message)
    {
        $url = config('sms.url2');
        $api_key = config('sms.api_key');
        $senderid = config('sms.senderid');
        $number = $phone;
        $message = $message;

        $data = [
            "api_key" => $api_key,
            "senderid" => $senderid,
            "number" => $number,
            "message" => $message,
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        $jsonresponse = json_decode($response);

        if($jsonresponse->response_code == 202) {
            // Session::flash('success', 'SMS সফলভাবে পাঠানো হয়েছে!');
        } elseif($jsonresponse->response_code == 1007) {
            // Session::flash('warning', 'অপর্যাপ্ত SMS ব্যালেন্সের কারণে SMS পাঠানো যায়নি!');
        } else {
            // Session::flash('warning', 'দুঃখিত! SMS পাঠানো যায়নি!');
        }

        // return $otp;
        return response()->json(['success' => true, 'message' => 'OTP পাঠানো হয়েছে!']);
    }
}