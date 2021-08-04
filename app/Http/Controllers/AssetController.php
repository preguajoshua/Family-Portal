<?php

namespace App\Http\Controllers;

use App\Services\AssetService;

class AssetController extends Controller
{
    //
    protected $assetService;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(AssetService $assetService)
	{
		$this->assetService = $assetService;
	}

    //
	public function get($assetId, $agencyId)
	{
		return $this->assetService->get($assetId, $agencyId)->response();
	}
}
