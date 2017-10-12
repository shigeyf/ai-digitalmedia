<?php

namespace DreamFactory\DocumentDb\Resources;

use DreamFactory\DocumentDb\Contracts\ClientInterface;

/**
 * Class BaseResource
 *
 * @package DreamFactory\DocumentDb\Resources
 */
class BaseResource
{
    /** @var ClientInterface */
    protected $client;

    /** @var string */
    protected $resourceType;

    /** @var string */
    protected $resourceId = '';

    /** @var array */
    protected $headers = [];

    /**
     * BaseResource constructor.
     *
     * @param ClientInterface $client       Azure DocumentDB client
     * @param string          $resourceType Resource type name
     * @param string          $resourceId   Resource id
     */
    public function __construct(ClientInterface $client, $resourceType, $resourceId = '')
    {
        $this->client = $client;
        $this->resourceType = $resourceType;
        $this->resourceId = $resourceId;
    }

    /**
     * Set any additional headers.
     *
     * @param array $headers Array of headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * Determines the resource id to use.
     *
     * @param string|null $id Resource id
     *
     * @return string|null Resource id
     * @throws \Exception
     */
    protected function getId($id = null)
    {
        $id = (empty($id)) ? $this->resourceId : $id;
        if (empty($id)) {
            throw new \Exception('Invalid id supplied [' . $id . ']. Operation requires valid resource id.');
        }

        return $id;
    }

    /**
     * Makes the request for resources
     *
     * @param string $verb     Request Method (HEAD, GET, POST, PUT, DELETE)
     * @param string $path     Requested resource path
     * @param string $resource Requested resource
     * @param array  $payload  Posted data
     *
     * @return array
     */
    protected function request($verb, $path, $resource = '', array $payload = [])
    {
        return $this->client->request($verb, $path, $this->resourceType, $resource, $payload, $this->headers);
    }
}