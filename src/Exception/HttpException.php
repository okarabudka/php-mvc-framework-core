<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Exception;

use RuntimeException;

/**
 * An error that maps to an HTTP status code; its code is sent as the response status.
 */
abstract class HttpException extends RuntimeException {}
