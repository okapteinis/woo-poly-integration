<?php

declare(strict_types=1);

namespace Hyyan\WPI\Tools;

class FlashMessages
{
    private const OPTION_NAME = 'wpi-flash-messages';

    public static function add(string $id, string $message, array $classes = [], bool $persist = false): void
    {
        $messages = self::getMessages();
        $data = [
            'id' => $id,
            'message' => $message,
            'classes' => $classes,
            'persist' => $persist,
        ];

        $messages[$id] = isset($messages[$id]) 
            ? array_replace_recursive($messages[$id], $data) 
            : $data;

        update_option(self::OPTION_NAME, $messages);
    }

    public static function remove(string $id): bool
    {
        $messages = self::getMessages();
        if (!isset($messages[$id])) {
            return false;
        }

        unset($messages[$id]);
        update_option(self::OPTION_NAME, $messages);
        return true;
    }

    public static function display(): void
    {
        $messages = self::getMessages();
        foreach ($messages as $id => $message) {
            if (!self::shouldDisplayMessage($message)) {
                continue;
            }

            $messages[$id]['displayed'] = !$message['persist'];
            $message['classes'][] = 'is-dismissible notice';

            printf(
                '<div class="%s"><p>%s</p></div>',
                esc_attr(implode(' ', $message['classes'])),
                wp_kses_post($message['message'])
            );
        }

        update_option(self::OPTION_NAME, $messages);
    }

    private static function getMessages(): array
    {
        return get_option(self::OPTION_NAME, []);
    }

    private static function shouldDisplayMessage(array $message): bool
    {
        return !isset($message['displayed']) || $message['persist'];
    }
}
