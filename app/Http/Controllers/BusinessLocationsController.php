<?php

namespace App\Http\Controllers;

use App\Models\Business_list;
use App\Models\Business_locations;
use App\Models\Subscriptions;
use App\Models\User;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;


class BusinessLocationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index()
    {

        $user = auth()->user();

        // Guard clause: Ensure user is authenticated and has an active business key
        if (!$user || !$user->active_business_key) {
            return redirect()->back()->with('error', 'No active business selected.');
        }

        // Fetch user's role via relationship
        $userRole = $user->user_roles;

        // Guard clause: Check read permission for users
        if ($userRole && $userRole->locations_read === 'no') {
            return redirect()->back()->with('error', 'Feature Unavailable.');
        }

        // Fetch paginated business locations
        $locations = Business_locations::with('user')
            ->where('business_key', $user->active_business_key)
            ->latest()
            ->paginate(10);


        $owners = User::all(); // or filter by role
        $businesses = Business_list::all();

        //fetch all the users linked with key
        $get_users = User::all()->where('active_business_key', $user->active_business_key);

        return view('locations.locations', compact('locations', 'owners', 'businesses', 'get_users'));
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $owners = User::all(); // or filter by role
        $businesses = Business_list::all();

        return view('locations.form', compact('owners', 'businesses'));
    }


    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $businessKey = auth()->user()->active_business_key;
            $ownerId = auth()->id();

            $subscription = Subscriptions::where('business_key', $businessKey)
                ->lockForUpdate()
                ->first();

            if (!$subscription) {
                return redirect()->back()->with('error', 'No subscription found for this business');
            }

            $locationCount = Business_locations::where('business_key', $businessKey)->count();

            if ($locationCount >= $subscription->locations) {
                return redirect()->back()->with('error', "Maximum {$subscription->locations} locations allowed for your subscription");
            }

            $validated = $request->validate([
                'location_status' => 'nullable|in:on,off',
                'location_name'   => 'required|string|max:255',
                'address'         => 'required|string|max:255',
                'city'            => 'required|string|max:255',
                'state'           => 'required|string|max:30',
                'phone'           => 'required|string|max:20',
                'country'         => 'required|string|max:100',
                'postal_code'     => 'nullable|string|max:20',
            ]);

            // Safely decrypt manager ID
            try {
                $decryptedUserId = Crypt::decrypt($request->input('manager'));
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                return redirect()->back()->withInput()->with('error', 'Invalid manager selection. Please try again.');
            }

            $validated['owner_id']     = $ownerId;
            $validated['business_key'] = $businessKey;
            $validated['location_id']  = Str::uuid()->toString();
            $validated['manager_id']   = $decryptedUserId;

            $locationName = trim(strtolower($validated['location_name']));

            $duplicate = Business_locations::where('business_key', $businessKey)
                ->whereRaw('LOWER(TRIM(location_name)) = ?', [$locationName])
                ->exists();

            if ($duplicate) {
                return redirect()->back()->withInput()->with('error', 'This location already exists for your business.');
            }

            Business_locations::create($validated);

            DB::commit();

            return redirect()->back()->with('success', 'Location created successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Location creation failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withInput()->with('error', 'An unexpected error occurred while creating the location.');
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $authUser = Auth::user();
            // Decrypt the location ID
            $id = Crypt::decrypt($id);

            $businessLocation = Business_locations::with('business')->with('user')
                ->where('status', 'active')
                ->where('id', $id)
                ->first();

            if (!$businessLocation) {
                return redirect()->back()->with('error', 'Business location not found or inactive.');
            }
            //get the users
            $managers = User::where('active_business_key', $authUser->active_business_key)->where('creator', 'user')->get();

            return view('locations.locations_edit', compact('businessLocation', 'managers'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Invalid or corrupted location.');
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Business_locations $business_locations)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
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
            'status' => 'required|in:active,inactive,pending',
            'manager_id' => 'required|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $id = Crypt::decrypt($id);
            $location = Business_locations::findOrFail($id);

            // Get the currently authenticated user
            $authUser = Auth::user();

            // Prevent the authenticated user from deleting themselves
            if ($authUser->creator != 'Host') {
                return redirect()
                    ->route('location.accounts.show', Crypt::encrypt($location->id))
                    ->with('error', 'Something went wrong while updating the business location.');
            }

            $location->update([
                // 'location_name' => $request->location_name,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'country' => $request->country,
                'status' => $request->status,
                'manager_id' => $request->manager_id,
            ]);

            DB::commit();

            return redirect()
                ->route('location.accounts.show', Crypt::encrypt($location->id))
                ->with('success', 'Business location updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Something went wrong while updating the business location.']);
        }
    }
    public function delete_location($id)
    {
        // Decrypt the business key
        $id = Crypt::decrypt($id);

        //users_deletes
        $fetchLocations = Business_locations::where('id', $id)->firstOrFail();
        //  dd($fetchLocations);
        return view('locations.locations_deletes', compact('fetchLocations'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Decrypt the user ID
            $id = Crypt::decrypt($id);
            // Check if the location exists in the database
            if (Business_locations::where('id', $id)->exists()) {
                $delete_Location = Business_locations::findOrFail($id);
                $delete_Location->delete();
            }
            // Redirect back with success message
            return redirect()->route('location.accounts.index')->with('success', 'Location deleted successfully.');
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Handle error if the encrypted ID is invalid
            return redirect()->route('location.accounts.index')->with('error', 'Invalid location ID.');
        } catch (\Exception $e) {
            return redirect()->route('location.accounts.index')->with('error', 'An error occurred while deleting the location.');
        }
    }
}
