<?php

namespace Hyyan\WPI;

use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;
use Hyyan\WPI\Utilities;
use stdClass;

class Reports
{
    protected ?string $tab;
    protected ?string $report;

    public function __construct()
    {
        $this->tab = isset($_GET['tab']) ? esc_attr($_GET['tab']) : null;
        $this->report = isset($_GET['report']) ? esc_attr($_GET['report']) : null;

        if ('off' === Settings::getOption('reports', Features::getID(), 'on')) {
            return;
        }

        if ('orders' === $this->tab || $this->report === null) {
            add_filter('woocommerce_reports_get_order_report_data', [$this, 'combineProductsByLanguage']);
            add_filter('woocommerce_reports_get_order_report_query', [$this, 'filterProductByLanguage']);
        }

        add_filter('woocommerce_report_most_stocked_query_from', [$this, 'filterStockByLanguage']);
        add_filter('woocommerce_report_out_of_stock_query_from', [$this, 'filterStockByLanguage']);
        add_filter('woocommerce_report_low_in_stock_query_from', [$this, 'filterStockByLanguage']);

        add_action('admin_init', [$this, 'translateProductIDS']);
        add_action('admin_init', [$this, 'translateCategoryIDS']);
        add_filter(
            'woocommerce_report_sales_by_category_get_products_in_category',
            [$this, 'addProductsInCategoryTranslations'],
            10,
            2
        );
    }

    public function filterProductByLanguage(array $query): array
    {
        $reports = ['sales_by_product', 'sales_by_category'];
        
        if (!in_array($this->report, $reports, true) || isset($_GET['product_ids'])) {
            return $query;
        }

        $lang = pll_current_language() ? [pll_current_language()] : pll_languages_list();
        $query['join'] .= PLL()->model->post->join_clause('posts');
        $query['where'] .= PLL()->model->post->where_clause($lang, 'post');

        return $query;
    }

    public function combineProductsByLanguage(array|false $results): array|false
    {
        if (!is_array($results)) {
            return $results;
        }

        $mode = $this->determineReportMode($results);
        if (!$mode) {
            return $results;
        }

        $lang = pll_current_language() ?: get_user_meta(get_current_user_id(), 'user_lang', true);
        $translated = $this->translateProducts($results, $lang);
        
        return array_values($this->combineUniqueProducts($translated, $mode));
    }

    private function determineReportMode(array $results): ?string
    {
        if (isset($results['0']->order_item_qty)) {
            return 'top_sellers';
        }
        if (is_array($results) && isset($results['0']->order_item_total)) {
            return 'top_earners';
        }
        return null;
    }

    private function translateProducts(array $results, string $lang): array
    {
        $translated = [];
        foreach ($results as $data) {
            $translation = Utilities::getProductTranslationByID($data->product_id, $lang);
            if ($translation) {
                $data->from = $data->product_id;
                $data->product_id = $translation->get_id();
            }
            $translated[] = $data;
        }
        return $translated;
    }

    private function combineUniqueProducts(array $translated, string $mode): array
    {
        $unique = [];
        foreach ($translated as $data) {
            if (!isset($unique[$data->product_id])) {
                $unique[$data->product_id] = $data;
                continue;
            }
            
            $property = $mode === 'top_sellers' ? 'order_item_qty' : 'order_item_total';
            $unique[$data->product_id]->$property += $data->$property;
        }
        return $unique;
    }

}
