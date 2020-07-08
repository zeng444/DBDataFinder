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

    public function makeInFilter($value);

    public function makeNotInFilter($value);

    public function makeNeqFilter($value);

    public function makeEqFilter($value);

    public function makeGtFilter($value);

    public function makeLtFilter($value);

    public function makeGteFilter($value);

    public function makeLteFilter($value);

    public function makeRegexFilter($value);
}