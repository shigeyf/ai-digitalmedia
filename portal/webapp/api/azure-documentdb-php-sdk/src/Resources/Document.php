<?php

namespace DreamFactory\DocumentDb\Resources;

use DreamFactory\DocumentDb\Contracts\ResourceInterface;
use DreamFactory\DocumentDb\Contracts\ClientInterface;
use DreamFactory\DocumentDb\Verbs;

/**
 * Class Document
 *
 * @package DreamFactory\DocumentDb\Resources
 */
class Document extends BaseResource implements ResourceInterface
{
    /** Resource Type */
    const TYPE = 'docs';

    /** @var string Database resource id */
    protected $dbId;

    /** @var string Collection resource id */
    protected $collId;

    /**
     * Document constructor.
     *
     * @param ClientInterface $client     Azure DocumentDB client
     * @param string          $dbId       Database resource id
     * @param string          $collId     Collection resource id
     * @param string          $resourceId Document resource id
     */
    public function __construct(ClientInterface $client, $dbId, $collId, $resourceId = '')
    {
        $this->dbId = $dbId;
        $this->collId = $collId;

        parent::__construct($client, static::TYPE, $resourceId);
    }

    //yoichika
    /** {@inheritdoc} */
    //public function list()
    public function getlist()
    {
        $path = '/' . Database::TYPE .
            '/' . $this->dbId .
            '/' . Collection::TYPE .
            '/' . $this->collId .
            '/' . static::TYPE;
        $resource = Database::TYPE .
            '/' . $this->dbId .
            '/' . Collection::TYPE .
            '/' . $this->collId;
        $result = $this->request(Verbs::GET, $path, $resource);

        return $result;
    }

    /** {@inheritdoc} */
    public function get($id = null)
    {
        $id = $this->getId($id);
        $path = '/' . Database::TYPE .
            '/' . $this->dbId .
            '/' . Collection::TYPE .
            '/' . $this->collId .
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
            throw new \InvalidArgumentException('No id found in data. Id is required to create a Document.');
        }
        $path = '/' . Database::TYPE .
            '/' . $this->dbId .
            '/' . Collection::TYPE .
            '/' . $this->collId .
            '/' . static::TYPE;
        $resource = Database::TYPE .
            '/' . $this->dbId .
            '/' . Collection::TYPE .
            '/' . $this->collId;
        $result = $this->request(Verbs::POST, $path, $resource, $data);

        return $result;
    }

    /** {@inheritdoc} */
    public function replace(array $data, $id = null)
    {
        if (!isset($data['id'])) {
            throw new \InvalidArgumentException('No id found in data. Id is required to create a Document.');
        }
        $id = $this->getId($id);
        $path = '/' . Database::TYPE .
            '/' . $this->dbId .
            '/' . Collection::TYPE .
            '/' . $this->collId .
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
            '/' . Collection::TYPE .
            '/' . $this->collId .
            '/' . static::TYPE .
            '/' . $id;
        $resource = trim($path, '/');
        $result = $this->request(Verbs::DELETE, $path, $resource);

        return $result;
    }

    /** {@inheritdoc} */
    public function query($sql, array $parameters = [])
    {
        if (empty($sql)) {
            throw new \InvalidArgumentException('No query provided. A SQL query string is required for Query operation');
        }
        $data = ['query' => $sql, 'parameters' => $parameters];
        $path = '/' . Database::TYPE .
            '/' . $this->dbId .
            '/' . Collection::TYPE .
            '/' . $this->collId .
            '/' . static::TYPE;
        $resource = Database::TYPE .
            '/' . $this->dbId .
            '/' . Collection::TYPE .
            '/' . $this->collId;
        $result = $this->request(Verbs::POST, $path, $resource, $data);

        return $result;
    }
}
