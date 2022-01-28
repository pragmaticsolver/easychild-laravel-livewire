<?php

namespace App\Traits;

trait HasSorting
{
    public $sortBy = '';
    public $sortOrder = 'ASC';

    private function toggleSortOrder()
    {
        if ($this->sortOrder == 'ASC') {
            $this->sortOrder = 'DESC';
        } else {
            $this->sortOrder = 'ASC';
        }
    }

    public function changeSort($name)
    {
        if ($name == $this->sortBy) {
            $this->toggleSortOrder();
        } else {
            $this->sortOrder = 'ASC';
        }

        $this->sortBy = $name;

        $this->mountHasSorting();
    }

    public function mountHasSorting()
    {
        if ($this->sortBy && ! in_array($this->sortBy, $this->sortableCols)) {
            $this->sortBy = $this->sortableCols[0];
        }
    }
}
