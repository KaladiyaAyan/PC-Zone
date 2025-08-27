 <!-- Top Bar -->
 <div class="top-bar">
   <div class="container">
     <div class="row align-items-center">
       <div class="col-md-8">
         <span class="highlight"><i class="bi bi-lightning-charge me-2"></i>Free shipping on orders over ₹5,999</span>
         <span class="ms-4 d-none d-lg-inline"><i class="bi bi-shield-check me-1"></i>2 Year Warranty on All Components</span>
       </div>
       <div class="col-md-4 text-end">
         <a href="/track-order" class="me-3"><i class="bi bi-truck me-1"></i>Track Order</a>
         <a href="/support" class="me-3"><i class="bi bi-headset me-1"></i>Tech Support</a>
         <a href="/build-guide"><i class="bi bi-tools me-1"></i>Build Guide</a>
       </div>
     </div>
   </div>
 </div>

 <!-- Main Header -->
 <header class="main-header">
   <div class="container">
     <div class="header-content">
       <div class="row align-items-center">
         <!-- Logo -->
         <div class="col-6 col-md-3">
           <a href="/" class="logo-section">
             <i class="bi bi-pc-display logo-icon"></i>
             <h1 class="logo-text">PCZONE</h1>
           </a>
         </div>

         <!-- Search Bar -->
         <div class="col-md-6 d-none d-lg-block">
           <div class="search-section">
             <div class="search-container">
               <select class="category-select">
                 <option value="">All Components</option>
                 <option value="cpu">Processors (CPU)</option>
                 <option value="gpu">Graphics Cards</option>
                 <option value="motherboard">Motherboards</option>
                 <option value="ram">Memory (RAM)</option>
                 <option value="storage">Storage</option>
                 <option value="cooling">Cooling</option>
                 <option value="psu">Power Supply</option>
                 <option value="case">PC Cases</option>
               </select>
               <input type="text" class="search-input" placeholder="Search for processors, graphics cards, motherboards...">
               <button class="search-btn" type="submit">
                 <i class="bi bi-search"></i>
               </button>
             </div>
           </div>
         </div>

         <!-- Header Actions -->
         <div class="col-6 col-md-3">
           <div class="header-actions justify-content-end">
             <!-- Search Icon for Mobile -->
             <button class="btn p-0 d-lg-none" onclick="toggleMobileSearch()">
               <div class="action-item">
                 <i class="bi bi-search action-icon"></i>
               </div>
             </button>

             <!-- User Account -->
             <div class="dropdown">
               <a href="#" class="action-item" data-bs-toggle="dropdown">
                 <i class="bi bi-person-circle action-icon"></i>
                 <span class="action-text d-none d-md-inline">Account</span>
               </a>
               <ul class="dropdown-menu dropdown-menu-end">
                 <li><a class="dropdown-item" href="/login"><i class="bi bi-box-arrow-in-right"></i>Login</a></li>
                 <li><a class="dropdown-item" href="/register"><i class="bi bi-person-plus"></i>Register</a></li>
                 <li>
                   <hr class="dropdown-divider">
                 </li>
                 <li><a class="dropdown-item" href="/profile"><i class="bi bi-person"></i>My Profile</a></li>
                 <li><a class="dropdown-item" href="/orders"><i class="bi bi-bag"></i>My Orders</a></li>
                 <li><a class="dropdown-item" href="/builds"><i class="bi bi-cpu"></i>My Builds</a></li>
               </ul>
             </div>

             <!-- Wishlist -->
             <a href="/wishlist" class="action-item">
               <i class="bi bi-heart action-icon"></i>
               <span class="badge-count">4</span>
               <span class="action-text d-none d-md-inline">Wishlist</span>
             </a>

             <!-- Cart -->
             <a href="/cart" class="action-item">
               <i class="bi bi-bag action-icon"></i>
               <span class="badge-count">2</span>
               <span class="action-text d-none d-md-inline">Cart</span>
             </a>
           </div>
         </div>
       </div>
     </div>
   </div>

   <!-- Mobile Search -->
   <div class="mobile-search" id="mobileSearch">
     <div class="container">
       <div class="search-container">
         <input type="text" class="search-input" placeholder="Search PC components...">
         <button class="search-btn" type="submit">
           <i class="bi bi-search"></i>
         </button>
       </div>
     </div>
   </div>
 </header>