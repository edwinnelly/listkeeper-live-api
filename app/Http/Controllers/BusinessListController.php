<?php

namespace App\Http\Controllers;

use App\Models\Business_list;
use App\Models\Business_locations;
use App\Models\Roles;
use App\Models\Subscriptions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

use Exception;
// use Illuminate\Container\Attributes\DB;
use Illuminate\Support\Facades\DB;

class BusinessListController extends Controller
{
    //middleware
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
            // Fetch only businesses that belong to the authenticated user
            $business_list = Business_list::where('owner_id', Auth::id())->get();

            // Count business
            $count_business = Business_list::where('owner_id', Auth::id())->count();

            // Count business active
            $count_business_active = Business_list::where('owner_id', Auth::id())
                ->where('status', 'active')
                ->count();

            // Count business inactive
            $count_business_inactive = Business_list::where('owner_id', Auth::id())
                ->where('status', 'inactive')
                ->count();

            // Count business pending
            $count_business_pending = Business_list::where('owner_id', Auth::id())
                ->where('status', 'pending')
                ->count();

            // Get business with subscription
            // $business = Business_list::with('subscription')
            //     ->where('business_key', Auth::user()->active_business_key)
            //     ->first();

            return view('manage_business.manage_business', compact(
                'business_list',
                'count_business',
                'count_business_active',
                'count_business_inactive',
                'count_business_pending'
            ));
        } catch (\Exception $e) {
            Log::error('Error in ManageBusinessController@index: ' . $e->getMessage());

            return redirect()->back()->with('error', 'An error occurred while fetching business data.');
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // Validate request data
        $validated = $request->validate([
            // 'business_name' => 'required|string|max:255',
            'business_name' => 'required|string|max:255|unique:business_lists,business_name,NULL,id,owner_id,' . Auth::id(),
            'address' => 'required|string|max:500',
            'website' => 'nullable|url|max:255',
            'phone' => 'required|string|max:20',
            'slug' => 'required|string|max:255',
            'about_business' => 'nullable|string',
            'country' => 'required|string|max:100',
            'subscription_plan' => 'required|string|max:100',
            'currency' => 'required|string|max:10',
            'language' => 'required|string|max:50',
            'industry_type' => 'required|string|max:150',
            'logo' => 'nullable|mimetypes:image/avif,image/jpeg,image/png,image/webp|max:2048',
        ]);

        try {
            // Count number of businesses created and limit it to 5
            $count_business = Business_list::where('owner_id', Auth::id())->count();
            if ($count_business >= 5) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Business limit reached, max allowed is 5');
            }

            DB::beginTransaction();

            // Create new business key
            $business_keys = Str::uuid()->toString();

            // Create new business instance
            $business = new Business_list();
            $business->business_name = $validated['business_name'];
            $business->address = $validated['address'];
            $business->website = $validated['website'];
            $business->phone = $validated['phone'];
            $business->slug = $validated['slug'];
            $business->about_business = $validated['about_business'];
            $business->country = $validated['country'];
            $business->subscription_plan = $validated['subscription_plan'];
            $business->currency = $validated['currency'];
            $business->language = $validated['language'];
            $business->industry_type = $validated['industry_type'];
            $business->owner_id = Auth::id();
            $business->business_key = $business_keys;

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('business_logo', $filename, 'public');
                $business->logo = 'business_logo/' . $filename;
            }

            $business->save(); // ✅ Save first to satisfy the foreign key constraint

            // Now create default location
            $default_location = new Business_locations();
            $default_location->business_key = $business_keys;
            $default_location->owner_id = Auth::id();
            $default_location->location_name = 'Main Branch';
            $default_location->head_office = 'yes';
            $default_location->manager_id = Auth::id();
            $default_location->phone = $validated['phone'];

            $default_location->location_id = rand(1234567, 123456789);
            $default_location->save();

            //create subscription for the business
            $subscription = new Subscriptions();
            $subscription->owner_id = Auth::id();
            $subscription->business_key = $business_keys;
            $subscription->plan_name = 'Basic';
            $subscription->plan_code = 'basic';
            $subscription->amount = '0';
            $subscription->start_date = now();
            $subscription->end_date = now()->addDays(30);
            $subscription->save();

            // Check if role already exists for the logged-in user and current business
            $existingRole = Roles::where('owner_id', Auth::id())
                ->where('user_id', Auth::id())->first();

            if (!$existingRole) {
                // If role does not exist, create it
                $role = new Roles();
                $role->owner_id = Auth::id();
                $role->user_id = Auth::id();
                $role->business_key = $business_keys;
                $role->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Business created successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the business: ' . $e->getMessage());
        }
    }

    public function switchBusiness(Request $request)
    {
        try {
            // Validate encrypted input
            $validated = $request->validate([
                'switch_business' => 'required|string',
            ]);

            // Decrypt the business key
            $decryptedKey = Crypt::decrypt($validated['switch_business']);

            $user = $request->user();

            // Check if the business belongs to the user
            $businessExists = $user->businesses()
                ->where('business_key', $decryptedKey)
                ->exists();

            if (!$businessExists) {
                return redirect()->back()->with('error', 'Invalid business selected.');
            }

            // Update active business
            $user->active_business_key = $decryptedKey;
            $user->save();

            return redirect('/dashboard')->with('success', 'You have successfully switched accounts.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while switching account: ' . $e->getMessage());
        }
    }




    /**
     * Display the specified resource.
     */
    public function show(Business_list $business_list)
    {
        //display one account
        return view('manage_business.switch_business');
    }



    public function switch(Business_list $business_list, $id)
    {
        try {
            // Decrypt the business key
            $decryptedKey = Crypt::decrypt($id);

            // Check if subscription exists
            $exists = Subscriptions::where('owner_id', Auth::id())
                ->where('business_key', $decryptedKey)
                ->exists();

            if (!$exists) {
                return redirect()->back()
                    ->with('error', 'You do not have an active subscription for this business.');
            }

            // Get business details
            $business_switches = Business_list::where('owner_id', Auth::id())
                ->where('business_key', $decryptedKey)->first();

            // Count locations
            $business_location = Business_locations::where('owner_id', Auth::id())
                ->where('business_key', $decryptedKey)->count();

            $location_status = Business_locations::where('owner_id', Auth::id())
                ->where('business_key', $decryptedKey)
                ->where('status', 'active')->count();

            $location_status_inactive = Business_locations::where('owner_id', Auth::id())
                ->where('business_key', $decryptedKey)
                ->where('status', 'inactive')->count();

            // Get subscription plan
            $subscription = Subscriptions::where('owner_id', Auth::id())
                ->where('business_key', $decryptedKey)->first();

            return view('manage_business.switch_business', compact(
                'business_switches',
                'business_location',
                'location_status',
                'location_status_inactive',
                'subscription'
            ));
        } catch (\Exception $e) {
            Log::error('Business switch error: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while switching account.');
        }
    }

    



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Business_list $business_list)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Business_list $business_list)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Business_list $business_list)
    {
        //
    }
}
