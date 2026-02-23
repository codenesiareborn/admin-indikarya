<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyQuote;
use Illuminate\Http\JsonResponse;

class DailyQuoteController extends Controller
{
    /**
     * Get random daily quote
     */
    public function random(): JsonResponse
    {
        $quote = DailyQuote::inRandomOrder()->first();

        if (!$quote) {
            return response()->json([
                'success' => false,
                'message' => 'No quotes available',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $quote->id,
                'title' => $quote->title,
                'content' => $quote->content,
                'author' => $quote->author,
                'created_at' => $quote->created_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Get list of all quotes
     */
    public function index(): JsonResponse
    {
        $quotes = DailyQuote::orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $quotes->map(function ($quote) {
                return [
                    'id' => $quote->id,
                    'title' => $quote->title,
                    'content' => $quote->content,
                    'author' => $quote->author,
                    'created_at' => $quote->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'pagination' => [
                'current_page' => $quotes->currentPage(),
                'last_page' => $quotes->lastPage(),
                'per_page' => $quotes->perPage(),
                'total' => $quotes->total(),
            ],
        ]);
    }

    /**
     * Get specific quote by ID
     */
    public function show($id): JsonResponse
    {
        $quote = DailyQuote::find($id);

        if (!$quote) {
            return response()->json([
                'success' => false,
                'message' => 'Quote not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $quote->id,
                'title' => $quote->title,
                'content' => $quote->content,
                'author' => $quote->author,
                'created_at' => $quote->created_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
