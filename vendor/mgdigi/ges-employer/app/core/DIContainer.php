<?php

// ===== 1. DIContainer - Le cœur du système =====
namespace App\Core;

use ReflectionClass;
use ReflectionParameter;
use Exception;

class DIContainer
{
    private static ?DIContainer $instance = null;
    private array $services = [];
    private array $instances = [];
    private string $servicesPath = __DIR__ . '/../config/services.yml';

    private function __construct()
    {
        $this->loadServices();
    }

    public static function getInstance(): DIContainer
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadServices(): void
    {
        if (file_exists($this->servicesPath)) {
            $yamlContent = file_get_contents($this->servicesPath);
            $this->services = $this->parseYaml($yamlContent);
        } else {
            throw new Exception("Le fichier services.yml est introuvable dans : " . $this->servicesPath);
        }
    }

    private function parseYaml(string $yamlContent): array
    {
        $lines = explode("\n", $yamlContent);
        $result = [];
        $currentCategory = null;

        foreach ($lines as $line) {
            $originalLine = $line;
            $line = trim($line);

            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }

            if (strpos($line, ':') !== false && strpos($originalLine, '  ') !== 0) {
                $currentCategory = trim(str_replace(':', '', $line));
                $result[$currentCategory] = [];
            } elseif (strpos($originalLine, '  ') === 0 && strpos($line, ':') !== false) {
                $parts = explode(':', $line, 2);
                $key = trim($parts[0]);
                $value = trim($parts[1]);

                if ($currentCategory) {
                    $result[$currentCategory][$key] = $value;
                }
            }
        }

        return $result;
    }

    public function get(string $identifier): object
    {
        if (isset($this->instances[$identifier])) {
            return $this->instances[$identifier];
        }

        $className = $this->resolveClassName($identifier);

        if (!class_exists($className)) {
            throw new Exception("La classe {$className} n'existe pas");
        }

        $instance = $this->createInstance($className);

        $this->instances[$identifier] = $instance;
        $this->instances[$className] = $instance;

        return $instance;
    }

    private function resolveClassName(string $identifier): string
    {
        foreach ($this->services as $category => $services) {
            if (is_array($services) && isset($services[$identifier])) {
                return $services[$identifier];
            }
        }

        if (class_exists($identifier)) {
            return $identifier;
        }

        foreach ($this->services as $category => $services) {
            if (is_array($services)) {
                foreach ($services as $key => $className) {
                    if ($className === $identifier || 
                        basename(str_replace('\\', '/', $className)) === $identifier) {
                        return $className;
                    }
                }
            }
        }

        throw new Exception("Impossible de résoudre l'identifiant: {$identifier}");
    }

    private function createInstance(string $className): object
    {
        $reflection = new ReflectionClass($className);

        $constructor = $reflection->getConstructor();
        if (!$constructor) {
            return new $className();
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependencies[] = $this->resolveDependency($parameter);
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    private function resolveDependency(ReflectionParameter $parameter): object
    {
        $type = $parameter->getType();
        
        if (!$type || $type->isBuiltin()) {
            throw new Exception("Impossible de résoudre la dépendance pour le paramètre: " . $parameter->getName());
        }

        $className = $type->getName();
        
        return $this->get($className);
    }

    public function bind(string $abstract, string $concrete): void
    {
        if (!isset($this->services['bindings'])) {
            $this->services['bindings'] = [];
        }
        $this->services['bindings'][$abstract] = $concrete;
    }

    public function reload(): void
    {
        $this->services = [];
        $this->instances = [];
        $this->loadServices();
    }
}

