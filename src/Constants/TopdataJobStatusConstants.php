<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Constants;

/**
 * Constants for import report statuses
 * 
 * 02/2025 created
 */
class TopdataJobStatusConstants
{
    public const RUNNING   = 'RUNNING';
    public const SUCCEEDED = 'SUCCEEDED';
    public const FAILED    = 'FAILED'; // exception got caught
    public const CRASHED = 'CRASHED'; // it crashed or was interrupted with ctrl+c
}
