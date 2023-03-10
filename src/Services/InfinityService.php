<?php

namespace KyleWLawrence\Infinity\Services;

use BadMethodCallException;
use Config;
use InvalidArgumentException;
use KyleWLawrence\Infinity\Api\HttpClient;

class InfinityService
{
    public int $workspace;

    private string $token;

    public bool $objects;

    public HttpClient $client;

    /**
     * Get auth parameters from config, fail if any are missing.
     * Instantiate API client and set auth bearer token.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->token = config('infinity-laravel.token');
        $this->workspace = config('infinity-laravel.workspace');
        $this->objects = config('infinity-laravel.objects', false);

        if (! $this->token || ! $this->workspace) {
            throw new InvalidArgumentException('Please set INF_TOKEN && INF_WORKSPACE environment variables.');
        }

        $this->client = new HttpClient($this->workspace, $this->objects);
        $this->client->setAuth('bearer', ['bearer' => $this->token]);
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
