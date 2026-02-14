<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\BusinessList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Business_locations;
use Illuminate\Support\Facades\Crypt;


class vendors extends Controller
{
    /**
     * Display a listing of vendors.
     */
    public function index()
    {
        $user = auth()->user();

        // Check if user is authenticated and has an active business
        if (!$user || !$user->active_business_key) {
            return response()->json([
                'success' => false,
                'message' => 'No active business selected.'
            ], 403);
        }

        // Check permission for vendors
        if (!$user->hasPermission('vendor_read')) {
            return response()->json([
                'success' => false,
                'message' => 'Feature unavailable.'
            ], 403);
        }

        // Cache key per business
        $cacheKey = "vendors_{$user->active_business_key}";

        // Cache for 60 seconds
        $vendors = Cache::remember($cacheKey, 60, function () use ($user) {
            return Vendor::where('business_key', $user->active_business_key)
                ->orderBy('vendor_name')
                ->get()
                ->map(function ($vendor) {
                    return [
                        'vid' => $vendor->id,
                        'id' => Crypt::encrypt($vendor->id),
                        'owner_id' => $vendor->owner_id,
                        'business_key' => $vendor->business_key,
                        'location_id' => $vendor->location_id,
                        'vendor_name' => $vendor->vendor_name,
                        'contact_person' => $vendor->contact_person,
                        'email' => $vendor->email,
                        'phone' => $vendor->phone,
                        'address' => $vendor->address,
                        'city' => $vendor->city,
                        'state' => $vendor->state,
                        'country' => $vendor->country,
                        'postal_code' => $vendor->postal_code,
                        'industry' => $vendor->industry,
                        'tax_id' => $vendor->tax_id,
                        'registration_number' => $vendor->registration_number,
                        'website' => $vendor->website,
                        'bank_name' => $vendor->bank_name,
                        'bank_account_number' => $vendor->bank_account_number,
                        'bank_account_name' => $vendor->bank_account_name,
                        'is_active' => (bool)$vendor->is_active,
                        'notes' => $vendor->notes,
                        'created_at' => $vendor->created_at,
                        'updated_at' => $vendor->updated_at,
                    ];
                });
        });

        return response()->json([
            'success' => true,
            'message' => 'Vendors retrieved successfully',
            'data' => [
                'vendors' => $vendors,
                'total' => $vendors->count(),
                'active_count' => $vendors->where('is_active', true)->count(),
            ]
        ]);
    }

