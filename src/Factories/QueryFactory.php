<?php

namespace kirillemko\QueryFactory\Factories;

use kirillemko\QueryFactory\Interfaces\CriteriaInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\di\Instance;

class QueryFactory
{
    /** @var ActiveQuery */
    protected $activeQuery;

    /** @var CriteriaInterface[] */
    protected $criteria = [];

    /** @var Pagination */
    protected $pagination;


    /**
     * @param ActiveQuery $query
     */
    public function __construct(ActiveQuery $query)
    {
        $this->activeQuery = $query;
    }


    public function getActiveQuery(): ActiveQuery
    {
        return $this->activeQuery;
    }
    public function setActiveQuery(ActiveQuery $activeQuery): self
    {
        $this->activeQuery = $activeQuery;
        return $this;
    }


    public function getPagination(): ?Pagination
    {
        return $this->pagination;
    }
    public function setPagination(Pagination $pagination=null, $params=[]): self
    {
        $this->pagination = $pagination ?: Yii::$container->get(Pagination::class, $params);
        return $this;
    }




    /**
     * @param string|array|CriteriaInterface $criterion
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function pushCriteria($criterion): self
    {
        try {
            $this->criteria[] = Instance::ensure($criterion, CriteriaInterface::class);
        } catch (InvalidConfigException $exception) {
            throw new \InvalidArgumentException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return $this;
    }




    public function build($clone=true): ActiveQuery
    {
        $query = $clone ? clone $this->activeQuery : $this->activeQuery;
        foreach ($this->criteria as $criterion) {
            $criterion->apply($query);
        }


        if( $this->pagination ){
            $this->pagination->totalCount = $query->count();
            $query->offset($this->pagination->offset);
            $query->limit($this->pagination->limit);
        }

        return $query;
    }
}
