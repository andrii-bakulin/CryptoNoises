<?php

namespace CryptoNoisesTezos\Models;

use CryptoCore\Vss\Vss;
use CryptoCore\Vss\VssModel;

class TokenTags extends VssModel
{
    public static function model($className=__CLASS__): self
    {
        return parent::model($className);
    }

    public function tableName(): string
    {
        return 'token_tags';
    }

    public static function NormalizeTag(?string $tag): ?string
    {
        $tag = strtolower((string) $tag);
        $tag = preg_replace("/[#\n\r\t ]+/", ' ', $tag);
        $tag = trim($tag);

        return ($tag && mb_strlen($tag) <= 32) ? $tag : null;
    }

    public function getOrCreateTagIdByTag(?string $tag): ?int
    {
        $tagNormalized = self::NormalizeTag($tag);

        if ($tagNormalized == null)
            return null;

        $tagId = $this->getIdByTag($tagNormalized);

        if ($tagId == null)
            $this->insert($tagNormalized);

        return $this->getIdByTag($tagNormalized);
    }

    private function getIdByTag(string $tagNormalized): ?int
    {
        $sql = "SELECT token_tag_id FROM {$this->tableName()} WHERE tag=:tag";

        return Vss::dbTezos()->queryScalar($sql, ['tag' => $tagNormalized]);
    }

    private function insert(string $tagNormalized): void
    {
        Vss::dbTezos()->insert($this->tableName(), ['tag' => $tagNormalized], 'IGNORE');
    }

    public function selectTagsForToken(int $contractAdid, int $tokenUnid): array
    {
        $sql = "
            SELECT t.tag 
              FROM {$this->tableName()} AS t 
              JOIN ".TokensRefTags::model()->tableName()." AS ref
                ON ref.token_tag_id = t.token_tag_id
               AND ref.contract_adid = :contract_adid
               AND ref.token_unid = :token_unid
            ORDER BY 1
        ";

        return Vss::dbTezos()->queryColumn($sql, [
            'contract_adid' => $contractAdid,
            'token_unid'    => $tokenUnid,
        ]);
    }
}
