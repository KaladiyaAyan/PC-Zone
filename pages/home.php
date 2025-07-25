<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="container my-5">
  <h2>Featured Products</h2>
  <div class="row">
    <?php
    $stmt = $pdo->query(
      "SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 4"
    );
    foreach ($stmt as $row): ?>
      <div class="col-md-3 mb-4">
        <div class="card h-100">
          <img src="../<?= htmlspecialchars($row['image']) ?>" class="card-img-top" alt="<?= $row['name'] ?>">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
            <p class="card-text text-muted">$<?= number_format($row['price'], 2) ?></p>
            <a href="product-detail.php?id=<?= $row['id'] ?>" class="btn btn-primary mt-auto">View</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>