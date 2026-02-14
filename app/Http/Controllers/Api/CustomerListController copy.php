<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Business_list; // make sure model is imported
use App\Models\Business_locations;
use App\Models\Customers;
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

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class CustomerListController extends Controller
{

    // Store method
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'nullable|email|unique:customers,email',
            'phone'      => 'nullable|string|max:30',
            'address'    => 'nullable|string|max:255',
            'city'       => 'nullable|string|max:100',
            'state'      => 'nullable|string|max:100',
            'country'    => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'customer_code' => 'nullable|string|unique:customers,customer_code',
            'registration_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'notes' => 'nullable|string',
            'location_id' => 'required|exists:business_locations,id',
        ]);

        // Apply database default values if not submitted
        $preview = array_merge([
            'country' => 'Nigeria',
            'total_purchases' => 0.00,
            'outstanding_balance' => 0.00,
            'is_active' => true,
            'loyalty_points' => 0,
            'registration_date' => now()->toDateString(),
        ], $validated);

        return response()->json([
            'success' => true,
            'message' => 'Validated input with defaults applied',
            'data' => $preview
        ], 200);
    }

    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'first_name' => 'required|string|max:100',
    //         'last_name'  => 'required|string|max:100',
    //         'email'      => 'nullable|email|unique:customers,email',
    //         'phone'      => 'nullable|string|max:30',
    //         'address'    => 'nullable|string|max:255',
    //         'city'       => 'nullable|string|max:100',
    //         'state'      => 'nullable|string|max:100',
    //         'country'    => 'nullable|string|max:100',
    //         'postal_code' => 'nullable|string|max:20',
    //         'customer_code' => 'nullable|string|unique:customers,customer_code',
    //         'registration_date' => 'nullable|date',
    //         'is_active' => 'nullable|boolean',
    //         'dob' => 'nullable|date',
    //         'gender' => 'nullable|in:male,female,other',
    //         'notes' => 'nullable|string',
    //         'location_id' => 'required|exists:business_locations,id',
    //     ]);

    //     $user = Auth::user();
    //     if (!$user || !$user->active_business_key) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'No active business found'
    //         ], 401);
    //     }

    //     // Backend fields
    //     $validated['owner_id'] = $user->id;
    //     $validated['business_key'] = $user->active_business_key;
    //     $validated['total_purchases'] = 0.0;
    //     $validated['outstanding_balance'] = 0.0;
    //     $validated['loyalty_points'] = 0;
    //     $validated['is_active'] = $validated['is_active'] ?? true;
    //     $validated['registration_date'] = $validated['registration_date'] ?? now()->toDateString();

    //     // Generate unique customer code if missing
    //     if (empty($validated['customer_code'])) {
    //         $validated['customer_code'] = $this->generateCustomerCode();
    //     }

    //     while (Customers::where('customer_code', $validated['customer_code'])->exists()) {
    //         $validated['customer_code'] = $this->generateCustomerCode() . '-' . rand(100, 999);
    //     }

    //     if (!empty($validated['email'])) {
    //         $validated['email'] = strtolower(trim($validated['email']));
    //     }

    //     // ✅ Mass assignment works without fillable
    //     $customer = Customers::create($validated);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Customer created successfully',
    //         'data' => $customer
    //     ], 201);
    // }

    // private function generateCustomerCode()
    // {
    //     return 'CUS-' . strtoupper(substr(md5(time()), 0, 6));
    // }


public function index()
{
    $user = auth()->user();

    if (!$user || !$user->active_business_key) {
        return response()->json([
            'error' => 'No active business selected.'
        ], 403);
    }

    $customers = Customers::with('location')
        ->where('business_key', $user->active_business_key)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($customer) {
            return [
                'customer_id'  => $customer->id,                // plain ID
                'encrypted_id' => Crypt::encrypt($customer->id), // encrypted ID
                'first_name'   => $customer->first_name,
                'last_name'    => $customer->last_name,
                'email'        => $customer->email,
                'phone'        => $customer->phone,
                'address'      => $customer->address,
                'location'     => $customer->location,
                'created_at'   => $customer->created_at,
            ];
        });

    return response()->json([
        'customers' => $customers,
        'customer_ids' => $customers->pluck('customer_id'), 
    ]);
}

    
}
