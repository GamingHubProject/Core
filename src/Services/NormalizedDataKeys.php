<?php
namespace Azuriom\Plugin\GamingHubCore\Services;
final class NormalizedDataKeys { public const GENERIC=['server.state','server.version','server.name','server.message','players.current','players.maximum','resources.cpu_percent','resources.memory_used_bytes','resources.memory_limit_bytes','resources.disk_used_bytes','uptime.seconds','observed_at','source_updated_at']; public static function valid(string $key):bool{return in_array($key,self::GENERIC,true)||preg_match('/^[a-z][a-z0-9-]*\.[a-z][a-z0-9_.-]*$/',$key)===1;} }
