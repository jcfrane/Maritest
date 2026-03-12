<?php

namespace App\Actions\Users;

use App\Models\Tenant;
use Illuminate\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ListUsers
{
    use AsAction;

    public function handle(Tenant $tenant): LengthAwarePaginator
    {
        return QueryBuilder::for($tenant->users())
            ->whereDoesntHave('roles', function ($query): void {
                $query->where('name', 'candidate');
            })
            ->with('roles')
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value): void {
                    $query->where(function ($searchQuery) use ($value): void {
                        $searchQuery
                            ->where('name', 'like', "%{$value}%")
                            ->orWhere('email', 'like', "%{$value}%");
                    });
                }),
                AllowedFilter::exact('role', 'roles.name'),
            ])
            ->allowedSorts(['name', 'email', 'created_at'])
            ->defaultSort('name')
            ->paginate(15)
            ->withQueryString();
    }
}
