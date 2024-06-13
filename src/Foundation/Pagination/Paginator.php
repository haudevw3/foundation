<?php

namespace Foundation\Pagination;

use Foundation\Contracts\Pagination\PaginatorContract;
use Foundation\Http\Request;

class Paginator implements PaginatorContract
{
    use PaginatorHelpers;

    /**
     * Create a new paginator instance.
     *
     * @param array $items
     * @param int $perPage
     * @param array $options
     * @param \Foundation\Http\Request $request
     * @return void
     */
    public function __construct($items, $perPage, $options = [], Request $request)
    {
        $this->options = $options;
        $this->setPerPage($perPage);
        $this->setPath($request);
        $this->setTotal(count($items));
        $this->setLastPage($this->total(), $this->perPage());
        $this->setCurrentPage($request->input('page'));
        $this->setNextPage($this->currentPage(), $this->lastPage());
        $this->setPreviousPage($this->currentPage());
        $this->setFrom($this->currentPage(), $this->perPage());
        $this->setTo($this->currentPage(), $this->perPage(), $this->total());
        $this->setItems($items);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'total' => $this->total(),
            'per_page' => $this->perPage(),
            'current_page' => $this->currentPage(),
            'last_page' => $this->lastPage(),
            'first_page_url' => $this->firstPageUrl(),
            'last_page_url' => $this->lastPageUrl(),
            'next_page_url' => $this->nextPageUrl(),
            'prev_page_url' => $this->previousPageUrl(),
            'path' => $this->path(),
            'from' => $this->from(),
            'to' => $this->to(),
            'data' => $this->items()
        ];
    }
}