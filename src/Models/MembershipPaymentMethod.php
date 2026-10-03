<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Models;

use Alumkit\Alumkit\Enums\MembershipMethodType;
use Alumkit\Alumkit\Traits\RendersEditorContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A dashboard-managed way for members to pay (bKash, Nagad, bank transfer).
 * Payments reference a method by its unique `type`, which also doubles as the
 * display label, so a method's identity never drifts from its history.
 * Ordering on the dashboard is drag-and-drop and stored in `sort_order`.
 *
 * @property int $id
 * @property string $type
 * @property string|null $instructions
 * @property bool $is_active
 * @property int $sort_order
 */
class MembershipPaymentMethod extends Model
{
    use RendersEditorContent;

    /** @var string */
    protected $table = 'membership_payment_methods';

    /** @var list<string> */
    protected $fillable = [
        'type',
        'instructions',
        'is_active',
        'sort_order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('type');
    }

    public function typeEnum(): MembershipMethodType
    {
        return MembershipMethodType::from($this->type);
    }

    /** The display label for the method, derived from its type. */
    public function label(): string
    {
        return $this->typeEnum()->label();
    }

    /** Convert Editor.js JSON instructions to HTML for display. */
    public function instructionsHtml(): string
    {
        return $this->renderEditorHtml($this->instructions);
    }
}
