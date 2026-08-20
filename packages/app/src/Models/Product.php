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
            sku VARCHAR(255) unique,
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
     * Convert the database record to the correct data format and populate the properties of the Product model
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
       parent::load($id);

        // load reviews for the product
        $reviewModel = new Review();
        $this->reviews = $reviewModel->getReviewsByProductId($id);

        return $this;
    }

    /**
     * load a product by its SKU and populate the properties of the Product model
     * @param string $sku
     * @return static
     */
    public function loadBySku(string $sku): static
    {
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM {$this->table} WHERE sku = :sku");
        $stmt->execute([':sku' => $sku]);

        $product = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;

        if (!$product) {
            return new static(); // Return an empty product if not found
        }
        
        // load the product data into the model
        $this->loadWithRecord($product);

        // load reviews for the product
        $reviewModel = new Review();
        $this->reviews = $reviewModel->getReviewsByProductId($product['id']);

        return $this;
    }

    /**
     * Summary of getProducts
     * @param int $offset
     * @param int $limit
     * @return array<int, static>
     */
    public function getProducts(int $offset = 0, int $limit = 16): array
    {
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM {$this->table} LIMIT :limit OFFSET :offset");

        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $stmt->execute();

        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($products as $product) {
            $productModel = new Product();
            $productModel->loadWithRecord($product);

            $result[] = $productModel;
        }

        return $result;
    }

    /**
     * Summary of getDatatable
     * @param int $offset
     * @param int $limit
     * @return array[]|array{data: array<string, static>, recordsFiltered: int, recordsTotal: int}
     */
    public function getDatatable(int $offset = 0, int $limit = 16): array
    {
        $columns = $_POST['columns'] ?? [];

        // Determine which columns are searchable and sortable
        $searchableColumns = [];
        $sortableColumns = [];
        foreach ($columns as $column) {
            if (isset($column['searchable']) && $column['searchable'] === 'true' && property_exists($this, $column['data'])) {
                $searchableColumns[] = $column['data'];
            }
            if (isset($column['orderable']) && $column['orderable'] === 'true' && property_exists($this, $column['data'])) {
                $sortableColumns[] = $column['data'];
            }
        }

        // get the datatable order data from the POST request
        $order = $_POST['order'] ?? [];
        $orderColumn = ($order[0]['column'] ?? null) ?: 1;
        $requestedOrderColumn = $columns[$orderColumn]['data'] ?? 'title';

        // validate the requested order column against a list of allowed columns (for security reasons)
        $orderColumnName = in_array($requestedOrderColumn, $sortableColumns, true)
            ? $requestedOrderColumn
            : 'title';
        $orderDirection = strtoupper($order[0]['dir'] ?? 'ASC');
        $orderDirection = in_array($orderDirection, ['ASC', 'DESC'], true) ? $orderDirection : 'ASC';

        // get the search value from the POST request
        $search = $_POST['search'] ?? [];
        $searchValue = $search['value'] ?? '';

        // get the total number of records and the filtered number of records
        $results = [
            "recordsTotal" => $this->getRecordCount(),
            "recordsFiltered" => $this->getRecordCount($searchableColumns, $searchValue),
            "data" => []
        ];

        // fetch the products from the database with the specified offset, limit, order, and search value
        $sql = "SELECT * FROM {$this->table}";

        $this->defineSearchConditions($sql, $searchableColumns, $searchValue);

        $sql .= " ORDER BY {$orderColumnName} {$orderDirection} LIMIT :limit OFFSET :offset";

        $pdo = $this->getConnection();
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $stmt->execute();

        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // load the products into the results array
        foreach ($products as $product) {
            $productModel = new Product();
            $productModel->loadWithRecord($product);

            $results['data'][] = $productModel;
        }

        return $results;
    }
}
