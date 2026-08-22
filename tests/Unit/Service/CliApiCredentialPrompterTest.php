<?php

declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Topdata\TopdataFoundationSW6\Service\AbstractTopdataWebserviceV2Client;
use Topdata\TopdataFoundationSW6\Service\CliApiCredentialPrompter;

/**
 * Tests the prompter flow headlessly via CommandTester::setInputs()
 * (ArrayInput implements StreamableInputInterface, verified in vendor).
 */
class CliApiCredentialPrompterTest extends TestCase
{
    private _FakeSystemConfigService $configService;
    private _ProbeClient $client;
    private CliApiCredentialPrompter $prompter;

    protected function setUp(): void
    {
        $this->configService = new _FakeSystemConfigService([]);
        $this->client        = new _ProbeClient($this->configService);
        $this->prompter      = new CliApiCredentialPrompter($this->configService);
    }

    public function testValidConfigShortCircuitsWithoutPrompts(): void
    {
        $this->configService->setMultiple([
            'TestPlugin.config.apiBaseUrl' => 'https://ws.example.com',
            'TestPlugin.config.apiKey'     => 'sk-' . str_repeat('a', 32),
        ]);
        $this->client->reloadConfig();

        $tester = $this->_tester();
        $tester->execute([], ['interactive' => true]); // no inputs provided; would hang if prompted
        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertSame(0, $this->client->testCalls);
    }

    public function testNonInteractiveReturnsFalse(): void
    {
        $tester = $this->_tester();
        $tester->execute([], ['interactive' => false]);
        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertSame([], $this->configService->values);
    }

    public function testInteractiveFlowPersistsAndVerifiesCredentials(): void
    {
        $tester = $this->_tester();
        // first attempt fails the connection test, second succeeds
        $this->client->failNextTest = true;

        $tester->setInputs([
            'y',                                                        // confirm: enter credentials now
            'https://ws.example.com', 'sk-' . str_repeat('a', 32),      // first attempt (api key hidden)
            'y',                                                        // confirm: retry
            'https://ws.example.com', 'sk-' . str_repeat('b', 32),      // second attempt
        ]);
        $tester->execute([], ['interactive' => true]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertSame(2, $this->client->testCalls);
        $this->assertSame('sk-' . str_repeat('b', 32), $this->configService->values['TestPlugin.config.apiKey']);
    }

    public function testAbortAfterFailedTestRevertsCredentials(): void
    {
        $tester                     = $this->_tester();
        $this->client->failNextTest = true;

        $tester->setInputs([
            'y',                                                        // confirm: enter credentials now
            'https://ws.example.com', 'sk-' . str_repeat('a', 32),
            'n',                                                        // confirm: no retry
        ]);
        $tester->execute([], ['interactive' => true]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertSame([], $this->configService->values, 'credentials must be reverted after aborted attempt');
    }

    public function testInteractiveFlowDerivesKeyFromV1Credentials(): void
    {
        $this->configService->setMultiple([
            'TopdataConnectorSW6.config' => [
                'apiUid'         => 6,
                'apiSecurityKey' => 'oateouq974fpby5t6ldf8glzo85mr9t6aebozrox',
            ],
        ]);

        $tester = $this->_tester();
        $tester->setInputs([
            'y',                                                    // confirm: enter credentials now
            'https://ws.example.com', 'y',                          // base url + confirm: derive key
        ]);
        $tester->execute([], ['interactive' => true]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertSame(1, $this->client->testCalls);
        $this->assertSame(
            'sk-tdws-EAvAHSJZzYgCc2FptDzJjDHtpFKmYhxXW6cvzHwnRGcvo',
            $this->configService->values['TestPlugin.config.apiKey']
        );
    }

    public function testDeriveOfferCanBeDeclinedForManualEntry(): void
    {
        $this->configService->setMultiple([
            'TopdataConnectorSW6.config' => [
                'apiUid'         => 6,
                'apiSecurityKey' => 'oateouq974fpby5t6ldf8glzo85mr9t6aebozrox',
            ],
        ]);

        $tester = $this->_tester();
        $tester->setInputs([
            'y',                                                    // confirm: enter credentials now
            'https://ws.example.com', 'n', 'sk-' . str_repeat('a', 32),
        ]);
        $tester->execute([], ['interactive' => true]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertSame('sk-' . str_repeat('a', 32), $this->configService->values['TestPlugin.config.apiKey']);
    }

    public function testNoDeriveOfferWithoutV1Credentials(): void
    {
        $tester = $this->_tester();
        // only 4 inputs: if a derive offer appeared, the input stream would
        // run dry and the command would fail/hang
        $tester->setInputs([
            'y',                                                    // confirm: enter credentials now
            'https://ws.example.com', 'sk-' . str_repeat('a', 32),
        ]);
        $tester->execute([], ['interactive' => true]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertSame('sk-' . str_repeat('a', 32), $this->configService->values['TestPlugin.config.apiKey']);
    }

    private function _tester(): CommandTester
    {
        return new CommandTester(new _PromptHarnessCommand($this->prompter, $this->client));
    }
}

final class _ProbeClient extends AbstractTopdataWebserviceV2Client
{
    public int $testCalls     = 0;
    public bool $failNextTest = false;

    public function __construct(_FakeSystemConfigService $configService)
    {
        parent::__construct($configService, 'TestPlugin.config');
    }

    public function testConnection(): void
    {
        $this->testCalls++;
        if ($this->failNextTest) {
            $this->failNextTest = false;
            throw new \RuntimeException('HTTP Error: 400');
        }
    }
}

final class _PromptHarnessCommand extends Command
{
    public function __construct(
        private readonly CliApiCredentialPrompter $prompter,
        private readonly AbstractTopdataWebserviceV2Client $client,
    ) {
        parent::__construct('test:prompt');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->prompter->ensureValidApiConfig($input, $output, $this->client, 'TestPlugin')
            ? Command::SUCCESS
            : Command::FAILURE;
    }
}
