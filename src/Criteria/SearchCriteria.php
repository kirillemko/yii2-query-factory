<?php

namespace kirillemko\QueryFactory\Criteria;


use kirillemko\QueryFactory\Interfaces\CriteriaInterface;
use kirillemko\QueryFactory\Interfaces\SearchableInterface;
use yii\db\ActiveQuery;


/**
 * public static function searchFields(): array
 * {
 *  return [
 *      'modelFieldName',
 *      'modelFieldName2' => [
 *          'condition' => '=', // default 'like'
 *      ],
 *      'relationTableName.relationModelFieldName' => [
 *          'relation' => 'someRelationName'
 *      ],
 *  ];
 * }
 */
class SearchCriteria implements CriteriaInterface
{
    protected $searchString;


    public function __construct($searchString)
    {
        $this->searchString = $searchString;
    }


    public function apply(ActiveQuery $query): ActiveQuery
    {
        if( !$this->searchString ){
            return $query;
        }

        // Check for SearchableInterface
        if( ! isset(class_implements($query->modelClass)[SearchableInterface::class]) ){
            return $query;
        }

        $classSearchFields = call_user_func($query->modelClass .'::searchFields');


        $searchConditions = ['AND'];
        foreach (explode(' ', $this->searchString) as $word) {
            $wordConditions = ['OR'];
            foreach ($classSearchFields as $fieldName => $settings) {
                if( is_numeric($fieldName) ){
                    $fieldName = $settings;
                    $settings = [];
                }
                $condition = $settings['condition'] ?? $this->likeCondition();

                if( isset($settings['relation']) ){
                    $query->joinWith($settings['relation']);
                }

                if( isset($settings['type']) ){
                    switch( $settings['type'] ){
                        case 'number':
                            if( !is_numeric( $word ) ){
                                continue 2;
                            }
                    }
                }

                $wordConditions[] = [$condition, $fieldName, $word];
            }
            $searchConditions[] = $wordConditions;
        }
        $query->andWhere($searchConditions);


        return $query;
    }


    protected function likeCondition(): string
    {
        return \Yii::$app->db->driverName === 'pgsql' ? 'ilike' : 'like';
    }
}