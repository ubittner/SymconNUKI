<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/Validator.php';

class SymconNUKIValidationTest extends TestCaseSymconValidation
{
    public function testValidateSymconNUKI(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }

    public function testValidateBridgeModule(): void
    {
        $this->validateModule(__DIR__ . '/../Bridge');
    }

    public function testValidateConfiguratorModule(): void
    {
        $this->validateModule(__DIR__ . '/../Configurator');
    }

    public function testValidateDiscoveryModule(): void
    {
        $this->validateModule(__DIR__ . '/../Discovery');
    }

    public function testValidateOpenerModule(): void
    {
        $this->validateModule(__DIR__ . '/../Opener');
    }

    public function testValidateSmartLockModule(): void
    {
        $this->validateModule(__DIR__ . '/../SmartLock');
    }
}