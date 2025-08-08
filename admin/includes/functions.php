<?php

function slugify($string)
{
  // Convert to lowercase, remove special characters and replace spaces with hyphens
  $slug = strtolower(trim($string));
  $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug); // remove non-alphanum
  $slug = preg_replace('/[\s-]+/', '-', $slug);      // replace spaces with -
  return trim($slug, '-');                           // trim hyphens
}


function show_toast_script($page = '')
{

  $extraTitles = [];
  if ($page === 'Category') {
    $extraTitles[] = "category_in_use: 'Category is used in products or brands.'";
  }
  if ($page === 'Brand') {
    $extraTitles[] = "brand_in_use: 'Brand is used in products.'";
  }
  $extraTitlesString = implode(",\n          ", $extraTitles);
?>
  <!-- Toast Container -->
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
    <div id="toastContainer"></div>
  </div>

  <script>
    const toastTypes = {
      success: {
        class: 'bg-success text-white',
        titles: {
          added: '<?= ucfirst($page) ?> added successfully!',
          updated: '<?= ucfirst($page) ?> updated successfully!',
          deleted: '<?= ucfirst($page) ?> deleted successfully!',
        }
      },
      error: {
        class: 'bg-danger text-white',
        titles: {
          duplicate: '<?= ucfirst($page) ?> already exists!',
          insert_failed: 'Failed to add <?= strtolower($page) ?>.',
          update_failed: 'Failed to update <?= strtolower($page) ?>.',
          delete_failed: 'Failed to delete <?= strtolower($page) ?>.',
          missing_fields: 'All fields are required.',
          <?= $extraTitlesString ?><?= $extraTitlesString ? ',' : '' ?>
        }
      }
    };

    const params = new URLSearchParams(window.location.search);
    ['success', 'error'].forEach(type => {
      const key = params.get(type);
      if (key && toastTypes[type].titles[key]) {
        const toastId = 'toast-' + type + '-' + key;
        const toastHtml = `
            <div class="toast align-items-center ${toastTypes[type].class}" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
              <div class="d-flex">
                <div class="toast-body">
                  ${toastTypes[type].titles[key]}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
              </div>
            </div>
          `;
        document.getElementById('toastContainer').innerHTML = toastHtml;
        const toastEl = new bootstrap.Toast(document.getElementById(toastId));
        toastEl.show();

        setTimeout(() => {
          const url = new URL(window.location);
          url.searchParams.delete(type);
          window.history.replaceState({}, document.title, url);
        }, 1000);
      }
    });
  </script>
<?php
}

?>