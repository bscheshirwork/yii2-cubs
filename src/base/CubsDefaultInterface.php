<?php

namespace bscheshirwork\cubs\base;

/**
 * Provide default set of constant
 *
 * example:
 *
 * class m170130_100000_createProjectTables extends Migration implements \bscheshirwork\cubs\base\CubsDefaultInterface
 * {
 *   use \bscheshirwork\cubs\db\CubsMigrationTrait;
 * }
 *
 * Interface CubsDefaultInterface
 * @package bscheshirwork\cubs\base
 */
interface CubsDefaultInterface
{
    const FIELD_CREATE_AT = 'createdAt';
    const FIELD_CREATE_BY = 'createdBy';
    const FIELD_UPDATE_AT = 'updatedAt';
    const FIELD_UPDATE_BY = 'updatedBy';
    const FIELD_BLOCKED_AT = 'blockedAt';
    const FIELD_STATE = 'stateOfFlags';
    const STATE_DISABLED = 0b00;
    const STATE_ENABLED = 0b01;
    const STATE_BLOCKED = 0b10;
    const LIST_STATE = [
        self::STATE_DISABLED => 'DISABLED',
        self::STATE_ENABLED => 'ACTIVE',
        self::STATE_BLOCKED => 'BLOCKED',
    ];
}