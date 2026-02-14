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


use Illuminate\Support\Facades\Cache;

class Businesslist extends Controller
{
    /**
     * Fetch all businesses linked to the logged-in account
     */


    public function index()
    {
        try {
            $userId = Auth::id();

            $total = Business_list::where('owner_id', $userId)->count();
            $threshold = 1000;

            if ($total <= $threshold) {
                $businesses = Business_list::where('owner_id', $userId)
                    ->orderBy('id')
                    ->get()
                    ->each(function ($business) {
                        $business->ekey = Crypt::encrypt($business->id);
                    });

                return response()->json([
                    'status'  => true,
                    'message' => 'Business list fetched successfully',
                    'data'    => $businesses,
                ], 200);
            }

            return response()->stream(function () use ($userId) {
                echo '[';
                $first = true;

                Business_list::where('owner_id', $userId)
                    ->orderBy('id')
                    ->chunkById(1000, function ($businesses) use (&$first) {
                        foreach ($businesses as $business) {
                            $business->ekey = Crypt::encrypt($business->id);

                            if (! $first) {
                                echo ',';
                            }

                            echo json_encode($business);
                            $first = false;
                        }
                    });

                echo ']';
            }, 200, [
                'Content-Type'  => 'application/json',
                'Cache-Control' => 'no-cache',
            ]);
        } catch (\Throwable $e) {
            Log::error('Business index error', ['exception' => $e]);

            return response()->json([
                'status'  => false,
                'message' => 'An error occurred while fetching business data.',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Validate request data
        $validated = $request->validate([
            'business_name' => 'required|string|max:255|unique:business_lists,business_name,NULL,id,owner_id,' . Auth::id(),
            'address' => 'required|string|max:500',
            'website' => 'nullable|url|max:255',
            'phone' => 'required|string|max:20',
            'slug' => 'required|string|max:255',
            'about_business' => 'nullable|string',
            'country' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'subscription_plan' => 'required|string|max:100',
            'currency' => 'required|string|max:10',
            'language' => 'required|string|max:50',
            'industry_type' => 'required|string|max:150',
            'logo' => 'nullable|mimetypes:image/avif,image/jpeg,image/png,image/webp|max:2048',
        ]);

        try {
            $user = Auth::user(); // Get the logged-in user info

            // Check business limit
            $count_business = Business_list::where('owner_id', Auth::id())->count();
            if ($count_business >= 5) {
                return response()->json([
                    'status' => false,
                    'message' => 'Business limit reached, max allowed is 5'
                ], 412);
            }
            // Check if user has a tier account

            DB::beginTransaction();

            // Create unique business key
            $business_key = (string) Str::uuid();

            // Create business
            $business = new Business_list($validated);
            $business->owner_id = Auth::id();
            $business->business_key = $business_key;

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $file->storeAs('business_logo', $filename, 'public');
                $business->logo = 'business_logo/' . $filename;
            }

            $business->save();

            // Create default location
            $default_location = new Business_locations();
            $default_location->business_key = $business_key;
            $default_location->owner_id = Auth::id();
            $default_location->location_name = 'Main Branch';
            $default_location->head_office = 'yes';
            $default_location->manager_id = Auth::id();
            $default_location->phone = $validated['phone'];
            $default_location->location_id = Str::uuid()->toString();

            $default_location->save();

            // Create subscription
            $subscription = new Subscriptions();
            $subscription->owner_id = Auth::id();
            $subscription->business_key = $business_key;
            $subscription->plan_name = 'Basic';
            $subscription->plan_code = 'basic';
            $subscription->amount = 0;
            $subscription->start_date = now();
            $subscription->end_date = now()->addDays(7);
            $subscription->save();

            // If role does not exist, create it
            $role = new Roles();
            $role->owner_id = Auth::id();
            $role->user_id = Auth::id();
            $role->business_key = $business_key;

            // Optional: grant admin full access if this is the business owner
            $role->permission = 'yes';
            $role->users_create = 'yes';
            $role->users_read = 'yes';
            $role->users_update = 'yes';
            $role->users_delete = 'yes';
            $role->subscriptions_read = 'yes';
            $role->subscriptions_update = 'yes';
            $role->locations_create = 'yes';
            $role->locations_read = 'yes';
            $role->locations_update = 'yes';
            $role->locations_delete = 'yes';
            $role->locations_analytics = 'yes';
            $role->category_create = 'yes';
            $role->category_read = 'yes';
            $role->category_update = 'yes';
            $role->category_delete = 'yes';
            $role->product_create = 'yes';
            $role->product_read = 'yes';
            $role->product_update = 'yes';
            $role->product_delete = 'yes';
            $role->unit_create = 'yes';
            $role->unit_read = 'yes';
            $role->unit_update = 'yes';
            $role->unit_delete = 'yes';
            $role->vendor_create = 'yes';
            $role->vendor_read = 'yes';
            $role->vendor_update = 'yes';
            $role->vendor_delete = 'yes';
            $role->purchase_create = 'yes';
            $role->purchase_read = 'yes';
            $role->purchase_update = 'yes';
            $role->purchase_delete = 'yes';
            $role->customer_create = 'yes';
            $role->customer_read = 'yes';
            $role->customer_update = 'yes';
            $role->customer_delete = 'yes';
            $role->credit_note_create = 'yes';
            $role->credit_note_read = 'yes';
            $role->credit_note_update = 'yes';
            $role->credit_note_delete = 'yes';
            $role->expense_create = 'yes';
            $role->expense_read = 'yes';
            $role->expense_update = 'yes';
            $role->expense_delete = 'yes';
            $role->invoice_create = 'yes';
            $role->invoice_read = 'yes';
            $role->invoice_update = 'yes';
            $role->invoice_delete = 'yes';
            $role->pos_create = 'yes';
            $role->pos_read = 'yes';
            $role->pos_update = 'yes';
            $role->pos_delete = 'yes';
            $role->save();

            if ($user->account_tier === 'no') {
                //update the user account with free tier
                User::where('id', Auth::id())
                    ->update(['account_tier' => 'yes']);

                Subscriptions::where('owner_id', Auth::id())->where('business_key', $business_key)
                    ->update(['status' => 'active', 'end_date' => now()->addDays(365), 'users' => 5, 'products' => 100, 'locations' => 5, 'invoice' => 500, 'transaction_id' => rand(123456789, 1234567890)]);

                Business_list::where('owner_id', Auth::id())->where('business_key', $business_key)->update(['status' => 'active', 'subscription_type' => 'Basic']);
                //add roles and permission
                // if (!$existingRole) {
                //     Roles::create([
                //         'owner_id' => Auth::id(),
                //         'user_id' => Auth::id(),
                //         'business_key' => $business_key
                //     ]);
                // }

            }


            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Business created successfully',
                'data' => $business
                // 'datas' => $ren,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in Businesslist@store: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the business',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    //get the business info
    public function business_details($id)
    {
        try {
            // Decrypt business key if encrypted
            $decryptedId = Crypt::decrypt($id); // remove Crypt if not encrypted

            // Check if business exists for the authenticated user
            $business = Business_list::where('owner_id', Auth::id())
                ->where('id', $decryptedId)
                ->first();

            if (!$business) {
                return response()->json([
                    'message' => 'Business not found.'
                ], 404);
            }

            // Count locations
            $totalLocations = Business_locations::where('owner_id', Auth::id())
                ->where('business_key', $business->business_key)
                ->count();

            //  $totalUsers = Users::where('active_business_key', $business->business_key)
            // ->count();

            $activeLocations = Business_locations::where('owner_id', Auth::id())
                ->where('business_key', $business->business_key)
                ->where('status', 'active')
                ->count();

            $inactiveLocations = Business_locations::where('owner_id', Auth::id())
                ->where('business_key', $business->business_key)
                ->where('status', 'inactive')
                ->count();

            // Get subscription
            $subscription = Subscriptions::where('owner_id', Auth::id())
                ->where('business_key', $business->business_key)
                ->first();

            // Return JSON in expected structure
            return response()->json([
                'business' => [
                    'id' => $business->id,
                    'name' => $business->business_name,
                    'logo' => $business->logo,
                    'bussiness_key' => $business->business_key,
                    'slug' => $business->slug,
                    'industry_type' => $business->industry_type,
                    'website' => $business->website,
                    'phone' => $business->phone,
                    'state' => $business->state,
                    'city' => $business->city,
                    'country' => $business->country,
                    'address' => $business->address,
                    'subscription' => $business->subscription,
                    'currency' => $business->currency,
                    'about_business' => $business->about_business,
                    'plan_name' => $business->plan_name,

                    'created_at' => $business->created_at->format('Y-m-d'),
                    'status' => $business->status,
                    'subscription_type' => $business->subscription_type,


                    'description' => $business->description,
                    'stats' => [
                        'totalLocations' => $totalLocations,
                        'activeLocations' => $activeLocations,
                        'inactiveLocations' => $inactiveLocations,
                        // 'totalusers' => $totalUsers,
                    ],
                    'details' => [
                        // 'website' => $business->website ?? '',
                        'country' => $business->country ?? '',
                        'currency' => $business->currency ?? '',
                        'businessType' => $business->industry_type ?? '',

                        'address' => $business->address ?? '',
                        // 'creation' => $business->created_at ? $business->created_at->format('F j, Y') : '',
                    ],

                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Business fetch error: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while fetching business.'
            ], 500);
        }
    }


    //switch business
    public function switchBusiness(string $id): JsonResponse
    {
        try {
            // Ensure the user is authenticated
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unauthorized. Please log in.',
                ], 401);
            }

            // Find the business that belongs to the authenticated user
            $business = Business_list::where('owner_id', $user->id)
                ->where('business_key', $id)
                ->first();

            if (!$business) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Business not found.',
                ], 404);
            }

            // Update the user's active business
            $user->update([
                'active_business_key' => $id,
                'business_key' => $id,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Business switched successfully.',
                'active_business_key' => $id,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'An unexpected error occurred while switching business.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    // public function updatebusiness(Request $request, $id)
    // {
    //     try {
    //         // 🔐 Decrypt ID if encrypted
    //         $decryptedId = Crypt::decrypt($id); // remove if ID isn't encrypted

    //         // 🔍 Find the business that belongs to the logged-in user
    //         $business = Business_list::where('owner_id', Auth::id())
    //             ->where('id', $decryptedId)
    //             ->first();

    //         if (!$business) {
    //             return response()->json([
    //                 'message' => 'Business not found.'
    //             ], 404);
    //         }

    //         // ✅ Validate incoming data
    //         $validatedData = $request->validate([
    //             'business_name' => 'sometimes|string|max:255',
    //             'industry_type' => 'sometimes|string|max:255',
    //             'website' => 'nullable|url|max:255',
    //             'phone' => 'nullable|string|max:20',
    //             'state' => 'nullable|string|max:255',
    //             'city' => 'nullable|string|max:255',
    //             'country' => 'nullable|string|max:255',
    //             'address' => 'nullable|string|max:500',
    //             'currency' => 'nullable|string|max:10',
    //             'about_business' => 'nullable|string',
    //             'description' => 'nullable|string',
    //             'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    //             // 'logo' => 'nullable', // 👈 changed
    //         ]);

    //         // 🖼️ Handle logo upload (if provided)
    //         if ($request->hasFile('logo')) {
    //             $file = $request->file('logo');
    //             $path = $file->store('business_logo', 'public');
    //             $validatedData['logo'] = $path;
    //         }

    //         // 💾 Update business
    //         $business->update($validatedData);

    //         return response()->json([
    //             'message' => 'Business updated successfully.',
    //             'business' => $business
    //         ], 200);
    //     } catch (\Exception $e) {
    //         Log::error('Business update error: ' . $e->getMessage());

    //         return response()->json([
    //             'message' => 'An error occurred while updating the business.'
    //         ], 500);
    //     }
    // }

    public function updatebusiness(Request $request, $id)
    {
        try {
            // Decrypt ID if encrypted
            $decryptedId = Crypt::decrypt($id);

            // 🔍 Find the business that belongs to the logged-in user
            $business = Business_list::where('owner_id', Auth::id())
                ->where('id', $decryptedId)
                ->first();

            if (!$business) {
                return response()->json([
                    'message' => 'Business not found.'
                ], 404);
            }

            // ✅ Validate incoming data
            $validatedData = $request->validate([
                'business_name' => 'sometimes|string|max:255',
                'industry_type' => 'sometimes|string|max:255',
                'website' => 'nullable|url|max:255',
                'phone' => 'nullable|string|max:20',
                'state' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'currency' => 'nullable|string|max:10',
                'about_business' => 'nullable|string',
                'description' => 'nullable|string',
                'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);

            // 🖼️ Handle logo upload (if provided)
            if ($request->hasFile('logo')) {
                // 🧹 Remove previous logo if it exists
                if ($business->logo && Storage::disk('public')->exists($business->logo)) {
                    Storage::disk('public')->delete($business->logo);
                }

                // 📤 Upload new logo
                $file = $request->file('logo');
                $path = $file->store('business_logo', 'public');
                $validatedData['logo'] = $path;
            }

            // 💾 Update business record
            $business->update($validatedData);

            return response()->json([
                'message' => 'Business updated successfully.',
                'business' => $business
            ], 200);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return response()->json([
                'message' => 'Invalid business ID.'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Business update error: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while updating the business.'
            ], 500);
        }
    }




    public function deleteBusiness(Request $request, $id)
    {
        try {
            //Decrypt ID if you encrypt IDs in URLs
            $decryptedId = Crypt::decrypt($id);

            // Find business owned by the logged-in user
            $business = Business_list::where('owner_id', Auth::id())
                ->where('id', $decryptedId)
                ->first();

            if (!$business) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Business not found or unauthorized.',
                ], 404);
            }

            // 🖼️ Delete logo file if it exists
            if ($business->logo && Storage::exists($business->logo)) {
                Storage::delete($business->logo);
            }

            //Delete the business record
            $business->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Business and its logo deleted successfully.',
            ], 200);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid business ID provided.',
            ], 400);
        } catch (\Exception $e) {
            Log::error('Business deletion error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while deleting the business.',
            ], 500);
        }
    }


    // public function suspendBusiness(Request $request, $id)
    // {
    //     try {
    //         // Decrypt ID if you encrypt IDs
    //         $decryptedId = Crypt::decrypt($id);

    //         // 🔍 Find the business owned by the logged-in user
    //         $business = Business_list::where('owner_id', Auth::id())
    //             ->where('id', $decryptedId)
    //             ->first();

    //         if (!$business) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Business not found or unauthorized.',
    //             ], 404);
    //         }

    //         // ✅ Validate input
    //         $validated = $request->validate([
    //             'status' => 'required|string|in:active,inactive,suspended',

    //         ]);

    //         // 📝 Update business status and optional reason
    //         $business->status = $validated['status'];

    //         $business->save();

    //         return response()->json([
    //             'status' => 'success',

    //         ], 200);
    //     } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Invalid business ID provided.',
    //         ], 400);
    //     } catch (\Exception $e) {
    //         Log::error('Business suspend error: ' . $e->getMessage());

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'An error occurred while updating business status.',
    //         ], 500);
    //     }
    // }


    public function suspendBusiness(Request $request, $id)
    {
        try {
            //Decrypt the business ID (remove if you’re not encrypting IDs)
            $businessId = Crypt::decrypt($id);

            //Find the business that belongs to the authenticated user
            $business = Business_list::where('owner_id', Auth::id())
                ->where('id', $businessId)
                ->first();

            if (!$business) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Business not found or unauthorized.',
                ], 404);
            }

            //Validate the status input
            $validated = $request->validate([
                'status' => 'required|string|in:active,inactive,suspended',
            ]);

            //Update the status
            $business->update(['status' => $validated['status']]);

            return response()->json([
                'status' => 'success',
                'message' => "Business status updated to '{$validated['status']}'.",
                'business' => $business,
            ], 200);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid business ID provided.',
            ], 400);
        } catch (\Exception $e) {
            Log::error('Business suspend error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while updating business status.',
            ], 500);
        }
    }
}
