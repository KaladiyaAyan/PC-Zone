<?php

function slugify($string)
{
  // Convert to lowercase, remove special characters and replace spaces with hyphens
  $slug = strtolower(trim($string));
  $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug); // remove non-alphanum
  $slug = preg_replace('/[\s-]+/', '-', $slug);      // replace spaces with -
  return trim($slug, '-');                           // trim hyphens
}

function message($page = '')
{
  // base titles
  $pageUc = $page === '' ? '' : ucfirst($page);
  $toastTypes = [
    'success' => [
      'class'  => 'bg-success text-white',
      'titles' => [
        'added'   => "{$pageUc} added successfully!",
        'updated' => "{$pageUc} updated successfully!",
        'deleted' => "{$pageUc} deleted successfully!",
      ]
    ],
    'error' => [
      'class'  => 'bg-danger text-white',
      'titles' => [
        'duplicate'      => "{$pageUc} already exists!",
        'insert_failed'  => "Failed to add " . strtolower($page) . ".",
        'update_failed'  => "Failed to update " . strtolower($page) . ".",
        'delete_failed'  => "Failed to delete " . strtolower($page) . ".",
        'missing_fields' => "All fields are required.",
      ]
    ]
  ];

  // page specific extras
  if (strcasecmp($page, 'Category') === 0) {
    $toastTypes['error']['titles']['category_in_use'] = 'Category is used in products or brands.';
  }
  if (strcasecmp($page, 'Brand') === 0) {
    $toastTypes['error']['titles']['brand_in_use'] = 'Brand is used in products.';
  }

  // output container + script
?>
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index:1055">
    <div id="toastContainer"></div>
  </div>

  <script>
    const toastTypes = <?= json_encode($toastTypes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    const params = new URLSearchParams(window.location.search);

    ['success', 'error'].forEach(type => {
      const key = params.get(type);
      if (!key) return;
      const titles = toastTypes[type] && toastTypes[type].titles ? toastTypes[type].titles : null;
      if (!titles || !titles[key]) return;

      const toastId = 'toast-' + type + '-' + key;
      const toastHtml = `
        <div class="toast align-items-center ${toastTypes[type].class}" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
          <div class="d-flex">
            <div class="toast-body">${titles[key]}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        </div>
      `;
      document.getElementById('toastContainer').innerHTML = toastHtml;
      const toastEl = new bootstrap.Toast(document.getElementById(toastId));
      toastEl.show();

      // remove the param from URL so toast doesn't reappear on reload
      setTimeout(() => {
        const url = new URL(window.location);
        url.searchParams.delete(type);
        window.history.replaceState({}, document.title, url);
      }, 1000);
    });
  </script>
<?php
}


?>