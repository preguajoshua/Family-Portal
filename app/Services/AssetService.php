<?php
namespace App\Services;

use File;
use Image;
use League\Flysystem\Filesystem;
use Intervention\Image\Exception\NotReadableException;

class AssetService {

	protected $path;

	protected $secret;

	protected $key;

	protected $duration;

	public function __construct()
	{
		$this->path = config('axxess.assets.url');
        $this->key = strtolower(config('axxess.assets.key'));
		$this->secret = strtolower(config('axxess.assets.secret'));

        $this->duration = 600;
	}

	public function get($assetId, $agencyId, $cache = true)
	{
		if(($cache = $this->cache($assetId, $agencyId)) !== false) {
			return $cache;
		}

		return $this->asset($assetId, $agencyId);
	}

	private function cache($assetId, $agencyId)
	{
		$filepath = $this->filename($assetId, $agencyId);

		if(File::exists($filepath)) {
			return Image::make($filepath);
		}

		return false;
	}

	private function filename($assetId, $agencyId)
	{
		return storage_path('cache').'/'.(md5($assetId.'.'.$agencyId)).'.jpg';
	}

	private function asset($assetId, $agencyId)
	{
		$filepath = $this->filename($assetId, $agencyId);

		$agencyIdBase64 = base64_encode($agencyId);

		$exp = date("YmdHis",time() + $this->duration);

		$sig = hash_hmac("sha256" , utf8_encode(strtolower($this->secret.$exp)) , utf8_encode(strtolower($this->key)));
		$sigBase64 = base64_encode($sig);

		$url = $this->path.$assetId."?key={$this->key}&key2={$agencyIdBase64}&exp={$exp}&sig={$sigBase64}";

		try {
			$img = Image::make($url);
		} catch(NotReadableException $e) {
			$img = Image::canvas(1, 1, '#ddd');
		}

		$img->save($filepath);

		return $img;
	}

}
