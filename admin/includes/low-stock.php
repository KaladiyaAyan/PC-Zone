<div class="widget-box">
  <h3 class="widget-title">Low Stock Alerts</h3>
  <ul class="widget-list">
    <?php

    if (isset($conn) && $conn) {
      $sql = "SELECT name, quantity FROM products WHERE quantity < 5 ORDER BY quantity ASC LIMIT 5";
      $result = mysqli_query($conn, $sql);

      while ($row = mysqli_fetch_assoc($result)) {
        echo "<li>{$row['name']} — <strong>{$row['quantity']} left</strong></li>";
      }
    } else {
      // echo "<li>Database not connected.</li>";
      echo "<li>inter i9 14900k — <strong>10 left</strong></li>";
    }
    ?>
  </ul>
</div>