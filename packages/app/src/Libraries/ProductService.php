<?php

namespace MaxServ\App\Libraries;

use MaxServ\App\Models\Product;
use RuntimeException;

class ProductService
{

    private string $baseUrl = 'https://dummyjson.com/products';

    /**
     * Fetches products from the API.
     * 
     * @param int $limit The number of products to fetch.
     * @param int $skip The zero-based offset to retrieve.
     * @return array<int, array<string, mixed>>
     */
    public function getProducts(int $limit = 100, int $skip = -1): array
    {
        if($limit <= 0) {
            throw new RuntimeException('Limit must be greater than zero.');
        }

        if($skip < 0) {
            // get last id from database and use it to skip the products already in the database
            $product = new Product();
            $skip = $product->getLatestId();
        }

        $url = $this->baseUrl . '?limit=' . $limit . '&skip=' . ($skip ?? 0);

        $response = file_get_contents($url);

        if ($response === false) {
            throw new RuntimeException('Failed to retrieve products.');
        }

        $data = json_decode($response, true);

        if (!isset($data['products']) || !is_array($data['products'])) {
            throw new RuntimeException('Invalid API response.');
        }

        return $data['products'];
    }
}
