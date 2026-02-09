<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusinessCard;
use App\Http\Resources\BusinessCardResource;

class BusinessCardController extends Controller
{
    /**
     * PUBLIC
     * Show all business cards
     */
    public function index()
    {
        $cards = BusinessCard::with(['company', 'user'])
            ->latest()
            ->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Business cards fetched successfully',
            'data'    => BusinessCardResource::collection($cards),
        ], 200);
    }

    /**
     * PUBLIC
     * Get single business card by ID
     */
    public function show($id)
    {
        $card = BusinessCard::with(['company', 'user'])->find($id);

        if (!$card) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Business card not found',
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Business card fetched successfully',
            'data'    => new BusinessCardResource($card),
        ], 200);
    }

    /**
     * PROTECTED
     * Get current user's business cards
     */
    public function myCards(Request $request)
    {
        $cards = BusinessCard::with(['company', 'user'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'My business cards fetched successfully',
            'data'    => BusinessCardResource::collection($cards),
        ], 200);
    }

    /**
     * PROTECTED
     * Create business card
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id'    => 'nullable|exists:companies,id',
            'position'      => 'nullable|string|max:255',

            'phones'        => 'nullable|array',
            'phones.*'      => 'string|min:6',

            'emails'        => 'nullable|array',
            'emails.*'      => 'email',

            'addresses'     => 'nullable|array',
            'addresses.*'   => 'string|max:255',

            'bio'           => 'nullable|string',
            'profile_image' => 'nullable|string',
        ]);

        $card = BusinessCard::create([
            'user_id' => $request->user()->id,
            ...$data,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Business card created successfully',
            'data'    => new BusinessCardResource(
                $card->load(['company', 'user'])
            ),
        ], 201);
    }

    /**
     * PROTECTED
     * Update business card
     */
    public function update(Request $request, $id)
    {
        $card = BusinessCard::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$card) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Business card not found or unauthorized',
            ], 404);
        }

        $data = $request->validate([
            'company_id'    => 'nullable|exists:companies,id',
            'position'      => 'nullable|string|max:255',

            'phones'        => 'nullable|array',
            'phones.*'      => 'string|min:6',

            'emails'        => 'nullable|array',
            'emails.*'      => 'email',

            'addresses'     => 'nullable|array',
            'addresses.*'   => 'string|max:255',

            'bio'           => 'nullable|string',
            'profile_image' => 'nullable|string',
        ]);

        $card->update($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Business card updated successfully',
            'data'    => new BusinessCardResource(
                $card->load(['company', 'user'])
            ),
        ], 200);
    }

    /**
     * PROTECTED
     * Delete business card
     */
    public function destroy(Request $request, $id)
    {
        $card = BusinessCard::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$card) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Business card not found or unauthorized',
            ], 404);
        }

        $card->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Business card deleted successfully',
        ], 200);
    }
}
