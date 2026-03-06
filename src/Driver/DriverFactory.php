<?php

declare(strict_types=1);

namespace BusinessG\LaravelExcel\Driver;

use BusinessG\LaravelExcel\Exception\InvalidDriverException;
use Psr\Container\ContainerInterface;

class DriverFactory
{

    /**
     * @var DriverInterface[]
     */
    protected array $drivers = [];

    protected array $configs = [];

    /**
     * @throws InvalidDriverException when the driver class not exist or the class is not implemented DriverInterface
     */
    public function __construct(protected ContainerInterface $container)
    {
        $options = config('excel.options');
        $this->configs = config('excel.drivers', []);

        foreach ($this->configs as $key => $item) {
            $item = array_merge($options ?? [], $item);
            $driverClass = $item['driver'];

            if (!class_exists($driverClass)) {
                throw new InvalidDriverException(sprintf('[Error] class %s is invalid.', $driverClass));
            }

            $driver = app()->make($driverClass, ['config' => $item, 'name' => $key]);
            if (!$driver instanceof DriverInterface) {
                throw new InvalidDriverException(sprintf('[Error] class %s is not instanceof %s.', $driverClass, DriverInterface::class));
            }

            $this->drivers[$key] = $driver;
        }
    }

    public function __get($name): DriverInterface
    {
        return $this->get($name);
    }

    /**
     * @throws InvalidDriverException when the driver invalid
     */
    public function get(string $name): DriverInterface
    {
        $driver = $this->drivers[$name] ?? null;
        if (!$driver instanceof DriverInterface) {
            throw new InvalidDriverException(sprintf('[Error]  %s is a invalid driver.', $name));
        }

        return $driver;
    }

    public function getConfig($name): array
    {
        return $this->configs[$name] ?? [];
    }
}
