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
            'mobile' => 'required|string|min:11|max:14',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'সঠিক মোবাইল নম্বর প্রদান করুন।',
                'errors'  => $validator->errors()
            ], 422);
        }

        $mobile = preg_replace('/[^0-9]/', '', $request->mobile);
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
        // if (RateLimiter::tooManyAttempts('daily-otp:'.$mobile, 5)) {
        //     return response()->json([
        //         'status'  => false,
        //         'message' => 'আজকের দিনের ওটিপি লিমিট শেষ। আগামীকাল আবার চেষ্টা করুন।'
        //     ], 429);
        // }

        // ৩. ১ মিনিটের কুলডাউন চেক
        if (Cache::has('otp_cooldown_'.$mobile)) {
            $ttl = Cache::get('otp_cooldown_'.$mobile) - time();
            return response()->json([
                'status'  => false,
                'message' => "অনুগ্রহ করে {$ttl} সেকেন্ড পর পুনরায় ওটিপি রিকোয়েস্ট করুন।"
            ], 429);
        }

        // ৪. চার ডিজিটের ওটিপি জেনারেট
        $otp = rand(1000, 9999);

        // টেস্ট এনভায়রনমেন্টের জন্য ফিক্সড ওটিপি
        if ($mobile === '01751398392') {
            $otp = 4321;
        }

        // ৫. ক্যাশে ওটিপি সেভ (মেয়াদ ৫ মিনিট)
        Cache::put('otp_'.$mobile, [
            'code' => $otp,
            'attempts' => 0
        ], now()->addMinutes(5));

        // ২ মিনিটের কুলডাউন ফ্ল্যাগ সেভ
        Cache::put('otp_cooldown_'.$mobile, time() + 120, 120);

        // হিট কাউন্ট বৃদ্ধি
        // হিট কাউন্ট বৃদ্ধি
        // হিট কাউন্ট বৃদ্ধি
        // হিট কাউন্ট বৃদ্ধি
        // RateLimiter::hit('ip-otp:'.$ip, 3600); // ১ ঘণ্টার মেয়াদে হিট
        // RateLimiter::hit('daily-otp:'.$mobile, 86400); // ২৪ ঘণ্টার মেয়াদে হিট

        // ৬. এসএমএস গেটওয়ে
        $this->sendSmsViaGateway($mobile, "{$otp} is your pin for Coaching App.");

        return response()->json([
            'status'  => true,
            'message' => 'আপনার মোবাইলে ৪ ডিজিটের ওটিপি পাঠানো হয়েছে।',
        ]);
    }

    /**
     * ওটিপি ভেরিফাই ও লং-লিভড সেশন টোকেন জেনারেট
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string',
            'otp'    => 'required|string|size:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => '৪ ডিজিটের ওটিপি লিখুন।'], 422);
        }

        $mobile = preg_replace('/[^0-9]/', '', $request->mobile);
        $cachedData = Cache::get('otp_'.$mobile);

        if (!$cachedData) {
            return response()->json([
                'status'  => false,
                'message' => 'ওটিপি এর মেয়াদ শেষ হয়ে গেছে। পুনরায় নতুন ওটিপি পাঠান।'
            ], 400);
        }

        if ($cachedData['attempts'] >= 5) {
            Cache::forget('otp_'.$mobile);
            return response()->json([
                'status'  => false,
                'message' => 'অতিরিক্ত ভুল চেষ্টার কারণে ওটিপিটি বাতিল করা হয়েছে। নতুন ওটিপি নিন।'
            ], 400);
        }

        if ($cachedData['code'] != $request->otp) {
            $cachedData['attempts']++;
            Cache::put('otp_'.$mobile, $cachedData, now()->addMinutes(5));

            return response()->json([
                'status'  => false,
                'message' => 'ভুল ওটিপি কোড। পুনরায় চেষ্টা করুন।'
            ], 400);
        }

        Cache::forget('otp_'.$mobile);
        Cache::forget('otp_cooldown_'.$mobile);

        // ইউজার আগে থেকেই আছে কি না তা চেক করা
        $user = User::where('mobile', $mobile)->first();
        $isNewUser = false;

        if (!$user) {
            $isNewUser = true;
            $user = User::create([
                'mobile' => $mobile,
                'name'   => null, // নতুন ইউজারের নাম ফাঁকা থাকবে
                'is_approved' => 0
            ]);
        } elseif (empty($user->name)) {
            $isNewUser = true; // আগে একাউন্ট থাকলেও যদি নাম না থাকে
        }

        $token = $user->createToken('coacing_mobile_app')->plainTextToken;

        return response()->json([
            'status'      => true,
            'message'     => 'সফলভাবে লগইন হয়েছে।',
            'token'       => $token,
            'is_new_user' => $isNewUser, // অ্যাপে নাম চাওয়ার জন্য ফ্ল্যাগ
            'user'        => [
                'id'     => $user->id,
                'name'   => $user->name,
                'mobile' => $user->mobile,
            ]
        ]);
    }

    /**
     * ইউজারের নাম আপডেট করার API
     */
    public function updateName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'দয়া করে আপনার সঠিক নাম লিখুন।',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $user->name = trim($request->name);
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'আপনার নাম সফলভাবে সেভ হয়েছে।',
            'user'    => [
                'id'     => $user->id,
                'name'   => $user->name,
                'mobile' => $user->mobile,
            ]
        ]);
    }

    private function sendSmsViaGateway($mobile, $message)
    {
        $url = config('sms.url2');
        $api_key = config('sms.api_key');
        $senderid = config('sms.senderid');

        $data = [
            "api_key"  => $api_key,
            "senderid" => $senderid,
            "number"   => $mobile,
            "message"  => $message,
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // Array-র বদলে http_build_query ব্যবহার করুন
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        return $response;
    }
}