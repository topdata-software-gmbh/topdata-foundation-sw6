<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topdata\TopdataFoundationSW6\Service\TopdataReportService;

class SetReportsPasswordCommand extends Command
{
    protected static $defaultName = 'topdata:reports:set-password';

    public function __construct(
        private readonly TopdataReportService $reportService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Set password for reports access')
            ->addArgument('password', InputArgument::REQUIRED, 'New password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $password = $input->getArgument('password');
        $this->reportService->setReportsPassword($password);
        $output->writeln('Password updated successfully');
        
        return Command::SUCCESS;
    }
}