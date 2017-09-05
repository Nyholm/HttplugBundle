<?php

namespace Http\HttplugBundle\ClientFactory;

use Http\Client\HttpClient;
use Http\Discovery\ClassDiscovery;
use Http\Discovery\HttpClientDiscovery;
use Http\HttplugBundle\Collector\ProxyFactory;

/**
 * Use auto discovery to find a HTTP client.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class AutoDiscoveryFactory extends ClassDiscovery implements ClientFactory
{
    /**
     * {@inheritdoc}
     */
    public function createClient(array $config = [])
    {
        // TODO $class might be a callable
        $class = static::findOneByType(HttpClient::class);
        if ($config['_profiling']) {
            $class = ProxyFactory::createProxy($class);
        }

        return $class();
    }
}
