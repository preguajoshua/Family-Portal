<?php

namespace App\Queries;

use Exception;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Models\Membership\UserApplication;

class QueryObject
{
    /**
     * Query parameters.
     *
     * @var  array
     */
	protected $params;

    /**
     * Query limit.
     *
     * @var  integer
     */
	protected $limit = 30;

    /**
     * [hasParam description]
     *
     * @param   [type]   $key
     * @return  boolean
     */
    public function hasParam($key)
    {
        return (array_key_exists($key, $this->params));
    }

    /**
     * Get parameter.
     *
     * @param   string  $key
     * @return  string
     *
     * @throws  InvalidArgumentException
     */
	public function getParam($key)
	{
		if (array_key_exists($key, $this->params)) {
			return $this->params[$key];
		}

		throw new InvalidArgumentException(sprintf('Invalid key [%s]', $key));
	}

    /**
     * Set parameter.
     *
     * @param  string  $key
     * @param  string  $value
     */
	public function setParam($key, $value)
	{
		$this->params[$key] = $value;
	}

    /**
     * Get all parameters.
     *
     * @return  array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set multiple parameters.
     *
     * @param   array  $params
     * @return  this
     */
	public function fill(array $params)
	{
		if (! count($params)) {
            return $this;
        }

		foreach ($params as $key => $value) {
			$this->setParam($key, $value);
		}

		return $this;
	}

    /**
     * Set the query limit.
     *
     * @return  integer
     */
	public function limit()
	{
		return $this->limit;
	}

    /**
     * {@inheritdoc}
     */
    public function fetch()
    {
        //
    }

    /**
     * Fetch query by application.
     *
     * @return  mixed
     *
     * @throws  Exception
     */
    public function fetchQueryByApplication()
    {
        $emrId = Session::get('emrId', 0);

        if ($emrId == UserApplication::APP_HOME_HEALTH) {
            return $this->fetchAgencyCoreQuery();
        }

        if ($emrId == UserApplication::APP_HOME_CARE) {
            return $this->fetchHomeCareQuery();
        }

        if ($emrId == UserApplication::APP_HOSPICE) {
            return $this->fetchHospiceQuery();
        }

        Log::channel('teams')->error('Unknown EMR.', ['emrId' => $emrId]);
        throw new Exception('Unknown EMR.');
    }
}
