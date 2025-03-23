<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topdata\TopdataFoundationSW6\Service\TopdataReportService;
use Shopware\Core\System\SystemConfig\SystemConfigService;

#[AsCommand(
    name: 'topdata:foundation:reports:set-password',
    description: 'Set password for reports access',
)]
class SetReportsPasswordCommand extends AbstractTopdataCommand
{
    public function __construct(
        private readonly TopdataReportService $reportService,
        private readonly SystemConfigService $systemConfigService
    ) {
        parent::__construct();
    }


    protected function configure(): void {
        $this->addArgument('password', InputArgument::REQUIRED, 'New password');
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $password = $input->getArgument('password');
        $this->reportService->setReportsPassword($password);
        $output->writeln('Password updated successfully');

        $this->done();

        return Command::SUCCESS;
    }
}