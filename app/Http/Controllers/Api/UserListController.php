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

class UserListController extends Controller
{

    public function index()
    {
        $user = auth()->user();

        if (!$user || !$user->active_business_key) {
            return response()->json([
                'error' => 'No active business selected.'
            ], 403);
        }

        // Fetch locations
        $locations = Business_locations::where('business_key', $user->active_business_key)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($location) {
                $location->id = Crypt::encrypt($location->id);
                return $location;
            });

        // Fetch staff + add extra user_id field
        $staffs = User::with('location')
            ->where('business_key', $user->active_business_key)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($staff) use ($user) {
                $staff->id = ($staff->id);          // encrypted staff ID
                $staff->user_id = Crypt::encrypt($staff->id);      // encrypted authenticated user ID
                // $staff->user_id = Crypt::encrypt($user->id);      // encrypted authenticated user ID
                return $staff;
            });

        return response()->json([
            // 'locations' => $locations,
            'users'     => $staffs,
        ]);
    }


    public function users_locations($id)
    {
        $user = auth()->user();

        if (!$user || !$user->active_business_key) {
            return response()->json([
                'error' => 'No active business selected.'
            ], 403);
        }

        // Fetch locations
        $locations = Business_locations::where('business_key', $user->active_business_key)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($location) {
                $location->id = Crypt::encrypt($location->id);
                return $location;
            });

        // Fetch staff + add extra user_id field
        $staffs = User::with('location')
            ->where('business_key', $user->active_business_key)->where('locations', $id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($staff) use ($user) {
                $staff->id = ($staff->id);          // encrypted staff ID
                $staff->user_id = Crypt::encrypt($staff->id);      // encrypted authenticated user ID
                // $staff->user_id = Crypt::encrypt($user->id);      // encrypted authenticated user ID
                return $staff;
            });

        return response()->json([
            // 'locations' => $locations,
            'users'     => $staffs,
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'state'        => 'required|string|max:150',
            'city'        => 'required|string|max:100',
            'country'        => 'required|string|max:150',
            'about'        => 'required|string|max:350',
            'role'        => 'required|string|max:55',
            'location_id'   => 'required|integer|exists:business_locations,id',
            'phone'       => 'required|string|max:30',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:6',
            'address'     => 'required|string|min:10',
            'photo'        => 'nullable|mimetypes:image/avif,image/jpeg,image/png,image/webp|max:2048',
        ]);

        $loggedInUser = Auth::user();

        // Count staff in the same business
        $staffUsersCount = User::where('business_key', $loggedInUser->active_business_key)->count();

        // Fetch business subscription
        $subscription = Subscriptions::where('business_key', $loggedInUser->active_business_key)->first();

        if ($subscription && $subscription->users <= $staffUsersCount) {
            return response()->json([
                'success' => false,
                'message' => 'User limit reached for your current subscription plan.',
            ], 403);
        }

        try {
            // Handle profile picture
            $logoPath = null;
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $file->storeAs('profile_pictures', $filename, 'public');
                $logoPath = 'profile_pictures/' . $filename;
            }

            $newUser = User::create([
                'name'                => $validated['name'],
                'state'                => $validated['state'],
                'country'                => $validated['country'],
                'about'                => $validated['about'],
                'city'                => $validated['city'],
                'email'               => $validated['email'],
                'creator'             => 'user',
                'phone_number'        => $validated['phone'],
                'role'                => $validated['role'],
                'address'             => $validated['address'],
                'business_key'        => $loggedInUser->active_business_key,
                'active_business_key' => $loggedInUser->active_business_key,
                'locations'           => $validated['location_id'],
                'password'            => bcrypt($validated['password']),
                'profile_pic'                => $logoPath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data'    => $newUser
            ], 201);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $loggedInUser = Auth::user();

        // Ensure the user being updated belongs to the same business
        if ($user->business_key !== $loggedInUser->active_business_key) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this user.'
            ], 403);
        }

        // Validation rules
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'state'       => 'required|string|max:150',
            'city'        => 'required|string|max:100',
            'country'     => 'required|string|max:150',
            'about'       => 'required|string|max:350',
            'role'        => 'required|string|max:55',
            'location_id' => 'required|integer|exists:business_locations,id',
            'phone'       => 'required|string|max:30',
            'email'       => 'required|email|unique:users,email,' . $user->id,
            'password'    => 'nullable|string|min:6',
            'address'     => 'required|string|min:10',
            'photo'       => 'nullable|mimetypes:image/avif,image/jpeg,image/png,image/webp|max:2048',
        ]);

        try {
            // Handle profile picture upload if provided
            if ($request->hasFile('photo')) {

                // Delete old profile picture if exists
                if ($user->profile_pic && Storage::disk('public')->exists($user->profile_pic)) {
                    Storage::disk('public')->delete($user->profile_pic);
                }

                $file = $request->file('photo');
                $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $file->storeAs('business_logo', $filename, 'public');

                $user->profile_pic = 'business_logo/' . $filename;
            }

            // Update user fields
            $user->name                 = $validated['name'];
            $user->state                = $validated['state'];
            $user->city                 = $validated['city'];
            $user->country              = $validated['country'];
            $user->about                = $validated['about'];
            $user->phone_number         = $validated['phone'];
            $user->role                 = $validated['role'];
            $user->address              = $validated['address'];
            $user->locations            = $validated['location_id'];
            $user->email                = $validated['email'];

            // Update password only if provided
            if (!empty($validated['password'])) {
                $user->password = bcrypt($validated['password']);
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data'    => $user
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function show($id)
    {
        try {
            $id = Crypt::decrypt($id);

            $user = User::with('location')->findOrFail($id);
            $user->id = Crypt::encrypt($user->id);

            return response()->json([
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Invalid Encrypted ID',
                'details' => $e->getMessage(),
            ], 400);
        }
    }

    public function roles($id)
    {
        try {
            // Decrypt incoming encrypted user ID
            $userId = Crypt::decrypt($id);

            // Fetch roles using where(...)->firstOrFail()
            $roles = Roles::where('user_id', $userId)->firstOrFail();

            // Encrypt role id before sending back
            $roles->id = Crypt::encrypt($roles->id);

            return response()->json([
                'data' => $roles,
                'id' =>  $userId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Invalid Encrypted ID or User Roles Not Found',
                'details' => $e->getMessage(),

            ], 400);
        }
    }


    public function updateRoles(Request $request, $id)
    {
        try {
            // Decrypt incoming encrypted user ID
            $userId = Crypt::decrypt($id);
            $loggedInUser = Auth::user();
            // Fetch the roles record for the user
            $roles = Roles::where('user_id', $userId)->where('business_key',$loggedInUser->active_business_key)->firstOrFail();

            // Update roles from the request
            $permissions = [
                // Users roles
                'users_create',
                'users_read',
                'users_update',
                'users_delete',

                // Subscriptions
                'subscriptions_read',
                'subscriptions_update',

                // Locations roles
                'locations_create',
                'locations_read',
                'locations_update',
                'locations_delete',
                'locations_analytics',

                // Product category roles
                'category_create',
                'category_read',
                'category_update',
                'category_delete',

                // Product roles
                'product_create',
                'product_read',
                'product_update',
                'product_delete',

                // Unit roles
                'unit_create',
                'unit_read',
                'unit_update',
                'unit_delete',

                // Vendor roles
                'vendor_create',
                'vendor_read',
                'vendor_update',
                'vendor_delete',

                // Purchase roles
                'purchase_create',
                'purchase_read',
                'purchase_update',
                'purchase_delete',

                // Customer roles
                'customer_create',
                'customer_read',
                'customer_update',
                'customer_delete',

                // Credit note roles
                'credit_note_create',
                'credit_note_read',
                'credit_note_update',
                'credit_note_delete',

                // Expenses roles
                'expense_create',
                'expense_read',
                'expense_update',
                'expense_delete',

                // Invoice roles
                'invoice_create',
                'invoice_read',
                'invoice_update',
                'invoice_delete',

                // POS roles
                'pos_create',
                'pos_read',
                'pos_update',
                'pos_delete'
            ];

            foreach ($permissions as $permission) {
                if ($request->has($permission)) {
                    $roles->$permission = $request->$permission;
                }
            }

            // Save changes
            $roles->save();

            // Encrypt role id before returning (optional)
            $roles->id = Crypt::encrypt($roles->id);

            return response()->json([
                'message' => 'Permissions updated successfully',
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update permissions',
                'details' => $e->getMessage(),
            ], 400);
        }
    }


    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'message' => 'User deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete user',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
