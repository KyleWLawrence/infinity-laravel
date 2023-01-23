<?php

namespace KyleWLawrence\Infinity\Services;

use BadMethodCallException;
use Config;
use Infinity\Api\HttpClient;
use InvalidArgumentException;

class InfinityService
{
    public int $workspace;

    private string $bearer;

    /**
     * Get auth parameters from config, fail if any are missing.
     * Instantiate API client and set auth bearer token.
     *
     * @throws Exception
     */
    public function __construct(
        public ?HttpClient $client,
    ) {
        $this->bearer = config('infinity-laravel.bearer');
        $this->workspace = config('infinity-laravel.workspace');

        if (! $this->bearer || ! $this->workspace) {
            throw new InvalidArgumentException('Please set INF_BEARER && INF_WORKSPACE environment variables.');
        }

        if (! $this->client) {
            $this->client = new HttpClient($this->workspace);
        }

        $this->client->setAuth('bearer', ['bearer' => $this->bearer]);
    }

    /**
     * Pass any method calls onto $this->client
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (is_callable([$this->client, $method])) {
            return call_user_func_array([$this->client, $method], $args);
        } else {
            throw new BadMethodCallException("Method $method does not exist");
        }
    }

    /**
     * Pass any property calls onto $this->client
     *
     * @return mixed
     */
    public function __get($property)
    {
        if (property_exists($this->client, $property)) {
            return $this->client->{$property};
        } else {
            throw new BadMethodCallException("Property $property does not exist");
        }
    }
}
