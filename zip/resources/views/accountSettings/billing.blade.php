<!DOCTYPE html>


<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact " dir="ltr" data-theme="theme-default" data-assets-path="../../../assets/" data-template="vertical-menu-template">

  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Account settings - Pages | Vuexy - Bootstrap Admin Template</title>

    
    <meta name="description" content="Start your development with a Dashboard for Bootstrap 5" />
    <meta name="keywords" content="dashboard, bootstrap 5 dashboard, bootstrap 5 design, bootstrap 5">
    <!-- Canonical SEO -->
    <link rel="canonical" href="https://1.envato.market/vuexy_admin">
    
    
    <!-- ? PROD Only: Google Tag Manager (Default ThemeSelection: GTM-5DDHKGP, PixInvent: GTM-5J3LMKC) -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
      new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
      j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
      'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
      })(window,document,'script','dataLayer','GTM-5J3LMKC');</script>
    <!-- End Google Tag Manager -->
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../../../assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;ampdisplay=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="../../../assets/vendor/fonts/fontawesome.css" />
    <link rel="stylesheet" href="../../../assets/vendor/fonts/tabler-icons.css"/>
    <link rel="stylesheet" href="../../../assets/vendor/fonts/flag-icons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="../../../assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../../../assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../../../assets/css/demo.css" />
    
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="../../../assets/vendor/libs/node-waves/node-waves.css" />
    <link rel="stylesheet" href="../../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="../../../assets/vendor/libs/typeahead-js/typeahead.css" /> 
    <link rel="stylesheet" href="../../../assets/vendor/libs/select2/select2.css" />
<link rel="stylesheet" href="../../../assets/vendor/libs/@form-validation/umd/styles/index.min.css" />
<link rel="stylesheet" href="../../../assets/vendor/libs/animate-css/animate.css" />
<link rel="stylesheet" href="../../../assets/vendor/libs/sweetalert2/sweetalert2.css" />

<link rel="stylesheet" href="../../../assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css">
<link rel="stylesheet" href="../../../assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css">
<link rel="stylesheet" href="../../../assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css">

    <!-- Page CSS -->
    

    <!-- Helpers -->
    <script src="../../../assets/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <script src="../../../assets/vendor/js/template-customizer.js"></script>
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="../../../assets/js/config.js"></script>
    
</head>

<body>

  
  <!-- ?PROD Only: Google Tag Manager (noscript) (Default ThemeSelection: GTM-5DDHKGP, PixInvent: GTM-5J3LMKC) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5DDHKGP" height="0" width="0" style="display: none; visibility: hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->
  
  <!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar  ">
  <div class="layout-container">

    
<!-- Menu -->

{{-- Sidebar Partial --}}
@include('partials.sidebar')
<!-- / Menu -->

    

    <!-- Layout container -->
    <div class="layout-page">
      
      



<!-- Navbar -->

@include('partials.navbar')

<!-- / Navbar -->

      <!-- Content wrapper -->
      <div class="content-wrapper">

        <!-- Content -->
        
          <div class="container-xxl flex-grow-1 container-p-y">
            
            
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Account Settings /</span> Billings & Plans
</h4>

