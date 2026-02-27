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
            'name'          => 'nullable|string|max:255',
            'company_id'    => 'nullable|exists:companies,id',
            'position'      => 'nullable|string|max:255',

            'phones'        => 'nullable|array',
            'phones.*'      => 'string|min:6',

            'emails'        => 'nullable|array',
            'emails.*'      => 'email',

            'addresses'     => 'nullable|array',
            'addresses.*'   => 'string|max:255',

            'bio'           => 'nullable|string',
            'profile_image' => 'nullable', // file or string
            
            'card_type'     => 'nullable|string|in:my_card,user_card,manual_contact',
            'qr_code_data'  => 'nullable|string',
            'social_links'  => 'nullable|array',
        ]);

        $imagePath = null;
        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $imagePath = asset('storage/' . $path);
        } elseif ($request->input('profile_image') && is_string($request->input('profile_image'))) {
            $imagePath = $request->input('profile_image');
        }
        
        $cardData = [
            'user_id' => $request->user()->id,
            'name' => $data['name'] ?? null,
            'company_id' => $data['company_id'] ?? null,
            'position' => $data['position'] ?? null,
            'phones' => $data['phones'] ?? [],
            'emails' => $data['emails'] ?? [],
            'addresses' => $data['addresses'] ?? [],
            'bio' => $data['bio'] ?? null,
            'card_type' => $data['card_type'] ?? 'my_card',
            'qr_code_data' => $data['qr_code_data'] ?? null,
            'social_links' => $data['social_links'] ?? [],
        ];

        if ($imagePath) {
            $cardData['profile_image'] = $imagePath;
        }

        $card = BusinessCard::create($cardData);

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
            'name'          => 'nullable|string|max:255',
            'company_id'    => 'nullable|exists:companies,id',
            'position'      => 'nullable|string|max:255',

            'phones'        => 'nullable|array',
            'phones.*'      => 'string|min:6',

            'emails'        => 'nullable|array',
            'emails.*'      => 'email',

            'addresses'     => 'nullable|array',
            'addresses.*'   => 'string|max:255',

            'bio'           => 'nullable|string',
            'profile_image' => 'nullable',
            
            'card_type'     => 'nullable|string',
            'qr_code_data'  => 'nullable|string',
            'social_links'  => 'nullable|array',
        ]);

        $updateData = [
            'name' => $data['name'] ?? $card->name,
            'company_id' => array_key_exists('company_id', $data) ? $data['company_id'] : $card->company_id,
            'position' => $data['position'] ?? $card->position,
            'phones' => $data['phones'] ?? $card->phones,
            'emails' => $data['emails'] ?? $card->emails,
            'addresses' => $data['addresses'] ?? $card->addresses,
            'bio' => $data['bio'] ?? $card->bio,
            'card_type' => $data['card_type'] ?? $card->card_type,
            'qr_code_data' => $data['qr_code_data'] ?? $card->qr_code_data,
            'social_links' => $data['social_links'] ?? $card->social_links,
        ];

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $updateData['profile_image'] = asset('storage/' . $path);
        } elseif ($request->input('profile_image') && is_string($request->input('profile_image'))) {
            $updateData['profile_image'] = $request->input('profile_image');
        }

        $card->update($updateData);

        return response()->json([
            'status'  => 'success',
            'message' => 'Business card updated successfully',
            'data'    => new BusinessCardResource(
                $card->refresh()->load(['company', 'user'])
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
