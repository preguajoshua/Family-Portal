<?php

namespace App\Services;

class AgencyCoreApiUrlService
{
    /**
     * Represents the current AgencyCore cluster.
     *
     * @var  integer
     */
    private $cluster;

    /**
     * Instantiate the AgencyCoreApiUrlService class.
     *
     * @param  integer  $cluster
     */
    public function __construct($cluster)
    {
        $this->cluster = $cluster;
    }

    /**
     * Build the AgencyCore URL.
     *
     * @param   integer  $cluster
     * @param   array    $queryParams
     * @return  string
     */
    public function url($slug, $queryParams = [])
    {
        $baseUrl = str_replace('{cluster}', $this->cluster, config('axxess.agencycore_api.base_url'));
        $query = http_build_query($queryParams);

        return ($queryParams)
            ? "{$baseUrl}/{$slug}?{$query}"
            : "{$baseUrl}/{$slug}";
    }
}
