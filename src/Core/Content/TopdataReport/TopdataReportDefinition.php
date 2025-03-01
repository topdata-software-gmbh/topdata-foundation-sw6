<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Core\Content\TopdataReport;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

/**
 * Definition for the import report entity
 */
class TopdataReportDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'topdata_report';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return TopdataReportEntity::class;
    }

    public function getCollectionClass(): string
    {
        return TopdataReportCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new StringField('status', 'status'))->addFlags(new Required()),
            (new StringField('command_line', 'commandLine'))->addFlags(new Required()),
            (new DateTimeField('started_at', 'startedAt'))->addFlags(new Required()),
            (new DateTimeField('succeeded_at', 'succeededAt')),
            (new JsonField('report_data', 'reportData'))->addFlags(new Required()),
        ]);
    }
}
