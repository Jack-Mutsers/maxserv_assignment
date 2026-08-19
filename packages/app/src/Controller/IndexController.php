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
     * load ecommerce products page
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function index(): void
    {
        $page = (int) resolvePost('page', 1);
        $perPage = (int) resolvePost('perPage', 16);

        $productModel = new Product();
        $totalRecords = $productModel->getRecordCount();

        $productsHtml = $this->products(true, $page, $perPage);

        echo $this->templateRenderer->render('index.html.twig', [
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
        $products = $productModel->getProducts($page, $perPage);

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

        echo $this->templateRenderer->render('product.html.twig', [
            'product' => $product
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
