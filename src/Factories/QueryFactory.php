<?php

namespace kirillemko\QueryFactory\Factories;

use kirillemko\QueryFactory\Interfaces\CriteriaInterface;
use kirillemko\QueryFactory\Interfaces\SortableInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\Pagination;
use yii\data\Sort;
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

    /** @var Sort */
    protected $sort;


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
    public function setPagination(Pagination $pagination=null, $params=[], $config=[]): self
    {
        $this->pagination = $pagination ?: Yii::$container->get(Pagination::class, $params, $config);
        return $this;
    }


    public function getSort(): ?Sort
    {
        return $this->sort;
    }
    public function setSort(Sort $sort=null): self
    {
        if( $sort ){
            $this->sort = $sort;
            return $this;
        }

        // Check for SearchableInterface
        if( ! isset(class_implements($this->activeQuery->modelClass)[SortableInterface::class]) ){
            return $this;
        }
        $sortParams = call_user_func($this->activeQuery->modelClass .'::sortParams');
        $sortParams['class'] = $sortParams['class'] ?? Sort::class;
        $this->sort = Yii::createObject($sortParams);

        $this->loadSortAttributesRelations();

        return $this;
    }

    private function loadSortAttributesRelations(): void
    {
        $attributes = $this->sort->getAttributeOrders();
        foreach ($attributes as $attribute => $order) {
            $lastPointPos = strrpos($attribute, '.');
            if( $lastPointPos === false ){ // no relations found
                continue;
            }
            $relationName = substr($attribute, 0, $lastPointPos);
            $this->activeQuery->joinWith($relationName);

            // PSql group_by issue fix
            if($this->activeQuery->groupBy){
                $this->activeQuery->addGroupBy($attribute);
            }
        }
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
        if( $this->sort ){
            $query->orderBy($this->sort->orders);
        }

        return $query;
    }
}
