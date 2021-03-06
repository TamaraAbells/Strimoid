<?php

namespace Strimoid\Models\Folders;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Strimoid\Models\Content;
use Strimoid\Models\FakeFolder;

class Blocked extends FakeFolder
{
    public bool $isPrivate = true;

    protected function getBuilder(string $model): Builder
    {
        $builder = with(new $model())->newQuery();

        $blockedGroups = Auth::user()->blockedGroups()->pluck('id');
        $builder->whereIn('group_id', $blockedGroups);

        $blockedUsers = Auth::user()->blockedUsers()->pluck('id');
        $builder->orWhereIn('user_id', $blockedUsers);

        return $builder;
    }

    public function contents($tab = null, $sortBy = null): Builder
    {
        $builder = static::getBuilder(Content::class);

        $blockedDomains = Auth::user()->blockedDomains();
        $builder->orWhereIn('domain', $blockedDomains);

        if ($tab == 'popular') {
            $builder->popular();
        }
        $builder->orderBy($sortBy ?: 'created_at', 'desc');

        return $builder;
    }
}
