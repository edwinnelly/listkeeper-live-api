<?php

namespace App\Http\Controllers;

use App\Models\Business_locations;
use App\Models\Roles;
use App\Models\Subscriptions;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $authUser = Auth::user();

            // Check if the user's ID exists in the roles table via relationship
            $userRole = $authUser->user_roles;

            if (!$userRole) {
                return redirect()->back()
                    ->with('error', 'No role assigned to this user.');
            }

            // Check role permission
            if ($userRole->users_read === 'no') {
                return redirect()->back()
                    ->with('error', 'Feature Unavailable');
            }

            $staffUsers = User::with('location')
                ->where('business_key', $authUser->active_business_key)
                ->where('creator', '!=', 'Host')
                ->get();


            $locations = $authUser->businessLocations;

            return view('users.users', compact('locations', 'staffUsers'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An unexpected error occurred: ');
        }
    }


    public function account_roles($id)
    {
        try {
            // Check if user is active and admin
            $authUser = Auth::user();

            if ($authUser->creator !== 'Host') {
                return redirect()->back()->with('error', 'Feature Unavailable');
            }

            // Decrypt the ID
            $decryptedID = Crypt::decrypt($id);

            // Validate that the user exists
            $user = User::find($decryptedID);
            if (!$user) {
                return redirect()->back()->with('error', 'Unauthorized');
            }


            // Fetch user role
            $fetch_users_roles = Roles::where('business_key', $authUser->active_business_key)
                ->where('user_id', $decryptedID)
                ->firstOrFail();

            return view('users.users_roles', compact('fetch_users_roles'));
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return redirect()->back()->with('error', 'Invalid ID format');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Role not found for this user');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An unexpected error occurred: ');
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    /**
     * Show the form for creating a new resource.
     */
    public function delete_users($id)
    {
        // Decrypt the business key
        $id = Crypt::decrypt($id);
        //users_deletes
        $fetchUser = User::where('id', $id)->firstOrFail();
        return view('users.users_deletes', compact('fetchUser'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ✅ Step 1: Validate the incoming request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:30',
            'phone_number' => 'required|string|max:24',
            'locations' => 'required|string|max:255',
            'email_address' => 'required|email|unique:users,email',
            'pwd' => 'required|string|min:8', // assumes 'pwd_confirmation' in the form
            'logo' => 'nullable|mimetypes:image/avif,image/jpeg,image/png,image/webp|max:2048',
        ]);

        try {
            //count number of users added
            $authUser = Auth::user();

            // Count the number of staff users under the same business key
            $staffUsersCount = User::where('business_key', $authUser->active_business_key)->count();

            // Fetch the current subscription for the business
            $subscription = Subscriptions::where('business_key', $authUser->active_business_key)->first();

            if ($subscription && $subscription->users <= $staffUsersCount) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'User limit reached for your current subscription plan.');
            }

            // Handle logo upload if present
            $filename = null;
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = time() . '_' . $file->getClientOriginalName();
                $stored = $file->storeAs('profile_pictures', $filename, 'public');
            }

            // ✅ Step 2: Create the user using validated data
            $user = User::create([
                'name' => $validated['name'],
                'business_key' => Auth::user()->active_business_key,
                'active_business_key' => Auth::user()->active_business_key,
                'email' => $validated['email_address'],
                'locations' => $validated['locations'],
                'phone_number' => $validated['phone_number'],
                'role' => $validated['role'],
                'creator' => 'user', // Hardcoded; change as needed
                'password' => Hash::make($validated['pwd']),
                'profile_pic' => isset($filename) ? 'profile_pictures/' . $filename : null,
            ]);

            //add users roles
            $roles = Roles::create(
                [
                    'user_id' => $user->id,
                    'business_key' => Auth::user()->active_business_key,
                    // 'location_id' => $validated['locations'],
                    'owner_id' => Auth::user()->id,
                ]
            );

            // ✅ Step 3: Do something with the user or redirect
            return redirect()->back()->with('success', 'User created successfully.');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the user: ' . $e->getMessage());
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        try {

            $authUser = Auth::user();

            // Check if the user's ID exists in the roles table via relationship
            $userRole = $authUser->user_roles;

            if (!$userRole) {
                return redirect()->back()
                    ->with('error', 'No role assigned to this user.');
            }

            // Check role permission
            if ($userRole->users_read === 'no') {
                return redirect()->back()
                    ->with('error', 'Feature Unavailable');
            }

            // Decrypt the ID if it's encrypted
            $id = Crypt::decrypt($id);

            // Fetch the user by ID
            $user = User::findOrFail($id);

            $locations = $authUser->businessLocations;

            //  dd($user);


            // Return the view with the user data
            return view('users.users_edit', compact('user', 'locations'));
        } catch (\Exception $e) {
            return redirect()->route('users.index')->with('error', 'User not found.');
        }
    }






    public function setPermssions(Request $request, string $id)
    {
        $authUser = Auth::user();

        $userRole = $authUser->user_roles;

        if (!$userRole || $userRole->permission === 'no' || $authUser->creator !== 'Host') {
            return redirect()->back()->with('error', 'Feature Unavailable');
        }

        try {
            DB::beginTransaction();

            $role = Roles::findOrFail($id); // findOrFail already throws if not found

            // 4. Assign permissions from checkboxes
            $permissions = [
                'users_create',
                'users_update',
                'users_delete',
                'users_read',
                'locations_create',
                'locations_update',
                'locations_delete',
                'locations_read',
            ];

            foreach ($permissions as $perm) {
                $role->$perm = $request->has($perm) ? 'yes' : 'no';
            }

            $role->save();

            DB::commit();

            return redirect()->back()->with('success', 'User permissions updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update user permissions', [
                'role_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'An error occurred. Please try again.');
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {

            $authUser = Auth::user();

            // Check if the user's ID exists in the roles table via relationship
            $userRole = $authUser->user_roles;

            if (!$userRole) {
                return redirect()->back()
                    ->with('error', 'No role assigned to this user.');
            }

            // Check role permission
            if ($userRole->users_read === 'no') {
                return redirect()->back()
                    ->with('error', 'Feature Unavailable');
            }

            // Decrypt the ID if it's encrypted
            $id = Crypt::decrypt($id);

            // Fetch the user by ID
            $user = User::findOrFail($id);

            $locations = $authUser->businessLocations;

            // Return the view with the user data
            return view('users.users_update', compact('user', 'locations'));
        } catch (\Exception $e) {
            return redirect()->route('users.account.users')->with('error', 'User not found.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Decrypt the user ID
            $id = Crypt::decrypt($id);

            // Get the currently authenticated user
            $authUser = Auth::user();

            // Prevent the authenticated user from deleting themselves
            if ($authUser->id === $id) {
                return redirect()->route('users.account.users')->with('error', 'You cannot delete your own account.');
            }

            // Set the fallback manager ID to the authenticated user (who is performing the delete)
            $fallbackManagerId = $authUser->id;

            // Check if the fallback manager (authenticated user) exists in the database
            if (User::where('id', $fallbackManagerId)->exists()) {
                // Reassign all business locations managed by the user being deleted
                // to the fallback manager
                Business_locations::where('manager_id', $id)->update([
                    'manager_id' => $fallbackManagerId
                ]);
            }
            // Retrieve and delete the user
            $user = User::findOrFail($id);
            $user->delete();

            // Redirect back with success message
            return redirect()->route('users.account.users')->with('success', 'User deleted successfully.');
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Handle error if the encrypted ID is invalid
            return redirect()->route('users.account.users')->with('error', 'Invalid user ID.');
        } catch (\Exception $e) {
            // Log any unexpected errors and redirect with error message
            Log::error("User deletion error for ID {$id}: " . $e->getMessage());
            return redirect()->route('users.account.users')->with('error', 'An error occurred while deleting the user.');
        }
    }




    public function updatedProfile(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // Decrypt ID
            $data = Crypt::decrypt($id);
            $user = User::findOrFail($data['id']);

            // Validate form data
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,

                'phone_number' => 'nullable|string|max:20',
                'age' => 'nullable|date',
                'address' => 'nullable|string',
                'city' => 'nullable|string|max:100',

                'emergency' => 'nullable|string|max:100',
                'role' => 'nullable|string|max:200',
                'locations' => 'nullable|string|max:20',
                'is_active' => 'nullable|string|max:20',

                'state' => 'nullable|string|max:100',
                'zip' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:100',
                'about' => 'nullable|string|max:1000',
                'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:550',
                'current_password' => 'nullable|string',
                'password' => 'nullable|string|min:8|confirmed',
            ]);

            // Handle profile photo upload with old image deletion
            if ($request->hasFile('profile_photo')) {

                // ✅ Delete old photo if it exists
                if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                    Storage::disk('public')->delete($user->profile_photo);
                }

                // ✅ Store new photo
                $path = $request->file('profile_photo')->store('profile_pictures', 'public');
                $user->profile_photo = $path;
            }


            // Update password if needed
            if ($request->filled('current_password') && $request->filled('password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return back()->withErrors(['current_password' => 'Current password is incorrect.']);
                }
                $user->password = Hash::make($request->password);
            }

            // Update other user details
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone_number = $request->phone_number;
            $user->age = $request->age;
            $user->address = $request->address;
            $user->city = $request->city;
            $user->state = $request->state;
            $user->postal_code = $request->zip;
            $user->country = $request->country;
            $user->about = $request->about;
            $user->emergency = $request->emergency;
            $user->role = $request->role;
            $user->locations = $request->locations;
            $user->is_active = $request->is_active;


            $user->save();

            DB::commit();

            return redirect()->back()->with('success', 'Profile updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Profile update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while updating the profile.');
        }
    }
}
