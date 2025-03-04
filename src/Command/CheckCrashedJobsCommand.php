<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topdata\TopdataFoundationSW6\Service\TopdataReportService;
use Topdata\TopdataFoundationSW6\Util\CliLogger;

#[AsCommand(
    name: 'topdata:foundation:check-crashed-jobs',
    description: 'Check for crashed jobs and mark them in the report table'
)]
class CheckCrashedJobsCommand extends AbstractTopdataCommand
{
    public function __construct(
        private readonly TopdataReportService $topdataReportService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command checks for jobs that are marked as running but have no active process.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        CliLogger::title('Checking for crashed jobs');

        $crashedCount = $this->topdataReportService->findAndMarkCrashedJobs();

        CliLogger::success("Marked $crashedCount jobs as crashed");
        return Command::SUCCESS;
    }
}
