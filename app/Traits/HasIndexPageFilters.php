<?php

namespace App\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait HasIndexPageFilters
{
    protected function getFilterFromId($type, $id)
    {
        $filters = collect(getColumnFilters($type, true));

        return $filters->where('id', $id)->first();
    }

    public function clearFilter($key)
    {
        if (Arr::has($this->colFilters, $key)) {
            $this->colFilters[$key]['filter'] = null;
            $this->colFilters[$key]['query'] = null;
        }
    }

    public function setFilters($key, $filter, $query)
    {
        if (Arr::has($this->colFilters, $key)) {
            $this->colFilters[$key]['filter'] = $filter;
            $this->colFilters[$key]['query'] = $query;
        }
    }

    protected function getColNameForFilter($key)
    {
        $filters = $this->colFiltersMap;

        if (Arr::has($filters, $key)) {
            return $filters[$key];
        }

        return null;
    }

    protected function getFilterColOperatorAndQuery($filter, $key, $query)
    {
        $filterItem = [];
        $colFilterFromMap = $this->getColNameForFilter($key);

        $filterItem['key'] = $key;
        $filterItem['col'] = $colFilterFromMap['key'];
        $filterItem['normal'] = $colFilterFromMap['normal'];

        $filterItem['operator'] = $filter['filter'];
        $filterItem['query'] = $query;

        if (Arr::has($filter, 'like') && $filter['like']) {
            $filterItem['operator'] = 'LIKE';

            if (Arr::has($filter, 'not') && $filter['not']) {
                $filterItem['operator'] = 'NOT LIKE';
            }

            $filterItem['query'] = Str::replaceFirst('LIKE', $query, $filter['filter']);
        }

        if (Arr::has($filter, 'bool')) {
            $filterItem['query'] = $filter['bool'];
        }

        return $filterItem;
    }

    public function getModelFilterProperty()
    {
        $filters = [];

        foreach ($this->colFilters as $key => $item) {
            if ($item['filter'] && $item['query']) {
                $filter = $this->getFilterFromId(null, $item['filter']);

                if ($filter) {
                    $filters[] = $this->getFilterColOperatorAndQuery($filter, $key, $item['query']);
                }
            }
        }

        return $filters;
    }

    protected function filterScope($query, $filters)
    {
        foreach ($filters as $filter) {
            $query->where($filter['col'], $filter['operator'], $filter['query']);
        }
    }

    protected function filterHavingScope($query, $filters)
    {
        foreach ($filters as $filter) {
            $query->having($filter['col'], $filter['operator'], $filter['query']);
        }
    }
}
