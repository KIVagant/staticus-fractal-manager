<?php

namespace FractalManager;

use FractalManager\Adapter\AdapterInterface;

/**
 * Class Manager
 * @package FractalManager
 */
class Manager
{
    /**
     * AdapterInterface
     * @var
     */
    protected $adapter;

    /**
     * Constructor
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->setAdapter($adapter);
    }

    /**
     * Set adapter
     * @param AdapterInterface $adapter
     * @return $this
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Get adapter
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Read audio from adapter
     * @param $text
     * @return mixed
     */
    public function generate($text)
    {
        return $this->getAdapter()->generate($text);
    }
}
