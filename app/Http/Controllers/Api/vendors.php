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


    public function store(Request $request)
    {
        \Log::info('Vendor creation started', ['ip' => $request->ip()]);

        try {
            $user = Auth::user();

            // Log authentication check
            if (!$user) {
                \Log::warning('Vendor creation failed: User not authenticated', [
                    'ip' => $request->ip(),
                    'session_id' => session()->getId()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.'
                ], 401);
            }

            \Log::info('User authenticated for vendor creation', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->name
            ]);

            // Check if user has an active business
            if (!$user->active_business_key) {
                \Log::warning('Vendor creation failed: No active business selected', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'active_business_key' => $user->active_business_key
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No active business selected.'
                ], 403);
            }

            \Log::info('Active business found for vendor creation', [
                'user_id' => $user->id,
                'active_business_key' => $user->active_business_key
            ]);

            // Check permission for creating vendors
            try {
                $hasPermission = $user->hasPermission('vendor_create');
                \Log::info('Permission check for vendor creation', [
                    'user_id' => $user->id,
                    'permission' => 'vendor_create',
                    'has_permission' => $hasPermission
                ]);

                if (!$hasPermission) {
                    \Log::warning('Vendor creation failed: Permission denied', [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'permission_needed' => 'vendor_create'
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Feature unavailable.'
                    ], 403);
                }
            } catch (\Exception $e) {
                \Log::error('Permission check failed for vendor creation', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Permission check failed.',
                    'error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }

            // Validate request
            \Log::info('Starting validation for vendor data', [
                'request_data' => $request->all(),
                'business_key' => $user->active_business_key
            ]);

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
                \Log::error('Vendor validation failed', [
                    'user_id' => $user->id,
                    'business_key' => $user->active_business_key,
                    'errors' => $validator->errors(),
                    'request_data' => $request->all()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validatedData = $validator->validated();
            \Log::info('Validation passed for vendor', [
                'validated_data' => $validatedData,
                'vendor_name' => $validatedData['vendor_name'],
                'email' => $validatedData['email']
            ]);

            try {
                DB::beginTransaction();
                \Log::info('Database transaction started for vendor creation');

                // Verify location belongs to current business
                \Log::info('Verifying location belongs to business', [
                    'location_id' => $request->location_id,
                    'business_key' => $user->active_business_key
                ]);

                $location = Business_locations::where('id', $request->location_id)
                    ->where('business_key', $user->active_business_key)
                    ->first();

                if (!$location) {
                    \Log::warning('Invalid location selected for vendor', [
                        'user_id' => $user->id,
                        'location_id' => $request->location_id,
                        'business_key' => $user->active_business_key
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid location selected.'
                    ], 422);
                }

                \Log::info('Location verified successfully', [
                    'location_id' => $location->id,
                    'location_name' => $location->location_name ?? 'N/A'
                ]);

                // Create vendor
                \Log::info('Attempting to create vendor', [
                    'owner_id' => $user->id,
                    'business_key' => $user->active_business_key,
                    'vendor_name' => $request->vendor_name,
                    'email' => $request->email
                ]);

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

                \Log::info('Vendor created successfully', [
                    'vendor_id' => $vendor->id,
                    'vendor_name' => $vendor->vendor_name,
                    'email' => $vendor->email,
                    'business_key' => $user->active_business_key
                ]);

                DB::commit();
                \Log::info('Database transaction committed for vendor creation');

                // Clear cache
                try {
                    Cache::forget("vendors_{$user->active_business_key}");
                    \Log::info('Cache cleared for vendors', ['business_key' => $user->active_business_key]);
                } catch (\Exception $e) {
                    \Log::warning('Failed to clear cache', [
                        'error' => $e->getMessage(),
                        'business_key' => $user->active_business_key
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Vendor created successfully',
                    'data' => $vendor
                ], 201);
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();
                \Log::error('Database error during vendor creation', [
                    'user_id' => $user->id,
                    'business_key' => $user->active_business_key,
                    'error_code' => $e->getCode(),
                    'error_message' => $e->getMessage(),
                    'sql' => $e->getSql() ?? 'N/A',
                    'bindings' => $e->getBindings() ?? [],
                    'trace' => $e->getTraceAsString()
                ]);

                // Check for duplicate entry error
                if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate entry')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A vendor with this email already exists for your business.',
                    ], 409);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Database error occurred while creating vendor.',
                    'error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Unexpected error during vendor creation', [
                    'user_id' => $user->id,
                    'business_key' => $user->active_business_key,
                    'error_type' => get_class($e),
                    'error_code' => $e->getCode(),
                    'error_message' => $e->getMessage(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'request_data' => $request->all()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create vendor due to an unexpected error.',
                    'error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Critical error in vendor store method', [
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'user_id' => Auth::id() ?? 'not_authenticated'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while processing your request.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created vendor.
     */
    // public function store(Request $request)
    // {
    //     $user = Auth::user();

    //     // Check if user is authenticated and has an active business
    //     if (!$user || !$user->active_business_key) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'No active business selected.'
    //         ], 403);
    //     }

    //     // Check permission for creating vendors
    //     if (!$user->hasPermission('vendor_create')) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Feature unavailable.'
    //         ], 403);
    //     }

    //     // Validate request
    //     $validator = Validator::make($request->all(), [
    //         'vendor_name' => 'required|string|max:100',
    //         'contact_person' => 'nullable|string|max:100',
    //         'email' => [
    //             'required',
    //             'email',
    //             'max:155',
    //             Rule::unique('vendors')->where(function ($query) use ($user) {
    //                 return $query->where('business_key', $user->active_business_key);
    //             })
    //         ],
    //         'phone' => 'nullable|string|max:20',
    //         'location_id' => 'required|exists:business_locations,id',
    //         'address' => 'nullable|string|max:150',
    //         'city' => 'nullable|string|max:100',
    //         'state' => 'nullable|string|max:100',
    //         'country' => 'nullable|string|max:100',
    //         'postal_code' => 'nullable|string|max:20',
    //         'industry' => 'nullable|string|max:100',
    //         'tax_id' => 'nullable|string|max:50',
    //         'registration_number' => 'nullable|string|max:50',
    //         'website' => 'nullable|url|max:155',
    //         'bank_name' => 'nullable|string|max:100',
    //         'bank_account_number' => 'nullable|string|max:50',
    //         'bank_account_name' => 'nullable|string|max:105',
    //         'is_active' => 'boolean',
    //         'notes' => 'nullable|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     try {
    //         DB::beginTransaction();

    //         // Verify location belongs to current business
    //         $location = Business_locations::where('id', $request->location_id)
    //             ->where('business_key', $user->active_business_key)
    //             ->first();

    //         if (!$location) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Invalid location selected.'
    //             ], 422);
    //         }

    //         // Create vendor
    //         $vendor = Vendor::create([
    //             'owner_id' => $user->id,
    //             'business_key' => $user->active_business_key,
    //             'location_id' => $request->location_id,
    //             'vendor_name' => $request->vendor_name,
    //             'contact_person' => $request->contact_person,
    //             'email' => $request->email,
    //             'phone' => $request->phone,
    //             'address' => $request->address,
    //             'city' => $request->city,
    //             'state' => $request->state,
    //             'country' => $request->country ?? 'Nigeria',
    //             'postal_code' => $request->postal_code,
    //             'industry' => $request->industry,
    //             'tax_id' => $request->tax_id,
    //             'registration_number' => $request->registration_number,
    //             'website' => $request->website,
    //             'bank_name' => $request->bank_name,
    //             'bank_account_number' => $request->bank_account_number,
    //             'bank_account_name' => $request->bank_account_name,
    //             'is_active' => $request->boolean('is_active', true),
    //             'notes' => $request->notes,
    //         ]);

    //         DB::commit();

    //         // Clear cache
    //         Cache::forget("vendors_{$user->active_business_key}");

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Vendor created successfully',
    //             'data' => $vendor
    //         ], 201);
    //     } catch (\Throwable $e) {
    //         DB::rollBack();

    //         Log::error('Vendor creation failed', [
    //             'user_id' => $user->id,
    //             'business_key' => $user->active_business_key,
    //             'error' => $e->getMessage(),
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to create vendor',
    //             'error' => config('app.debug') ? $e->getMessage() : null
    //         ], 500);
    //     }
    // }

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
                // 'message' => 'Invalid vendor identifier.'$id,
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
