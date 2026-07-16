<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoices;
use App\Models\Invoice_items;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices with pagination and filters
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
{
    try {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:all,draft,sent,paid,partial,overdue,cancelled',
            'date_range' => 'nullable|string|in:all,today,week,month,year',
            'payment_status' => 'nullable|string|in:all,unpaid,partial,paid',
            'customer_id' => 'nullable|string|exists:customers,id',
            'location_id' => 'nullable|string|exists:business_locations,id',
            'staff_id' => 'nullable|string|exists:business_employees,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'sort_by' => 'nullable|string|in:invoice_number,total_amount,created_at,due_date,invoice_date,status,payment_status',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $perPage = $request->input('per_page', 25);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        $invoices = Invoices::with([
                'customer:id,first_name,last_name,email,phone,address,city,state,country,customer_code',
                // 'items.product:id,name,sku,image',
                'location:id,location_name,address,phone,city,state,country', // Changed from 'name' to 'location_name'
                'staff:id,first_name,last_name,email',
                // 'business:id,business_key,business_name,logo',
        ])

        // $invoices = Invoices::with([
        //         'customer:id,first_name,last_name,email,phone,address,city,state,country,customer_code',
        //         'items.product:id,name,sku,image',
        //         'location:id,location_name,address,phone,city,state,country', // Changed from 'name' to 'location_name'
        //         'staff:id,first_name,last_name,email',
        //         'business:id,business_key,business_name,logo',
        // ])
            // ->where('owner_id', auth()->id())
            ->filter($request->only([
                'search',
                'status',
                'date_range',
                'payment_status',
                'customer_id',
                'location_id',
                'staff_id',
                'from_date',
                'to_date',
            ]))
            ->sortBy($sortBy, $sortOrder)
            ->paginate($perPage);

        // Add items count to each invoice
        $invoices->getCollection()->transform(function ($invoice) {
            $invoice->items_count = $invoice->items()->count();
            $invoice->total_quantity = $invoice->items()->sum('quantity');
            return $invoice;
        });

        return response()->json([
            'success' => true,
            'data' => $invoices->items(),
            'pagination' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
                'next_page_url' => $invoices->nextPageUrl(),
                'prev_page_url' => $invoices->previousPageUrl(),
            ],
        ]);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Invoice listing validation failed', [
            'errors' => $e->errors(),
            'request_params' => $request->except(['password', 'token']),
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        \Log::error('Invoice model not found', [
            'error' => $e->getMessage(),
            'request_params' => $request->except(['password', 'token']),
            'user_id' => auth()->id(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Requested resource not found',
        ], 404);
        
    } catch (\Illuminate\Database\QueryException $e) {
        \Log::error('Database error in invoice listing', [
            'error' => $e->getMessage(),
            'sql' => $e->getSql(),
            'bindings' => $e->getBindings(),
            'request_params' => $request->except(['password', 'token']),
            'user_id' => auth()->id(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while fetching invoices',
        ], 500);
        
    } catch (\Exception $e) {
        \Log::error('Unexpected error in invoice listing', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'request_params' => $request->except(['password', 'token']),
            'user_id' => auth()->id(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'An unexpected error occurred',
        ], 500);
    }
}

    /**
     * Display the specified invoice with all details
     * 
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $invoice = Invoices::with([
            'customer:id,name,email,phone,address',
            'items.product:id,name,sku,image',
            'items' => function ($query) {
                $query->with('product:id,name,sku,image');
            },
            'location:id,name,address,phone',
            'staff:id,first_name,last_name,email',
            'business:id,business_key,business_name,logo',
        ])
            ->where('owner_id', auth()->id())
            ->find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }

        // Add computed properties
        $invoice->items_count = $invoice->items->count();
        $invoice->total_quantity = $invoice->items->sum('quantity');

        return response()->json([
            'success' => true,
            'data' => $invoice,
        ]);
    }

    /**
     * Store a newly created invoice
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|string|exists:customers,id',  // Changed to nullable
            'location_id' => 'required|string|exists:business_locations,id',
            'staff_id' => 'nullable|string|exists:business_employees,id',
            'invoice_number' => 'required|string|unique:invoices,invoice_number',  // Add this
            'invoice_date' => 'required|date',  // Uncomment this
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'status' => 'nullable|string|in:draft,sent,paid,partial,overdue,cancelled',

            // Recurring invoice fields
            'is_recurring' => 'nullable|boolean',
            'frequency' => 'nullable|required_if:is_recurring,true|string|in:daily,weekly,monthly,quarterly,yearly',
            'interval' => 'nullable|integer|min:1',
            'next_invoice_date' => 'nullable|required_if:is_recurring,true|date',
            'total_cycles' => 'nullable|integer|min:1',

            // Payment fields
            'payment_method' => 'nullable|string',
            'transaction_reference' => 'nullable|string',
            'payment_note' => 'nullable|string',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',

            // Items array
            'items' => 'required|array|min:1',  // Uncomment this
            'items.*.product_id' => 'nullable|string|exists:product_lists,id',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Get authenticated user info
            $user = auth()->user();
            $businessKey = $user->business_key;
            $locationId = $request->location_id;

            // Handle file upload
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('invoices/attachments', 'public');
            }

            // Create invoice
            $invoice = Invoices::create([
                'owner_id' => $user->id,
                'business_key' => $businessKey,
                'location_id' => $locationId,
                'staff_id' => $request->staff_id,
                'customer_id' => $request->customer_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'status' => $request->status ?? 'draft',
                'frequency' => $request->frequency ?? 'monthly',
                'interval' => $request->interval ?? 1,
                'next_invoice_date' => $request->next_invoice_date ?? $request->invoice_date,
                'total_cycles' => $request->total_cycles,
                'cycles_completed' => 0,
                'active' => $request->is_recurring ?? false,
                'subtotal' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'amount_paid' => 0,
                'balance_due' => 0,
                'payment_status' => 'unpaid',
                'payment_method' => $request->payment_method,
                'transaction_reference' => $request->transaction_reference,
                'payment_note' => $request->payment_note,
                'notes' => $request->notes,
                'attachment' => $attachmentPath,
            ]);

            // Create invoice items
            foreach ($request->items as $itemData) {
                $quantity = (int) $itemData['quantity'];
                $unitPrice = (float) $itemData['unit_price'];
                $tax = (float) ($itemData['tax'] ?? 0);
                $discount = (float) ($itemData['discount'] ?? 0);

                // Calculate total: (quantity × unit_price) + tax - discount
                $total = ($quantity * $unitPrice) + $tax - $discount;

                Invoice_items::create([
                    'owner_id' => $user->id,
                    'business_key' => $businessKey,
                    'location_id' => $locationId,
                    'invoice_id' => $invoice->id,
                    'product_id' => $itemData['product_id'] ?? null,
                    'description' => $itemData['description'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax' => $tax,
                    'discount' => $discount,
                    'total' => $total,
                ]);
            }

            // Recalculate invoice totals from items
            $invoice->calculateTotals();

            DB::commit();

            // Load relationships for response
            $invoice->load([
                'customer:id,name,email,phone,address',
                'items.product:id,name,sku,image',
                'location:id,name',
                'staff:id,first_name,last_name',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'data' => $invoice,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up uploaded file
            if (isset($attachmentPath)) {
                Storage::disk('public')->delete($attachmentPath);
            }

            Log::error('Invoice creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create invoice',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Update the specified invoice (draft only)
     * 
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $invoice = Invoices::where('owner_id', auth()->id())->find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }

        // Only allow editing draft invoices
        if (!$invoice->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Only draft invoices can be edited',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|string|exists:customers,id',
            'location_id' => 'nullable|string|exists:business_locations,id',
            'staff_id' => 'nullable|string|exists:business_employees,id',
            'invoice_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'status' => 'nullable|string|in:draft,sent,cancelled',

            // Payment fields
            'payment_method' => 'nullable|string',
            'transaction_reference' => 'nullable|string',
            'payment_note' => 'nullable|string',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',

            // Items (optional for partial updates)
            'items' => 'nullable|array',
            'items.*.id' => 'nullable|string|exists:invoice_items,id',
            'items.*.product_id' => 'nullable|string|exists:product_lists,id',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Handle file upload
            if ($request->hasFile('attachment')) {
                // Delete old attachment
                if ($invoice->attachment) {
                    Storage::disk('public')->delete($invoice->attachment);
                }
                $attachmentPath = $request->file('attachment')->store('invoices/attachments', 'public');
                $invoice->attachment = $attachmentPath;
            }

            // Update invoice main fields
            $invoice->fill($request->only([
                'customer_id',
                'location_id',
                'staff_id',
                'invoice_date',
                'due_date',
                'status',
                'payment_method',
                'transaction_reference',
                'payment_note',
                'notes',
            ]));

            $invoice->save();

            // Update items if provided
            if ($request->has('items') && is_array($request->items)) {
                // Get existing item IDs
                $existingItemIds = $invoice->items()->pluck('id')->toArray();
                $updatedItemIds = [];

                foreach ($request->items as $itemData) {
                    $quantity = (int) $itemData['quantity'];
                    $unitPrice = (float) $itemData['unit_price'];
                    $tax = (float) ($itemData['tax'] ?? 0);
                    $discount = (float) ($itemData['discount'] ?? 0);

                    // Calculate total: (quantity × unit_price) + tax - discount
                    $total = ($quantity * $unitPrice) + $tax - $discount;

                    $itemPayload = [
                        'product_id' => $itemData['product_id'] ?? null,
                        'description' => $itemData['description'],
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'tax' => $tax,
                        'discount' => $discount,
                        'total' => $total,
                    ];

                    if (isset($itemData['id']) && !empty($itemData['id'])) {
                        // Update existing item
                        $item = Invoice_items::where('id', $itemData['id'])
                            ->where('invoice_id', $invoice->id)
                            ->first();

                        if ($item) {
                            // Only update if location/business changes
                            if ($request->has('location_id')) {
                                $itemPayload['location_id'] = $request->location_id;
                            }

                            $item->update($itemPayload);
                            $updatedItemIds[] = $item->id;
                        }
                    } else {
                        // Create new item
                        $itemPayload['owner_id'] = $invoice->owner_id;
                        $itemPayload['business_key'] = $invoice->business_key;
                        $itemPayload['location_id'] = $request->location_id ?? $invoice->location_id;
                        $itemPayload['invoice_id'] = $invoice->id;

                        $newItem = Invoice_items::create($itemPayload);
                        $updatedItemIds[] = $newItem->id;
                    }
                }

                // Delete items that were removed
                $itemsToDelete = array_diff($existingItemIds, $updatedItemIds);
                if (!empty($itemsToDelete)) {
                    Invoice_items::whereIn('id', $itemsToDelete)
                        ->where('invoice_id', $invoice->id)
                        ->delete();
                }
            }

            // Recalculate invoice totals
            $invoice->calculateTotals();

            DB::commit();

            // Reload with relationships
            $invoice->load([
                'customer:id,name,email,phone,address',
                'items.product:id,name,sku,image',
                'location:id,name',
                'staff:id,first_name,last_name',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully',
                'data' => $invoice,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Invoice update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update invoice',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Remove the specified invoice (draft only)
     * 
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $invoice = Invoices::where('owner_id', auth()->id())->find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }

        // Only allow deleting draft invoices
        if (!$invoice->isDeletable()) {
            return response()->json([
                'success' => false,
                'message' => 'Only draft invoices can be deleted',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Delete attachment if exists
            if ($invoice->attachment) {
                Storage::disk('public')->delete($invoice->attachment);
            }

            // Delete all items
            $invoice->items()->delete();

            // Delete invoice
            $invoice->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Invoice deletion failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete invoice',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Record a payment for an invoice
     * 
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function recordPayment(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,card,bank_transfer,cheque,online,other',
            'transaction_reference' => 'nullable|string|max:255',
            'payment_note' => 'nullable|string|max:1000',
            'payment_date' => 'nullable|date|before_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $invoice = Invoices::where('owner_id', auth()->id())->find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }

        // Check if invoice can receive payments
        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot record payment for ' . $invoice->status . ' invoices',
            ], 422);
        }

        // Check if payment amount exceeds balance
        if ($request->amount > $invoice->balance_due) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount exceeds the balance due of ' . number_format($invoice->balance_due, 2),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Use the model's recordPayment method
            $invoice->recordPayment(
                (float) $request->amount,
                $request->payment_method,
                $request->transaction_reference,
                $request->payment_note
            );

            DB::commit();

            // Reload with relationships
            $invoice->load([
                'customer:id,name,email,phone',
                'items.product:id,name,sku',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'data' => $invoice,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Payment recording failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to record payment',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Send invoice to customer (change status from draft to sent)
     * 
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(string $id)
    {
        $invoice = Invoices::where('owner_id', auth()->id())
            ->with(['customer', 'items'])
            ->find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }

        // Only draft invoices can be sent
        if ($invoice->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft invoices can be sent. Current status: ' . $invoice->status,
            ], 422);
        }

        // Check if invoice has items
        if (!$invoice->hasItems()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot send an invoice with no items',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Mark invoice as sent
            $invoice->markAsSent();

            // TODO: Send email notification to customer
            // This is where you would integrate with your email service
            // Mail::to($invoice->customer->email)->send(new InvoiceSent($invoice));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice sent successfully to ' . ($invoice->customer->email ?? 'customer'),
                'data' => $invoice->fresh(['customer', 'items']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Invoice send failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send invoice',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Cancel an invoice
     * 
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(string $id)
    {
        $invoice = Invoices::where('owner_id', auth()->id())->find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }

        // Cannot cancel already paid or cancelled invoices
        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel an invoice that is already ' . $invoice->status,
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Cancel the invoice
            $invoice->cancel();

            DB::commit();

            // Reload with relationships
            $invoice->load(['customer:id,name,email', 'items']);

            return response()->json([
                'success' => true,
                'message' => 'Invoice cancelled successfully',
                'data' => $invoice,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Invoice cancellation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel invoice',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Add a single item to an existing invoice
     * 
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function addItem(Request $request, string $id)
    {
        $invoice = Invoices::where('owner_id', auth()->id())->find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }

        if ($invoice->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only add items to draft invoices',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'nullable|string|exists:product_lists,id',
            'description' => 'required|string|max:500',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $quantity = (int) $request->quantity;
            $unitPrice = (float) $request->unit_price;
            $tax = (float) ($request->tax ?? 0);
            $discount = (float) ($request->discount ?? 0);

            // Calculate total: (quantity × unit_price) + tax - discount
            $total = ($quantity * $unitPrice) + $tax - $discount;

            $item = Invoice_items::create([
                'owner_id' => $invoice->owner_id,
                'business_key' => $invoice->business_key,
                'location_id' => $invoice->location_id,
                'invoice_id' => $invoice->id,
                'product_id' => $request->product_id,
                'description' => $request->description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
            ]);

            // Recalculate invoice totals
            $invoice->calculateTotals();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item added successfully',
                'data' => $item->load('product:id,name,sku,image'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Add item failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to add item',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Remove a single item from an invoice
     * 
     * @param string $invoiceId
     * @param string $itemId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeItem(string $invoiceId, string $itemId)
    {
        $invoice = Invoices::where('owner_id', auth()->id())->find($invoiceId);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }

        if ($invoice->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only remove items from draft invoices',
            ], 422);
        }

        $item = Invoice_items::where('id', $itemId)
            ->where('invoice_id', $invoiceId)
            ->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found',
            ], 404);
        }

        try {
            DB::beginTransaction();

            $item->delete();

            // Recalculate invoice totals
            $invoice->calculateTotals();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item removed successfully',
                'data' => $invoice->fresh(['items']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Remove item failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }
}
