<?php

namespace Topdata\TopdataFoundationSW6\Service;

use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Context\CachedSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Topdata\TopdataFoundationSW6\Util\CliLogger;

/**
 * 05/2025 created (extracted from Command_TestFetchLinkedProducts)
 */
class SalesChannelSelectionService
{

    public function __construct(
        private readonly EntityRepository                 $salesChannelRepository,
        private readonly CachedSalesChannelContextFactory $salesChannelContextFactory,
    )
    {
    }

    public function askForSalesChannelId(): string
    {
        // ---- Fetch available sales channels
        $criteria = new Criteria();
        $criteria->addAssociation('domains');
        $salesChannels = $this->salesChannelRepository->search($criteria, Context::createDefaultContext());

        $choices = [];
        foreach ($salesChannels as $salesChannel) {
            $domain = $salesChannel->getDomains()->first();
            $url = $domain ? $domain->getUrl() : 'no domain';
            $choices[$salesChannel->getId()] = sprintf('%s (%s)', $salesChannel->getName(), $url);
        }

        if (empty($choices)) {
            throw new \RuntimeException('No sales channels found');
        }

        $defaultSalesChannelId = array_keys($choices)[0];
        $salesChannelId = CliLogger::getCliStyle()->choice('Select sales channel', $choices, $defaultSalesChannelId);

        CliLogger::info('Selected sales channel: ' . $salesChannelId);

        return $salesChannelId;
    }


    /**
     * 05/2025 created
     */
    public function askToGetSalesChannelContext(): SalesChannelContext
    {
        $salesChannelId = $this->askForSalesChannelId();

        // Create sales channel context
        $context = new Context(new SystemSource());
        $salesChannelContext = $this->salesChannelContextFactory->create(
            '',  // token can be empty for admin context
            $salesChannelId,
            [],  // no specific options needed
            $context
        );

        return $salesChannelContext;
    }

    public function askForSalesChannel(): SalesChannelEntity
    {
        $id = $this->askForSalesChannelId();

        return $this->salesChannelRepository->search(new Criteria([$id]), Context::createDefaultContext())->first();
    }

}