<?php

namespace App\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait HasSearchScope
{
    public function scopeSearch($query, $terms)
    {
        $config = $this->searchConfig;

        $cols = $config['cols'];
        $table = $config['table'];

        Str::of($terms)->explode(' ')->filter()->each(function ($term) use ($query, $table, $cols) {
            $term = '%' . $term . '%';

            $query->whereIn('id', function ($query) use ($term, $table, $cols) {
                $query->select('id')
                    ->from(function ($query) use ($term, $table, $cols) {
                        $query->select('id')
                            ->from($table);

                        foreach ($cols as $col) {
                            if (is_array($col)) {
                                $query->union(
                                    $query->newQuery()
                                        ->select($col['select'])
                                        ->from($col['from'])
                                        ->when(true, function ($query) use ($col) {
                                            $multiJoin = false;

                                            if (Arr::has($col, 'multiJoin') && $col['multiJoin']) {
                                                $multiJoin = true;
                                            }

                                            if ($multiJoin) {
                                                foreach ($col['join'] as $join) {
                                                    $query->join(...$join);
                                                }
                                            } else {
                                                $query->join(...$col['join']);
                                            }
                                        })
                                        ->where(function ($query) use ($col, $term) {
                                            foreach ($col['where'] as $where) {
                                                $query->orWhere($where, 'like', $term);
                                            }
                                        })
                                );
                            } else {
                                $query->orWhere($col, 'like', $term);
                            }
                        }
                    }, 'matches');
            });
        });
    }

    public function scopeSort($query, $key, $order = 'ASC')
    {
        if ($key) {
            $query->orderBy($key, $order);
        }
    }
}
