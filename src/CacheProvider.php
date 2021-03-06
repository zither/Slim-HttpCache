<?php
namespace Slim\HttpCache;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Http\Message\ResponseInterface;

class CacheProvider implements ServiceProviderInterface
{
    /**
     * Register this cache provider with a Pimple container
     *
     * @param  Container $container
     */
    public function register(Container $container)
    {
        $container['cache'] = $this;
    }

    /**
     * Enable client-side HTTP caching
     *
     * @param  ResponseInterface $response PSR7 response object
     * @param  string            $type     Cache-Control type: "private" or "public"
     * @param  null|int|string   $maxAge   Maximum cache age (integer timestamp or datetime string)
     *
     * @return ResponseInterface           A new PSR7 response object with `Cache-Control` header
     */
    public function allowCache(ResponseInterface $response, $type = 'private', $maxAge = null)
    {
        if (!in_array($type, ['private', 'public'])) {
            throw new \InvalidArgumentException('Invalid Cache-Control type. Must be "public" or "private".');
        }
        $headerValue = $type;
        if ($maxAge) {
            if (!is_integer($maxAge)) {
                $maxAge = strtotime($maxAge);
            }
            $headerValue = $headerValue . ', max-age=' . $maxAge;
        }

        return $response->withHeader('Cache-Control', $headerValue);
    }

    /**
     * Disable client-side HTTP caching
     *
     * @param  ResponseInterface $response PSR7 response object
     *
     * @return ResponseInterface           A new PSR7 response object with `Cache-Control` header
     */
    public function denyCache(ResponseInterface $response)
    {
        return $response->withHeader('Cache-Control', 'no-store,no-cache');
    }

    /**
     * Add `Expires` header to PSR7 response object
     *
     * @param  ResponseInterface $response A PSR7 response object
     * @param  int|string        $time     A UNIX timestamp or a valid `strtotime()` string
     *
     * @return ResponseInterface A new PSR7 response object with `Expires` header
     */
    public function withExpires(ResponseInterface $response, $time)
    {
        if (!is_integer($time)) {
            $time = strtotime($time);
            if ($time === false) {
                throw new \InvalidArgumentException('Expiration value could not be parsed with `strtotime()`.');
            }
        }

        return $response->withHeader('Expires', gmdate('D, d M Y H:i:s T', $time));
    }

    /**
     * Add `ETag` header to PSR7 response object
     *
     * @param  ResponseInterface $response A PSR7 response object
     * @param  string            $value    The ETag value
     * @param  string            $type     ETag type: "strong" or "weak"
     *
     * @return ResponseInterface           A new PSR7 response object with `ETag` header
     */
    public function withEtag(ResponseInterface $response, $value, $type = 'strong')
    {
        if (!in_array($type, ['strong', 'weak'])) {
            throw new \InvalidArgumentException('Invalid etag type. Must be "strong" or "weak".');
        }
        $value = '"' . $value . '"';
        if ($type === 'weak') {
            $value = 'W/' . $value;
        }

        return $response->withHeader('ETag', $value);
    }

    /**
     * Add `Last-Modified` header to PSR7 response object
     *
     * @param  ResponseInterface $response A PSR7 response object
     * @param  int|string        $time     A UNIX timestamp or a valid `strtotime()` string
     *
     * @return ResponseInterface           A new PSR7 response object with `Last-Modified` header
     */
    public function withLastModified(ResponseInterface $response, $time)
    {
        if (!is_integer($time)) {
            $time = strtotime($time);
            if ($time === false) {
                throw new \InvalidArgumentException('Last Modified value could not be parsed with `strtotime()`.');
            }
        }

        return $response->withHeader('Last-Modified', gmdate('D, d M Y H:i:s T', $time));
    }
}
