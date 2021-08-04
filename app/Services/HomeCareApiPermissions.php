<?php

namespace App\Services;

use Exception;
use App\Services\HomeCareApi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class HomeCareApiPermissions
{
    /**
     * The client object
     *
     * @var  object
     */
    protected $client;

    /**
     * The clients "isPayor" flag.
     *
     * @var  boolean
     */
    protected $isPayor;

    /**
     * The clients "isAgencyBankAccountSetup" flag.
     *
     * @var  boolean
     */
    protected $isAgencyBankAccountSetup;

    /**
     * The clients "canViewDocumentation" flag.
     *
     * @var  boolean
     */
    protected $canViewDocumentation;

    /**
     * Instantiate a new Home Care API permissions service.
     *
     * @param  object  &$client
     */
    public function __construct(&$client)
    {
        $this->client = $client;
    }

    /**
     * Load and cache client permissions.
     *
     * @return  void
     */
    public function loadAndCache(): void
    {
        $this->fetchPermissions();

        $this->cachePermissions();

        $this->savePermissions();
    }

    /**
     * Load client permissions.
     *
     * @return  void
     */
    public function load(): void
    {
        if (! $this->isCached()) {
            $this->loadAndCache();
            return;
        }

        $this->loadPermissions();

        $this->savePermissions();
    }

    /**
     * Fetch Home Care permissions via the Home Care API.
     *
     * @return  void
     */
    private function fetchPermissions(): void
    {
        try {
            $clientPermissions = (new HomeCareApi)->permissions($this->client->agencyId, $this->client->patientContactId, $this->client->id);

            $this->isPayor = $clientPermissions->isPayor ?? false;
            $this->isAgencyBankAccountSetup = $clientPermissions->isAgencyBankAccountSetup ?? false;
            $this->canViewDocumentation = $clientPermissions->familyViewDocumentationAccess ?? false;

        } catch (Exception $e) {
            $this->isPayor = false;
            $this->isAgencyBankAccountSetup = false;
            $this->canViewDocumentation = false;

            Log::channel('teams')->error($e->getMessage());
            Log::channel('teams')->info('Can not obtain client permissions, invoicing disabled');
        }
    }

    /**
     * Cache the Home Care permissions.
     *
     * @return  void
     */
    private function cachePermissions(): void
    {
        Cache::put($this->cacheKey(), [
            'isPayor' => $this->isPayor,
            'isAgencyBankAccountSetup' => $this->isAgencyBankAccountSetup,
            'canViewDocumentation' => $this->canViewDocumentation,
        ]);
    }

    /**
     * Define the cache key string.
     *
     * @return  string
     */
    private function cacheKey()
    {
        return sprintf('contact-permissons-%s', $this->client->patientContactId);
    }

    /**
     * Save the Home Care permissions.
     *
     * @return  void
     */
    private function savePermissions(): void
    {
        $this->client->isPayor = $this->isPayor;
        $this->client->isAgencyBankAccountSetup = $this->isAgencyBankAccountSetup;
        $this->client->canViewDocumentation = $this->canViewDocumentation;
    }

    /**
     * Determine if the Home Care permissions are cached.
     *
     * @return  boolean
     */
    private function isCached()
    {
        return Cache::has($this->cacheKey());
    }

    /**
     * Load the Home Care permissions.
     *
     * @return  void
     */
    private function loadPermissions(): void
    {
        $permissions = Cache::get($this->cacheKey());

        $this->isPayor = $permissions['isPayor'];
        $this->isAgencyBankAccountSetup = $permissions['isAgencyBankAccountSetup'];
        $this->canViewDocumentation = $permissions['canViewDocumentation'];
    }
}