<div class="row">
  <div class="col-md-12">
    <!-- Accountbar -->
    @include('partials.accountbar')
    <!-- / Accountbar -->
    <div class="card mb-4">
      <!-- Current Plan -->
      <h5 class="card-header">Current Plan</h5>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-1">
            <div class="mb-3">
              <h6 class="mb-1">Your Current Plan is Basic</h6>
              <p>A simple start for everyone</p>
            </div>
            <div class="mb-3">
              <h6 class="mb-1">Active until Dec 09, 2021</h6>
              <p>We will send you a notification upon Subscription expiration</p>
            </div>
            <div class="mb-3">
              <h6 class="mb-1"><span class="me-2">$199 Per Month</span> <span class="badge bg-label-primary">Popular</span></h6>
              <p>Standard plan for small to medium businesses</p>
            </div>
          </div>
          <div class="col-md-6 mb-1">
            <div class="alert alert-warning mb-3" role="alert">
              <h5 class="alert-heading mb-1">We need your attention!</h5>
              <span>Your plan requires update</span>
            </div>
            <div class="plan-statistics">
              <div class="d-flex justify-content-between">
                <h6 class="mb-2">Days</h6>
                <h6 class="mb-2">24 of 30 Days</h6>
              </div>
              <div class="progress">
                <div class="progress-bar w-75" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
              <p class="mt-1 mb-0">6 days remaining until your plan requires update</p>
            </div>
          </div>
          <div class="col-12">
            <button class="btn btn-primary me-2 mt-2" data-bs-toggle="modal" data-bs-target="#pricingModal">Upgrade Plan</button>
            <button class="btn btn-label-danger cancel-subscription mt-2">Cancel Subscription</button>
          </div>
        </div>
      </div>
      <!-- /Current Plan -->
    </div>
    <div class="card mb-4">
      <h5 class="card-header">Payment Methods</h5>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <form id="creditCardForm" class="row g-3" onsubmit="return false">
              <div class="col-12 mb-2">
                <div class="form-check form-check-inline">
                  <input name="collapsible-payment" class="form-check-input" type="radio" value="" id="collapsible-payment-cc" checked="" />
                  <label class="form-check-label" for="collapsible-payment-cc">Credit/Debit/ATM Card</label>
                </div>
                <div class="form-check form-check-inline">
                  <input name="collapsible-payment" class="form-check-input" type="radio" value="" id="collapsible-payment-cash" />
                  <label class="form-check-label" for="collapsible-payment-cash">Paypal account</label>
                </div>
              </div>
              <div class="col-12">
                <label class="form-label w-100" for="paymentCard">Card Number</label>
                <div class="input-group input-group-merge">
                  <input id="paymentCard" name="paymentCard" class="form-control credit-card-mask" type="text" placeholder="1356 3215 6548 7898" aria-describedby="paymentCard2" />
                  <span class="input-group-text cursor-pointer p-1" id="paymentCard2"><span class="card-type"></span></span>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label" for="paymentName">Name</label>
                <input type="text" id="paymentName" class="form-control" placeholder="John Doe" />
              </div>
              <div class="col-6 col-md-3">
                <label class="form-label" for="paymentExpiryDate">Exp. Date</label>
                <input type="text" id="paymentExpiryDate" class="form-control expiry-date-mask" placeholder="MM/YY" />
              </div>
              <div class="col-6 col-md-3">
                <label class="form-label" for="paymentCvv">CVV Code</label>
                <div class="input-group input-group-merge">
                  <input type="text" id="paymentCvv" class="form-control cvv-code-mask" maxlength="3" placeholder="654" />
                  <span class="input-group-text cursor-pointer" id="paymentCvv2"><i class="ti ti-help text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="Card Verification Value"></i></span>
                </div>
              </div>
              <div class="col-12">
                <label class="switch">
                  <input type="checkbox" class="switch-input">
                  <span class="switch-toggle-slider">
                    <span class="switch-on"></span>
                    <span class="switch-off"></span>
                  </span>
                  <span class="switch-label">Save card for future billing?</span>
                </label>
              </div>
              <div class="col-12 mt-4">
                <button type="submit" class="btn btn-primary me-sm-3 me-1">Save Changes</button>
                <button type="reset" class="btn btn-label-secondary">Cancel</button>
              </div>
            </form>
          </div>
          <div class="col-md-6 mt-5 mt-md-0">
            <h6>My Cards</h6>
            <div class="added-cards">
              <div class="cardMaster bg-lighter p-3 rounded mb-3">
                <div class="d-flex justify-content-between flex-sm-row flex-column">
                  <div class="card-information me-2">
                    <img class="mb-3 img-fluid" src="../../../assets/img/icons/payments/mastercard.png" alt="Master Card">
                    <div class="d-flex align-items-center mb-2 flex-wrap gap-2">
                      <p class="mb-0 me-2">Tom McBride</p>
                      <span class="badge bg-label-primary">Primary</span>
                    </div>
                    <span class="card-number">&#8727;&#8727;&#8727;&#8727; &#8727;&#8727;&#8727;&#8727; 9856</span>
                  </div>
                  <div class="d-flex flex-column text-start text-lg-end">
                    <div class="d-flex order-sm-0 order-1 mt-sm-0 mt-3">
                      <button class="btn btn-label-primary me-2" data-bs-toggle="modal" data-bs-target="#editCCModal">Edit</button>
                      <button class="btn btn-label-secondary">Delete</button>
                    </div>
                    <small class="mt-sm-auto mt-2 order-sm-1 order-0">Card expires at 12/26</small>
                  </div>
                </div>
              </div>
              <div class="cardMaster bg-lighter p-3 rounded">
                <div class="d-flex justify-content-between flex-sm-row flex-column">
                  <div class="card-information me-2">
                    <img class="mb-3 img-fluid" src="../../../assets/img/icons/payments/visa.png" alt="Visa Card">
                    <p class="mb-2">Mildred Wagner</p>
                    <span class="card-number">&#8727;&#8727;&#8727;&#8727; &#8727;&#8727;&#8727;&#8727; 5896</span>
                  </div>
                  <div class="d-flex flex-column text-start text-lg-end">
                    <div class="d-flex order-sm-0 order-1 mt-sm-0 mt-3">
                      <button class="btn btn-label-primary me-2" data-bs-toggle="modal" data-bs-target="#editCCModal">Edit</button>
                      <button class="btn btn-label-secondary">Delete</button>
                    </div>
                    <small class="mt-sm-auto mt-2 order-sm-1 order-0">Card expires at 10/27</small>
                  </div>
                </div>
              </div>
            </div>
            <!-- Modal -->
            <!-- Add New Credit Card Modal -->
