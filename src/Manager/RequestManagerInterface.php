<?php

namespace Insitaction\RequestManagerBundle\Manager;

use Insitaction\RequestManagerBundle\Manager\Adapter\RequestAdapterInterface;
use Psr\Container\ContainerInterface;

interface RequestManagerInterface
{
    public function __construct(ContainerInterface $container);

    public function getAdapter(string $adapterClassname): RequestAdapterInterface;
}
