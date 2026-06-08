<?php

namespace App\Http\Controllers;

use App\Models\Subscribe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class SiteController extends Controller
{
    public function addSubscribes(Request $request)
    {
        $ip = $request->ip();

        try {
            if (RateLimiter::tooManyAttempts('subscribe:' . $ip, 10)) {
                return response('error', 429);
            }
            RateLimiter::hit('subscribe:' . $ip, 60);
        } catch (\Exception $e) {
            Log::warning('RateLimiter unavailable: ' . $e->getMessage());
        }

        $receivedKey = $request->get('key');
        $expectedKey = config('services.site.subscribe_key') ?? env('SITE_SUBSCRIBE_KEY');

        Log::info('addSubscribes', [
            'received_key' => $receivedKey,
            'expected_key' => $expectedKey,
            'ip'           => $ip,
            'all_keys'     => array_keys($request->all()),
            'subscribe'    => $request->get('Subscribe'),
        ]);

        if ($receivedKey !== $expectedKey) {
            return response('error', 403);
        }

        $rawPost = $request->get('post', []);
        $post = (isset($rawPost['Subscribe']) && is_array($rawPost['Subscribe']))
            ? $rawPost['Subscribe']
            : $rawPost;

        $phone    = isset($post['phone'])    ? strip_tags(trim((string) $post['phone']))       : null;
        $fullName = isset($post['full_name']) ? strip_tags(trim((string) $post['full_name']))  : null;
        $doctor   = isset($post['doctor'])   ? strip_tags(trim((string) $post['doctor']))      : null;
        $desc     = isset($post['description']) ? strip_tags(trim((string) $post['description'])) : null;
        $hospital = isset($post['hospital']) ? strip_tags(trim((string) $post['hospital']))    : null;

        if (empty($phone) && empty($fullName)) {
            return response('error', 400);
        }

        try {
            DB::setDefaultConnection('crm_doctor911');

            Subscribe::create([
                'date'        => now()->format('Y-m-d H:i:s'),
                'year'        => now()->year,
                'month'       => now()->month,
                'phone'       => $phone,
                'full_name'   => $fullName,
                'doctor'      => $doctor,
                'description' => $desc,
                'hospital'    => $hospital,
                'status'      => 0,
                'color'       => '#000000',
            ]);
        } catch (\Exception $e) {
            Log::error('addSubscribes error: ' . $e->getMessage());
            return response('error', 500);
        }

        return response('success');
    }
}