<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Customers;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;

class CustomerListController extends Controller
{

    public function index()
    {
        $user = auth()->user();

        // Check permission for product categories
        if (!$user->hasPermission('customer_read')) {
            return response()->json([
                'error' => 'Feature Unavailable.'
            ], 403);
        }


        if (!$user || !$user->active_business_key) {
            return response()->json([
                'error' => 'No active business selected.'
            ], 403);
        }

        // Fetch customers and encrypt ID
        $customers = Customers::where('business_key', $user->active_business_key)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($customer) {
                $customer->id = ($customer->id);
                $customer->customer_key = Crypt::encrypt($customer->id);
                return $customer;
            });

        return response()->json([
            'customers' => $customers,
        ]);
    }



    public function show(string $customerKey)
    {
        $user = auth()->user();

        // Permission check
        if (!$user->hasPermission('customer_read')) {
            return response()->json([
                'error' => 'Feature Unavailable.'
            ], 403);
        }

        // Business check
        if (!$user || !$user->active_business_key) {
            return response()->json([
                'error' => 'No active business selected.'
            ], 403);
        }

        try {
            $customerId = Crypt::decrypt($customerKey);
        } catch (DecryptException $e) {
            return response()->json([
                'error' => 'Invalid customer identifier.'
            ], 400);
        }

        $customer = Customers::where('id', $customerId)
            ->where('business_key', $user->active_business_key)
            ->first();

        if (!$customer) {
            return response()->json([
                'error' => 'Customer not found.'
            ], 404);
        }

        // Re-attach encrypted key
        $customer->customer_key = Crypt::encrypt($customer->id);

        return response()->json([
            'customer' => $customer
        ]);
    }





    // Store method
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:30',
            'last_name'  => 'required|string|max:30',
            'email'      => 'unique:customers,email,NULL,id,business_key,' . auth()->user()->active_business_key,
            'phone'      => 'nullable|string|max:30',
            'address'    => 'nullable|string|max:255',
            'city'       => 'nullable|string|max:50',
            'state'      => 'nullable|string|max:60',
            'country'    => 'nullable|string|max:50',
            'postal_code' => 'nullable|string|max:20',
            'customer_code' => 'nullable|string|unique:customers,customer_code',
            'registration_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'notes' => 'nullable|string',
            'location_id' => [
                'required',
                'integer',
                Rule::exists('business_locations', 'id')
                    ->where('business_key', auth()->user()->active_business_key),
            ],
        ]);

        $user = Auth::user();
        if (!$user || !$user->active_business_key) {
            return response()->json([
                'success' => false,
                'message' => 'No active business found'
            ], 401);
        }

        // Check permission for product categories
        if (!$user->hasPermission('customer_create')) {
            return response()->json([
                'error' => 'Feature Unavailable.'
            ], 403);
        }

        // Backend defaults
        $defaults = [
            // 'country' => 'Nigeria',
            'total_purchases' => 0.0,
            'outstanding_balance' => 0.0,
            'loyalty_points' => 0,
            'is_active' => true,
            'registration_date' => now()->toDateString(),
        ];

        // Merge validated input with defaults
        $data = array_merge($defaults, $validated);

        // Set backend-only fields
        $data['owner_id'] = $user->id;
        $data['business_key'] = $user->active_business_key;

        // Generate unique customer code if missing
        if (empty($data['customer_code'])) {
            $data['customer_code'] = $this->generateCustomerCode();
        }
        while (\App\Models\Customers::where('customer_code', $data['customer_code'])->exists()) {
            $data['customer_code'] = $this->generateCustomerCode() . '-' . rand(100, 999);
        }

        // Clean email
        if (!empty($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
        }

        // Insert column by column dynamically
        $customer = new \App\Models\Customers();
        foreach ($data as $key => $value) {
            $customer->$key = $value;
        }
        $customer->save();

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',

        ], 201);
    }

    private function generateCustomerCode()
    {
        return 'CUS-' . strtoupper(substr(md5(time()), 0, 6));
    }



    public function destroy($encryptedId)
    {
        $user = auth()->user();

        if (!$user || !$user->active_business_key) {
            return response()->json([
                'error' => 'No active business selected.'
            ], 403);
        }

        // Check permission for product categories
        if (!$user->hasPermission('customer_delete')) {
            return response()->json([
                'error' => 'Feature Unavailable.'
            ], 403);
        }

        // Decrypt the incoming ID
        try {
            $id = Crypt::decrypt($encryptedId);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Invalid customer ID.'
            ], 400);
        }

        // Find the customer for this business
        $customer = Customers::where('id', $id)
            ->where('business_key', $user->active_business_key)
            ->first();

        if (!$customer) {
            return response()->json([
                'error' => 'Customer not found.'
            ], 404);
        }

        // Delete the customer
        $customer->delete();

        // Clear cache if needed
        Cache::forget('customers_' . $user->active_business_key);

        return response()->json([
            'success' => 'Customer deleted successfully.'
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user || !$user->active_business_key) {
            return response()->json([
                'success' => false,
                'message' => 'No active business found'
            ], 401);
        }

        // Permission check
        if (!$user->hasPermission('customer_update')) {
            return response()->json([
                'error' => 'Feature Unavailable.'
            ], 403);
        }

        try {
            $id = Crypt::decrypt($id);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Invalid customer ID.'
            ], 400);
        }


        // Fetch customer scoped to business
        $customer = \App\Models\Customers::where('id', $id)
            ->where('business_key', $user->active_business_key)
            ->firstOrFail();

        $validated = $request->validate([
            'first_name' => 'required|string|max:40',
            'last_name'  => 'required|string|max:40',
            'email' => [
                'nullable',
                'email:rfc,dns',
                'max:130',
                Rule::unique('customers')
                    ->where('business_key', $user->active_business_key)
                    ->ignore($customer->id),
            ],
            'phone' => [
                'required',
                'string',
                'max:18',
                'regex:/^\+?[0-9 ]{7,18}$/'
            ],

            'address' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:40',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'customer_code' => [
                'nullable',
                Rule::unique('customers')
                    ->where('business_key', $user->active_business_key)
                    ->ignore($customer->id),
            ],
            'registration_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'notes' => 'nullable|string|max:500',
            'location_id' => [
                'required',
                'integer',
                Rule::exists('business_locations', 'id')
                    ->where('business_key', auth()->user()->active_business_key),
            ],
        ]);

        // Normalize email
        if (!empty($validated['email'])) {
            $validated['email'] = strtolower(trim($validated['email']));
        }

        // Update only provided fields
        foreach ($validated as $key => $value) {
            $customer->$key = $value;
        }

        $customer->save();

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully',
        ], 200);
    }
}
