<?php
namespace Azuriom\Plugin\GamingHubCore\Contracts;
interface CapabilityReaderRegistry { public function register(string $providerType,string $capability,string $readerClass):void; public function has(string $providerType,string $capability):bool; public function readerClass(string $providerType,string $capability):?string; }
