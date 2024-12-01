<?php

namespace Hyyan\WPI\Tools;

final class FlashMessages
{
    private const OPTION_NAME = 'hyyan-wpi-flash-messages';

    public static function register(): void
    {
        add_action('admin_notices', [__CLASS__, 'display']);
    }

    public static function add(
        string $id,
        string $message,
        array $classes = ['updated'],
        bool $persist = false
    ): void {
        $messages = self::getMessages();
        $data = [
            'id' => $id,
            'message' => $message,
            'classes' => $classes,
            'persist' => $persist,
        ];

        $messages[$id] = isset($messages[$id]) ? 
            array_replace_recursive($messages[$id], $data) : 
            $data;

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
                implode(' ', $message['classes']),
                $message['message']
            );
        }

        update_option(self::OPTION_NAME, $messages);
    }

    public static function clearMessages(): void
    {
        delete_option(self::OPTION_NAME);
    }

    private static function getMessages(): array
    {
        $messages = get_option(self::OPTION_NAME, []);
        return is_array($messages) ? $messages : [];
    }

    private static function shouldDisplayMessage(array $message): bool
    {
        if (!isset($message['displayed'])) {
            return true;
        }

        return $message['persist'] || !$message['displayed'];
    }
}
