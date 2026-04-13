<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Business_list; // make sure model is imported
use App\Models\purchase_orders;
use App\Models\purchase_order_items;
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
use Illuminate\Support\Facades\Validator;
//  use Illuminate\Support\Facades\Crypt;


class PurchaseController extends Controller
{


    public function store(Request $request)
    {
        Log::info('Purchase Order Payload:', $request->all());

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:vendors,id', // ✅ FIXED TABLE
            'location_id' => 'required|exists:business_locations,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:product_lists,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $businessKey = $user->active_business_key;
        $ownerId = $user->id;

        if (!$businessKey) {
            return response()->json(['error' => 'No active business selected.'], 403);
        }

        DB::beginTransaction();

        try {
            // ✅ Generate Order Number
            $orderNumber = 'PO-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));

            $subtotal = 0;

            // ✅ Calculate subtotal
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_cost'];
            }

            $tax = 0;
            $discount = 0;
            $totalAmount = $subtotal + $tax - $discount;
            $amountPaid = 0;
            $balanceDue = $totalAmount;

            // ✅ Insert Purchase Order
            $purchaseOrderId = DB::table('purchase_orders')->insertGetId([
                'owner_id' => $ownerId,
                'business_key' => $businessKey,
                'location_id' => $request->location_id,
                'vendors_id' => $request->supplier_id, // ✅ FIXED
                'order_number' => $orderNumber,
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'amount_paid' => $amountPaid,
                'balance_due' => $balanceDue,
                'payment_status' => 'unpaid',
                'notes' => $request->notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            //Insert Items
            $itemsToInsert = [];

            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_cost'];

                $itemsToInsert[] = [
                    'owner_id' => $ownerId,
                    'business_key' => $businessKey,
                    'location_id' => $request->location_id,
                    'purchase_order_id' => $purchaseOrderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $lineTotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('purchase_order_items')->insert($itemsToInsert);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Purchase order created successfully',
                'data' => [
                    'purchase_order_id' => $purchaseOrderId,
                    'order_number' => $orderNumber,
                    'total_amount' => $totalAmount
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Purchase Order Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }



    // public function purchase_order()
    // {
    //     $user = Auth::user();
    //     $businessKey = $user->active_business_key;

    //     if (!$businessKey) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'No active business selected.'
    //         ], 403);
    //     }

    //     $orders = purchase_orders::with(['vendor', 'location'])
    //         ->forBusiness($businessKey, $user->id)
    //         ->orderBy('id', 'desc')
    //         ->get();

    //     return response()->json([
    //         'status' => true,
    //         'data' => $orders
    //     ]);
    // }



    public function purchase_order()
    {
        $user = Auth::user();
        $businessKey = $user->active_business_key;

        if (!$businessKey) {
            return response()->json([
                'status' => false,
                'message' => 'No active business selected.'
            ], 403);
        }

        $orders = purchase_orders::with(['vendor', 'location'])
            ->forBusiness($businessKey, $user->id)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($order) {
                $order->encrypted_id = urlencode(encrypt($order->id));
                // unset($order->id); // optional
                return $order;
            });

        return response()->json([
            'status' => true,
            'data' => $orders
        ]);
    }


    // public function purchaseOrderWithItems($id)
    // {
    //     $user = Auth::user();
    //     $businessKey = $user->active_business_key;

    //     if (!$businessKey) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'No active business selected.'
    //         ], 403);
    //     }

    //     try {
    //         // ✅ reverse URL encoding first, then decrypt
    //         $decryptedId = decrypt(urldecode($id));
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Invalid purchase order ID'
    //         ], 400);
    //     }

    //     $order = purchase_orders::with([
    //         'vendor:id,vendor_name',
    //         'location:id,location_name',
    //         'items:id,unit_cost,quantity,total_cost',
    //         'purchaseOrder:name'
    //     ])
    //         ->where('id', $decryptedId)
    //         ->forBusiness($businessKey, $user->id)
    //         ->first();

    //     if (!$order) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Purchase order not found'
    //         ], 404);
    //     }

    //     return response()->json([
    //         'status' => true,
    //         'data' => $order
    //     ]);
    // }


    public function purchaseOrderWithItems($id)
    {
        $user = Auth::user();
        $businessKey = $user->active_business_key;

        if (!$businessKey) {
            return response()->json([
                'status' => false,
                'message' => 'No active business selected.'
            ], 403);
        }

        try {
            $decryptedId = decrypt(urldecode($id));
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid purchase order ID'
            ], 400);
        }

        $order = purchase_orders::with([
            'vendor:id,vendor_name',
            'location:id,location_name',
            'items' => function ($query) {
                $query->select(
                    'id',
                    'purchase_order_id',
                    'product_id',
                    'quantity',
                    'unit_cost',
                    'total_cost'
                )->with([
                    'product:id,name'
                ]);
            }
        ])
            ->where('id', $decryptedId)
            ->forBusiness($businessKey, $user->id)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Purchase order not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $order
        ]);
    }
}
