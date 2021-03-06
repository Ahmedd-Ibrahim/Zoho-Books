<?php

namespace Ahmedd\ZohoBooks\Api;

use GuzzleHttp\Psr7\Stream;
use Ahmedd\ZohoBooks\Client;

class BaseApi
{
    const API_PATH = 'base_path';
    const API_KEY = 'base_key';

    const SORT_ORDER_ASCENDING = 'A';
    const SORT_ORDER_DESCENDING = 'D';

    /**
     * @var Client
     */
    protected $client;
    /**
     * @var string
     */
    protected $organizationId;

    /**
     * BaseApi constructor.
     *
     * @param Client $client
     * @param string $organizationId
     */
    public function __construct(Client $client, $organizationId)
    {
        $this->client = $client;
        $this->organizationId = $organizationId;
    }

    /**
     * @param array $filters
     *
     * @return ItemList
     */
    public function getList(array $filters = [])
    {
        $response = $this->client->getList(static::API_PATH, $this->organizationId, $filters);

        return new ItemList($response[static::API_KEY.'s'], $response['page_context']);
    }

    /**
     * @param string $id
     * @param array $params
     *
     * @return array|Stream
     */
    public function get($id, array $params = [])
    {
        $response = $this->client->get(static::API_PATH, $this->organizationId, $id, $params);
        if ($response instanceof Stream) {
            return $response;
        }

        return $response[static::API_KEY];
    }

    /**
     * @param array $data
     * @param array $params
     *
     * @return array
     */
    public function create(array $data, array $params = [])
    {
        $response = $this->client->post(static::API_PATH, $this->organizationId, $data, $params);

        return $response[static::API_KEY];
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function update(array $data)
    {
        $id = $data[static::API_KEY.'_id'];
        unset($data[static::API_KEY.'_id']);
        $response = $this->client->put(static::API_PATH, $this->organizationId, $id, $data);

        return $response[static::API_KEY];
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function delete($id)
    {
        $this->client->delete(static::API_PATH, $this->organizationId, $id);

        return true;
    }
}
