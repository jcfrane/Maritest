<?php

namespace App\Models;

use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Multitenancy\Concerns\UsesMultitenancyConfig;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Models\Concerns\ImplementsTenant;

class Tenant extends Model implements IsTenant
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory, ImplementsTenant, UsesMultitenancyConfig;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'logo',
        'settings',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function candidates(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->whereHas('roles', function ($query): void {
                $query->where('name', 'candidate');
            });
    }

    /**
     * @return HasMany<Questionnaire, $this>
     */
    public function questionnaires(): HasMany
    {
        return $this->hasMany(Questionnaire::class);
    }

    /**
     * @return HasMany<ExamSet, $this>
     */
    public function examSets(): HasMany
    {
        return $this->hasMany(ExamSet::class);
    }
}
