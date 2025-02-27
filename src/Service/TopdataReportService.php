<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Uuid\Uuid;
use Topdata\TopdataFoundationSW6\Constants\TopdataReportStatusConstants;
use Topdata\TopdataConnectorSW6\Util\ImportReport;

/**
 * Service for managing (import) reports
 */
class TopdataReportService
{
    private ?string $currentReportId = null;

    public function __construct(
        private readonly EntityRepository $topdataReportRepository
    )
    {
    }

    /**
     * Start a new import report
     */
    public function startImport(string $commandLine): string
    {
        $reportId = Uuid::randomHex();

        $this->topdataReportRepository->create([
            [
                'id'          => $reportId,
                'status'      => TopdataReportStatusConstants::RUNNING,
                'commandLine' => $commandLine,
                'startedAt'   => new \DateTime(),
            ]
        ], Context::createDefaultContext());

        $this->currentReportId = $reportId;

        return $reportId;
    }

    /**
     * Mark the current import as succeeded
     */
    public function markAsSucceeded(array $reportData): void
    {
        if (!$this->currentReportId) {
            return;
        }

        $this->topdataReportRepository->update([
            [
                'id'          => $this->currentReportId,
                'status'      => TopdataReportStatusConstants::SUCCEEDED,
                'succeededAt' => new \DateTime(),
                'reportData'  => json_encode($reportData),
            ]
        ], Context::createDefaultContext());
    }

    /**
     * Mark the current import as failed
     */
    public function markAsFailed(array $reportData): void
    {
        if (!$this->currentReportId) {
            return;
        }

        $this->topdataReportRepository->update([
            [
                'id'          => $this->currentReportId,
                'status'      => TopdataReportStatusConstants::FAILED,
                'succeededAt' => new \DateTime(),
                'reportData'  => json_encode($reportData),
            ]
        ], Context::createDefaultContext());
    }
}
