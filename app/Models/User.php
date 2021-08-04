<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use App\Models\Concerns\AppDbBase;
use Illuminate\Auth\Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Exceptions\MissingCustomerIdException;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends AppDbBase implements
    AuthenticatableContract,
    AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory, Notifiable, UsesUuid;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'updated_at',
        'created_at',
    ];

    /**
     * Client.
     * Note: Retrieve data from subuserapplications table in membership.
     *
     * @var  string
     */
    protected $client;

    /**
     * Patient client.
     * Note: Retrieve data from subuserapplications table in membership.
     *
     * @var  string
     */
    protected $patientClient;

    /**
     * The accounts that belong to the user.
     */
    public function accounts()
    {
        return $this->belongsToMany(Account::class);
    }

    /**
     * Get ID.
     *
     * @return  string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get login ID.
     *
     * @return  string
     */
    public function getLoginId()
    {
        return $this->login_id;
    }

    /**
     * Get name.
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get email.
     *
     * @return  string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get application.
     *
     * @return  string
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Get cluster.
     *
     * @return  string
     */
    public function getCluster()
    {
        return $this->cluster;
    }

    /**
     * Get patient ID.
     *
     * @return  string
     */
    public function getPatientId()
    {
        return $this->client()->Id;
    }

    public function getPatientContactId()
    {
        return $this->client()->ContactId;
    }

    public function getAgencyId()
    {
        return $this->client()->AgencyId;
    }

    /**
     * Determine if the user has payor access.
     *
     * @return  boolean
     */
    public function hasPayorAccess()
    {
        return $this->client()->isPayor;
    }

     /**
     * Determine if the user has documentation access.
     *
     * @return  boolean
     */
    public function canViewDocumentation()
    {
        return $this->client()->canViewDocumentation;
    }

    /**
     * Reset customer ID.
     *
     * @return  string
     */
    public function resetCustomerId()
    {
        $this->setCustomerId(null);
    }

    /**
     * Set customer ID.
     *
     * @param  string  $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customer_id = $customerId;

        $this->save();
    }

    /**
     * Get customer ID.
     *
     * @return  string
     */
    public function getCustomerId()
    {
        if (!$this->customer_id) {
            throw new MissingCustomerIdException('Customer Id cannot be empty.');
        }

        return $this->customer_id;
    }

    /**
     * Set client.
     *
     * @param  string  $client
     */
    public function setClient($client)
    {
        $this->patientClient = $client;
    }

    /**
     * Get client.
     *
     * @return  string
     */
    public function client() :object
    {
        if (! isset($this->patientClient)) {
            $this->patientClient = new Client(session('client', []));
        }

        return $this->patientClient;
    }


    public function accountUser()
    {
        return $this->hasMany(AccountUser::class);
    }


}
