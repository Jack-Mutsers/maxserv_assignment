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
