<?php

namespace Http\HttplugBundle\ClientFactory;

use Http\Adapter\Guzzle6\Client;
use Http\HttplugBundle\Collector\ProxyFactory;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Guzzle6Factory implements ClientFactory
{
    /**
     * {@inheritdoc}
     */
    public function createClient(array $config = [])
    {
        if (!class_exists('Http\Adapter\Guzzle6\Client')) {
            throw new \LogicException('To use the Guzzle6 adapter you need to install the "php-http/guzzle6-adapter" package.');
        }
        $class = Client::class;
        if ($config['_profiling']) {
            $class = ProxyFactory::createProxy($class);
        }

        return call_user_func(sprintf('%s::createWithConfig', $class), $config);
    }
}