<div class="modal fade" id="editCCModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-simple modal-add-new-cc">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3 class="mb-2">Edit Card</h3>
          <p class="text-muted">Edit your saved card details</p>
        </div>
        <form id="editCCForm" class="row g-3" onsubmit="return false">
          <div class="col-12">
            <label class="form-label w-100" for="modalEditCard">Card Number</label>
            <div class="input-group input-group-merge">
              <input id="modalEditCard" name="modalEditCard" class="form-control credit-card-mask-edit" type="text" placeholder="4356 3215 6548 7898" value="4356 3215 6548 7898" aria-describedby="modalEditCard2" />
              <span class="input-group-text cursor-pointer p-1" id="modalEditCard2"><span class="card-type-edit"></span></span>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditName">Name</label>
            <input type="text" id="modalEditName" class="form-control" placeholder="John Doe" value="John Doe" />
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label" for="modalEditExpiryDate">Exp. Date</label>
            <input type="text" id="modalEditExpiryDate" class="form-control expiry-date-mask-edit" placeholder="MM/YY" value="08/28" />
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label" for="modalEditCvv">CVV Code</label>
            <div class="input-group input-group-merge">
              <input type="text" id="modalEditCvv" class="form-control cvv-code-mask-edit" maxlength="3" placeholder="654" value="XXX" />
              <span class="input-group-text cursor-pointer" id="modalEditCvv2"><i class="ti ti-help text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="Card Verification Value"></i></span>
            </div>
          </div>
          <div class="col-12">
            <label class="switch">
              <input type="checkbox" class="switch-input">
              <span class="switch-toggle-slider">
                <span class="switch-on"></span>
                <span class="switch-off"></span>
              </span>
              <span class="switch-label">Set as primary card</span>
            </label>
          </div>
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-sm-3 me-1">Update</button>
            <button type="reset" class="btn btn-label-danger" data-bs-dismiss="modal" aria-label="Close">Remove</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Add New Credit Card Modal -->

            <!--/ Modal -->
          </div>
        </div>
      </div>
    </div>
    <div class="card mb-4">
      <!-- Billing Address -->
      <h5 class="card-header">Billing Address</h5>
      <div class="card-body">
        <form id="formAccountSettings" onsubmit="return false">
          <div class="row">
            <div class="mb-3 col-sm-6">
              <label for="companyName" class="form-label">Company Name</label>
              <input type="text" id="companyName" name="companyName" class="form-control" placeholder="Pixinvent" />
            </div>
            <div class="mb-3 col-sm-6">
              <label for="billingEmail" class="form-label">Billing Email</label>
              <input class="form-control" type="text" id="billingEmail" name="billingEmail" placeholder="john.doe@example.com" />
            </div>
            <div class="mb-3 col-sm-6">
              <label for="taxId" class="form-label">Tax ID</label>
              <input type="text" id="taxId" name="taxId" class="form-control" placeholder="Enter Tax ID" />
            </div>
            <div class="mb-3 col-sm-6">
              <label for="vatNumber" class="form-label">VAT Number</label>
              <input class="form-control" type="text" id="vatNumber" name="vatNumber" placeholder="Enter VAT Number" />
            </div>
            <div class="mb-3 col-sm-6">
              <label for="mobileNumber" class="form-label">Mobile</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text">US (+1)</span>
                <input class="form-control mobile-number" type="text" id="mobileNumber" name="mobileNumber" placeholder="202 555 0111" />
              </div>
            </div>
            <div class="mb-3 col-sm-6">
              <label for="country" class="form-label">Country</label>
              <select id="country" class="form-select select2" name="country">
                <option selected>USA</option>
                <option>Canada</option>
                <option>UK</option>
                <option>Germany</option>
                <option>France</option>
              </select>
            </div>
            <div class="mb-3 col-12">
              <label for="billingAddress" class="form-label">Billing Address</label>
              <input type="text" class="form-control" id="billingAddress" name="billingAddress" placeholder="Billing Address" />
            </div>
            <div class="mb-3 col-sm-6">
              <label for="state" class="form-label">State</label>
              <input class="form-control" type="text" id="state" name="state" placeholder="California" />
            </div>
            <div class="mb-3 col-sm-6">
              <label for="zipCode" class="form-label">Zip Code</label>
              <input type="text" class="form-control zip-code" id="zipCode" name="zipCode" placeholder="231465" maxlength="6" />
            </div>
          </div>
          <div class="mt-2">
            <button type="submit" class="btn btn-primary me-2">Save changes</button>
            <button type="reset" class="btn btn-label-secondary">Discard</button>
          </div>
        </form>
      </div>
      <!-- /Billing Address -->
    </div>
    <div class="card">
      <!-- Billing History -->
      <h5 class="card-header">Billing History</h5>
      <div class="card-datatable table-responsive">
        <table class="invoice-list-table table border-top">
          <thead>
            <tr>
              <th></th>
              <th>#ID</th>
              <th><i class='ti ti-trending-up'></i></th>
              <th>Client</th>
              <th>Total</th>
              <th class="text-truncate">Issued Date</th>
              <th>Balance</th>
              <th>Invoice Status</th>
              <th class="cell-fit">Actions</th>
            </tr>
          </thead>
        </table>
      </div>
      <!--/ Billing History -->
    </div>
  </div>
