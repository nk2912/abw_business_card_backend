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
     * PROTECTED
     * Add card as friend
     */
    public function addFriend(Request $request, $id)
    {
        $card = BusinessCard::findOrFail($id);
        
        // If it's my own card, I cannot add myself as friend (optional check)
        if ($card->user_id === $request->user()->id) {
             return response()->json([
                'status'  => 'error',
                'message' => 'Cannot add yourself as friend',
            ], 400);
        }

        // Check if I already collected this card
        $exists = $request->user()->collectedCards()->where('business_card_id', $id)->first();

        if ($exists) {
            // Update existing pivot
            $request->user()->collectedCards()->updateExistingPivot($id, [
                'is_friend' => true,
                'friend_status' => 'accepted', // Auto accept for now, or 'pending'
            ]);
        } else {
            // Attach new
            $request->user()->collectedCards()->attach($id, [
                'is_friend' => true,
                'friend_status' => 'accepted',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Friend added successfully',
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
     * Get current user's collected business cards
     */
    public function myCards(Request $request)
    {
        // Get cards I created manually OR cards I collected from others
        $manualCards = BusinessCard::with(['company', 'user'])
            ->where('user_id', $request->user()->id)
            ->where('card_type', 'my_card')
            ->latest()
            ->get();
            
        $collectedCards = $request->user()->collectedCards()->with(['company', 'user'])->get();

        // Merge them (optional: or return separate lists)
        $allCards = $manualCards->merge($collectedCards);

        return response()->json([
            'status'  => 'success',
            'message' => 'My business cards fetched successfully',
            'data'    => BusinessCardResource::collection($allCards),
        ], 200);
    }

    /**
     * PROTECTED
     * Create business card
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'nullable|string|max:255', // Add name validation
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

            // New fields
            'card_type'         => 'nullable|string|in:my_card,user_card,manual_contact,scan',
            'qr_code_data'      => 'nullable|string',
            'social_links'      => 'nullable|array',
            'social_links.*.platform' => 'required_with:social_links|string',
            'social_links.*.url'      => 'required_with:social_links|string',
        ]);

        $card = BusinessCard::create([
            'user_id' => $request->user()->id,
            // If name is not provided (e.g. creating my own card), use my user name?
            // Or keep it null and fallback to user name in Resource?
            // Let's store it if provided.
            'name' => $data['name'] ?? null, 
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
     * Collect another user's card (Add Friend)
     */
    public function collectCard(Request $request)
    {
        $data = $request->validate([
            'business_card_id' => 'required|exists:business_cards,id',
            'is_friend'        => 'boolean',
            'tag'              => 'nullable|string'
        ]);
        
        // Cannot add own card
        $card = BusinessCard::findOrFail($data['business_card_id']);
        if ($card->user_id === $request->user()->id) {
             return response()->json([
                'status'  => 'error',
                'message' => 'You cannot add your own card to collection',
            ], 400);
        }

        // Check if already added
        if ($request->user()->collectedCards()->where('business_card_id', $card->id)->exists()) {
             return response()->json([
                'status'  => 'error',
                'message' => 'Card already in collection',
            ], 409);
        }

        // Add to collection
        $request->user()->collectedCards()->attach($card->id, [
            'is_friend'     => $data['is_friend'] ?? false,
            'friend_status' => ($data['is_friend'] ?? false) ? 'pending' : 'none',
            'tag'           => $data['tag'] ?? null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Card added to collection successfully',
        ], 201);
    }

    /**
     * PROTECTED
     * Remove card from collection (Unfriend)
     */
    public function uncollectCard(Request $request, $id)
    {
        // Check if card exists in collection
        $exists = $request->user()->collectedCards()->where('business_card_id', $id)->exists();

        if (!$exists) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Card not found in your collection',
            ], 404);
        }

        // Detach
        $request->user()->collectedCards()->detach($id);

        return response()->json([
            'status'  => 'success',
            'message' => 'Card removed from collection',
        ], 200);
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
            'name'          => 'nullable|string|max:255', // Name update
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

            'card_type'         => 'nullable|string|in:my_card,user_card,manual_contact',
            'qr_code_data'      => 'nullable|string',
            'social_links'      => 'nullable|array',
            'social_links.*.platform' => 'required_with:social_links|string',
            'social_links.*.url'      => 'required_with:social_links|string',
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
