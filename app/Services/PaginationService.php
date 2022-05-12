<?php

namespace App\Services;

class PaginationService
{

    protected $paginate = [];

    public function paginate($array, $page = 1,$perPage = 15)
    {
        $totalData = count($array);

        if ($totalData == 0) {
            $this->paginate = [
                "total_data" => $totalData,
                "total_pages" => 0,
                "data" => [],
                "last_id" => "",
                "page" => $page,
                "next_page" => $page +1
            ];

            return $this->paginate;
        }

        $totalPages = ceil($totalData / $perPage);

        if ($page > $totalPages) {
            $this->paginate = [
                "total_data" => $totalData,
                "total_pages" => $totalPages,
                "data" => [],
                "last_id" => "",
                "page" => $page,
                "next_page" => ""
            ];

            return $this->paginate;
        }

        //we find first index
        $firstDataIndex = ($perPage * $page) - $perPage;
        

        //get slice form array
        $slicedArray = array_slice($array, $firstDataIndex, $perPage);
        $lastDataIndex = count($slicedArray)-1;
        

        //last id in sliced array
        $lastID = $slicedArray[$lastDataIndex]["id"];

        $this->paginate = [
            "total_data" => $totalData,
            "total_pages" => $totalPages,
            "data" => $slicedArray,
            "last_id" => $lastID,
            "page" => $page,
            "next_page" => $page +1
        ];

        return $this->paginate;
    }

}