<?php

    // show alert baised on message session data 

    if (isset($_SESSION['message'])) {
    ?>
        <div class="popup <?= $_SESSION['message']['type'] ?>">
            <div class="icon">
                <?= $_SESSION['message']['icon'] ?>
            </div>
            <p class="title">
                <?= $_SESSION['message']['title'] ?>
            </p>
        </div>
    <?php
        unset($_SESSION['message']);
    }

?>

