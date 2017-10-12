<?php

namespace DreamFactory\DocumentDb\Contracts;

/**
 * Interface ClientInterface
 *
 * @package DreamFactory\DocumentDb\Contracts
 */
interface ClientInterface
{
    /**
     * REST API request function
     *
     * @param string $verb         Request Method (HEAD, GET, POST, PUT, DELETE)
     * @param string $resourcePath Requested resource path
     * @param string $resourceType Requested resource type
     * @param string $resourceId   Requested resource id
     * @param array  $payload      Posted data
     * @param array  $extraHeaders Additional request headers
     *
     * @return array
     * @throws \Exception
     */
    public function request(
        $verb,
        $resourcePath,
        $resourceType,
        $resourceId = '',
        array $payload = [],
        array $extraHeaders = []
    );
}
