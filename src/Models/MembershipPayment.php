<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Models;

use Alumkit\Alumkit\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $membership_plan_id
 * @property int|null $membership_id
 * @property string $amount
 * @property string $method
 * @property string|null $reference
 * @property Carbon $paid_at
 * @property string|null $proof_path
 * @property string|null $notes
 * @property string|null $review_notes
 * @property string $status
 * @property int|null $reviewed_by
 * @property Carbon|null $reviewed_at
 * @property int|null $created_by
 */
class MembershipPayment extends Model
{
    /** @var string */
    protected $table = 'membership_payments';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'membership_plan_id',
        'membership_id',
        'amount',
        'method',
        'reference',
        'paid_at',
        'proof_path',
        'notes',
        'review_notes',
        'status',
        'reviewed_by',
        'reviewed_at',
        'created_by',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    /** @phpstan-ignore missingType.generics */
    public function user(): BelongsTo
    {
        /** @phpstan-ignore argument.templateType */
        return $this->belongsTo(config('alumkit.auth.user_model'));
    }

    /** @return BelongsTo<MembershipPlan, $this> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }

    /** @return BelongsTo<MembershipPaymentMethod, $this> */
    public function methodDetail(): BelongsTo
    {
        return $this->belongsTo(MembershipPaymentMethod::class, 'method', 'type');
    }

    /**
     * The human label for the payment method (resolved by type, with a raw
     * string fallback when no dashboard-managed method matches the type).
     */
    public function methodLabel(): string
    {
        /** @var MembershipPaymentMethod|null $method */
        $method = $this->getRelationValue('methodDetail');

        return $method?->typeEnum()->label() ?? $this->method;
    }

    /** @return BelongsTo<Membership, $this> */
    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    /** @phpstan-ignore missingType.generics */
    public function reviewer(): BelongsTo
    {
        /** @phpstan-ignore argument.templateType */
        return $this->belongsTo(config('alumkit.auth.user_model'), 'reviewed_by');
    }

    public function statusEnum(): PaymentStatus
    {
        return PaymentStatus::from($this->status);
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatus::Pending->value;
    }

    public function proofUrl(): ?string
    {
        return $this->proof_path
            ? route('alumkit.membership.payments.proof', $this)
            : null;
    }
}
