<?php

namespace kirillemko\QueryFactory\Interfaces;

interface SortableInterface {

    /**
     * See Sort doc for params
     * https://www.yiiframework.com/doc/guide/2.0/en/output-sorting
     */
    public static function sortParams() : array;
    
}