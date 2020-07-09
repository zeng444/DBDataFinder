<?php

namespace Janfish\Database\Criteria\Adapter;

/**
 * Author:Robert
 *
 * Interface DirectiveInterface
 * @package Janfish\Database\Criteria\Adapter
 */
interface DirectiveInterface
{

    /**
     * @param string $field
     * @param array $value
     * @return mixed
     */
    public function makeInFilter(string $field, array $value);

    /**
     * @param string $field
     * @param array $value
     * @return mixed
     */
    public function makeNotInFilter(string $field, array $value);

    /**
     * @param string $field
     * @param string $value
     * @return mixed
     */
    public function makeNeqFilter(string $field, string $value);

    /**
     * @param string $field
     * @param string $value
     * @return mixed
     */
    public function makeEqFilter(string $field, string $value);

    /**
     * @param string $field
     * @param string $value
     * @return mixed
     */
    public function makeGtFilter(string $field, string $value);

    /**
     * @param string $field
     * @param string $value
     * @return mixed
     */
    public function makeLtFilter(string $field, string $value);

    /**
     * @param string $field
     * @param string $value
     * @return mixed
     */
    public function makeGteFilter(string $field, string $value);

    /**
     * @param string $field
     * @param string $value
     * @return mixed
     */
    public function makeLteFilter(string $field, string $value);

    /**
     * @param string $field
     * @param string $value
     * @return mixed
     */
    public function makeRegexFilter(string $field, string $value);
}