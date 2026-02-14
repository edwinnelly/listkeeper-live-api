<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Business_list; // make sure model is imported
use App\Models\Business_locations;
use App\Models\Roles;
use App\Models\Subscriptions;
use Illuminate\Support\Str;
use App\Models\User;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;


class LocationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        //Check if user is authenticated and has an active business
        if (!$user || !$user->active_business_key) {
            return response()->json([
                'error' => 'No active business selected.'
            ], 403);
        }

        //Check permission using your method
        if (!$user->hasPermission('locations_read')) {
            return response()->json(['error' => 'Feature Unavailable.'], 403);
        }

        //Fetch business locations linked to the user’s active business key
        $locations = Business_locations::with('user', 'business')
            ->where('business_key', $user->active_business_key)
            ->get();

        $staffs = User::where('active_business_key', $user->active_business_key)
            ->get();

        //Return JSON response instead of view
        return response()->json([
            'locations' => $locations,
            'staffs' => $staffs,
        ]);
    }


    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = auth()->user();
            $businessKey = $user->active_business_key;
            $ownerId = $user->id;

            if (!$businessKey) {
                return response()->json(['error' => 'No active business selected.'], 403);
            }

            $subscription = \App\Models\Subscriptions::where('business_key', $businessKey)
                ->lockForUpdate()
                ->first();

            if (!$subscription) {
                return response()->json(['error' => 'No subscription found for this business.'], 404);
            }

            $locationCount = \App\Models\Business_locations::where('business_key', $businessKey)->count();

            if ($locationCount >= $subscription->locations) {

                return response()->json([
                    'message' => "Maximum {$subscription->locations} locations allowed for your subscription."
                ], 403);
            }

            $validated = $request->validate([
                // 'location_status' => 'nullable|in:on,off',
                'location_name'   => 'required|string|max:255',
                'address'         => 'required|string|max:255',
                'city'            => 'required|string|max:255',
                'state'           => 'required|string|max:30',
                'phone'           => 'required|string|max:20',
                'country'         => 'required|string|max:100',
                'postal_code'     => 'nullable|string|max:20',

            ]);

            $validated['owner_id']     = $ownerId;
            $validated['business_key'] = $businessKey;
            $validated['location_id']  = Str::uuid()->toString();
            $validated['manager_id']  = $ownerId;
            $locationName = trim(strtolower($validated['location_name']));

            $duplicate = \App\Models\Business_locations::where('business_key', $businessKey)
                ->whereRaw('LOWER(TRIM(location_name)) = ?', [$locationName])
                ->exists();

            if ($duplicate) {
                return response()->json(['error' => 'This location already exists for your business.'], 409);
            }

            $location = \App\Models\Business_locations::create($validated);

            DB::commit();

            return response()->json([
                'message' => 'Location created successfully!',
                'location' => $validated
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Location creation failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'An unexpected error occurred while creating the location.'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'location_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            // 'status' => 'required|in:active,inactive,pending',
            'staffs' => 'exists:users,id',
            // 'manager_id' => 'required|exists:users,id',
        ]);

        try {
            DB::beginTransaction();


            $location = Business_locations::findOrFail($id);

            $authUser = Auth::user();

            // Example business rule: only host users can update
            if ($authUser->creator !== 'Host') {
                return response()->json([
                    'message' => 'Unauthorized: You do not have permission to update this location.',
                ], 403);
            }

            $location->update([
                'location_name' => $request->location_name,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'country' => $request->country,
                // 'status' => $request->status,
                'manager_id' => $request->staffs,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Business location updated successfully.',
                'data' => $location,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Location not found.',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong while updating the business location.',
                'error' => $e->getMessage(), // you can hide this in production
            ], 500);
        }
    }


    public function destroy(Business_locations $id)
    {
        try {
            $id->delete();

            return response()->json([
                'success' => true,
                'message' => 'Location deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the location.'
            ], 500);
        }
    }
}
