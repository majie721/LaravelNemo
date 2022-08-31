<?php

namespace LaravelNemo\Library;

use JetBrains\PhpStorm\ArrayShape;
use LaravelNemo\AttributeClass\ArrayShapeConst;
use LaravelNemo\Exceptions\App\HttpForbiddenException;
use LaravelNemo\Exceptions\App\HttpNotFoundException;
use LaravelNemo\Exceptions\App\ParamsException;
use LaravelNemo\Nemo;

class Router
{

    /**
     * @param string $controllerPath
     * @param string $action
     * @param array $config
     * @return mixed
     * @throws HttpForbiddenException
     * @throws HttpNotFoundException
     * @throws ParamsException
     */
    public static function dispatchDefault(string $controllerPath, string $action, #[ArrayShape(ArrayShapeConst::ROUTE_CONFIG)] array $config): mixed{
        return self::dispatchRoute($controllerPath,$action,$config,\request()->all());
    }

    /**
     * @param string $controllerPath
     * @param string $action
     * @param array $config
     * @param array $requestData
     * @return mixed
     * @throws HttpForbiddenException
     * @throws HttpNotFoundException
     * @throws ParamsException
     */
    private static function dispatchRoute(string $controllerPath, string $action, #[ArrayShape(ArrayShapeConst::ROUTE_CONFIG)] array $config, array $requestData = []): mixed
    {
        $controllerArr = explode('/', $controllerPath);
        foreach ($controllerArr as &$item) {
            $item = Utils::camelize($item, $config['separator'] ?? '_');
        }

        $controller = implode("\\", $controllerArr);
        $controller = "{$config['namespace']}\\{$controller}Controller"; //eg. "App\Http\Web\Controllers\IndexController"

        $action = lcfirst(Utils::camelize($action));//action为小驼峰
        if (in_array($action, config('nemo.forbidden_actions'), true)) {
            throw new HttpForbiddenException('The method can not access.');
        }

        if (!class_exists($controller) || !method_exists($controller, $action)) {
            throw new HttpNotFoundException('The method not found.');
        }


        $method = new \ReflectionMethod($controller, $action);
        if ($method->isStatic() || !$method->isPublic()) {
            throw new HttpForbiddenException('The method can not access.');
        }

        $args = [];
        $parameters = $method->getParameters();

        foreach ($parameters as $parameter) {
            $paramName = $parameter->getName();
            $type = $parameter->getType()?->getName();

            $args[] = match (true) {
                class_exists($type) => self::matchTypeIsClass($type,$requestData),
                in_array($type, ['int', 'string', 'bool', 'float', 'array'], true) => self::matchTypeIsNormal($parameter, $paramName, $type,$requestData),
                $type === null => self::matchTypeIsNll($parameter, $paramName,$requestData),
                default => throw new ParamsException("The type of parameter ({$paramName}) is exception.")
            };
        }

        $class = app($controller);

        return $class->{$action}(...$args);
    }

    /**
     * @param \ReflectionParameter $parameter
     * @param string $paramName
     * @param string $type
     * @param array $requestData
     * @return mixed|null
     * @throws ParamsException
     */
    private static function matchTypeIsNormal(\ReflectionParameter $parameter, string $paramName, string $type,array $requestData): mixed
    {
        if (isset($requestData[Utils::uncamelize($paramName)])) { //约定为蛇形前端参数
            self::checkParamType($type, $paramName, $requestData[Utils::uncamelize($paramName)]);
            return $requestData[Utils::uncamelize($paramName)];
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->allowsNull()) {
            return null;
        }

        throw new ParamsException("The value of parameter ({$paramName}) is required.");
    }


    /**
     * @param string $className
     * @param array $requestData
     * @return Nemo
     */
    private static function matchTypeIsClass(string $className,array $requestData):Nemo
    {
        $resolvedClass = app($className);
        if ($resolvedClass instanceof Nemo) {
            $resolvedClass =  $resolvedClass::fromItem($requestData);
        }

        return $resolvedClass;
    }

    /**
     * @param \ReflectionParameter $parameter
     * @param string $paramName
     * @param array $requestData
     * @return mixed|null
     * @throws ParamsException
     */
    private static function matchTypeIsNll(\ReflectionParameter $parameter, string $paramName,array $requestData): mixed
    {
        return self::matchTypeIsNormal($parameter, $paramName, 'null',$requestData);
    }

    /**
     * @param string $typeName
     * @param string $paramName
     * @param $value
     * @return void
     * @throws ParamsException
     */
    protected static function checkParamType(string $typeName, string $paramName, &$value): void
    {
        switch ($typeName) {
            case 'string':
                if (!is_string($value) &&
                    !is_int($value) &&
                    !is_float($value)
                ) {
                    throw new ParamsException("The type of parameter ({$paramName}) must be string data.");
                }
                $value = (string)$value;
                break;
            case 'array':
                if (!is_array($value)) {
                    throw new ParamsException("The type of parameter ({$paramName}) must be array.");
                }
                break;
            case 'int':
                if (!preg_match('/^-?[1-9]?\d*$/', $value)) {
                    throw new ParamsException("The type of parameter ({$paramName}) must be integer.");
                }
                $value = (int)$value;
                break;
            case 'bool':
                if (!is_bool($value)) {
                    throw new ParamsException("The type of parameter ({$paramName}) must be bool.");
                }
                break;
            case 'float':
                if (!Utils::isTrueFloat($value)) {
                    throw new ParamsException("The type of parameter ({$paramName}) must be float.");
                }
                $value = (float)$value;
                break;
        }
    }

}
