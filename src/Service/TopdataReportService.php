<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Topdata\TopdataFoundationSW6\Constants\TopdataJobStatusConstants;
use Topdata\TopdataConnectorSW6\Util\ImportReport;
use Topdata\TopdataFoundationSW6\Core\Content\TopdataReport\TopdataReportEntity;
use Topdata\TopdataFoundationSW6\Util\CliLogger;
use Topdata\TopdataFoundationSW6\Util\UtilCli;

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

    public function findAndMarkCrashedJobs(): int
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('jobStatus', TopdataJobStatusConstants::RUNNING));
        /** @var TopdataReportEntity[] $runningJobs */
        $runningJobs = $this->topdataReportRepository->search($criteria, Context::createDefaultContext());
        $crashedCount = 0;

        // collect the PIDs of the running jobs
        foreach ($runningJobs as $job) {
            if(!$job->getPid()) {
                CliLogger::warning("Job #{$job->getId()} [{$job->getCommandLine()}] has no PID");
                continue;
            }
            if (!UtilCli::isProcessActive($job->getPid())) {
                $this->_markJobAsCrashed($job);
                $crashedCount++;
                CliLogger::notice("Marked job #{$job->getId()} [{$job->getCommandLine()}] with PID {$job->getPid()} as crashed");
            }
        }
        return $crashedCount;
    }

    private function _markJobAsCrashed(TopdataReportEntity $jobReport): void
    {
        $this->topdataReportRepository->update([
            [
                'id'         => $jobReport->getId(),
                'jobStatus'  => TopdataJobStatusConstants::CRASHED,
                'finishedAt' => new \DateTime(),
            ]
        ], Context::createDefaultContext());
    }

    /**
     * Find all reports that have no PID associated
     */
    public function findReportsWithNoPid(): EntityCollection
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('pid', null));
        return $this->topdataReportRepository->search($criteria, Context::createDefaultContext())->getEntities();
    }

    /**
     * Delete a collection of reports
     */
    public function deleteReports(EntityCollection $reports): void
    {
        $ids = array_values(array_map(function(TopdataReportEntity $report) {
            return ['id' => $report->getId()];
        }, $reports->getElements()));

        $this->topdataReportRepository->delete($ids, Context::createDefaultContext());
    }

    public function getLatestReports(int $limit = 10): EntityCollection
    {
        $criteria = new Criteria();
        $criteria->addSorting(Criteria::DESC('startedAt'));
        $criteria->setLimit($limit);
        
        return $this->topdataReportRepository
            ->search($criteria, Context::createDefaultContext())
            ->getEntities();
    }
}
