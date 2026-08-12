<?php

declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Service;

use Shopware\Core\System\SystemConfig\SystemConfigException;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Topdata\TopdataFoundationSW6\Helper\CliStyle;

/**
 * Interactively collects missing API credentials on the CLI, persists them
 * to the plugin configuration and verifies them with a connection test.
 *
 * V2 credentials: API base URL + API key (sk-...). Reusable across all
 * plugins that talk to the Topdata webservice V2
 * (config keys apiBaseUrl/apiKey are standardized).
 *
 * 08/2026 created
 */
class CliApiCredentialPrompter
{
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
    ) {
    }

    /**
     * Ensures the webservice client has valid API credentials.
     *
     * When credentials are missing and the CLI runs interactively, the user is
     * asked to enter them one-by-one; the credentials are persisted and verified
     * with a connection test (with the option to retry).
     * In non-interactive contexts (cron, CI, --no-interaction) no prompt is shown.
     *
     * @return bool true when the client has valid credentials after this call
     */
    public function ensureValidApiConfig(
        InputInterface $input,
        OutputInterface $output,
        AbstractTopdataWebserviceV2Client $client,
        string $pluginName,
    ): bool {
        if ($client->hasValidConfig()) {
            return true;
        }

        if (!$input->isInteractive()) {
            return false;
        }

        $cliStyle = new CliStyle($input, $output);

        if (!$cliStyle->confirm('API credentials are not configured. Do you want to enter them now?', false)) {
            return false;
        }

        $previous = [];

        while (true) {
            $values = $this->_askCredentials($cliStyle, $previous);

            if (!$this->_saveCredentials($cliStyle, $pluginName, $values)) {
                return false;
            }

            if ($this->_testConnection($cliStyle, $client)) {
                $cliStyle->success('Connection test successful, API credentials saved.');

                return true;
            }

            if (!$cliStyle->confirm('Retry with different credentials?', false)) {
                $this->_deleteCredentials($cliStyle, $pluginName);

                return false;
            }

            $previous = $values;
        }
    }

    /**
     * Asks for the credentials one-by-one. The API key is entered hidden.
     * On retries, the previously entered base URL is prefilled.
     *
     * @param array<string, string> $previous previously entered values
     * @return array{baseUrl: string, apiKey: string}
     */
    private function _askCredentials(CliStyle $cliStyle, array $previous): array
    {
        $cliStyle->section('API Credentials');

        $baseUrl = $cliStyle->ask('API Base URL', $previous['baseUrl'] ?? null);

        return [
            'baseUrl' => rtrim((string) $baseUrl, '/'),
            'apiKey'  => $this->_askHidden($cliStyle, 'API Key (sk-...)'),
        ];
    }

    /**
     * Asks a secret value hidden, with visible fallback when the terminal
     * does not support hidden input (e.g. Windows cmd). Empty values are rejected,
     * and the key must match the webservice format (starts with "sk-", >= 10 chars).
     */
    private function _askHidden(CliStyle $cliStyle, string $prompt): string
    {
        $question = new Question($prompt);
        $question->setHidden(true);
        $question->setHiddenFallback(true);
        $question->setValidator(static function (?string $value): ?string {
            $value = trim((string) $value);
            if ($value === '') {
                return 'Value must not be empty.';
            }
            if (!str_starts_with($value, 'sk-') || strlen($value) < 10) {
                return 'API Key must start with "sk-" and be at least 10 characters long.';
            }

            return null;
        });

        return (string) $cliStyle->askQuestion($question);
    }

    /**
     * Persists the credentials to the plugin configuration.
     *
     * @param array{baseUrl: string, apiKey: string} $values
     * @return bool false when the config is managed by static files (Symfony config) and cannot be changed
     */
    private function _saveCredentials(CliStyle $cliStyle, string $pluginName, array $values): bool
    {
        try {
            $this->systemConfigService->setMultiple([
                $pluginName . '.config.apiBaseUrl' => $values['baseUrl'],
                $pluginName . '.config.apiKey'     => $values['apiKey'],
            ]);

            return true;
        } catch (SystemConfigException $e) {
            $cliStyle->error('Could not save credentials: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Reloads the client config from the system config and verifies
     * the credentials with a connection test.
     */
    private function _testConnection(CliStyle $cliStyle, AbstractTopdataWebserviceV2Client $client): bool
    {
        $client->reloadConfig();

        try {
            $client->testConnection();

            return true;
        } catch (\Throwable $e) {
            $cliStyle->error('Connection test failed: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Removes the credentials again after a failed and aborted attempt,
     * so no invalid values remain in the configuration.
     */
    private function _deleteCredentials(CliStyle $cliStyle, string $pluginName): void
    {
        try {
            foreach (['apiBaseUrl', 'apiKey'] as $key) {
                $this->systemConfigService->delete($pluginName . '.config.' . $key);
            }
        } catch (\Throwable $e) {
            $cliStyle->warning('Could not remove invalid credentials: ' . $e->getMessage());
        }
    }
}