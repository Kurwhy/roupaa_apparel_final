<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\AiDesign;
use Carbon\Carbon;

class AIController extends Controller
{
    private const MAX_CREDITS = 3;
    private const TIMEOUT_CHAT = 30;
    private const TIMEOUT_IMAGE = 120;

    public function index()
    {
        $histories = collect();
        $creditsLeft = self::MAX_CREDITS;

        return view('customer.ai.index', compact('histories', 'creditsLeft'));
    }

    public function getSessionData()
    {
        $userId = auth('api')->id();

        $usedToday = AiDesign::where('user_id', $userId)
            ->whereDate('created_at', Carbon::today())
            ->count();


        $histories = AiDesign::where('user_id', $userId)
            ->latest()
            ->get(['id', 'prompt', 'image_path', 'created_at']);

        return response()->json([
            'credits_left' => max(0, self::MAX_CREDITS - $usedToday),
            'histories' => $histories->map(fn($h) => [
                'image_url' => asset('storage/' . $h->image_path),
                'prompt' => $h->prompt,
                'created_at' => $h->created_at->diffForHumans(),
            ]),
        ]);
    }

    public function generate(Request $request)
    {
        set_time_limit(150);
        ini_set('max_execution_time', 150);
        $userId = auth('api')->id();
        if (!$userId) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
        }

        $request->validate(['prompt' => 'required|string|min:5|max:1000']);

        $usedToday = AiDesign::where('user_id', $userId)
            ->whereDate('created_at', Carbon::today())
            ->count();