    /**
     * Store a newly created vendor.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Check if user is authenticated and has an active business
        if (!$user || !$user->active_business_key) {
            return response()->json([
                'success' => false,
                'message' => 'No active business selected.'
            ], 403);
        }

        // Check permission for creating vendors
        if (!$user->hasPermission('vendor_create')) {
            return response()->json([
                'success' => false,
                'message' => 'Feature unavailable.'
            ], 403);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'vendor_name' => 'required|string|max:100',
            'contact_person' => 'nullable|string|max:100',
            'email' => [
                'required',
                'email',
                'max:155',
                Rule::unique('vendors')->where(function ($query) use ($user) {
                    return $query->where('business_key', $user->active_business_key);
                })
            ],
            'phone' => 'nullable|string|max:20',
            'location_id' => 'required|exists:business_locations,id',
            'address' => 'nullable|string|max:150',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'industry' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:155',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:105',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Verify location belongs to current business
            $location = Business_locations::where('id', $request->location_id)
                ->where('business_key', $user->active_business_key)
                ->first();

            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid location selected.'
                ], 422);
            }

            // Create vendor
            $vendor = Vendor::create([
                'owner_id' => $user->id,
                'business_key' => $user->active_business_key,
                'location_id' => $request->location_id,
                'vendor_name' => $request->vendor_name,
                'contact_person' => $request->contact_person,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country ?? 'Nigeria',
                'postal_code' => $request->postal_code,
                'industry' => $request->industry,
                'tax_id' => $request->tax_id,
                'registration_number' => $request->registration_number,
                'website' => $request->website,
                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_account_name' => $request->bank_account_name,
                'is_active' => $request->boolean('is_active', true),
                'notes' => $request->notes,
            ]);

            DB::commit();

            // Clear cache
            Cache::forget("vendors_{$user->active_business_key}");

            return response()->json([
                'success' => true,
                'message' => 'Vendor created successfully',
                'data' => $vendor
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Vendor creation failed', [
                'user_id' => $user->id,
                'business_key' => $user->active_business_key,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create vendor',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified vendor.
     */
    public function show($id)
    {
        $user = Auth::user();

        if (!$user || !$user->active_business_key) {
            return response()->json([
                'success' => false,
                'message' => 'No active business selected.'
            ], 403);
        }

        if (!$user->hasPermission('vendor_read')) {
            return response()->json([
                'success' => false,
                'message' => 'Feature unavailable.'
            ], 403);
        }

        $vendor = Vendor::with('location')
            ->where('id', $id)
            ->where('business_key', $user->active_business_key)
            ->first();

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Vendor retrieved successfully',
            'data' => $vendor
        ]);
    }

    public function update(Request $request, $id)
    {
        
        try {
            $id = Crypt::decrypt($id);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                // 'message' => 'Invalid vendor identifier.',
                'message' =>  $id,
            ], 400);
        }
        $user = Auth::user();

        if (!$user || !$user->active_business_key) {
            return response()->json([
                'success' => false,
                'message' => 'No active business selected.',
            ], 403);
        }

        if (!$user->hasPermission('vendor_update')) {
            return response()->json([
                'success' => false,
                'message' => 'Feature unavailable.',
            ], 403);
        }

        $vendor = Vendor::where('id', $id)
            ->where('business_key', $user->active_business_key)
            ->first();

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found.',
            ], 404);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'vendor_name' => 'required|string|max:50',
            'contact_person' => 'nullable|string|max:105',
            'email' => [
                'required',
                'email',
                'max:155',
                Rule::unique('vendors')
                    ->where('business_key', $user->active_business_key)
                    ->ignore($vendor->id),
            ],
            'phone' => 'nullable|string|max:20',
            'location_id' => 'required|exists:business_locations,id',

            'address' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',

            'industry' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',

            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:255',

            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $location = Business_locations::where('id', $request->location_id)
            ->where('business_key', $user->active_business_key)
            ->first();

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid location selected.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $vendor->update([
                'location_id' => $request->location_id,
                'vendor_name' => $request->vendor_name,
                'contact_person' => $request->contact_person,
                'email' => $request->email,
                'phone' => $request->phone,

                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country ?? 'Nigeria',
                'postal_code' => $request->postal_code,

                'industry' => $request->industry,
                'tax_id' => $request->tax_id,
                'registration_number' => $request->registration_number,
                'website' => $request->website,

                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_account_name' => $request->bank_account_name,

                'is_active' => $request->boolean('is_active', $vendor->is_active),
                'notes' => $request->notes,
            ]);

            DB::commit();

            // Invalidate vendor cache for this business
            Cache::forget("vendors_{$user->active_business_key}");

            return response()->json([
                'success' => true,
                'message' => 'Vendor updated successfully',
                'data' => $vendor->fresh(),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Vendor update failed', [
                'user_id' => $user->id,
                'business_key' => $user->active_business_key,
                'vendor_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update vendor',
            ], 500);
        }
    }


    /**
     * Remove the specified vendor.
     */
    public function destroy($id)
    {
        // Validate ID format
        if (!is_string($id) || empty($id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid vendor identifier format.',
            ], 400);
        }
        try {
            $id = Crypt::decryptString($id);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid vendor identifier.',
            ], 400);
        }

        $user = Auth::user();

        if (!$user || !$user->active_business_key) {
            return response()->json([
                'success' => false,
                'message' => 'No active business selected.',
            ], 403);
        }

        if ($user->creator !== 'Host') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized role.',
            ], 403);
        }

        if (!$user->hasPermission('vendor_delete')) {
            return response()->json([
                'success' => false,
                'message' => 'Feature unavailable.',
            ], 403);
        }

        $vendor = Vendor::where('id', $id)
            ->where('business_key', $user->active_business_key)
            ->first();

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found.',
            ], 404);
        }

        try {
            $vendor->delete();

            Cache::forget("vendors_{$user->active_business_key}");

            return response()->json([
                'success' => true,
                'message' => 'Vendor deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Vendor deletion failed', [
                'user_id' => $user->id,
                'business_key' => $user->active_business_key,
                'vendor_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete vendor.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
