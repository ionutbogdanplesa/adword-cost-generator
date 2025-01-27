<?php
namespace AdWords\Services\Base;

use AdWords\Entities\Campaign;

class BaseCampaignService
{
    public function __construct(public readonly Campaign $campaign)
    {
    }

    protected function getCampaign(): Campaign
    {
        return $this->campaign;
    }
}
