<?php

namespace App;

use App\Traits\NormalizesWhatsAppPhones;
use Illuminate\Database\Eloquent\Model;

/**
 * A public review left by (or on behalf of) a customer for OGERA as a whole.
 *
 * The legacy App\Review model represents per-product ratings from the Stock
 * Manager and stays untouched. Site-wide client reviews sit in their own
 * "site_reviews" table so the two features never step on each other.
 */
class SiteReview extends Model
{
    use NormalizesWhatsAppPhones;

    protected $table = 'site_reviews';

    protected $whatsappPhoneAttributes = ['phone'];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'country',
        'rating',
        'title',
        'message',
        'customer_id',
        'source',
        'reference',
        'admin_reply',
        'replied_at',
        'is_public',
        'is_pinned',
        'sort_order',
        'ip',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_public' => 'boolean',
        'is_pinned' => 'boolean',
        'sort_order' => 'integer',
        'replied_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_public', false);
    }

    public function scopeOrdered($query)
    {
        return $query
            ->orderByDesc('is_pinned')
            ->orderBy('sort_order')
            ->orderByDesc('created_at');
    }
}
