<?php

namespace MaxServ\App\Models;

use stdClass;

class Product extends BaseModel
{
    protected string $table = 'products';
    
    public int $id;
    public string $title;
    public string $description;
    public string $category;
    public float $price;
    public float $discountPercentage;
    public float $rating;
    public int $stock;
    public array $tags;
    public string $brand;
    public string $sku;
    public float $weight;
    public stdClass $dimensions;
    public string $warrantyInformation;
    public string $shippingInformation;
    public string $availabilityStatus;
    public array $reviews;
    public string $returnPolicy;
    public int $minimumOrderQuantity;
    public stdClass $meta;
    public array $images;
    public string $thumbnail;

    /**
     * Create the table if it does not exist yet (normally done via migration)
     * @return void
     */
    protected function createTableIfNotExists(): void
    {
        $pdo = $this->getConnection();
        $pdo->exec("CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT PRIMARY KEY,
            title VARCHAR(255),
            description TEXT,
            category VARCHAR(255),
            price DECIMAL(10, 2),
            discountPercentage DECIMAL(5, 2),
            rating DECIMAL(3, 2),
            stock INT,
            tags JSON,
            brand VARCHAR(255),
            sku VARCHAR(255),
            weight DECIMAL(10, 2),
            dimensions JSON,
            warrantyInformation TEXT,
            shippingInformation TEXT,
            availabilityStatus VARCHAR(50),
            returnPolicy TEXT,
            minimumOrderQuantity INT,
            meta JSON,
            images JSON,
            thumbnail VARCHAR(255)
        )");
    }

    /**
     * Populate the properties of the Product model
     * @param array $record
     * @return void
     */
    public function loadWithRecord(array $record): void
    {
        $this->id = (int)$record['id'];
        $this->title = $record['title'];
        $this->description = $record['description'];
        $this->category = $record['category'];
        $this->price = (float)$record['price'];
        $this->discountPercentage = (float)$record['discountPercentage'];
        $this->rating = (float)$record['rating'];
        $this->stock = (int)$record['stock'];
        $this->tags = json_decode($record['tags'], true);
        $this->brand = $record['brand'];
        $this->sku = $record['sku'];
        $this->weight = (float)$record['weight'];
        $this->dimensions = json_decode($record['dimensions']);
        $this->warrantyInformation = $record['warrantyInformation'];
        $this->shippingInformation = $record['shippingInformation'];
        $this->availabilityStatus = $record['availabilityStatus'];
        $this->returnPolicy = $record['returnPolicy'];
        $this->minimumOrderQuantity = (int)$record['minimumOrderQuantity'];
        $this->meta = json_decode($record['meta']);
        $this->images = json_decode($record['images'], true);
        $this->thumbnail = $record['thumbnail'];
    }

    /**
     * Summary of insertProducts
     * @param array $products
     * @return void
     */
    public function insertProducts(array $products): void
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare("INSERT INTO {$this->table} (id, title, description, category, price, discountPercentage, rating, stock, tags, brand, sku, weight, dimensions, warrantyInformation, shippingInformation, availabilityStatus, returnPolicy, minimumOrderQuantity, meta, images, thumbnail) VALUES (:id, :title, :description, :category, :price, :discountPercentage, :rating, :stock, :tags, :brand, :sku, :weight, :dimensions, :warrantyInformation, :shippingInformation, :availabilityStatus, :returnPolicy, :minimumOrderQuantity, :meta, :images, :thumbnail)");

        if(isset($products['id'])) {
            $products = [$products]; // Wrap single product in an array
        }

        $reviewModel = new Review();

        foreach ($products as $product) {
            $stmt->execute([
                ':id' => $product['id'],
                ':title' => $product['title'],
                ':description' => $product['description'],
                ':category' => $product['category'],
                ':price' => $product['price'],
                ':discountPercentage' => $product['discountPercentage'],
                ':rating' => $product['rating'],
                ':stock' => $product['stock'],
                ':tags' => json_encode($product['tags']),
                ':brand' => $product['brand'] ?? '',
                ':sku' => $product['sku'],
                ':weight' => $product['weight'],
                ':dimensions' => json_encode($product['dimensions']),
                ':warrantyInformation' => $product['warrantyInformation'],
                ':shippingInformation' => $product['shippingInformation'],
                ':availabilityStatus' => $product['availabilityStatus'],
                ':returnPolicy' => $product['returnPolicy'],
                ':minimumOrderQuantity' => $product['minimumOrderQuantity'],
                ':meta' => json_encode($product['meta']),
                ':images' => json_encode($product['images']),
                ':thumbnail' => $product['thumbnail']
            ]);

            // Insert reviews for the product
            if (!empty($product['reviews'])) {
                $reviewModel->insertReviews($product['reviews'], $product['id']);
            }
        }
    }

    /**
     * load a product by its ID and populate the properties of the Product model
     * @param int $id
     * @return static
     */
    public function load(int $id): static
    {
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $product = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;

        if (!$product) {
            return new static(); // Return an empty product if not found
        }
        
        // load the product data into the model
        $this->loadWithRecord($product);

        // load reviews for the product
        $reviewModel = new Review();
        $this->reviews = $reviewModel->getReviewsByProductId($id);

        return $this;
    }

    /**
     * Summary of getProducts
     * @param int $page
     * @param int $limit
     * @return array<int, static>
     */
    public function getProducts(int $page = 1, int $limit = 15): array
    {
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM {$this->table} LIMIT :limit OFFSET :offset");
        $stmt->execute([
            ':limit' => $limit,
            ':offset' => ($page - 1) * $limit
        ]);

        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($products as $product) {
            $productModel = new Product();
            $productModel->loadWithRecord($product);

            $result[] = $productModel;
        }

        return $result;
    }
}
