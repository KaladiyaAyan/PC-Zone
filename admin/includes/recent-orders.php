<div class="widget-box">
  <h3 class="widget-title">Recent Orders</h3>
  <table class="widget-table">
    <thead>
      <tr>
        <th>Order ID</th>
        <th>Customer</th>
        <th>Date</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php
      // Connect to DB
      // include __DIR__ . '/../config/db.php'; // adjust path
      // include __DIR__ . '/db/db.php';

      if (isset($conn) && $conn) {
        $sql = "SELECT id, customer_name, order_date, status FROM orders ORDER BY order_date DESC LIMIT 5";
        $result = mysqli_query($conn, $sql);

        while ($row = mysqli_fetch_assoc($result)) {
          echo "<tr>
            <td>#{$row['id']}</td>
            <td>{$row['customer_name']}</td>
            <td>{$row['order_date']}</td>
            <td>{$row['status']}</td>
          </tr>";
        }
      } else {
        // echo "<tr><td colspan='4'>Database not connected.</td></tr>";
        echo "<tr>
            <td>#1</td>
            <td>John Doe</td>
            <td>2023-08-01</td>
            <td>Pending</td>
          </tr>";
      }
      ?>
    </tbody>
  </table>
</div>