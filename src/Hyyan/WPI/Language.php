public function downloadWhenPolylangAddLanguage(): bool
{
    if ('off' === Settings::getOption('language-downloader', Features::getID(), 'on')) {
        return false;
    }

    if (!isset($_REQUEST['pll_action']) ||
        'add' !== esc_attr($_REQUEST['pll_action'] ?? '')
    ) {
        return false;
    }

    $name = esc_attr($_REQUEST['name'] ?? '');
    $locale = esc_attr($_REQUEST['locale'] ?? '');
    if ('en_us' === strtolower($locale)) {
        return true;
    }

    try {
        return TranslationsDownloader::download($locale, $name);
    } catch (RuntimeException $ex) {
        add_settings_error(
            'general',
            $ex->getCode(),
            $ex->getMessage()
        );
        return false;
    }
}
