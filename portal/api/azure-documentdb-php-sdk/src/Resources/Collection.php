<?php

namespace DreamFactory\DocumentDb\Resources;

use DreamFactory\DocumentDb\Contracts\ResourceInterface;
use DreamFactory\DocumentDb\Contracts\ClientInterface;
use DreamFactory\DocumentDb\Verbs;

/**
 * Class Collection
 *
 * @package DreamFactory\DocumentDb\Resources
 */
class Collection extends BaseResource implements ResourceInterface
{
    /** Resource Type */
    const TYPE = 'colls';

    /** @var string Database resource id */
    protected $dbId;

    /**
     * Collection constructor.
     *
     * @param ClientInterface $client     Azure DocumentDB client
     * @param string          $dbId       Database resource id
     * @param string          $resourceId Collection resource id
     */
    public function __construct(ClientInterface $client, $dbId, $resourceId = '')
    {
        $this->dbId = $dbId;

        parent::__construct($client, static::TYPE, $resourceId);
    }

    //yoichika
    /** {@inheritdoc} */
    public function getlist()
    {
        $path = '/' . Database::TYPE .
            '/' . $this->dbId .
            '/' . static::TYPE;
        $resource = Database::TYPE .
            '/' . $this->dbId;
        $result = $this->request(Verbs::GET, $path, $resource);

        return $result;
    }

    /** {@inheritdoc} */
    public function get($id = null)
    {
        $id = $this->getId($id);
        $path = '/' . Database::TYPE .
            '/' . $this->dbId .
            '/' . static::TYPE .
            '/' . $id;
        $resource = trim($path, '/');
        $result = $this->request(Verbs::GET, $path, $resource);

        return $result;
    }

    /** {@inheritdoc} */
    public function create(array $data)
    {
        if (!isset($data['id'])) {
            throw new \InvalidArgumentException('No id found in data. Id is required to create a Collection.');
        }
        $path = '/' . Database::TYPE .
            '/' . $this->dbId .
            '/' . static::TYPE;
        $resource = Database::TYPE .
            '/' . $this->dbId;
        $result = $this->request(Verbs::POST, $path, $resource, $data);

        return $result;
    }

    /** {@inheritdoc} */
    public function replace(array $data, $id = null)
    {
        if (!isset($data['id'])) {
            throw new \InvalidArgumentException('No id found in data. Id is required to replace a Collection.');
        }
        $id = $this->getId($id);
        $path = '/' . Database::TYPE .
            '/' . $this->dbId .
            '/' . static::TYPE .
            '/' . $id;
        $resource = trim($path, '/');
        $result = $this->request(Verbs::PUT, $path, $resource, $data);

        return $result;
    }

    /** {@inheritdoc} */
    public function delete($id = null)
    {
        $id = $this->getId($id);
        $path = '/' . Database::TYPE .
            '/' . $this->dbId .
            '/' . static::TYPE .
            '/' . $id;
        $resource = trim($path, '/');
        $result = $this->request(Verbs::DELETE, $path, $resource);

        return $result;
    }

    /** {@inheritdoc} */
    public function query($sql, array $parameters = [])
    {
        throw new \Exception('Querying Collection resource is not supported.');
    }
}

