<?php

namespace Application\Model;

class Pagination
{
    private $currentPage;
    private $totalPages;
    private $displayMaxPages = 5;

    /**
     * @param $currentPage
     * @param $totalPages
     */
    public function __construct($currentPage, $totalPages)
    {
        $this->currentPage = $currentPage;
        $this->totalPages = $totalPages;
    }


    public function firstPageToList()
    {
        $limit = $this->totalPages - floor($this->displayMaxPages / 2);

        $first = $this->currentPage > $limit
            ? $this->totalPages - $this->displayMaxPages+1
            : $this->currentPage - floor($this->displayMaxPages / 2);

        if ($first < 1) {
            return 1;
        }
        return $first;
    }

    public function lastPageToList()
    {
        $limit = floor($this->displayMaxPages / 2);

        $last = $this->currentPage <= $limit
            ? $this->displayMaxPages
            : $this->currentPage + floor($this->displayMaxPages / 2);
        if ($last > $this->totalPages) {
            return $this->totalPages;
        }
        return $last;
    }


    /**
     * @return mixed
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @return mixed
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /**
     * @return int
     */
    public function getDisplayMaxPages(): int
    {
        return $this->displayMaxPages;
    }

    /**
     * @param int $displayMaxPages
     */
    public function setDisplayMaxPages(int $displayMaxPages): void
    {
        $this->displayMaxPages = $displayMaxPages;
    }


}
