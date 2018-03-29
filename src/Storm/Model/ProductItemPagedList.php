<?php


namespace Storm\Model;


use Storm\Model\Support\StormModel;

class ProductItemPagedList extends StormModel
{
    protected $perPage = 12;
    protected $currentPage;

    public function pageCount()
    {
        return ceil($this->ItemCount / $this->perPage);
    }

    public function withPerPage($perPage)
    {
        $this->perPage = $perPage;
        return $this;
    }

    public function withCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;
        return $this;
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * @return mixed
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }
    
}