<?php

namespace Http\HttplugBundle\ClientFactory;

use GuzzleHttp\Client;
use Http\Adapter\Guzzle5\Client as Adapter;
use Http\HttplugBundle\Collector\ProxyFactory;
use Http\Message\MessageFactory;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Guzzle5Factory implements ClientFactory
{
    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @param MessageFactory $messageFactory
     */
    public function __construct(MessageFactory $messageFactory)
    {
        $this->messageFactory = $messageFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function createClient(array $config = [])
    {
        if (!class_exists('Http\Adapter\Guzzle5\Client')) {
            throw new \LogicException('To use the Guzzle5 adapter you need to install the "php-http/guzzle5-adapter" package.');
        }

        $client = new Client($config);

        $class = Adapter::class;
        if ($config['_profiling']) {
            $class = ProxyFactory::createProxy($class);
        }

        return new $class($client, $this->messageFactory);
    }
}
