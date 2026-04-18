<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Business_list; // make sure model is imported
use App\Models\purchase_orders;
use App\Models\purchase_order_items;
use App\Models\Purchase_order_items as ModelsPurchase_order_items;
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
            //Generate Order Number
            $orderNumber = 'PO-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));

            $subtotal = 0;

            //Calculate subtotal
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_cost'];
            }

            $tax = 0;
            $discount = 0;
            $totalAmount = $subtotal + $tax - $discount;
            $amountPaid = 0;
            $balanceDue = $totalAmount;

            //Insert Purchase Order
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


    // public function update(Request $request, $id)
    // {
    //     $user = Auth::user();
    //     $businessKey = $user->active_business_key;

    //     if (!$businessKey) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'No active business selected.'
    //         ], 403);
    //     }

    //     // Check permission for product categories
    //     if (!$user->hasPermission('purchase_update')) {
    //         return response()->json([
    //             'error' => 'Feature Unavailable.'
    //         ], 403);
    //     }

    //     try {
    //         // Decrypt the ID
    //         $decryptedId = decrypt(urldecode($id));
    //     } catch (\Exception $e) {

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Invalid purchase order identifier'
    //         ], 400);
    //     }

    //     // Find the purchase order
    //     // $purchaseOrder = purchase_orders::find($decryptedId);

    //     $purchaseOrder = purchase_orders::where('id', $decryptedId)
    //         ->where('business_key', $businessKey)
    //         ->first();

    //     if (!$purchaseOrder) {

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Purchase order not found'
    //         ], 404);
    //     }

    //     // Check if order can be updated (only pending orders)
    //     if ($purchaseOrder->status !== 'pending') {

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Only pending orders can be updated. Current status: ' . $purchaseOrder->status
    //         ], 422);
    //     }

    //     // Validate the request - matching your exact payload
    //     $validator = Validator::make($request->all(), [
    //         'vendor_id' => 'sometimes|integer|exists:vendors,id', // Read-only but validate if present
    //         'location_id' => 'sometimes|integer|exists:business_locations,id', // Read-only but validate if present
    //         'order_date' => 'required|date',
    //         'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
    //         'status' => 'required|in:pending,sent,received,partially_received,cancelled',
    //         'subtotal' => 'required|numeric|min:0',
    //         'tax' => 'nullable|numeric|min:0',
    //         'discount' => 'nullable|numeric|min:0',
    //         'discount_type' => 'nullable|in:fixed,percentage', // Note: Not in DB, just validate
    //         'shipping_cost' => 'nullable|numeric|min:0',
    //         'total_amount' => 'required|numeric|min:0',
    //         'notes' => 'nullable|string|max:5000',
    //         'terms' => 'nullable|string|max:5000',
    //         'items' => 'required|array|min:1',
    //         'items.*.product_id' => 'required|integer|exists:product_lists,id',
    //         'items.*.quantity' => 'required|integer|min:1',
    //         'items.*.unit_cost' => 'required|numeric|min:0',
    //         'items.*.total' => 'required|numeric|min:0',
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

    //         // Store original data for logging
    //         $originalData = [
    //             'order_date' => $purchaseOrder->order_date,
    //             'expected_delivery_date' => $purchaseOrder->expected_delivery_date,
    //             'status' => $purchaseOrder->status,
    //             'subtotal' => $purchaseOrder->subtotal,
    //             'tax' => $purchaseOrder->tax,
    //             'discount' => $purchaseOrder->discount,
    //             'shipping_cost' => $purchaseOrder->shipping_cost ?? 0,
    //             'total_amount' => $purchaseOrder->total_amount,
    //             'notes' => $purchaseOrder->notes,
    //             'terms' => $purchaseOrder->terms ?? '',
    //         ];

    //         // Update purchase order main fields
    //         // IMPORTANT: vendor_id and location_id are NOT updated even if sent in request
    //         $purchaseOrder->order_date = $request->order_date;
    //         $purchaseOrder->expected_delivery_date = $request->expected_delivery_date;
    //         $purchaseOrder->status = $request->status;
    //         $purchaseOrder->subtotal = $request->subtotal;
    //         $purchaseOrder->tax = $request->tax ?? 0;
    //         $purchaseOrder->discount = $request->discount ?? 0;
    //         $purchaseOrder->shipping_cost = $request->shipping_cost ?? 0;
    //         $purchaseOrder->total_amount = $request->total_amount;
    //         $purchaseOrder->notes = $request->notes;
    //         $purchaseOrder->terms = $request->terms ?? '';
    //         $purchaseOrder->save();

    //         // Track changes for logging
    //         $changes = [];
    //         foreach ($originalData as $field => $oldValue) {
    //             $newValue = $purchaseOrder->$field;
    //             if ($oldValue != $newValue) {
    //                 $changes[$field] = [
    //                     'old' => $oldValue,
    //                     'new' => $newValue
    //                 ];
    //             }
    //         }

    //         // Track updated items for logging
    //         $updatedItems = [];
    //         $itemErrors = [];

    //         // Get existing items for this order (keyed by product_id for easy lookup)
    //         $existingItems = purchase_order_items::where('purchase_order_id', $purchaseOrder->id) ->where('business_key', $businessKey)
    //             ->get()
    //             ->keyBy('product_id');

    //         // Process items - Update existing items by product_id
    //         foreach ($request->items as $index => $itemData) {
    //             // Find item by product_id
    //             $item = $existingItems->get($itemData['product_id']);

    //             if (!$item) {
    //                 $itemErrors[] = [
    //                     'index' => $index,
    //                     'product_id' => $itemData['product_id'],
    //                     'error' => 'Item not found in this purchase order'
    //                 ];

    //                 continue;
    //             }

    //             // Track changes before update
    //             $oldQuantity = $item->quantity;
    //             $oldUnitCost = $item->unit_cost;
    //             $oldTotalCost = $item->total_cost;

    //             // Update item fields
    //             $item->quantity = $itemData['quantity'];
    //             $item->unit_cost = $itemData['unit_cost'];
    //             $item->total_cost = $itemData['total'];
    //             $item->save();

    //             // Log if changes were made
    //             if (
    //                 $oldQuantity != $itemData['quantity'] ||
    //                 $oldUnitCost != $itemData['unit_cost'] ||
    //                 $oldTotalCost != $itemData['total']
    //             ) {

    //                 $updatedItems[] = [
    //                     'item_id' => $item->id,
    //                     'product_id' => $item->product_id,
    //                     'changes' => [
    //                         'quantity' => ['old' => $oldQuantity, 'new' => $itemData['quantity']],
    //                         'unit_cost' => ['old' => $oldUnitCost, 'new' => $itemData['unit_cost']],
    //                         'total_cost' => ['old' => $oldTotalCost, 'new' => $itemData['total']]
    //                     ]
    //                 ];
    //             }
    //         }

    //         DB::commit();
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Purchase order updated successfully',
    //             // 'data' => [
    //             //     'id' => encrypt($purchaseOrder->id),
    //             //     'order_number' => $purchaseOrder->order_number,
    //             //     'status' => $purchaseOrder->status,
    //             //     'subtotal' => $purchaseOrder->subtotal,
    //             //     'tax' => $purchaseOrder->tax,
    //             //     'discount' => $purchaseOrder->discount,
    //             //     'shipping_cost' => $purchaseOrder->shipping_cost,
    //             //     'total_amount' => $purchaseOrder->total_amount,
    //             //     'terms' => $purchaseOrder->terms,
    //             // ]
    //         ], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to update purchase order. ' . $e->getMessage(),
    //             'debug' => config('app.debug') ? $e->getMessage() : null
    //         ], 500);
    //     }
    // }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $businessKey = $user->active_business_key;

        if (!$businessKey) {
            return response()->json([
                'success' => false,
                'message' => 'No active business selected.'
            ], 403);
        }

        if (!$user->hasPermission('purchase_update')) {
            return response()->json([
                'success' => false,
                'message' => 'Feature unavailable.'
            ], 403);
        }

        // Decrypt ID
        try {
            $decryptedId = decrypt(urldecode($id));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid purchase order identifier'
            ], 400);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'status' => 'required|in:pending,sent,received,partially_received,cancelled',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:5000',
            'terms' => 'nullable|string|max:5000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|distinct|exists:product_lists,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::transaction(function () use ($request, $decryptedId, $businessKey) {

                // =========================
                // LOCK ORDER (prevents race conditions)
                // =========================
                $purchaseOrder = purchase_orders::where('id', $decryptedId)
                    ->where('business_key', $businessKey)
                    ->lockForUpdate()
                    ->first();

                if (!$purchaseOrder) {
                    throw new \Exception('Purchase order not found');
                }

                if ($purchaseOrder->status !== 'pending') {
                    throw new \Exception(
                        'Only pending orders can be updated. Current status: ' . $purchaseOrder->status
                    );
                }

                // =========================
                // CALCULATE TOTALS (CORRECT)
                // =========================
                $items = collect($request->items);

                $subtotal = $items->sum(
                    fn($item) =>
                    $item['quantity'] * $item['unit_cost']
                );

                $tax = $request->tax ?? 0;
                $discount = $request->discount ?? 0;
                $shipping = $request->shipping_cost ?? 0;

                if ($discount > $subtotal) {
                    throw new \Exception('Discount cannot exceed subtotal');
                    
                }

                $totalAmount = $subtotal + $tax + $shipping - $discount;

                if ($totalAmount < 0) {
                    throw new \Exception('Invalid total amount');
                }

                // =========================
                // UPDATE ORDER
                // =========================
                $purchaseOrder->update([
                    'order_date' => $request->order_date,
                    'expected_delivery_date' => $request->expected_delivery_date,
                    'status' => $request->status,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'discount' => $discount,
                    'shipping_cost' => $shipping,
                    'total_amount' => $totalAmount,
                    'notes' => $request->notes,
                    'terms' => $request->terms ?? '',
                ]);

                // =========================
                // LOCK & SYNC ITEMS
                // =========================
                $existingItems = purchase_order_items::where('purchase_order_id', $purchaseOrder->id)
                    ->where('business_key', $businessKey)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('product_id');

                $incomingIds = $items->pluck('product_id')->toArray();

                // DELETE removed items
                purchase_order_items::where('purchase_order_id', $purchaseOrder->id)
                    ->where('business_key', $businessKey)
                    ->whereNotIn('product_id', $incomingIds)
                    ->delete();

                foreach ($items as $itemData) {

                    $itemTotal = $itemData['quantity'] * $itemData['unit_cost'];

                    if ($existingItems->has($itemData['product_id'])) {

                        $existingItems[$itemData['product_id']]->update([
                            'quantity' => $itemData['quantity'],
                            'unit_cost' => $itemData['unit_cost'],
                            'total_cost' => $itemTotal,
                        ]);
                    } else {

                        purchase_order_items::create([
                            'purchase_order_id' => $purchaseOrder->id,
                            'business_key' => $businessKey,
                            'product_id' => $itemData['product_id'],
                            'quantity' => $itemData['quantity'],
                            'unit_cost' => $itemData['unit_cost'],
                            'total_cost' => $itemTotal,
                        ]);
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Purchase order updated successfully'
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
