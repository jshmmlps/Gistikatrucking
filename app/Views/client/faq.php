<?= $this->extend('templates/client_layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
<div>
    <h1> Frequently Ask Question</h1>
</div>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Frequently Asked Questions</title>
</head>
<body>
  <div class="container-fluid my-5 ">
    <h2 class="faq-header text-center"><strong>Frequently Asked Questions (FAQs)</strong></h2>
    <div class="accordion" id="faqAccordion">
      <!-- FAQ 1 -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqHeading1">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1" aria-expanded="false" aria-controls="faqCollapse1">
            What is the Truck Logistics Management System?
          </button>
        </h2>
        <div id="faqCollapse1" class="accordion-collapse collapse" aria-labelledby="faqHeading1" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            It is a platform that helps businesses manage truck fleets, track shipments, optimize routes, and monitor driver performance in real time.
          </div>
        </div>
      </div>
      <!-- FAQ 2 -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqHeading2">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">
            How can I track my trucks in real time?
          </button>
        </h2>
        <div id="faqCollapse2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            The system integrates with GPS tracking, allowing you to monitor vehicle locations, routes, and estimated arrival times.
          </div>
        </div>
      </div>
      <!-- FAQ 3 -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqHeading3">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">
            Can I schedule shipments through the system?
          </button>
        </h2>
        <div id="faqCollapse3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Yes, you can create, assign, and track shipments while the system optimizes routes for efficiency.
          </div>
        </div>
      </div>
      <!-- FAQ 4 -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqHeading4">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse4" aria-expanded="false" aria-controls="faqCollapse4">
            Does the system provide automated route optimization?
          </button>
        </h2>
        <div id="faqCollapse4" class="accordion-collapse collapse" aria-labelledby="faqHeading4" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Yes, the system suggests the best routes based on traffic, distance, and delivery deadlines to reduce fuel costs and delays.
          </div>
        </div>
      </div>
      <!-- FAQ 5 -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqHeading5">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse5" aria-expanded="false" aria-controls="faqCollapse5">
            How does the system help with fuel and maintenance tracking?
          </button>
        </h2>
        <div id="faqCollapse5" class="accordion-collapse collapse" aria-labelledby="faqHeading5" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            It records fuel usage, generates reports, and sets reminders for vehicle maintenance based on mileage or time intervals.
          </div>
        </div>
      </div>
      <!-- FAQ 6 -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqHeading6">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse6" aria-expanded="false" aria-controls="faqCollapse6">
            Can I manage drivers and their assignments?
          </button>
        </h2>
        <div id="faqCollapse6" class="accordion-collapse collapse" aria-labelledby="faqHeading6" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Yes, you can assign drivers to specific trucks and shipments, track their performance, and generate reports on their trips.
          </div>
        </div>
      </div>
      <!-- FAQ 7 -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqHeading7">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse7" aria-expanded="false" aria-controls="faqCollapse7">
            Can I generate reports on fleet performance?
          </button>
        </h2>
        <div id="faqCollapse7" class="accordion-collapse collapse" aria-labelledby="faqHeading7" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Yes, the system provides detailed reports on fleet efficiency, fuel consumption, driver performance, and overall operational costs.
          </div>
        </div>
      </div>
      <!-- FAQ 8 -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqHeading8">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse8" aria-expanded="false" aria-controls="faqCollapse8">
            Can I access the system on my mobile phone?
          </button>
        </h2>
        <div id="faqCollapse8" class="accordion-collapse collapse" aria-labelledby="faqHeading8" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Yes, the system is mobile-friendly and accessible through a web browser or a dedicated mobile app (if available).
          </div>
        </div>
      </div>
      <!-- FAQ 9 -->
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqHeading9">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse9" aria-expanded="false" aria-controls="faqCollapse9">
            What should I do if I forget my password?
          </button>
        </h2>
        <div id="faqCollapse9" class="accordion-collapse collapse" aria-labelledby="faqHeading9" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Click on "Forgot Password" on the login page to reset your password via email instructions.
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="<?= base_url('public/assets/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>

<?= $this->endSection() ?>