</div>

<!-- Modal -->
<!-- Pricing Modal -->
<div class="modal fade" id="pricingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-simple modal-pricing">
    <div class="modal-content p-2 p-md-5">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <!-- Pricing Plans -->
        <div class="py-0 rounded-top">
          <h2 class="text-center mb-2">Pricing Plans</h2>
          <p class="text-center"> Get started with us - it's perfect for individuals and teams. Choose a subscription plan that meets your needs. </p>
          <div class="d-flex align-items-center justify-content-center flex-wrap gap-2 pt-3 mb-4">
            <label class="switch switch-primary ms-3 ms-sm-0 mt-2">
              <span class="switch-label">Monthly</span>
              <input type="checkbox" class="switch-input price-duration-toggler" checked />
              <span class="switch-toggle-slider">
                <span class="switch-on"></span>
                <span class="switch-off"></span>
              </span>
              <span class="switch-label">Annual</span>
            </label>
            <div class="mt-n5 ms-n5 d-none d-sm-block">
              <i class="ti ti-corner-left-down ti-sm text-muted me-1 scaleX-n1-rtl"></i>
              <span class="badge badge-sm bg-label-primary">Save up to 10%</span>
            </div>
          </div>
          <div class="row mx-0 gy-3">
            <!-- Basic -->
            <div class="col-xl mb-md-0 mb-4">
              <div class="card border rounded shadow-none">
                <div class="card-body">
                  <div class="my-3 pt-2 text-center">
                    <img src="../../../assets/img/illustrations/page-pricing-basic.png" alt="Basic Image" height="140">
                  </div>
                  <h3 class="card-title text-center text-capitalize mb-1">Basic</h3>
                  <p class="text-center">A simple start for everyone</p>
                  <div class="text-center h-px-100 mb-2">
                    <div class="d-flex justify-content-center">
                      <sup class="h6 pricing-currency mt-3 mb-0 me-1 text-primary">$</sup>
                      <h1 class="display-4 mb-0 text-primary">0</h1>
                      <sub class="h6 pricing-duration mt-auto mb-2 text-muted fw-normal">/month</sub>
                    </div>
                    <small class="price-yearly price-yearly-toggle text-muted">$ 0 / year</small>
                  </div>

                  <ul class="list-group ps-3 my-4">
                    <li class="mb-2">100 responses a month</li>
                    <li class="mb-2">Unlimited forms and surveys</li>
                    <li class="mb-2">Unlimited fields</li>
                    <li class="mb-2">Basic form creation tools</li>
                    <li class="mb-0">Up to 2 subdomains</li>
                  </ul>

                  <button type="button" class="btn btn-label-success d-grid w-100 mt-3" data-bs-dismiss="modal">Your Current Plan</button>
                </div>
              </div>
            </div>

            <!-- Pro -->
            <div class="col-xl mb-md-0 mb-4">
              <div class="card border-primary border shadow-none">
                <div class="card-body position-relative">
                  <div class="position-absolute end-0 me-4 top-0 mt-4">
                    <span class="badge bg-label-primary">Popular</span>
                  </div>
                  <div class="my-3 pt-2 text-center">
                    <img src="../../../assets/img/illustrations/page-pricing-standard.png" alt="Standard Image" height="140">
                  </div>
                  <h3 class="card-title text-center text-capitalize mb-1">Pro</h3>
                  <p class="text-center">For small to medium businesses</p>
                  <div class="text-center h-px-100 mb-2">
                    <div class="d-flex justify-content-center">
                      <sup class="h6 pricing-currency mt-3 mb-0 me-1 text-primary">$</sup>
                      <h1 class="price-toggle price-yearly display-4 text-primary mb-0">7</h1>
                      <h1 class="price-toggle price-monthly display-4 text-primary mb-0 d-none">9</h1>
                      <sub class="h6 text-muted pricing-duration mt-auto mb-2 fw-normal">/month</sub>
                    </div>
                    <small class="price-yearly price-yearly-toggle text-muted">$ 90 / year</small>
                  </div>

                  <ul class="list-group ps-3 my-4">
                    <li class="mb-2">Up to 5 users</li>
                    <li class="mb-2">120+ components</li>
                    <li class="mb-2">Basic support on Github</li>
                    <li class="mb-2">Monthly updates</li>
                    <li class="mb-0">Integrations</li>
                  </ul>

                  <button type="button" class="btn btn-primary d-grid w-100 mt-3" data-bs-dismiss="modal">Upgrade</button>
                </div>
              </div>
            </div>

            <!-- Enterprise -->
            <div class="col-xl">
              <div class="card border rounded shadow-none">
                <div class="card-body">

                  <div class="my-3 pt-2 text-center">
                    <img src="../../../assets/img/illustrations/page-pricing-enterprise.png" alt="Enterprise Image" height="140">
                  </div>
                  <h3 class="card-title text-center text-capitalize mb-1">Enterprise</h3>
                  <p class="text-center">Solution for big organizations</p>

                  <div class="text-center h-px-100 mb-2">
                    <div class="d-flex justify-content-center">
                      <sup class="h6 text-primary pricing-currency mt-3 mb-0 me-1">$</sup>
                      <h1 class="price-toggle price-yearly display-4 text-primary mb-0">16</h1>
                      <h1 class="price-toggle price-monthly display-4 text-primary mb-0 d-none">19</h1>
                      <sub class="h6 pricing-duration mt-auto mb-2 fw-normal text-muted">/month</sub>
                    </div>
                    <small class="price-yearly price-yearly-toggle text-muted">$ 190 / year</small>
                  </div>

                  <ul class="list-group ps-3 my-4">
                    <li class="mb-2">Up to 10 users</li>
                    <li class="mb-2">150+ components</li>
                    <li class="mb-2">Basic support on Github</li>
                    <li class="mb-2">Monthly updates</li>
                    <li class="mb-0">Speedy build tooling</li>
                  </ul>

                  <button type="button" class="btn btn-label-primary d-grid w-100 mt-3" data-bs-dismiss="modal">Upgrade</button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!--/ Pricing Plans -->
      </div>
    </div>
  </div>
