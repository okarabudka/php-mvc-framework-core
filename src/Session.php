<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore;

/**
 * Wraps $_SESSION. Flash messages set during one request are readable in the next one and then removed.
 */
class Session
{
    private const string FLASH_KEY = 'flash_messages';

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $flashMessages = $this->flashMessages();
        foreach ($flashMessages as &$flashMessage) {
            $flashMessage['remove'] = true;
        }
        unset($flashMessage);
        $_SESSION[self::FLASH_KEY] = $flashMessages;
    }

    public function __destruct()
    {
        $_SESSION[self::FLASH_KEY] = array_filter(
            $this->flashMessages(),
            static fn(array $flashMessage): bool => !$flashMessage['remove'],
        );
    }

    public function setFlash(string $key, string $message): void
    {
        $_SESSION[self::FLASH_KEY][$key] = [
            'remove' => false,
            'value' => $message,
        ];
    }

    public function getFlash(string $key): ?string
    {
        return $this->flashMessages()[$key]['value'] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    public function unset(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * @return array<string, array{remove: bool, value: string}>
     */
    private function flashMessages(): array
    {
        $flashMessages = $_SESSION[self::FLASH_KEY] ?? [];
        if (!is_array($flashMessages)) {
            return [];
        }

        $valid = [];
        foreach ($flashMessages as $key => $flashMessage) {
            if (is_array($flashMessage) && is_string($flashMessage['value'] ?? null)) {
                $valid[(string) $key] = ['remove' => ($flashMessage['remove'] ?? false) === true, 'value' => $flashMessage['value']];
            }
        }

        return $valid;
    }
}
