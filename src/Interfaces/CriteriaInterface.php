<?php

namespace kirillemko\QueryFactory\Interfaces;

use yii\db\ActiveQuery;

/**
 * Interface CriteriaInterface
 * @package Horat1us\Yii\Criteria\Interfaces
 */
interface CriteriaInterface
{
    public function apply(ActiveQuery $query): ActiveQuery;
}