<?php

namespace DreamFactory\DocumentDb\Contracts;

/**
 * Interface ResourceInterface
 *
 * @package DreamFactory\DocumentDb\Contracts
 */
interface ResourceInterface
{
    //yoichika
    /**
     * Lists resources
     *
     * @return array
     */
    //public function list();
    public function getlist();

    /**
     * Gets a resource
     *
     * @param string|null $id
     *
     * @return array
     */
    public function get($id = null);

    /**
     * Creates a resource
     *
     * @param array $data
     *
     * @return array
     */
    public function create(array $data);

    /**
     * Replaces an existing resource with a new one
     *
     * @param array       $data
     * @param string|null $id
     *
     * @return array
     */
    public function replace(array $data, $id = null);

    /**
     * @param string $sql
     * @param array  $parameters
     *
     * @return array
     */
    public function query($sql, array $parameters = []);

    /**
     * @param string|null $id
     *
     * @return array
     */
    public function delete($id = null);
}
