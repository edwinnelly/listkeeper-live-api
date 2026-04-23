<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Business_list; // make sure model is imported
use App\Models\LocationProductList;
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
use Illuminate\Support\Facades\Schema;


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



    public function approve($id)
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

        // Decode ID
        try {
            $purchaseOrderId = decrypt(urldecode($id));
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid purchase order identifier'
            ], 400);
        }

        // Fetch order
        $purchaseOrder = purchase_orders::with('items')
            ->where('business_key', $businessKey)
            ->find($purchaseOrderId);


        if (!$purchaseOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase order not found'
            ], 404);
        }

        // Validation rules
        if ($purchaseOrder->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => "Only pending orders can be approved. Current status: {$purchaseOrder->status}"
            ], 422);
        }

        if ($purchaseOrder->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot approve order with no items'
            ], 422);
        }

        if (!$purchaseOrder->vendors_id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot approve order without a supplier'
            ], 422);
        }

        try {
            DB::transaction(function () use ($purchaseOrder, $user) {

                $oldStatus = $purchaseOrder->status;


                $updated = purchase_orders::where('id', $purchaseOrder->id)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'approved',
                        'approved_date' => now()
                    ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Purchase order approved successfully',
                'data' => [
                    'id' => encrypt($purchaseOrder->id),
                    'order_number' => $purchaseOrder->order_number,
                    'status' => 'approved',
                    'approved_at' => now()
                ]
            ]);
        } catch (\Throwable $e) {

            Log::error('Purchase order approval failed', [
                'order_id' => $purchaseOrder->id ?? null,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve purchase order'
            ], 500);
        }
    }



    public function receiveItems(Request $request, $id)
    {
        $user = Auth::user();
        $businessKey = $user->active_business_key;

        if (!$businessKey) {
            return response()->json([
                'success' => false,
                'message' => 'No active business selected.'
            ], 403);
        }

        if (!$user->hasPermission('purchase_received')) {
            return response()->json([
                'success' => false,
                'message' => 'Feature unavailable.'
            ], 403);
        }

        try {
            $decryptedId = decrypt($id);
        } catch (\Exception $e) {
            Log::error('Purchase order decryption failed', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid purchase order identifier'
            ], 400);
        }

        // Find the purchase order with items AND verify business key
        $purchaseOrder = purchase_orders::with(['items.product'])
            ->where('id', $decryptedId)
            ->where('business_key', $businessKey)
            ->first();

        if (!$purchaseOrder) {
            Log::warning('Purchase order not found or business key mismatch', [
                'purchase_order_id' => $decryptedId,
                'business_key' => $businessKey,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Purchase order not found'
            ], 404);
        }

        // Check if order can receive items (approved/sent or partially_received)
        if (!in_array($purchaseOrder->status, ['approved'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only approved orders can receive items.'
            ], 422);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'status' => 'required|in:received,partially_received',
                'received_date' => 'required|date|before_or_equal:today',

                // 'posting_date' => 'required|date', // Added posting_date validation,

                'items' => 'required|array|min:1',
                'items.*.id' => [
                    'required',
                    'integer',
                    function ($attribute, $value, $fail) use ($purchaseOrder) {
                        $itemExists = purchase_order_items::where('id', $value)
                            ->where('purchase_order_id', $purchaseOrder->id)
                            ->exists();

                        if (!$itemExists) {
                            $fail("The selected item (ID: {$value}) is invalid or does not belong to this purchase order.");
                        }
                    },
                ],
                'items.*.received_quantity' => 'required|integer|min:0',
                'notes' => 'nullable|string|max:500',
            ],
            [
                // Status
                'status.required' => 'Order status is required.',
                'status.in' => 'Invalid status. Allowed values are received or partially_received.',

                // Received Date
                'received_date.required' => 'Received date is required.',
                'received_date.date' => 'Received date must be a valid date.',
                'received_date.before_or_equal' => 'Received date cannot be in the future.',

                // Items
                'items.required' => 'At least one item is required.',
                'items.array' => 'Items must be a valid array.',
                'items.min' => 'You must provide at least one item.',

                // Item ID
                'items.*.id.required' => 'Item ID is required.',
                'items.*.id.integer' => 'Item ID must be a valid number.',

                // Quantity
                'items.*.received_quantity.required' => 'Received quantity is required for each item.',
                'items.*.received_quantity.integer' => 'Received quantity must be a number.',
                'items.*.received_quantity.min' => 'Received quantity cannot be negative.',

                // Notes
                'notes.string' => 'Notes must be a valid text.',
                'notes.max' => 'Notes cannot exceed 500 characters.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $totalReceived = 0;
            $totalOrdered = 0;
            $itemsProcessed = [];
            $productsUpdated = [];

            // Get location_id and business_key from purchase order
            $locationId = $purchaseOrder->location_id ?? null;
            $businessKey = $purchaseOrder->business_key ?? null;

            // Process each item
            foreach ($request->items as $itemData) {
                $item = purchase_order_items::where('id', $itemData['id'])
                    ->where('purchase_order_id', $purchaseOrder->id)
                    ->first();

                if (!$item) {

                    throw new \Exception('Unable to process one or more items. Please try again.');
                }

                $newReceived = (int) $itemData['received_quantity'];

                // Validate received quantity doesn't exceed remaining to receive
                $remaining = $item->quantity - ($item->received_quantity ?? 0);
                if ($newReceived > $remaining) {

                    throw new \Exception('Received quantity exceeds the remaining amount for one or more items.');
                }

                // Skip if zero received
                if ($newReceived === 0) {
                    continue;
                }

                // Update purchase order item
                $item->received_quantity = ($item->received_quantity ?? 0) + $newReceived;
                $item->backordered_quantity = $item->quantity - $item->received_quantity;
                $item->save();

                // Update product quantity in location_product_lists table
                if ($item->product_id && $locationId && $businessKey) {
                    $product = LocationProductList::where('product_id', $item->product_id)
                        ->where('location_id', $locationId)
                        ->where('business_key', $businessKey)
                        ->first();

                    if ($product) {
                        $oldStock = $product->stock_quantity ?? 0;
                        $product->stock_quantity = $oldStock + $newReceived;
                        $product->save();

                        $productsUpdated[] = [
                            'product_id' => $product->product_id,
                            'location_id' => $locationId,
                            'business_key' => $businessKey,
                            'product_name' => $product->name ?? 'Unknown',
                            'old_stock' => $oldStock,
                            'added_quantity' => $newReceived,
                            'new_stock' => $product->stock_quantity
                        ];
                    }
                }

                $totalReceived += $item->received_quantity;
                $totalOrdered += $item->quantity;

                $itemsProcessed[] = [
                    'item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product' => $item->product->name ?? 'Unknown',
                    'received' => $newReceived,
                    'total_received' => $item->received_quantity,
                    'backordered' => $item->backordered_quantity
                ];
            }

            // Auto-determine status if not fully received
            $finalStatus = $totalReceived >= $totalOrdered ? 'received' : 'received';

            // Update purchase order
            $purchaseOrder->status = $finalStatus;
            $purchaseOrder->received_date = $request->received_date;
            $purchaseOrder->posting_date = $request->received_date;

            // Append notes if provided
            if ($request->has('notes') && $request->notes) {
                $note = "\n\n--- Received: " . now()->format('Y-m-d H:i') . " by " . (auth()->user()->name ?? 'System') . " ---\n" . $request->notes;
                $purchaseOrder->notes = ($purchaseOrder->notes ?? '') . $note;
            }

            $purchaseOrder->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $totalReceived >= $totalOrdered
                    ? 'All items received and inventory updated successfully'
                    : 'Items received and inventory updated successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            // Return user-friendly error message
            return response()->json([
                'success' => false,
                'message' => 'Unable to process the receipt at this time. Please try again or contact support if the problem persists.'
            ], 500);
        }
    }



    public function destroy($id)
    {
        $user = Auth::user();
        $businessKey = $user->active_business_key;

        if (!$businessKey) {
            return response()->json([
                'success' => false,
                'message' => 'No active business selected.'
            ], 403);
        }

        if (!$user->hasPermission('purchase_delete')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        try {
            $id = decrypt(urldecode($id));
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid purchase order ID'
            ], 400);
        }

        $order = purchase_orders::where('id', $id)
            ->where('business_key', $businessKey)
            ->where('owner_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase order not found.'
            ], 404);
        }

        // 🔒 Business rule: prevent deleting processed orders
        if (in_array($order->status, ['approved', 'received', 'completed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a processed purchase order.'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // If you have related items, delete them first
            $order->items()->delete();

            $order->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase order deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Purchase order delete failed', [
                'order_id' => $id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete purchase order.'
            ], 500);
        }
    }
}
