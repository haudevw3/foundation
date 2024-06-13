<?php

namespace Foundation\Contracts\Pagination;

interface PaginatorContract
{
    /**
     * Get the base path for paginator generated URLs.
     *
     * @return string
     */
    public function path();

    /**
     * Get the total number of items.
     *
     * @return int
     */
    public function total();

    /**
     * Get the number of items shown per page.
     *
     * @return int
     */
    public function perPage();

    /**
     * Get the current page.
     *
     * @return int
     */
    public function currentPage();

    /**
     * Get the last page number.
     *
     * @return int
     */
    public function lastPage();

    /**
     * Set the next page number.
     *
     * @return int
     */
    public function nextPage();

    /**
     * Set the previous page number.
     *
     * @return int
     */
    public function previousPage();

    /**
     * Get the URL for the first page.
     *
     * @return string
     */
    public function firstPageUrl();

    /**
     * Get the URL for the last page.
     *
     * @return string
     */
    public function lastPageUrl();

    /**
     * Get the URL for the next page.
     *
     * @return string|null
     */
    public function nextPageUrl();

    /**
     * Get the URL for the previous page.
     *
     * @return string|null
     */
    public function previousPageUrl();

    /**
     * Get the slice of items being paginated.
     *
     * @return array
     */
    public function items();
}