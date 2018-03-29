<?php


namespace Storm\Middleware;


/**
 * Class MiddlewareContainer
 * @package Storm\Middleware
 */
class MiddlewareContainer
{
    /**
     * @var array
     */
    protected $middlewares = [];

    /**
     * @param $key
     * @param IMiddleware $middleware
     */
    public function add($key, IMiddleware $middleware)
    {
        $this->middlewares[$key][] = $middleware;
    }

    /**
     * Remove a middleware
     * Usable when running through cli and want to avoid frontend middlewares
     * @param $key
     * @param $class
     */
    public function remove($key, $class)
    {
        foreach ($this->middlewares[$key] as $k => $m) {
            if ($m instanceof $class) {
                unset($this->middlewares[$key][$k]);
            }
        }
    }

    /**
     * @param $key
     * @param $value
     * @param array $data
     * @return mixed
     */
    public function resolve($key, $value, $data = [])
    {
        if (isset($this->middlewares[$key])) {
            foreach ($this->middlewares[$key] as $m) {
                $m->setData($data);
                $value = $m->handle($value);
            }
        }
        return $value;
    }
}