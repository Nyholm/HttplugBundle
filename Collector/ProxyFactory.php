<?php

namespace Http\HttplugBundle\Collector;

/**
 * Generate proxies over your http clients. This should only be used in development.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ProxyFactory
{
    /**
     * @type string
     */
    public static $proxyDirectory;

    /**
     * Create a proxy that handles data collecting better.
     *
     * @param string $class
     * @param string &$proxyFile where we store the proxy class
     *
     * @return string the name of a much much better class
     */
    public static function createProxy($class, &$proxyFile = null)
    {
        if (null === self::$proxyDirectory) {
            self::$proxyDirectory = sys_get_temp_dir();
        }

        $proxyClass = self::getProxyClass($class);
        $class = '\\'.rtrim($class, '\\');
        $proxyFile = self::$proxyDirectory.'/'.$proxyClass.'.php';

        if (class_exists($proxyClass)) {
            return $proxyClass;
        }

        $content = file_get_contents(dirname(__DIR__).'/Resources/proxy/ProfileClientTemplate.php');
        $content = str_replace('__TPL_CLASS__', $proxyClass, $content);
        $content = str_replace('__TPL_EXTENDS__', $class, $content);

        self::checkProxyDirectory();
        file_put_contents($proxyFile, $content);
        require $proxyFile;

        return $proxyClass;
    }

    private static function checkProxyDirectory()
    {
        if (!is_dir(self::$proxyDirectory)) {
            @mkdir(self::$proxyDirectory, 0777, true);
        }
    }

    private static function getProxyClass($namespace)
    {
        return 'httplug_proxy_'.str_replace('\\', '_', $namespace);
    }
}
