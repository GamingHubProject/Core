<?php
namespace Azuriom\Plugin\GamingHubCore\Tests\Unit; use PHPUnit\Framework\TestCase;
final class ServerThemeCompatibilityTest extends TestCase { public function test_views_extend_standard_theme_layout():void{$root=dirname(__DIR__,2);foreach(['resources/views/games/show.blade.php','resources/views/servers/show.blade.php'] as $view)$this->assertStringContainsString("@extends('layouts.app')",file_get_contents($root.'/'.$view));} }
