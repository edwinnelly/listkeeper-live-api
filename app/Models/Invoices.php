<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Invoices extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'owner_id',
        'business_key',
        'location_id',
        'staff_id',
        'customer_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'status',
        'next_invoice_date',
        'frequency',
        'interval',
        'total_cycles',
        'cycles_completed',
        'active',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'amount_paid',
        'balance_due',
        'payment_status',
        'payment_method',
        'transaction_reference',
        'payment_note',
        'notes',
        'attachment',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'next_invoice_date' => 'date',
        'active' => 'boolean',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'interval' => 'integer',
        'total_cycles' => 'integer',
        'cycles_completed' => 'integer',
    ];

    /**
     * Boot the model - Auto-generate invoice number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
        });
    }

    /**
     * Generate unique invoice number
     * Format: INV-YYYYMMDD-XXXXX
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';
        $lastInvoice = self::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function business()
    {
        return $this->belongsTo(Business_list::class, 'business_key', 'business_key');
    }

    public function location()
    {
        return $this->belongsTo(Business_locations::class, 'location_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    /**
     * Invoice items relationship
     * Each invoice can have multiple items
     */
    public function items()
    {
        return $this->hasMany(Invoice_items::class, 'invoice_id');
    }

    /**
     * Get the count of items in this invoice
     */
    public function getItemsCountAttribute(): int
    {
        return $this->items()->count();
    }

    /**
     * Get the total quantity of all items
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->items()->sum('quantity');
    }

    /**
     * Check if invoice has any items
     */
    public function hasItems(): bool
    {
        return $this->items()->exists();
    }

    /**
     * Calculate totals based on invoice items
     * 
     * This method recalculates:
     * - subtotal: sum of (quantity × unit_price) for all items
     * - tax_amount: sum of all item taxes
     * - discount_amount: sum of all item discounts
     * - total_amount: subtotal + tax_amount - discount_amount
     * - balance_due: total_amount - amount_paid
     * 
     * Also auto-updates payment_status based on amounts
     */
    public function calculateTotals(): void
    {
        // Load items if not already loaded
        if (!$this->relationLoaded('items')) {
            $this->load('items');
        }
        
        // Calculate subtotal: sum of (quantity × unit_price) for all items
        $subtotal = $this->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
        
        // Calculate total tax: sum of tax from all items
        $totalTax = $this->items->sum('tax');
        
        // Calculate total discount: sum of discount from all items
        $totalDiscount = $this->items->sum('discount');
        
        // Calculate grand total: subtotal + tax - discount
        $totalAmount = $subtotal + $totalTax - $totalDiscount;
        
        // Update invoice financial fields
        $this->subtotal = $subtotal;
        $this->tax_amount = $totalTax;
        $this->discount_amount = $totalDiscount;
        $this->total_amount = $totalAmount;
        $this->balance_due = $totalAmount - $this->amount_paid;
        
        // Auto-update payment status based on amounts
        if ($this->amount_paid <= 0) {
            $this->payment_status = 'unpaid';
            
            // Don't change status if it's draft or cancelled
            if (!in_array($this->status, ['draft', 'cancelled'])) {
                $this->status = 'sent';
            }
        } elseif ($this->amount_paid >= $this->total_amount) {
            $this->payment_status = 'paid';
            $this->status = 'paid';
            $this->balance_due = 0;
        } else {
            $this->payment_status = 'partial';
            $this->status = 'partial';
        }
        
        $this->save();
    }

    /**
     * Add a single item to the invoice and recalculate totals
     */
    public function addItem(array $itemData): Invoice_items
    {
        $quantity = (int) ($itemData['quantity'] ?? 1);
        $unitPrice = (float) ($itemData['unit_price'] ?? 0);
        $tax = (float) ($itemData['tax'] ?? 0);
        $discount = (float) ($itemData['discount'] ?? 0);
        
        // Calculate total: (quantity × unit_price) + tax - discount
        $total = ($quantity * $unitPrice) + $tax - $discount;
        
        $item = $this->items()->create([
            'owner_id' => $this->owner_id,
            'business_key' => $this->business_key,
            'location_id' => $this->location_id,
            'invoice_id' => $this->id,
            'product_id' => $itemData['product_id'] ?? null,
            'description' => $itemData['description'] ?? '',
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
        ]);
        
        // Recalculate invoice totals
        $this->calculateTotals();
        
        return $item;
    }

    /**
     * Remove an item from the invoice and recalculate totals
     */
    public function removeItem(string $itemId): bool
    {
        $item = $this->items()->where('id', $itemId)->first();
        
        if ($item) {
            $item->delete();
            $this->calculateTotals();
            return true;
        }
        
        return false;
    }

    /**
     * Clear all items from the invoice
     */
    public function clearItems(): void
    {
        $this->items()->delete();
        $this->calculateTotals();
    }

    /**
     * Record a payment for this invoice
     */
    public function recordPayment(float $amount, string $method, ?string $reference = null, ?string $note = null): void
    {
        $this->amount_paid += $amount;
        $this->payment_method = $method;
        $this->transaction_reference = $reference;
        $this->payment_note = $note;
        
        // Recalculate balance and update status
        $this->balance_due = $this->total_amount - $this->amount_paid;
        
        if ($this->amount_paid >= $this->total_amount) {
            $this->payment_status = 'paid';
            $this->status = 'paid';
            $this->balance_due = 0;
        } elseif ($this->amount_paid > 0) {
            $this->payment_status = 'partial';
            $this->status = 'partial';
        }
        
        $this->save();
    }

    /**
     * Mark invoice as sent to customer
     */
    public function markAsSent(): void
    {
        if ($this->status === 'draft') {
            $this->status = 'sent';
            $this->save();
        }
    }

    /**
     * Cancel this invoice
     */
    public function cancel(): void
    {
        if (!in_array($this->status, ['paid', 'cancelled'])) {
            $this->status = 'cancelled';
            $this->active = false;
            $this->save();
        }
    }

    /**
     * Check if invoice is editable
     * Only draft invoices can be edited
     */
    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if invoice is deletable
     * Only draft invoices can be deleted
     */
    public function isDeletable(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if invoice is recurring
     */
    public function isRecurring(): bool
    {
        return $this->active && $this->total_cycles > 0 && $this->cycles_completed < $this->total_cycles;
    }

    /**
     * Scope: Get only recurring invoices
     */
    public function scopeRecurring($query)
    {
        return $query->where('active', true)
                     ->where('total_cycles', '>', 0)
                     ->whereColumn('cycles_completed', '<', 'total_cycles');
    }

    /**
     * Scope: Filter invoices
     */
    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                      ->orWhereHas('customer', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                      })
                      ->orWhereHas('staff', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->when($filters['status'] ?? null, function ($q, $status) {
                if ($status !== 'all') {
                    $q->where('status', $status);
                }
            })
            ->when($filters['date_range'] ?? null, function ($q, $range) {
                switch ($range) {
                    case 'today':
                        $q->whereDate('invoice_date', today());
                        break;
                    case 'week':
                        $q->whereBetween('invoice_date', [now()->startOfWeek(), now()->endOfWeek()]);
                        break;
                    case 'month':
                        $q->whereMonth('invoice_date', now()->month)
                          ->whereYear('invoice_date', now()->year);
                        break;
                    case 'year':
                        $q->whereYear('invoice_date', now()->year);
                        break;
                }
            })
            ->when($filters['payment_status'] ?? null, function ($q, $paymentStatus) {
                if ($paymentStatus !== 'all') {
                    $q->where('payment_status', $paymentStatus);
                }
            })
            ->when($filters['customer_id'] ?? null, function ($q, $customerId) {
                $q->where('customer_id', $customerId);
            })
            ->when($filters['location_id'] ?? null, function ($q, $locationId) {
                $q->where('location_id', $locationId);
            })
            ->when($filters['staff_id'] ?? null, function ($q, $staffId) {
                $q->where('staff_id', $staffId);
            })
            ->when($filters['from_date'] ?? null, function ($q, $fromDate) {
                $q->whereDate('invoice_date', '>=', $fromDate);
            })
            ->when($filters['to_date'] ?? null, function ($q, $toDate) {
                $q->whereDate('invoice_date', '<=', $toDate);
            });
    }

    /**
     * Scope: Sort invoices
     */
    public function scopeSortBy($query, $sortBy = 'created_at', $sortOrder = 'desc')
    {
        $allowedSorts = [
            'invoice_number', 
            'total_amount', 
            'created_at', 
            'due_date', 
            'invoice_date',
            'status',
            'payment_status',
        ];
        
        if (in_array($sortBy, $allowedSorts)) {
            return $query->orderBy($sortBy, $sortOrder);
        }
        
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total_amount, 2);
    }

    /**
     * Get formatted balance due
     */
    public function getFormattedBalanceDueAttribute(): string
    {
        return number_format($this->balance_due, 2);
    }

    /**
     * Get formatted amount paid
     */
    public function getFormattedAmountPaidAttribute(): string
    {
        return number_format($this->amount_paid, 2);
    }

    /**
     * Get status label with proper formatting
     */
    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Get payment status label with proper formatting
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return ucfirst($this->payment_status);
    }
}