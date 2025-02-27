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
    protected ?string $reportData = null;

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getCommandLine(): string
    {
        return $this->commandLine;
    }

    public function setCommandLine(string $commandLine): void
    {
        $this->commandLine = $commandLine;
    }

    public function getStartedAt(): \DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeInterface $startedAt): void
    {
        $this->startedAt = $startedAt;
    }

    public function getSucceededAt(): ?\DateTimeInterface
    {
        return $this->succeededAt;
    }

    public function setSucceededAt(?\DateTimeInterface $succeededAt): void
    {
        $this->succeededAt = $succeededAt;
    }

    public function getReportData(): ?string
    {
        return $this->reportData;
    }

    public function setReportData(?string $reportData): void
    {
        $this->reportData = $reportData;
    }
}
