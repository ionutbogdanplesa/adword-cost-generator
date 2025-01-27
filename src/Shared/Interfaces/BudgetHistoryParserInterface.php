<?php
namespace AdWords\Shared\Interfaces;

interface BudgetHistoryParserInterface
{
    public function parse(string $filepath): array;
}
