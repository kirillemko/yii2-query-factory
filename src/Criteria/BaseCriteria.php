<?php

namespace kirillemko\QueryFactory\Criteria;


use kirillemko\QueryFactory\Interfaces\CriteriaInterface;
use yii\db\ActiveQuery;

abstract class BaseCriteria implements CriteriaInterface
{

    /** @var bool Для совмещения этого критерия внутри OR другого запроса */
    protected bool $returnInNewQuery = false;

    protected string $alias = '';

    public function setReturnInNewQuery(): self
    {
        $this->returnInNewQuery = true;
        return $this;
    }

    public function setAlias($alias): self
    {
        $this->alias = $alias;
        return $this;
    }


    abstract public function apply(ActiveQuery $query): ActiveQuery;
}