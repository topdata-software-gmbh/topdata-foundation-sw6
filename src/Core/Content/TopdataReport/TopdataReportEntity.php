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

    protected string $commandLine;
    protected string $jobType;
    protected string $jobStatus;
    protected string $pid;
    protected \DateTimeInterface $startedAt;
    protected ?\DateTimeInterface $finishedAt = null;
    protected array $reportData;

    public function getCommandLine(): string
    {
        return $this->commandLine;
    }

    public function getStartedAt(): \DateTimeInterface
    {
        return $this->startedAt;
    }

    public function getFinishedAt(): ?\DateTimeInterface
    {
        return $this->finishedAt;
    }

    public function getReportData(): array
    {
        return $this->reportData;
    }

    public function getJobType(): string
    {
        return $this->jobType;
    }

    public function getJobStatus(): string
    {
        return $this->jobStatus;
    }

    public function getPid(): string
    {
        return $this->pid;
    }
}
