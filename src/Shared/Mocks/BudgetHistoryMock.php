<?php
namespace AdWords\Shared\Mocks;

class BudgetHistoryMock
{
    public static function get(): array
    {
        return [
            '2019-01-01' => [
                ['time' => '10:00', 'amount' => 7],
                ['time' => '11:00', 'amount' => 0],
                ['time' => '12:00', 'amount' => 1],
                ['time' => '23:00', 'amount' => 6]
            ],
            '2019-01-05' => [
                ['time' => '10:00', 'amount' => 2],
            ],
            '2019-01-06' => [
                ['time' => '00:00', 'amount' => 0],
            ],
            '2019-02-09' => [
                ['time' => '13:13', 'amount' => 1],
            ],
            '2019-03-01' => [
                ['time' => '12:00', 'amount' => 0],
                ['time' => '14:00', 'amount' => 1],
            ],
            '2019-03-22' => [
                ['time' => '11:00', 'amount' => 0],
            ],
        ];
    }
}
