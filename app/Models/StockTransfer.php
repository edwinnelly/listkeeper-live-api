<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockTransfer extends Model
{
    use HasFactory, HasUlids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stock_transfers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'from_location_id',
        'to_location_id',
        'transfer_date',
        'expected_delivery_date',
        'notes',
        'reference_number',
        'status',
        'product_id',
        'stock_quantity',
        'stock_quantity_before',
        'unit_cost',
        'total',
        'business_key',
        'postby',
        'received_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'transfer_date' => 'date',
        'expected_delivery_date' => 'date',
        'unit_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'stock_quantity' => 'integer',
        'stock_quantity_before' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // Hide sensitive fields if needed
    ];

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        // Add computed attributes here if needed
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'pending',
        'unit_cost' => 0.00,
        'total' => 0.00,
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function (StockTransfer $stockTransfer) {
            // Auto-generate reference number if not provided
            if (empty($stockTransfer->reference_number)) {
                $stockTransfer->reference_number = self::generateReferenceNumber();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the source location of the transfer.
     */
    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Business_locations::class, 'from_location_id', 'id');
    }

    /**
     * Get the destination location of the transfer.
     */
    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Business_locations::class, 'to_location_id', 'id');
    }

    /**
     * Get the product being transferred.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product_list::class, 'product_id', 'id');
    }

    /**
     * Get the user who posted/created the transfer.
     */
    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'postby', 'name');
    }

    /**
     * Get the user who received/approved the transfer.
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by', 'name');
    }

    /**
     * Get the business that owns this transfer.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business_list::class, 'business_key', 'business_key');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include pending transfers.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved transfers.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include suspended transfers.
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    /**
     * Scope a query to filter by business key.
     */
    public function scopeByBusiness($query, $businessKey)
    {
        return $query->where('business_key', $businessKey);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $fromDate, $toDate)
    {
        return $query->whereBetween('transfer_date', [$fromDate, $toDate]);
    }

    /**
     * Scope a query to filter by location (source or destination).
     */
    public function scopeByLocation($query, $locationId)
    {
        return $query->where(function ($q) use ($locationId) {
            $q->where('from_location_id', $locationId)
              ->orWhere('to_location_id', $locationId);
        });
    }

    /**
     * Scope a query to filter by product.
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */

    /**
     * Get the formatted unit cost.
     */
    public function getFormattedUnitCostAttribute(): string
    {
        return number_format($this->unit_cost, 2);
    }

    /**
     * Get the formatted total.
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 2);
    }

    /**
     * Get the transfer status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'approved' => 'success',
            'pending' => 'warning',
            'suspended' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Check if transfer can be edited.
     */
    public function getCanEditAttribute(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if transfer can be deleted.
     */
    public function getCanDeleteAttribute(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get the remaining days until expected delivery.
     */
    public function getDaysUntilDeliveryAttribute(): ?int
    {
        if (!$this->expected_delivery_date) {
            return null;
        }
        
        return now()->startOfDay()->diffInDays($this->expected_delivery_date, false);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Approve the stock transfer.
     *
     * @param string|null $receivedBy
     * @return bool
     */
    public function approve(?string $receivedBy = null): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        return $this->update([
            'status' => 'approved',
            'received_by' => $receivedBy,
        ]);
    }

    /**
     * Suspend the stock transfer.
     *
     * @return bool
     */
    public function suspend(): bool
    {
        return $this->update([
            'status' => 'suspended',
        ]);
    }

    /**
     * Generate a unique reference number.
     *
     * @return string
     */
    public static function generateReferenceNumber(): string
    {
        $prefix = 'TRF';
        $year = date('Y');
        $month = date('m');
        
        $lastTransfer = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastTransfer && $lastTransfer->reference_number) {
            // Extract the last number from reference (e.g., TRF-202401-0001)
            $lastNumber = (int) substr($lastTransfer->reference_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return "{$prefix}-{$year}{$month}-{$newNumber}";
    }

    /**
     * Calculate the total based on quantity and unit cost.
     *
     * @return float
     */
    public function calculateTotal(): float
    {
        return $this->stock_quantity * $this->unit_cost;
    }

    /**
     * Check if stock is available for transfer.
     *
     * @return bool
     */
    public function hasEnoughStock(): bool
    {
        return $this->stock_quantity <= $this->stock_quantity_before;
    }
}