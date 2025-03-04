<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Uuid\Uuid;
use Topdata\TopdataFoundationSW6\Constants\TopdataJobStatusConstants;
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
     * Start create a new job report in the database with status RUNNING
     *
     * @param string $jobType see TopdataJobTypeConstants
     * @param string $commandLine the command line that was used to start the job
     */
    public function newJobReport(string $jobType, string $commandLine): void
    {
        $reportId = Uuid::randomHex();

        $this->topdataReportRepository->create([
            [
                'id'          => $reportId,
                'pid'         => getmypid(),
                'jobStatus'   => TopdataJobStatusConstants::RUNNING,
                'jobType'     => $jobType, // eg WEBSERVICE_IMPORT
                'commandLine' => $commandLine,
                'startedAt'   => new \DateTime(),
                'reportData'  => [],
            ]
        ], Context::createDefaultContext());

        $this->currentReportId = $reportId;
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
                'id'         => $this->currentReportId,
                'jobStatus'  => TopdataJobStatusConstants::SUCCEEDED,
                'finishedAt' => new \DateTime(),
                'reportData' => $reportData,
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
                'id'         => $this->currentReportId,
                'jobStatus'  => TopdataJobStatusConstants::FAILED,
                'finishedAt' => new \DateTime(),
                'reportData' => $reportData,
            ]
        ], Context::createDefaultContext());
    }
}
