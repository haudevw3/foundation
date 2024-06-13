<?php

namespace Foundation\Pagination;

use Foundation\Http\Request;

trait PaginatorHelpers
{
    /**
     * All of the items being paginated.
     *
     * @var array
     */
    protected $items = [];

    /**
     * The total number of items.
     *
     * @var int
     */
    protected $total;

    /**
     * The number of items to be shown per page.
     *
     * @var int
     */
    protected $perPage;

    /**
     * The current page being "viewed".
     *
     * @var int
     */
    protected $currentPage;

    /**
     * The base path to assign to all URLs.
     *
     * @var string
     */
    protected $path;

    /**
     * The query string variable used to store the page.
     *
     * @var string
     */
    protected $pageName;

    /**
     * The last page number in the pagination.
     *
     * @var int
     */
    protected $lastPage;

    /**
     * The next page number in the pagination.
     *
     * @var int
     */
    protected $nextPage;

    /**
     * The previous page number in the pagination.
     *
     * @var int
     */
    protected $previousPage;

    /**
     * The starting item number on the current page.
     *
     * @var int
     */
    protected $from;

    /**
     * The ending item number on the current page.
     *
     * @var int
     */
    protected $to;

    /**
     * The paginator options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Get the slice of items being paginated.
     *
     * @return array
     */
    public function items()
    {
        return $this->items;
    }

    /**
     * Set the items for the paginator.
     *
     * @param array $items
     * @return void
     */
    protected function setItems($items)
    {
        $this->items = array_slice(
            $items, $this->from(), $this->to()
        );
    }

    /**
     * Get the base path for paginator generated URLs.
     *
     * @return string
     */
    public function path()
    {
        return $this->path;
    }

    /**
     * Set the base path to assign to all URLs.
     *
     * @param \Foundation\Http\Request $request
     * @param string $path
     * @return void
     */
    protected function setPath(Request $request, $path = '/')
    {
        $this->path = $path.substr(
            $request->path(), 0, strlen($request->path()) - 1
        );
    }

    /**
     * Set the query string variable used to store the page.
     *
     * @param string $name
     * @return void
     */
    protected function setPageName($name = 'page')
    {
        $this->pageName = $name;
    }

    /**
     * Get the total number of items.
     *
     * @return int
     */
    public function total()
    {
        return $this->total;
    }

    /**
     * Set the total number of items.
     *
     * @param int $total
     * @return void
     */
    protected function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * Get the number of items shown per page.
     *
     * @return int
     */
    public function perPage()
    {
        return $this->perPage;
    }

    /**
     * Set the number of items shown per page.
     *
     * @param int $perPage
     * @return int
     */
    protected function setPerPage($perPage)
    {
        return $this->perPage = $perPage;
    }

    /**
     * Get the current page.
     *
     * @return int
     */
    public function currentPage()
    {
        return $this->currentPage;
    }

    /**
     * Get the current page.
     *
     * @param int $currentPage
     * @return void
     */
    protected function setCurrentPage($currentPage)
    {
        $this->currentPage = (
            ($currentPage < 1) || ($currentPage > $this->lastPage) ? 1 : $currentPage
        );
    }

    /**
     * Get the last page number.
     *
     * @return int
     */
    public function lastPage()
    {
        return $this->lastPage;
    }

    /**
     * Set the last page number based on the total items and items per page.
     *
     * @param int $total
     * @param int $perPage
     * @return void
     */
    protected function setLastPage($total, $perPage)
    {
        $this->lastPage = (int)ceil($total / $perPage);
    }

    /**
     * Set the next page number.
     *
     * @return int
     */
    public function nextPage()
    {
        return $this->nextPage;
    }

    /**
     * Set the next page number based on the current page and last page.
     *
     * @param int $currentPage
     * @param int $lastPage
     * @return void
     */
    protected function setNextPage($currentPage, $lastPage)
    {
        $this->nextPage = (
            ($currentPage >= 1) && ($currentPage < $lastPage) ? $currentPage + 1 : null
        );
    }

    /**
     * Set the previous page number.
     *
     * @return int
     */
    public function previousPage()
    {   
        return $this->previousPage;
    }

    /**
     * Set the previous page number based on the current page.
     *
     * @param int $currentPage
     * @return void
     */
    protected function setPreviousPage($currentPage)
    {   
        $this->previousPage = ($currentPage > 1) ? $currentPage - 1 : null;
    }

    /**
     * Get the starting item number.
     *
     * @return int
     */
    protected function from()
    {
        return $this->from;
    }

    /**
     * Set the starting item number on the current page.
     *
     * @param int $currentPage
     * @param int $perPage
     * @return void
     */
    protected function setFrom($currentPage, $perPage)
    {
        $this->from = ($currentPage == 1) ? 0 : ($currentPage - 1) * $perPage;
    }

    /**
     * Get the ending item number.
     *
     * @return int
     */
    protected function to()
    {
        return $this->to;
    }

    /**
     * Set the ending item number on the current page.
     *
     * @param int $currentPage
     * @param int $perPage
     * @param int $total
     * @return void
     */
    protected function setTo($currentPage, $perPage, $total)
    {
        $this->to = min(($currentPage - 1) * $perPage + $perPage, $total);
    }

    /**
     * Get the URL for the first page.
     *
     * @return string
     */
    public function firstPageUrl()
    {
        return $this->path.'1';
    }

    /**
     * Get the URL for the last page.
     *
     * @return string
     */
    public function lastPageUrl()
    {
        return $this->path.$this->lastPage;
    }

    /**
     * Get the URL for the next page.
     *
     * @return string|null
     */
    public function nextPageUrl()
    {
        return (
            (($this->currentPage + 1) < $this->lastPage) ? $this->path.($this->currentPage + 1) : null
        );
    }

    /**
     * Get the URL for the previous page.
     *
     * @return string|null
     */
    public function previousPageUrl()
    {
        return (
            ((($this->currentPage - 1) > 0) || ! is_null($this->nextPageUrl())) ? $this->path.($this->currentPage - 1) : null
        );
    }
}