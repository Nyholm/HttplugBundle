<?php

namespace Http\HttplugBundle\Collector;

use Http\Client\Common\FlexibleHttpClient;
use Http\Client\Exception\HttpException;
use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

/**
 * The ProfileClient extends any client to gather target url and response status code.
 *
 * @author Fabien Bourigault <bourigaultfabien@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 */
class __TPL_CLASS__ extends __TPL_EXTENDS__
{
    /**
     * @var Collector
     */
    private $_collector;

    /**
     * @var Formatter
     */
    private $_formatter;

    /**
     * @var Stopwatch
     */
    private $_stopwatch;

    /**
     * @var array
     */
    private $_eventNames = [];

    /**
     * @param HttpClient|HttpAsyncClient $client    The client to profile. Client must implement both HttpClient and
     *                                              HttpAsyncClient interfaces.
     * @param Collector                  $collector
     * @param Formatter                  $formatter
     * @param Stopwatch                  $stopwatch
     */
    public function __construct($client, Collector $collector, Formatter $formatter, Stopwatch $stopwatch)
    {
        if (!($client instanceof HttpClient && $client instanceof HttpAsyncClient)) {
            throw new \RuntimeException(sprintf(
                '%s first argument must implement %s and %s. Consider using %s.',
                    __METHOD__,
                    HttpClient::class,
                    HttpAsyncClient::class,
                    FlexibleHttpClient::class
            ));
        }
        $this->_collector = $collector;
        $this->_formatter = $formatter;
        $this->_stopwatch = $stopwatch;
    }

    /**
     * @param Stopwatch $stopwatch
     */
    public function _setStopwatch(Stopwatch $stopwatch)
    {
        $this->_stopwatch = $stopwatch;
    }

    /**
     * {@inheritdoc}
     */
    public function sendAsyncRequest(RequestInterface $request)
    {
        if (!method_exists(__TPL_EXTENDS__, __FUNCTION__)) {
            throw new \Exception(sprintf('Method "%s" in class "%s" does not exist.', __FUNCTION__, __TPL_EXTENDS__));
        }
        $activateStack = true;
        $stack = $this->_collector->getActiveStack();
        if ($stack === null) {
            //When using a discovered client not wrapped in a PluginClient, we don't have a stack from StackPlugin. So
            //we create our own stack and activate it!
            $stack = new Stack('Default', $this->_formatter->formatRequest($request));
            $this->_collector->addStack($stack);
            $this->_collector->activateStack($stack);
            $activateStack = false;
        }

        $this->collectRequestInformation($request, $stack);
        $event = $this->_stopwatch->start($this->getStopwatchEventName($request));

        $onFulfilled = function (ResponseInterface $response) use ($event, $stack) {
            $this->collectResponseInformation($response, $event, $stack);

            return $response;
        };

        $onRejected = function (\Exception $exception) use ($event, $stack) {
            $this->collectExceptionInformation($exception, $event, $stack);

            throw $exception;
        };

        $this->_collector->deactivateStack($stack);

        try {
            return $this->client->sendAsyncRequest($request)->then($onFulfilled, $onRejected);
        } finally {
            $event->stop();
            if ($activateStack) {
                //We only activate the stack when created by the StackPlugin.
                $this->_collector->activateStack($stack);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request)
    {
        if (!method_exists(__TPL_EXTENDS__, __FUNCTION__)) {
            throw new \Exception(sprintf('Method "%s" in class "%s" does not exist.', __FUNCTION__, __TPL_EXTENDS__));
        }

        $stack = $this->_collector->getActiveStack();
        if ($stack === null) {
            //When using a discovered client not wrapped in a PluginClient, we don't have a stack from StackPlugin. So
            //we create our own stack but don't activate it.
            $stack = new Stack('Default', $this->_formatter->formatRequest($request));
            $this->_collector->addStack($stack);
        }

        $this->collectRequestInformation($request, $stack);
        $event = $this->_stopwatch->start($this->getStopwatchEventName($request));

        try {
            $response = $this->client->sendRequest($request);
            $this->collectResponseInformation($response, $event, $stack);

            return $response;
        } catch (\Exception $e) {
            $this->collectExceptionInformation($e, $event, $stack);

            throw $e;
        } catch (\Throwable $e) {
            $this->collectExceptionInformation($e, $event, $stack);

            throw $e;
        } finally {
            $event->stop();
        }
    }

    /**
     * @param RequestInterface $request
     * @param Stack            $stack
     */
    private function collectRequestInformation(RequestInterface $request, Stack $stack)
    {
        $stack->setRequestTarget($request->getRequestTarget());
        $stack->setRequestMethod($request->getMethod());
        $stack->setRequestScheme($request->getUri()->getScheme());
        $stack->setRequestHost($request->getUri()->getHost());
        $stack->setClientRequest($this->_formatter->formatRequest($request));
        $stack->setCurlCommand($this->_formatter->formatAsCurlCommand($request));
    }

    /**
     * @param ResponseInterface $response
     * @param StopwatchEvent    $event
     * @param Stack             $stack
     */
    private function collectResponseInformation(ResponseInterface $response, StopwatchEvent $event, Stack $stack)
    {
        $stack->setDuration($event->getDuration());
        $stack->setResponseCode($response->getStatusCode());
        $stack->setClientResponse($this->_formatter->formatResponse($response));
    }

    /**
     * @param \Exception     $exception
     * @param StopwatchEvent $event
     * @param Stack          $stack
     */
    private function collectExceptionInformation(\Exception $exception, StopwatchEvent $event, Stack $stack)
    {
        if ($exception instanceof HttpException) {
            $this->collectResponseInformation($exception->getResponse(), $event, $stack);
        }

        $stack->setDuration($event->getDuration());
        $stack->setClientException($this->_formatter->formatException($exception));
    }

    /**
     * Generates the event name.
     *
     * @param RequestInterface $request
     *
     * @return string
     */
    private function getStopwatchEventName(RequestInterface $request)
    {
        $name = sprintf('%s %s', $request->getMethod(), $request->getUri());

        if (isset($this->_eventNames[$name])) {
            $name .= sprintf(' [#%d]', ++$this->_eventNames[$name]);
        } else {
            $this->_eventNames[$name] = 1;
        }

        return $name;
    }
}
