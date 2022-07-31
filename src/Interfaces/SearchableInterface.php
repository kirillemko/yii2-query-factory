<?php

namespace kirillemko\QueryFactory\Interfaces;

interface SearchableInterface {

    /**
     * @return string[]
     */
    public static function searchFields() : array;
}