</div>
<!--/ Pricing Modal -->

<script src="../../../assets/js/pages-pricing.js"></script>

<!--/ Modal -->


          </div>
          <!-- / Content -->

          
          

<!-- Footer -->
<footer class="content-footer footer bg-footer-theme">
  <div class="container-xxl">
    <div class="footer-container d-flex align-items-center justify-content-between py-2 flex-md-row flex-column">
      <div>
        © <script>
        document.write(new Date().getFullYear())

        </script>
        , made with ❤️ by <a href="https://pixinvent.com" target="_blank" class="fw-medium">Pixinvent</a>
      </div>
      <div class="d-none d-lg-inline-block">
        
        <a href="https://themeforest.net/licenses/standard" class="footer-link me-4" target="_blank">License</a>
        <a href="https://1.envato.market/pixinvent_portfolio" target="_blank" class="footer-link me-4">More Themes</a>
        
        <a href="../../../documentation/index.html" target="_blank" class="footer-link me-4">Documentation</a>
        
        
        <a href="https://pixinvent.ticksy.com/" target="_blank" class="footer-link d-none d-sm-inline-block">Support</a>
        
      </div>
    </div>
  </div>
</footer>
<!-- / Footer -->

          
          <div class="content-backdrop fade"></div>
        </div>
        <!-- Content wrapper -->
      </div>
      <!-- / Layout page -->
    </div>

    
    
    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
    
    
    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div>
    
  </div>
  <!-- / Layout wrapper -->

  
  

  <!-- Core JS -->
  <!-- build:js assets/vendor/js/core.js -->
  
  <script src="../../../assets/vendor/libs/jquery/jquery.js"></script>
  <script src="../../../assets/vendor/libs/popper/popper.js"></script>
  <script src="../../../assets/vendor/js/bootstrap.js"></script>
  <script src="../../../assets/vendor/libs/node-waves/node-waves.js"></script>
  <script src="../../../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
  <script src="../../../assets/vendor/libs/hammer/hammer.js"></script>
  <script src="../../../assets/vendor/libs/i18n/i18n.js"></script>
  <script src="../../../assets/vendor/libs/typeahead-js/typeahead.js"></script>
   <script src="../../../assets/vendor/js/menu.js"></script>
  
  <!-- endbuild -->

  <!-- Vendors JS -->
  <script src="../../../assets/vendor/libs/select2/select2.js"></script>
<script src="../../../assets/vendor/libs/@form-validation/umd/bundle/popular.min.js"></script>
<script src="../../../assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js"></script>
<script src="../../../assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js"></script>
<script src="../../../assets/vendor/libs/cleavejs/cleave.js"></script>
<script src="../../../assets/vendor/libs/cleavejs/cleave-phone.js"></script>
<script src="../../../assets/vendor/libs/sweetalert2/sweetalert2.js"></script>

<script src="../../../assets/vendor/libs/moment/moment.js"></script>
<script src="../../../assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>

  <!-- Main JS -->
  <script src="../../../assets/js/main.js"></script>
  

  <!-- Page JS -->
  <script src="../../../assets/js/pages-pricing.js"></script>
<script src="../../../assets/js/pages-account-settings-billing.js"></script>
<script src="../../../assets/js/app-invoice-list.js"></script>
<script src="../../../assets/js/modal-edit-cc.js"></script>
  
</body>

</html>

<!-- beautify ignore:end -->

