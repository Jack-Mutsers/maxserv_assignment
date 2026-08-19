<?php

namespace MaxServ\App\Models;

class Review extends BaseModel
{
    protected string $table = 'reviews';

    public float $productId;
    public float $rating;
    public string $comment;
    public string $date;
    public string $reviewerName;
    public string $reviewerEmail;

    /**
     * Create the table if it does not exist yet (normally done via migration)
     * @return void
     */
    protected function createTableIfNotExists(): void
    {
        $pdo = $this->getConnection();
        $pdo->exec("CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT PRIMARY KEY AUTO_INCREMENT,
            productId INT,
            rating DECIMAL(3, 2),
            comment TEXT,
            date DATETIME,
            reviewerName VARCHAR(255),
            reviewerEmail VARCHAR(255)
        )");
    }

    /**
     * Summary of insertReviews
     * @param array $reviews
     * @return void
     */
    public function insertReviews(array $reviews, int $productId): void
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare("INSERT INTO {$this->table} (productId, rating, comment, date, reviewerName, reviewerEmail) VALUES (:productId, :rating, :comment, :date, :reviewerName, :reviewerEmail)");

        if(isset($reviews['rating'])) {
            $reviews = [$reviews]; // Wrap single review in an array
        }

        foreach ($reviews as $review) {
            $date = new \DateTime($review['date']);

            $stmt->execute([
                ':productId' => $productId,
                ':rating' => $review['rating'],
                ':comment' => $review['comment'],
                ':date' => $date->format('Y-m-d H:i:s'),
                ':reviewerName' => $review['reviewerName'],
                ':reviewerEmail' => $review['reviewerEmail'],
            ]);
        }
    }

    /**
     * Summary of getReviewsByProductId
     * @param int $productId
     * @return array
     */
    public function getReviewsByProductId(int $productId): array
    {
        $pdo = $this->getConnection();

        $stmt = $pdo->prepare("SELECT * FROM {$this->table} WHERE productId = :productId");
        $stmt->execute([':productId' => $productId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