        if ($usedToday >= self::MAX_CREDITS) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kuota harian Anda sudah habis. Kembali besok untuk 3 kredit baru!',
            ], 403);
        }

        $apiKey = config('services.openai.api_key');
        if (!$apiKey) {
            Log::error('[AI Studio] OPENAI_API_KEY tidak ditemukan di .env');
            return response()->json(['status' => 'error', 'message' => 'Konfigurasi server belum lengkap.'], 500);
        }

        try {
            $systemPrompt = "You are an expert Prompt Engineer for AI image generation specializing in APPAREL DESIGN.
Your job: translate and enhance the user's rough idea into a highly detailed English image prompt.

CRITICAL RULES — always include ALL of these:
1. ONE single cohesive graphic. NO split screens, NO multiple variations, NO grids.
2. Subject MUST be centered and isolated on a PURE SOLID WHITE BACKGROUND (#FFFFFF).
3. Style: 2D flat vector graphic, bold outlines, no 3D renders, no photorealism.
4. Keep it printable: high contrast, bold shapes, clean edges.

Output ONLY the final English prompt. No explanations, no preamble.";

            $chatResponse = Http::withToken($apiKey)
                ->timeout(self::TIMEOUT_CHAT)
                ->retry(2, 1000)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $request->prompt],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 500,
                ]);

            if (!$chatResponse->successful()) {
                $errorBody = $chatResponse->json();
                Log::error('[AI Studio] GPT-4o-mini error', [
                    'status' => $chatResponse->status(),
                    'response' => $errorBody,
                    'user_id' => $userId,
                ]);
                return response()->json(['status' => 'error', 'message' => 'AI gagal memproses prompt. Coba lagi.'], 500);
            }

            $superPrompt = trim($chatResponse->json('choices.0.message.content') ?? '');

            if (empty($superPrompt)) {
                Log::error('[AI Studio] GPT response kosong', ['user_id' => $userId]);
                return response()->json(['status' => 'error', 'message' => 'AI gagal memproses prompt. Coba lagi.'], 500);
            }

            $refusalPatterns = [
                "i'm sorry",
                'i am sorry',
                "i can't assist",
                'i cannot assist',
                "i can't help",
                'i cannot help',
                "i can't create",
                'i cannot create',
                "i can't generate",
                'i cannot generate',
                "i'm not able to",
                'i am not able to',
                'as an ai',
            ];
            
            $superPromptLower = strtolower($superPrompt);
            foreach ($refusalPatterns as $pattern) {
                if (str_contains($superPromptLower, $pattern)) {
                    Log::warning('[AI Studio] Prompt ditolak gpt-4o-mini sebelum sampai ke image generation', [
                        'user_id' => $userId,
                        'original_prompt' => $request->prompt,
                        'gpt_response' => $superPrompt,
                    ]);
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Desain tidak dapat dibuat karena melanggar kebijakan konten. Coba deskripsi yang berbeda.',
                    ], 422);
                }
            }

            Log::info('[AI Studio] Super prompt generated', [
                'user_id' => $userId,
                'super_prompt' => $superPrompt,
            ]);

            Log::info('[AI Studio] Mengirim request ke gpt-image-1...', ['user_id' => $userId]);

            $imageResponse = Http::withToken($apiKey)
                ->timeout(self::TIMEOUT_IMAGE)
                ->post('https://api.openai.com/v1/images/generations', [
                    'model' => 'gpt-image-1',
                    'prompt' => $superPrompt,
                    'n' => 1,
                    'size' => '1024x1024',
                ]);

            if (!$imageResponse->successful()) {
                $errorBody = $imageResponse->json();
                Log::error('[AI Studio] gpt-image-1 error', [
                    'status' => $imageResponse->status(),
                    'response' => $errorBody,
                    'user_id' => $userId,
                ]);

                $errCode = $errorBody['error']['code'] ?? '';
                $errMsg = $errorBody['error']['message'] ?? '';

                $userMessage = match (true) {
                    str_contains(strtolower($errMsg), 'safety'),
                    str_contains(strtolower($errMsg), 'policy'),
                    $errCode === 'content_policy_violation'
                    => 'Desain tidak dapat dibuat karena melanggar kebijakan konten. Coba deskripsi yang berbeda.',
                    $errCode === 'rate_limit_exceeded'
                    => 'Server AI sedang sibuk. Tunggu beberapa detik lalu coba lagi.',
                    $errCode === 'insufficient_quota'
                    => 'Kuota API habis. Hubungi administrator.',
                    default => 'Gagal generate gambar. Coba lagi dalam beberapa saat.',
                };

                return response()->json(['status' => 'error', 'message' => $userMessage], 500);
            }

            $base64Image = $imageResponse->json('data.0.b64_json');

            if (empty($base64Image)) {
                Log::error('[AI Studio] b64_json dari gpt-image-1 kosong', [
                    'response_keys' => array_keys($imageResponse->json('data.0') ?? []),
                    'user_id' => $userId,
                ]);
                return response()->json(['status' => 'error', 'message' => 'Gambar berhasil dibuat tapi data tidak tersedia.'], 500);
            }

            $imageContent = base64_decode($base64Image);

            if (empty($imageContent)) {
                Log::error('[AI Studio] Gagal decode b64_json gpt-image-1', ['user_id' => $userId]);
                return response()->json(['status' => 'error', 'message' => 'Gagal memproses gambar. Coba lagi.'], 500);
            }

            $filename = 'ai-designs/' . $userId . '_' . Str::random(12) . '.png';
            Storage::disk('public')->put($filename, $imageContent);

            AiDesign::create([
                'user_id' => $userId,
                'prompt' => $request->prompt,
                'theme' => 'Default',
                'image_path' => $filename,
            ]);

            $creditsLeft = self::MAX_CREDITS - ($usedToday + 1);

            Log::info('[AI Studio] Berhasil generate', [
                'user_id' => $userId,
                'credits_left' => $creditsLeft,
                'filename' => $filename,
            ]);

            return response()->json([
                'status' => 'success',
                'image_url' => asset('storage/' . $filename),
                'credits_left' => max(0, $creditsLeft),
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('[AI Studio] Connection timeout', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Koneksi ke server OpenAI timeout. Server AI sedang sibuk, coba lagi dalam 30 detik.',
            ], 504);
        } catch (\Exception $e) {
            Log::error('[AI Studio] Exception tidak terduga', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem. Tim kami sudah diberitahu.',
            ], 500);
        }
    }

    public function historyView()
    {
        return view('customer.ai.history');
    }

    public function historyData()
    {
        $userId = auth('api')->id();
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $histories = AiDesign::where('user_id', $userId)
            ->latest()
            ->paginate(12);

        return response()->json([
            'status' => 'success',
            'data' => $histories,
        ]);
    }

}
