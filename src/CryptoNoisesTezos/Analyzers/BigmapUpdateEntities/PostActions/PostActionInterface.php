<?php

namespace CryptoNoisesTezos\Analyzers\BigmapUpdateEntities\PostActions;

interface PostActionInterface
{
    /**
     * @param int $bigmap
     * @param int $level
     * @param mixed $key
     * @param string $cValString
     * @return void
     */
    public function save(int $bigmap, int $level, $key, string $cValString): void;

    /**
     * @param int $bigmap
     * @param int $level
     * @param mixed $key
     * @return void
     */
    public function remove(int $bigmap, int $level, $key): void;

    /**
     * @param int $bigmap
     * @param int $level
     * @return void
     */
    public function removeAllInBigmap(int $bigmap, int $level): void;
}
