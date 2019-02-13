<?php

namespace Swoorpc\Router;

class Router
{
    protected $groupStack = [];

    protected $methods = [];

    protected $prefix = '';

    protected $lastMethodName = '';

    /**
     * 创建一组方法
     *
     * @param array $attributes
     * @param callable $callback
     *
     * @return void
     */
    public function group(array $attributes, callable $callback)
    {
        $attributes = $this->mergeLastGroupAttributes($attributes);

        if ((!isset($attributes['prefix']) || empty($attributes['prefix'])) && isset($this->prefix)) {
            $attributes['prefix'] = $this->prefix;
        }

        $this->groupStack[] = $attributes;

        call_user_func($callback, $this);

        array_pop($this->groupStack);
    }

    /**
     * 添加方法
     *
     * @param string $name
     * @param string|callable $action
     * @param array $options
     *  是一个关联数组，它里面包含了一些对该服务函数的特殊设置，详情参考hprose-php文档介绍
     *  https://github.com/hprose/hprose-php/wiki/06-Hprose-%E6%9C%8D%E5%8A%A1%E5%99%A8#addfunction-%E6%96%B9%E6%B3%95
     *
     * @return $this
     */
    public function add(string $name, $action, array $options = [])
    {
        if (is_string($action)) {
            $action = ['controller' => $action, 'type' => 'method'];
        }
        $action = $this->mergeLastGroupAttributes($action);
        if (!empty($action['prefix'])) {
            $name = ltrim(rtrim(trim($action['prefix'], '_') . '_' . trim($name, '_'), '_'), '_');
        }
        list($class, $method) = $this->parseController($action['namespace'], $action['controller']);
        $this->addMethod($method, $class, $name, $options);
        $this->appendMethod($name);
        return $this;
    }

    /**
     * 获取所有已添加方法列表
     *
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param $name 添加方法
     */
    public function appendMethod($name){
        $this->methods[] = $name;
    }

    /**
     * 合并最后一组属性
     *
     * @param array $attributes
     *
     * @return array
     */
    private function mergeLastGroupAttributes(array $attributes)
    {
        if (empty($this->groupStack)) {
            return $this->mergeGroup($attributes, []);
        }
        return $this->mergeGroup($attributes, end($this->groupStack));
    }

    /**
     * 合并新加入的组
     *
     * @param array $new
     * @param array $old
     *
     * @return array
     */
    private function mergeGroup(array $new, array $old)
    {
        $new['namespace'] = $this->formatNamespace($new, $old);
        $new['prefix'] = $this->formatPrefix($new, $old);
        return array_merge_recursive(array_except($old, ['namespace', 'prefix']), $new);
    }

    /**
     * 格式化命名空间
     *
     * @param array $new
     * @param array $old
     *
     * @return string
     */
    private function formatNamespace(array $new, array $old)
    {
        if (isset($new['namespace']) && isset($old['namespace'])) {
            return trim($old['namespace'], '\\') . '\\' . trim($new['namespace'], '\\');
        } elseif (isset($new['namespace'])) {
            return trim($new['namespace'], '\\');
        }

        return array_get($old, 'namespace');
    }

    /**
     * 解析控制器
     *
     * @param string|null $namespace
     * @param string $controller
     *
     * @return array
     */
    private function parseController($namespace, string $controller): array
    {
        list($classAsStr, $method) = explode('@', $controller);
        $class = resolve(join('\\', array_filter([$namespace, $classAsStr])));
        return [$class, $method];
    }

    /**
     * 格式化前缀
     *
     * @param array $new
     * @param array $old
     *
     * @return string
     */
    private function formatPrefix(array $new, array $old)
    {
        if (isset($new['prefix'])) {
            return trim(array_get($old, 'prefix'), '_') . '_' . trim($new['prefix'], '_');
        }
        return array_get($old, 'prefix', '');
    }


    /**
     * 添加类方法
     *
     * @param string $method
     * @param object $class
     * @param string $alias
     * @param array $alias
     *
     * @return void
     */
    private function addMethod(string $method, $class, string $alias, array $options)
    {
        app('_swoorpc.server')->addMethod($class, $method, $alias);
    }
}