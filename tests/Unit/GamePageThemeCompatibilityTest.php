<?php
namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;
use PHPUnit\Framework\TestCase;
final class GamePageThemeCompatibilityTest extends TestCase {
 public function test_core_owns_canonical_game_page_but_uses_theme_layout():void{$c=file_get_contents(dirname(__DIR__,2).'/src/Controllers/GameController.php');$v=file_get_contents(dirname(__DIR__,2).'/resources/views/games/show.blade.php');$this->assertStringContainsString("view('gaming-hub-core-runtime-v043::games.show-v043'",$c);$this->assertStringContainsString("@extends('layouts.app')",$v);foreach(['gh-game-page','gh-game-hero','gh-game-section','gh-server-card','gh-game-navigation','gh-empty-state'] as $x)$this->assertStringContainsString($x,$v);}
}
