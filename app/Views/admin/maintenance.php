<?= $this->extend('templates/admin_layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">

<div class="container-fluid mt-4">
    <h1>Maintenance Analytics</h1>

    <ul class="nav nav-tabs" id="managementTabs" role="tablist">
        <li class="nav-item">
            <a href="<?= base_url('admin/trucks'); ?>" class="nav-link <?= (current_url(true)->getSegment(2) == 'trucks') ? 'active' : '' ?>">
                <span class="description">Truck Records</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/geolocation'); ?>" class="nav-link <?= (current_url(true)->getSegment(2) == 'geolocation') ? 'active' : '' ?>">
                <span class="description">Geolocation</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/maintenance'); ?>" class="nav-link <?= (current_url(true)->getSegment(2) == 'maintenance') ? 'active' : '' ?>">
                <span class="description">Maintenance Analytics</span>
            </a>
        </li>
    </ul>
</div>

<div class="row mt-4">
    <div class="col-lg-7 mb-4"> <div class="mb-3 d-flex align-items-center">
             <label for="chartTypeSelector" class="form-label me-2 fw-bold mb-0">Chart Type:</label>
             <select class="form-select w-auto" id="chartTypeSelector">
                 <option value="components" selected>Components Due</option>
                 <option value="distance">Truck Distance Frequency</option>
             </select>
        </div>
        <h3 id="chartTitle" class="text-center mb-3">Components Due Chart</h3> <div style="position: relative; height: 300px; width: 100%;"> <canvas id="analyticsChart"></canvas> </div>
    </div>
    <div class="col-lg-5 mb-4"> <h3>Maintenance Condition Table</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-sm text-center">
                <thead class="table-info">
                    <tr>
                        <th>Component</th>
                        <th>New Trucks<br><small>(0–5 yrs / &lt;100k km)</small></th>
                        <th>Old Trucks<br><small>(&gt;5 yrs / &gt;100k km)</small></th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Engine System</td><td>Every 5,000 km</td><td>Every 4,000 km</td></tr>
                    <tr><td>Transmission &amp; Drivetrain</td><td>Every 20,000 km</td><td>Every 15,000 km</td></tr>
                    <tr><td>Brake System</td><td>Every 10,000 km</td><td>Every 4,000 km</td></tr>
                    <tr><td>Suspension &amp; Chassis</td><td>Every 5,000 km</td><td>Every 4,000 km</td></tr>
                    <tr><td>Fuel &amp; Cooling System</td><td>Every 20,000 km</td><td>Every 15,000 km</td></tr>
                    <tr><td>Steering System</td><td>Every 20,000 km</td><td>Every 10,000 km</td></tr>
                    <tr><td>Electrical &amp; Auxiliary</td><td>Every 10,000 km</td><td>Every 7,000 km</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <h3 class="text-center">Trucks Needing Inspection</h3>
        <?php if (isset($dueTrucks) && !empty($dueTrucks)): // Check variable exists and is not empty ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-info text-center">
                        <tr>
                            <th>Truck ID</th>
                            <th>Truck Model</th>
                            <th>Last Service Mileage (Overall)</th>
                            <th>Condition</th>
                            <th>Last Inspection Date (Overall)</th>
                            <th>Current Mileage (km)</th>
                            <th>Action Needed (Due Components)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            // Define color-coded badges for components (ensure $allComponents exists)
                            $badgeClasses = [];
                            if(isset($allComponents) && is_array($allComponents)) {
                                $colors = [
                                    'primary', 'success', 'danger', 'warning text-dark',
                                    'info text-dark', 'secondary', 'dark'
                                ];
                                $i = 0;
                                foreach (array_keys($allComponents) as $key) {
                                    $badgeClasses[$key] = 'badge bg-' . ($colors[$i % count($colors)]);
                                    $i++;
                                }
                            } else {
                                // Fallback if $allComponents isn't set correctly
                                $allComponents = []; // Prevent further errors below
                            }
                        ?>
                        <?php foreach ($dueTrucks as $item): ?>
                            <?php $truckId = $item['truckId'] ?? 'N/A_ID'; ?>
                            <tr>
                                <td class="text-center">
                                    <a href="#" class="truck-details-link fw-bold"
                                       data-bs-toggle="modal"
                                       data-bs-target="#truckDetailModal"
                                       data-truck-id="<?= esc($truckId, 'attr') ?>">
                                        <?= esc($truckId) ?>
                                    </a>
                                </td>
                                <td class="text-center"><?= esc($item['truckModel'] ?? 'N/A') ?></td>
                                <td class="text-center"><?= isset($item['lastServiceMileage']) && is_numeric($item['lastServiceMileage']) ? number_format($item['lastServiceMileage']) : esc($item['lastServiceMileage'] ?? 'N/A') ?></td>
                                <td class="text-center"><?= esc($item['condition'] ?? 'N/A') ?></td>
                                <td class="text-center"><?= esc($item['lastInspectionDate'] ?? 'N/A') ?></td>
                                <td class="text-center"><?= isset($item['currentMileage']) && is_numeric($item['currentMileage']) ? number_format($item['currentMileage']) : esc($item['currentMileage'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if (!empty($item['dueComponents']) && is_array($item['dueComponents'])): ?>
                                        <?php foreach ($item['dueComponents'] as $compKey): ?>
                                            <?php
                                                // Ensure label and CSS class can be found or use fallback
                                                $label = isset($allComponents[$compKey]) ? $allComponents[$compKey] : $compKey; // Use key if label missing
                                                $css   = isset($badgeClasses[$compKey]) ? $badgeClasses[$compKey] : 'badge bg-light text-dark'; // Default badge
                                            ?>
                                            <span class="<?= $css ?>" style="margin-right: 4px; margin-bottom: 4px; display: inline-block;">
                                                <?= esc($label) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center alert alert-info">No trucks currently need inspection based on mileage or reported defects.</p>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="componentModal" tabindex="-1" aria-labelledby="componentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="componentModalLabel">Trucks Needing <span id="componentName">Component</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-info">
                    <tr>
                        <th>Truck ID</th>
                        <th>Truck Model</th>
                    </tr>
                </thead>
                <tbody id="componentModalBody">
                    </tbody>
            </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="truckDetailModal" tabindex="-1" aria-labelledby="truckDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="truckDetailModalLabel">Truck Details: <span id="modalTruckId" class="fw-bold"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-3 p-2 bg-light border rounded">
            <div class="col-md-6 mb-2 mb-md-0">
                <strong>Truck Model:</strong> <span id="modalTruckModel"></span><br>
                <strong>Condition:</strong> <span id="modalTruckCondition"></span> (<span id="modalTruckYearsOld"></span> years old)<br>
                <strong>Manufacturing Date:</strong> <span id="modalManufacturingDate"></span><br>
            </div>
            <div class="col-md-6">
                <strong>Current Mileage:</strong> <span id="modalCurrentMileage"></span> km<br>
                <strong>Last Overall Inspection:</strong> <span id="modalLastInspectionDate"></span> at <span id="modalLastInspectionMileage"></span> km<br>
                <strong>Total Bookings Recorded:</strong> <span id="modalBookingCount" class="badge bg-info text-dark"></span>
            </div>
        </div>

        <h6>Component Maintenance Status</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-striped table-hover">
                <thead class="table-info text-center">
                    <tr>
                        <th>Component</th>
                        <th>Status</th>
                        <th>Required Interval (km)</th>
                        <th>Last Service Date</th>
                        <th>Last Service Mileage (km)</th>
                    </tr>
                </thead>
                <tbody id="truckDetailModalBody">
                    </tbody>
            </table>
        </div>
        <small class="text-muted d-block mt-2">* Status indicates if component is currently defective or past its mileage interval.</small>
       </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // --- Data passed from Controller ---
    // Use null coalescing operator ?? to provide default empty structures if variables aren't set
    const componentChartRawData = <?= json_encode($componentChartData ?? ['labels' => [], 'datasets' => []]) ?>;
    const distanceChartRawData  = <?= json_encode($distanceChartData ?? ['labels' => [], 'datasets' => []]) ?>;
    const componentTrucks       = <?= json_encode($componentTrucks ?? []) ?>; // For component chart clicks
    const allComponents         = <?= json_encode($allComponents ?? []) ?>; // Map key -> label
    const allTrucksData         = <?= json_encode($allTrucksDataForJs ?? []) ?>; // Detailed data for ALL trucks
    const componentKeys         = Object.keys(allComponents);

    // --- Chart Configuration ---
    const ctx = document.getElementById('analyticsChart')?.getContext('2d');
    let myChart = null; // Global chart instance variable

    // --- Define Colors and Options ---
    const componentColors = [
        'rgba(0, 123, 255, 0.7)',   // Primary (Engine)
        'rgba(40, 167, 69, 0.7)',   // Success (Transmission)
        'rgba(220, 53, 69, 0.7)',   // Danger (Brake)
        'rgba(255, 193, 7, 0.7)',   // Warning (Suspension)
        'rgba(23, 162, 184, 0.7)',  // Info (Fuel/Cooling)
        'rgba(108, 117, 125, 0.7)', // Secondary (Steering)
        'rgba(52, 58, 64, 0.7)'     // Dark (Electrical)
    ];

    const componentChartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            title: { display: false }, // Title is set outside chart via H3
            legend: { display: false }, // Keep chart area clean
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || context.label || '';
                        if (label) { label += ': '; }
                        if (context.parsed.y !== null) {
                             label += context.parsed.y + ' truck(s)';
                        }
                        return label;
                    }
                }
            }
        },
        onClick: function(evt, elements) { // Specific onClick for component chart
            if (!elements || elements.length === 0 || !componentKeys) return; // Exit if no click target or keys missing

            // Find the corresponding component key based on index
            const elementIndex = elements[0].index;
            if (elementIndex >= 0 && elementIndex < componentKeys.length) {
                 const componentKey = componentKeys[elementIndex];
                 showComponentModal(componentKey); // Call function to show modal
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: { display: true, text: 'Number of Trucks Needing Inspection' },
                ticks: { stepSize: 1, precision: 0 } // Ensure whole numbers
            },
            x: {
                title: { display: true, text: 'Maintenance Components' }
            }
        }
    };

    const distanceChartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            title: { display: false }, // Title is set outside chart via H3
            legend: { display: true, position: 'top' }, // Show legend for stacked bars
            tooltip: {
                 mode: 'index', // Show tooltip for all stacks at that index
                 intersect: false,
                 callbacks: {
                    label: function(context) {
                         let label = context.dataset.label || '';
                         if (label) { label += ': '; }
                         if (context.parsed.y !== null) {
                              label += context.parsed.y + ' booking(s)';
                         }
                         return label;
                     }
                 }
            }
        },
        scales: {
            x: {
                stacked: true, // Enable stacking
                title: { display: true, text: 'Truck ID' }
            },
            y: {
                stacked: true, // Enable stacking
                beginAtZero: true,
                title: { display: true, text: 'Number of Bookings' },
                ticks: { stepSize: 1, precision: 0 } // Ensure whole numbers
            }
        },
        onClick: null // No specific click action defined for distance chart bars
    };

    // --- Chart Rendering Function ---
    function renderChart(type) {
        if (!ctx) {
            console.error("Canvas context 'analyticsChart' not found!");
            return;
        }

        // Destroy previous chart instance if it exists
        if (myChart) {
            myChart.destroy();
            myChart = null;
        }

        let chartConfig = {};
        const chartTitleEl = document.getElementById('chartTitle');
        let dataAvailable = false; // Flag to track if we should render

        // Clear canvas before potentially showing 'no data' or rendering new chart
        clearNoDataMessage();

        if (type === 'components') {
            chartTitleEl.textContent = 'Components Due Chart';
            // Check if data is valid and has entries
             if (componentChartRawData && componentChartRawData.labels && componentChartRawData.labels.length > 0 && componentChartRawData.datasets && componentChartRawData.datasets[0]) {
                 // Prepare component chart data (add colors)
                 componentChartRawData.datasets[0].backgroundColor = componentColors.slice(0, componentChartRawData.labels.length); // Ensure enough colors
                 componentChartRawData.datasets[0].borderColor = componentChartRawData.datasets[0].backgroundColor.map(color => color.replace('0.7', '1'));
                 componentChartRawData.datasets[0].borderWidth = 1;

                 chartConfig = {
                     type: 'bar',
                     data: componentChartRawData,
                     options: componentChartOptions
                 };
                 dataAvailable = true;
             } else {
                displayNoDataMessage("No component maintenance data available.");
             }

        } else if (type === 'distance') {
            chartTitleEl.textContent = 'Truck Distance Frequency Chart';
             // Check if data is valid and has entries
             if (distanceChartRawData && distanceChartRawData.labels && distanceChartRawData.labels.length > 0 && distanceChartRawData.datasets) {
                 chartConfig = {
                     type: 'bar',
                     data: distanceChartRawData, // Colors are already defined in controller for distance
                     options: distanceChartOptions
                 };
                 dataAvailable = true;
             } else {
                displayNoDataMessage("No truck booking distance data available.");
             }
        } else {
             console.error("Unknown chart type requested:", type);
             displayNoDataMessage("Invalid chart type selected.");
             return; // Exit if type is unknown
        }

        // Create the new chart ONLY if data is available
        if (dataAvailable) {
            myChart = new Chart(ctx, chartConfig);
        }
    }

     // --- Helper to display message on canvas ---
     function displayNoDataMessage(message) {
         clearNoDataMessage(); // Clear previous chart/message first
         if (ctx) {
             ctx.save();
             ctx.font = "16px Arial";
             ctx.fillStyle = "#6c757d";
             ctx.textAlign = "center";
             ctx.fillText(message, ctx.canvas.width / 2, ctx.canvas.height / 2);
             ctx.restore();
         }
     }

     // --- Helper to clear canvas ---
     function clearNoDataMessage() {
         if (ctx) {
            // Clear the entire canvas
            ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
         }
     }

    // --- Event Listener for Dropdown ---
    const chartSelector = document.getElementById('chartTypeSelector');
    if (chartSelector) {
        chartSelector.addEventListener('change', function() {
            renderChart(this.value);
        });
    } else {
        console.error("Chart type selector dropdown not found!");
    }


    // --- Initial Chart Load & Modal Setup ---
    document.addEventListener('DOMContentLoaded', function () {
        // Render the default chart ('components')
        renderChart('components');

        // Setup Truck Detail Modal Listener
        const truckDetailModalEl = document.getElementById('truckDetailModal');
        if (truckDetailModalEl && typeof bootstrap !== 'undefined') { // Check if Bootstrap JS is loaded
             const truckDetailModalInstance = bootstrap.Modal.getOrCreateInstance(truckDetailModalEl);

             // Use event delegation on a static parent (like the table body or document)
             // This is more robust if the table content changes dynamically
             const dueTrucksTableBody = document.querySelector('.table-hover tbody'); // Adjust selector if needed
             if (dueTrucksTableBody) {
                 dueTrucksTableBody.addEventListener('click', function(event) {
                    // Check if the clicked element or its parent is the link
                    let targetElement = event.target;
                    while (targetElement != null && !targetElement.classList.contains('truck-details-link')) {
                        targetElement = targetElement.parentElement;
                    }

                    if (targetElement && targetElement.classList.contains('truck-details-link')) {
                         event.preventDefault(); // Prevent default link behavior
                         const truckId = targetElement.getAttribute('data-truck-id');
                         if(truckId) {
                             showTruckDetailModal(truckId, truckDetailModalInstance);
                         }
                    }
                 });
             } else {
                 // Fallback for direct listeners if delegation target isn't easily found
                 document.querySelectorAll('.truck-details-link').forEach(function(link) {
                    link.addEventListener('click', function(event) {
                        event.preventDefault();
                        const truckId = this.getAttribute('data-truck-id');
                         if(truckId) {
                            showTruckDetailModal(truckId, truckDetailModalInstance);
                         }
                    });
                 });
             }
        } else {
            if (!truckDetailModalEl) console.error("Truck detail modal element not found!");
            if (typeof bootstrap === 'undefined') console.error("Bootstrap JS not loaded, modals may not work.");
        }
    });


    // --- Modal Population Functions ---

    // Function to populate and show the Component Modal (for component chart clicks)
    function showComponentModal(componentKey) {
        if (!componentKey || !allComponents || !allComponents[componentKey] || !componentTrucks) {
            console.error("Missing data for component modal:", componentKey);
            return;
        }

        const trucks = componentTrucks[componentKey] || []; // Trucks needing this component
        const componentLabel = allComponents[componentKey];

        const modalTitle = document.querySelector('#componentModalLabel #componentName');
        const modalBody = document.getElementById('componentModalBody');

        if (modalTitle) modalTitle.textContent = componentLabel;
        if (!modalBody) {
            console.error("Component modal body not found");
            return;
        }
        modalBody.innerHTML = ''; // Clear previous content

        if (trucks.length > 0) {
            trucks.forEach(function(t) {
                // Ensure truck data is valid before trying to access properties
                const truck_id = t && t.truck_id ? t.truck_id : 'N/A';
                const truck_model = t && t.truck_model ? t.truck_model : 'N/A';
                const tr = `<tr><td>${escapeHtml(truck_id)}</td><td>${escapeHtml(truck_model)}</td></tr>`;
                modalBody.insertAdjacentHTML('beforeend', tr);
            });
        } else {
            const tr = `<tr><td colspan="2" class="text-center text-muted">No trucks currently marked as needing ${escapeHtml(componentLabel)}.</td></tr>`;
            modalBody.insertAdjacentHTML('beforeend', tr);
        }

        // Show the modal using Bootstrap's JS API
        const componentModalEl = document.getElementById('componentModal');
        if (componentModalEl && typeof bootstrap !== 'undefined') {
            const componentModal = bootstrap.Modal.getOrCreateInstance(componentModalEl);
            componentModal.show();
        }
    }

    // Function to populate and show the Truck Detail Modal (for table link clicks)
    function showTruckDetailModal(truckId, modalInstance) {
         if (!allTrucksData || !truckId) {
             console.error("Missing allTrucksData or truckId for detail modal");
             return;
         }
         const truckData = allTrucksData[truckId]; // Get the detailed data for this specific truck

         if (truckData) {
             // Populate Header/Info fields
             setElementText('modalTruckId', truckData.truckId);
             setElementText('modalTruckModel', truckData.truckModel);
             setElementText('modalTruckCondition', truckData.condition);
             setElementText('modalTruckYearsOld', truckData.yearsOld);
             setElementText('modalManufacturingDate', truckData.manufacturingDate);
             setElementText('modalCurrentMileage', formatNumber(truckData.currentMileage));
             setElementText('modalLastInspectionDate', truckData.lastInspectionDate);
             setElementText('modalLastInspectionMileage', formatNumber(truckData.lastInspectionMileage));
             setElementText('modalBookingCount', truckData.bookingCount);

             // Populate Component Table Body in the detail modal
             const tbodyDetail = document.getElementById('truckDetailModalBody');
             if (!tbodyDetail) {
                 console.error("Truck detail modal body not found!");
                 return;
             }
             tbodyDetail.innerHTML = ''; // Clear previous rows

             // Check if componentDetails exists and is an object
             if (truckData.componentDetails && typeof truckData.componentDetails === 'object' && Object.keys(truckData.componentDetails).length > 0) {
                 // Iterate through the defined order in allComponents to maintain consistency
                 Object.keys(allComponents).forEach(function(compKey) {
                     // Check if this truck *has* data for this specific component key
                     if (truckData.componentDetails.hasOwnProperty(compKey)) {
                         const compData = truckData.componentDetails[compKey];
                         let statusText = 'OK';
                         let statusClass = 'text-success fw-bold'; // Default OK

                         if (compData.is_defective) {
                             statusText = 'Defective';
                             statusClass = 'text-danger fw-bold';
                         } else if (compData.is_currently_due) { // Check the combined due flag
                             statusText = 'Due (Mileage)';
                             statusClass = 'text-warning fw-bold';
                         } else {
                             // Explicitly OK, maybe less bold
                              statusClass = 'text-success';
                         }

                         // Format data safely for display
                         const requiredInterval = (compData.required_interval === 'N/A' || compData.required_interval == Number.MAX_SAFE_INTEGER || compData.required_interval === PHP_INT_MAX) ? 'N/A' : formatNumber(compData.required_interval);
                         const lastServiceDate = compData.last_service_date || 'N/A';
                         const lastServiceMileage = formatNumber(compData.last_service_mileage); // formatNumber handles 0 or N/A

                         const rowHtml = `
                             <tr>
                                 <td>${escapeHtml(compData.label || compKey)}</td>
                                 <td class="text-center"><span class="${statusClass}">${statusText}</span></td>
                                 <td class="text-center">${escapeHtml(requiredInterval)}</td>
                                 <td class="text-center">${escapeHtml(lastServiceDate)}</td>
                                 <td class="text-center">${escapeHtml(lastServiceMileage)}</td>
                             </tr>`;
                         tbodyDetail.insertAdjacentHTML('beforeend', rowHtml);
                     } else {
                         // Optional: Add a row indicating missing maintenance data for a specific component for this truck
                          const rowHtml = `
                              <tr>
                                  <td>${escapeHtml(allComponents[compKey] || compKey)}</td>
                                  <td colspan="4" class="text-center text-muted fst-italic">No maintenance data recorded</td>
                              </tr>`;
                          tbodyDetail.insertAdjacentHTML('beforeend', rowHtml);
                     }
                 });
             } else {
                 // Handle case where componentDetails object is missing or empty for the truck
                 const tr = `<tr><td colspan="5" class="text-center text-muted">Component maintenance data is not available for this truck.</td></tr>`;
                 tbodyDetail.insertAdjacentHTML('beforeend', tr);
             }

             // Show the Modal instance passed to the function
             if (modalInstance) {
                modalInstance.show();
             }

         } else {
             console.error("Could not find data in allTrucksData for truck ID:", truckId);
             alert("Error: Could not load details for truck " + escapeHtml(truckId));
         }
    }


    // --- Helper Functions ---
    function setElementText(id, text) {
        const element = document.getElementById(id);
        if (element) {
            // Provide 'N/A' if text is null, undefined, or empty string
            element.textContent = (text !== null && text !== undefined && text !== '') ? text : 'N/A';
        } else {
            // console.warn("Element not found:", id); // Reduce console noise, enable if debugging
        }
    }

    function formatNumber(value) {
        // Handle various non-numeric or zero cases explicitly
        if (value === null || value === undefined || value === 'N/A' || value === '' || value === 0) {
            return '0'; // Consistently return '0' for 0 mileage/interval
        }
        // Check if it's genuinely numeric before formatting
        const num = Number(value);
        if (isNaN(num)) {
            return escapeHtml(String(value)); // Return original escaped value if not a number
        }
        // Format valid numbers with commas
        return num.toLocaleString();
    }

    function escapeHtml(unsafe) {
        if (unsafe === null || unsafe === undefined) return ''; // Return empty string for null/undefined
        // Ensure input is a string before replacing
        return String(unsafe)
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    // Define PHP_INT_MAX for JavaScript side if needed for comparison (used in showTruckDetailModal)
    const PHP_INT_MAX = 9223372036854775807; // Max 64-bit integer

</script>

<?= $this->endSection() ?>