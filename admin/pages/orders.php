<?php
session_start();

// Admin check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

include('../includes/db_connect.php');

// Fetch orders with customer info
$sql = "
    SELECT 
        o.order_id,
        o.order_date,
        o.total_amount,
        o.order_status,
        c.customer_id,
        c.first_name,
        c.last_name,
        c.email,
        c.phone
    FROM orders o
    JOIN customers c ON o.customer_id = c.customer_id
    ORDER BY o.order_date DESC
";

$result = mysqli_query($conn, $sql);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PC-Zone Admin - Brands</title>

  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">
  <!-- Custom styles -->
  <link rel="stylesheet" href="../assets/css/style.css">
  <!-- Bootstrap JS -->
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <style>
    /* .container-fluid {
      padding: 20px;
    } */

    /* .table th {
      background-color: #343a40;
      color: white;
    } */

    .table-hover tbody tr:hover {
      background-color: #e9ecef;
    }

    .status-badge {
      padding: 5px 10px;
      border-radius: 5px;
      color: white;
    }

    .status-pending {
      background-color: orange;
    }

    .status-completed {
      background-color: green;
    }

    .status-cancelled {
      background-color: red;
    }
  </style>
</head>

<body>
  <?php include '../includes/header.php'; ?>
  <?php $page = 'orders';
  include '../includes/sidebar.php'; ?>

  <div class="main-content pt-5 mt-4">

    <div class="container mt-2">
      <h2 class="mb-4"><i class="fas fa-shopping-cart"></i> Orders</h2>

      <div class="table-responsive card-body">
        <table class="table table-bordered table-hover align-middle">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Customer</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Date</th>
              <th>Total Amount</th>
              <th>Status</th>
              <th>View</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                  <td>#<?php echo $row['order_id']; ?></td>
                  <td><?php echo htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']); ?></td>
                  <td><?php echo htmlspecialchars($row['email']); ?></td>
                  <td><?php echo htmlspecialchars($row['phone']); ?></td>
                  <td><?php echo date("d M Y, h:i A", strtotime($row['order_date'])); ?></td>
                  <td>₹<?php echo number_format($row['total_amount'], 2); ?></td>
                  <td>
                    <?php
                    $statusClass = 'status-pending';
                    if ($row['order_status'] === 'Completed') $statusClass = 'status-completed';
                    elseif ($row['order_status'] === 'Cancelled') $statusClass = 'status-cancelled';
                    ?>
                    <span class="status-badge <?php echo $statusClass; ?>">
                      <?php echo $row['order_status']; ?>
                    </span>
                  </td>
                  <td>
                    <a href="order_view.php?id=<?php echo $row['order_id']; ?>" class="btn btn-sm btn-primary">
                      <i class="fas fa-eye"></i> View
                    </a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center text-muted">No orders found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>

</body>

</html>