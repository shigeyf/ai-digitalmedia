<?php

namespace DreamFactory\DocumentDb\Resources;

use DreamFactory\DocumentDb\Contracts\ResourceInterface;
use DreamFactory\DocumentDb\Contracts\ClientInterface;
use DreamFactory\DocumentDb\Verbs;

/**
 * Class Database
 *
 * @package DreamFactory\DocumentDb\Resources
 */
class Database extends BaseResource implements ResourceInterface
{
    /** Resource type */
    const TYPE = 'dbs';

    /**
     * Database constructor.
     *
     * @param ClientInterface $client     Azure DocumentDB client
     * @param string          $resourceId Database resource id
     */
    public function __construct(ClientInterface $client, $resourceId = '')
    {
        parent::__construct($client, static::TYPE, $resourceId);
    }

//yoichika
    /** {@inheritdoc} */
    //public function list()
    public function getlist()
    {
        $result = $this->request(Verbs::GET, '/' . static::TYPE);

        return $result;
    }

    /** {@inheritdoc} */
    public function get($id = null)
    {
        $id = $this->getId($id);
        $path = '/' . static::TYPE . '/' . $id;
        $resource = static::TYPE . '/' . $id;
        $result = $this->request(Verbs::GET, $path, $resource);

        return $result;
    }

    /** {@inheritdoc} */
    public function create(array $data)
    {
        if (!isset($data['id'])) {
            throw new \InvalidArgumentException('No id found in data. Id is required to create a Database.');
        }
        $path = '/' . static::TYPE;
        $result = $this->request(Verbs::POST, $path, '', $data);

        return $result;
    }

    /** {@inheritdoc} */
    public function delete($id = null)
    {
        $id = $this->getId($id);
        $path = '/' . static::TYPE . '/' . $id;
        $resource = static::TYPE . '/' . $id;
        $result = $this->request(Verbs::DELETE, $path, $resource);

        return $result;
    }

    /** {@inheritdoc} */
    public function replace(array $data, $id = null)
    {
        throw new \Exception('Replacing Database resource is not supported.');
    }

    /** {@inheritdoc} */
    public function query($sql, array $parameters = [])
    {
        throw new \Exception('Querying Database resource is not supported.');
    }
}
