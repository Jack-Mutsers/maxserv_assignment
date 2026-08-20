<?php

declare(strict_types=1);

namespace MaxServ\App\Controller;

use MaxServ\App\Libraries\ProductService;
use MaxServ\App\Models\Product;
use MaxServ\Core\Render\TemplateRenderer;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class IndexController
{
    public function __construct(
        private TemplateRenderer $templateRenderer
    ) {
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function index(): void
    {
        // Your logic here
        echo $this->templateRenderer->render('index.html.twig', [
            'message' => 'Hello world!'
        ]);
    }

    /**
     * Summary of datatable
     * @return void
     */
    public function datatable(): void
    {
        $offset = (int) resolvePost('start', 0);
        $recordCount = (int) resolvePost('length', 10);

        // TODO: implement search and order functionality

        $productModel = new Product();
        $records = $productModel->getDatatable($offset, $recordCount);

        $results = $records;
        $results['data'] = [];
        foreach ($records['data'] as $record) {
            $discountPercentage = max(0, min(100, $record->discountPercentage));
            $standardPrice = number_format($record->price, 2, ',', '.');
            $discountedPrice = number_format($record->getDiscountedPrice($record->price, $discountPercentage), 2, ',', '.');

            $row = [
                'thumbnail' => "<img src='{$record->thumbnail}' alt='{$record->title}'>",
                'title' => "<a href='/product/{$record->sku}'>{$record->title}</a>",
                'price' => $discountPercentage > 0
                    ? "<span class='standard-price'>&euro;{$standardPrice}</span> <span class='discounted-price'>&euro;{$discountedPrice}</span>"
                    : "<span class='discounted-price'>&euro;{$standardPrice}</span>",
                'brand' => $record->brand
            ];

            $tagHtml = '';
            foreach ($record->tags as $tag) {
                $tagHtml .= "<span class='tag'>{$tag}</span> ";
            }

            $row['tags'] = $tagHtml;
            $results['data'][] = $row;
        }

        echo json_encode($results);
    }

    /**
     * load ecommerce products page
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function ecommerce(): void
    {
        $page = (int) resolvePost('page', 1);
        $perPage = (int) resolvePost('perPage', 16);

        $productModel = new Product();
        $totalRecords = $productModel->getRecordCount();

        $productsHtml = $this->products(true, $page, $perPage);

        echo $this->templateRenderer->render('ecommerce.html.twig', [
            'products' => $productsHtml,
            'currentPage' => $page,
            'totalPages' => max(1, (int) ceil($totalRecords / $perPage)),
        ]);
    }

    /**
     * Summary of products
     * @param bool $return
     * @param int|null $page
     * @param int|null $perPage
     * @return string|null
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function products(bool $return = false, ?int $page = null, ?int $perPage = null): ?string
    {
        $page ??= (int) resolvePost('page', 1);
        $perPage ??= (int) resolvePost('perPage', 16);

        $productModel = new Product();
        $products = $productModel->getProducts(($page -1) * $perPage, $perPage);

        $html = $this->templateRenderer->render('products.html.twig', [
            'products' => $products
        ]);

        if ($return) {
            return $html;
        }

        echo $html;
        return null;
    }

    public function product(string $sku): void
    {
        $productModel = new Product();
        $product = $productModel->loadBySku($sku);
        $productExists = isset($product->id) && is_numeric($product->id) && $product->id > 0;

        if (!$productExists) {
            http_response_code(404);
        }

        echo $this->templateRenderer->render('product.html.twig', [
            'product' => $productExists ? $product : null,
            'standardPrice' => $productExists ? $product->price : null,
            'discountedPrice' => $productExists
                ? $product->getDiscountedPrice()
                : null,
        ]);
    }

    /**
     * Retrieve products from the API and insert them into the database (supposed to be run via cron job)
     * @param int $amount The number of products to retrieve
     * @return void
     */
    public function retrieveApiProducts(int $amount = 100): void
    {
        $productService = new ProductService();
        $products = $productService->getProducts($amount);

        $product = new Product();
        $product->insertProducts($products);

        echo "Inserted " . count($products) . " products into the database.\n";
    }
}
