<?php

namespace Http\ClientBundle\Tests\Unit\DependencyInjection;

use Http\ClientBundle\DependencyInjection\HttplugExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

/**
 * @author David Buchmann <mail@davidbu.ch>
 */
class HttplugExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return array(
            new HttplugExtension(),
        );
    }

    public function testConfigLoadDefault()
    {
        $this->load();

        foreach (['client', 'message_factory', 'uri_factory'] as $type) {
            $this->assertContainerBuilderHasAlias("httplug.$type", "httplug.$type.default");
        }

        $this->assertContainerBuilderHasService('httplug.client.default', 'Http\Adapter\HttpAdapter');
        $this->assertContainerBuilderHasService('httplug.message_factory.default', 'Http\Message\MessageFactory');
        $this->assertContainerBuilderHasService('httplug.uri_factory.default', 'Http\Message\UriFactory');
    }

    public function testConfigLoadClass()
    {
        $this->load(array(
            'classes' => array(
                'client' => 'Http\Adapter\Guzzle6HttpAdapter'
            ),
        ));

        foreach (['client', 'message_factory', 'uri_factory'] as $type) {
            $this->assertContainerBuilderHasAlias("httplug.$type", "httplug.$type.default");
        }

        $this->assertContainerBuilderHasService('httplug.client.default', 'Http\Adapter\Guzzle6HttpAdapter');
        $this->assertContainerBuilderHasService('httplug.message_factory.default', 'Http\Message\MessageFactory');
        $this->assertContainerBuilderHasService('httplug.uri_factory.default', 'Http\Message\UriFactory');
    }

    public function testConfigLoadService()
    {
        $this->load(array(
            'main_alias' => array(
                'client' => 'my_client_service',
                'message_factory' => 'my_message_factory_service',
                'uri_factory' => 'my_uri_factory_service',
            ),
        ));

        foreach (['client', 'message_factory', 'uri_factory'] as $type) {
            $this->assertContainerBuilderHasAlias("httplug.$type", "my_{$type}_service");
        }

        $this->assertContainerBuilderHasService('httplug.client.default', 'Http\Adapter\HttpAdapter');
        $this->assertContainerBuilderHasService('httplug.message_factory.default', 'Http\Message\MessageFactory');
        $this->assertContainerBuilderHasService('httplug.uri_factory.default', 'Http\Message\UriFactory');
    }
}
