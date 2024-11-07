<?php

namespace Topdata\TopdataFoundationSW6\DTO;

/**
 * A storage class for configuration of a (topdata)plugin
 *
 * 11/2024 created
 */
final class TopConfig
{
    public function __construct(
        private string $name,
        private array  $systemConfig,
        private array  $mapping
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSystemConfig(): array
    {
        return $this->systemConfig;
    }

    public function setSystemConfig(array $systemConfig): void
    {
        $this->systemConfig = $systemConfig;
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }

    public function setMapping(array $mapping): void
    {
        $this->mapping = $mapping;
    }


}
