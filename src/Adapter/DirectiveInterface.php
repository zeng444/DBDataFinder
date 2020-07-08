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

    public function makeInFilter(string $field, array $value);

    public function makeNotInFilter(string $field, array $value);

    public function makeNeqFilter(string $field, string $value);

    public function makeEqFilter(string $field, string $value);

    public function makeGtFilter(string $field, string $value);

    public function makeLtFilter(string $field, string $value);

    public function makeGteFilter(string $field, string $value);

    public function makeLteFilter(string $field, string $value);

    public function makeRegexFilter(string $field, string $value);
}