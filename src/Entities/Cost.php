<?php

namespace AdWords\Entities;

use AdWords\Shared\Utils\DateUtil;
use DateTimeImmutable;
use JsonSerializable;

/**
 * I implemented the JsonSerializable interface to be able to
 * serialize the object to JSON for the API
 */
class Cost implements JsonSerializable {

    public function __construct(private readonly float $amount, private readonly DateTimeImmutable $datetime)
    {
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getDateTime(): DateTimeImmutable
    {
        return $this->datetime;
    }

    public function jsonSerialize(): array
    {
        return [
            'amount' => $this->amount,
            'timestamp' => $this->datetime->format(DateUtil::DATE_TIME_FORMAT),
        ];
    }

}
