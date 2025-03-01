<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Core\Content\TopdataReport;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

/**
 * Entity for import report
 */
class TopdataReportEntity extends Entity
{
    use EntityIdTrait;

    protected string $status;
    protected string $commandLine;
    protected \DateTimeInterface $startedAt;
    protected ?\DateTimeInterface $succeededAt = null;
    protected array $reportData;

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCommandLine(): string
    {
        return $this->commandLine;
    }

    public function getStartedAt(): \DateTimeInterface
    {
        return $this->startedAt;
    }

    public function getSucceededAt(): ?\DateTimeInterface
    {
        return $this->succeededAt;
    }

    public function getReportData(): array
    {
        return $this->reportData;
    }
}
