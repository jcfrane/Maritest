<?php

namespace App\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin Model
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $query): void {
            $tenant = Tenant::current();

            if ($tenant) {
                $query->where($query->getModel()->getTable().'.tenant_id', $tenant->id);
            }
        });

        static::creating(function (Model $model): void {
            $tenant = Tenant::current();

            if ($tenant && ! $model->getAttribute('tenant_id')) {
                $model->setAttribute('tenant_id', $tenant->id);
            }
        });
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
