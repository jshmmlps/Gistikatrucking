<?= $this->extend('templates/operations_coordinator_layout') ?>

<?= $this->section('content') ?>
<!-- Global CSS -->
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
<!-- Dashboard-specific CSS -->
<link href="<?= base_url('public/assets/css/dashboard.css'); ?>" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="headerbg">
    <h1>Dashboard</h1>
</div>

<div class="content">
    <div class="dashboard-grid">
        <!-- Maintenance Visualization Section -->
        <div class="dashboard-card maintenance-viz">
            <h3>Maintenance Visualization</h3>
            <div class="chart-container">
                <div class="chart-row">
                    <div class="pie-chart">
                        <canvas id="maintenanceStatusChart"></canvas>
                    </div>
                    <div class="bar-chart">
                        <canvas id="monthlyMaintenanceChart"></canvas>
                    </div>
                </div>
                <div class="line-chart">
                    <canvas id="costTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Available Trucks Section -->
        <div class="dashboard-card available-trucks">
            <div class="align-middle text-center trucks-header">
                <h3>Available Trucks</h3>
            </div>
            <div class="truck-count">
                <img src="<?= base_url('/public/images/delivery-truck.gif'); ?>" alt="Truck" class="truck-icon">
                <div class="count-display">
                    <span class="number">6</span>
                    <span class="label">Available</span>
                </div>
            </div>
            <div class="available-trucks-list">
                <!-- List of trucks -->
                <div class="truck-item" onclick="showTruckDetails(1); updateTruckLocation(1)">
                    <div class="truck-brief">
                        <img src="<?= base_url('/public/images/delivery-truck.gif'); ?>" alt="Truck" class="small-truck-icon">
                        <span class="truck-name">Isuzu F-Series FSR34</span>
                        <span class="truck-status">Available</span>
                    </div>
                </div>
                <div class="truck-item" onclick="showTruckDetails(2); updateTruckLocation(2)">
                    <div class="truck-brief">
                        <img src="<?= base_url('/public/images/delivery-truck.gif'); ?>" alt="Truck" class="small-truck-icon">
                        <span class="truck-name">Hino 700 Series</span>
                        <span class="truck-status">Available</span>
                    </div>
                </div>
                <div class="truck-item" onclick="showTruckDetails(3); updateTruckLocation(3)">
                    <div class="truck-brief">
                        <img src="<?= base_url('/public/images/delivery-truck.gif'); ?>" alt="Truck" class="small-truck-icon">
                        <span class="truck-name">Fuso Fighter FM</span>
                        <span class="truck-status">Available</span>
                    </div>
                </div>
                <div class="truck-item" onclick="showTruckDetails(4); updateTruckLocation(4)">
                    <div class="truck-brief">
                        <img src="<?= base_url('/public/images/delivery-truck.gif'); ?>" alt="Truck" class="small-truck-icon">
                        <span class="truck-name">Isuzu Giga CYZ</span>
                        <span class="truck-status">Available</span>
                    </div>
                </div>
                <div class="truck-item" onclick="showTruckDetails(5); updateTruckLocation(5)">
                    <div class="truck-brief">
                        <img src="<?= base_url('/public/images/delivery-truck.gif'); ?>" alt="Truck" class="small-truck-icon">
                        <span class="truck-name">Hino 500 Series</span>
                        <span class="truck-status">Available</span>
                    </div>
                </div>
                <div class="truck-item" onclick="showTruckDetails(6); updateTruckLocation(6)">
                    <div class="truck-brief">
                        <img src="<?= base_url('/public/images/delivery-truck.gif'); ?>" alt="Truck" class="small-truck-icon">
                        <span class="truck-name">Fuso Super Great</span>
                        <span class="truck-status">Available</span>
                    </div>
                </div>
            </div>
            <button class="w-100 fs-6 add-truck-btn" data-bs-toggle="modal" data-bs-target="#addTruckModal" onclick="showAddTruckModal()">
                <i class="fas fa-plus"></i> Add New Truck
            </button>
        </div>

        <!-- Geolocation Section -->
        <div class="dashboard-card geolocation">
            <h3>Geolocation</h3>
            <div class="truck-info" id="currentTruckInfo">
                <img src="<?= base_url('/public/images/delivery-truck.gif'); ?>" alt="Truck" class="small-truck-icon">
                <span>Select a truck to view location</span>
            </div>
            <div id="mapContainer">
                <div id="map"></div>
            </div>
        </div>
    </div>
</div>

<!-- Truck Details Modal -->
<div id="truckModal" class="modal">
    <div class="truck-details">
        <div class="details-header">
            <h2>Truck Details</h2>
            <span class="close-modal">&times;</span>
        </div>
        <div class="detail-grid">
            <div class="detail-item">
                <strong>Model:</strong>
                <span id="truckModel">Isuzu F-Series FSR34</span>
            </div>
            <div class="detail-item">
                <strong>Plate Number:</strong>
                <span id="plateNumber">ABC 123</span>
            </div>
            <div class="detail-item">
                <strong>Last Maintenance:</strong>
                <span id="lastMaintenance">2025-02-10</span>
            </div>
            <div class="detail-item">
                <strong>Next Maintenance:</strong>
                <span id="nextMaintenance">2025-03-10</span>
            </div>
            <div class="detail-item">
                <strong>Current Status:</strong>
                <span id="truckStatus" class="status-available">Available</span>
            </div>
            <div class="detail-item">
                <strong>Mileage:</strong>
                <span id="mileage">45,000 km</span>
            </div>
        </div>
        <div class="maintenance-indicators">
            <div class="indicator">
                <img src="<?= base_url('/public/images/oil.gif'); ?>" alt="Oil" class="indicator-icon">
                <span>Oil: Good</span>
            </div>
            <div class="indicator">
                <img src="<?= base_url('/public/images/battery.gif'); ?>" alt="Battery" class="indicator-icon">
                <span>Battery: Good</span>
            </div>
            <div class="indicator">
                <img src="<?= base_url('/public/images/engine.gif'); ?>" alt="Engine" class="indicator-icon">
                <span>Engine: Good</span>
            </div>
        </div>
    </div>
</div>

<!-- Add New Truck Modal -->
<div id="addTruckModal" class="modal fade" tabindex="-1" aria-labelledby="addTruckModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center w-100" id="offcanvasRightLabel">Add New Truck</h4>
                <span class="btn-close close-modal" data-bs-dismiss="modal" aria-label="Close" onclick="closeAddTruckModal()">&times;</span>
            </div>
            <form id="addTruckForm" onsubmit="handleAddTruck(event)">
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <tbody>
                                <tr>
                                    <th><label for="truckModel">Model:</label></th>
                                    <td><input type="text" id="truckModel" name="model" required></td>
                                </tr>
                                <tr>
                                    <th><label for="plateNumber">Plate Number:</label></th>
                                    <td><input type="text" id="plateNumber" name="plateNumber" required></td>
                                </tr>
                                <tr>
                                    <th><label for="lastMaintenance">Last Maintenance:</label></th>
                                    <td><input type="date" id="lastMaintenance" name="lastMaintenance" required></td>
                                </tr>
                                <tr>
                                    <th><label for="nextMaintenance">Next Maintenance:</label></th>
                                    <td><input type="date" id="nextMaintenance" name="nextMaintenance" required></td>
                                </tr>
                                <tr>
                                    <th><label for="mileage">Mileage (km):</label></th>
                                    <td><input type="number" id="mileage" name="mileage" required></td>
                                </tr>
                                <tr>
                                    <th><label for="status">Status:</label></th>
                                    <td>
                                        <select id="status" name="status" required>
                                            <option value="available">Available</option>
                                            <option value="maintenance">In Maintenance</option>
                                            <option value="route">On Route</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><label for="oilStatus">Oil Status:</label></th>
                                    <td>
                                        <select id="oilStatus" name="oilStatus" required>
                                            <option value="good">Good</option>
                                            <option value="fair">Fair</option>
                                            <option value="poor">Poor</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><label for="batteryStatus">Battery Status:</label></th>
                                    <td>
                                        <select id="batteryStatus" name="batteryStatus" required>
                                            <option value="good">Good</option>
                                            <option value="fair">Fair</option>
                                            <option value="poor">Poor</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><label for="engineStatus">Engine Status:</label></th>
                                    <td>
                                        <select id="engineStatus" name="engineStatus" required>
                                            <option value="good">Good</option>
                                            <option value="fair">Fair</option>
                                            <option value="poor">Poor</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="modal-footer">
                            <button type="submit" class="submit-btn">Add Truck</button>
                            <button type="button" class="cancel-btn" onclick="closeAddTruckModal()">Cancel</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- External JS Libraries -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Inline JavaScript for charts, maps, and modal functionality -->
<script>
// Chart initialization
function initCharts() {
    const maintenanceStatusCtx = document.getElementById('maintenanceStatusChart').getContext('2d');
    new Chart(maintenanceStatusCtx, {
        type: 'pie',
        data: {
            labels: ['Good Condition', 'Needs Maintenance', 'Under Repair'],
            datasets: [{
                data: [4, 1, 1],
                backgroundColor: ['#4CAF50', '#FFC107', '#F44336']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                title: { display: true, text: 'Maintenance Status' }
            }
        }
    });

    const monthlyMaintenanceCtx = document.getElementById('monthlyMaintenanceChart').getContext('2d');
    new Chart(monthlyMaintenanceCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Scheduled',
                data: [3, 6, 4, 5, 2, 3],
                backgroundColor: '#2196F3'
            }, {
                label: 'Completed',
                data: [3, 5, 4, 4, 2, 2],
                backgroundColor: '#4CAF50'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            },
            plugins: {
                legend: { position: 'bottom' },
                title: { display: true, text: 'Monthly Maintenance' }
            }
        }
    });

    const costTrendCtx = document.getElementById('costTrendChart').getContext('2d');
    new Chart(costTrendCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Maintenance Costs',
                data: [15000, 18000, 12000, 16000, 14000, 17000],
                borderColor: '#2196F3',
                tension: 0.4,
                fill: false
            }, {
                label: 'Repair Costs',
                data: [8000, 12000, 6000, 9000, 7000, 10000],
                borderColor: '#F44336',
                tension: 0.4,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: function(value) { return '₱' + value.toLocaleString(); } }
                }
            },
            plugins: {
                legend: { position: 'bottom' },
                title: { display: true, text: 'Cost Trends' }
            }
        }
    });
}

// Truck data and map functions
const truckData = {
    1: {
        model: 'Isuzu F-Series FSR34',
        plateNumber: 'ABC 123',
        lastMaintenance: '2025-02-10',
        nextMaintenance: '2025-03-10',
        status: 'Available',
        mileage: '45,000 km',
        location: { lat: 14.6016, lng: 120.9765, address: 'Puregold Tondo 1st, Manila' },
        route: [
            { lat: 14.6016, lng: 120.9765, address: 'Puregold Tondo 1st, Manila' },
            { lat: 14.5942, lng: 120.9711, address: 'Puregold Divisoria, Manila' },
            { lat: 14.6069, lng: 120.9824, address: 'Puregold Recto, Manila' }
        ],
        currentRouteIndex: 0
    },
    2: {
        model: 'Hino 700 Series',
        plateNumber: 'XYZ 789',
        lastMaintenance: '2025-02-15',
        nextMaintenance: '2025-03-15',
        status: 'Available',
        mileage: '32,000 km',
        location: { lat: 14.6547, lng: 121.0306, address: 'Puregold Novaliches, Quezon City' },
        route: [
            { lat: 14.6547, lng: 121.0306, address: 'Puregold Novaliches, Quezon City' },
            { lat: 14.6300, lng: 121.0330, address: 'Puregold FTI Quezon City' },
            { lat: 14.6157, lng: 121.0417, address: 'Puregold Cubao, Quezon City' }
        ],
        currentRouteIndex: 0
    },
    3: {
        model: 'Fuso Fighter FM',
        plateNumber: 'DEF 456',
        lastMaintenance: '2025-02-12',
        nextMaintenance: '2025-03-12',
        status: 'Available',
        mileage: '28,000 km',
        location: { lat: 10.2932, lng: 123.9019, address: 'Puregold Cebu, Cebu City' },
        route: [
            { lat: 10.2932, lng: 123.9019, address: 'Puregold Cebu, Cebu City' },
            { lat: 10.3132, lng: 123.9154, address: 'Puregold Mandaue, Cebu' },
            { lat: 10.2977, lng: 123.9062, address: 'Puregold Basak, Cebu' }
        ],
        currentRouteIndex: 0
    },
    4: {
        model: 'Isuzu Giga CYZ',
        plateNumber: 'GHI 789',
        lastMaintenance: '2025-02-18',
        nextMaintenance: '2025-03-18',
        status: 'Available',
        mileage: '35,000 km',
        location: { lat: 7.0736, lng: 125.6115, address: 'Puregold Victoria Plaza, Davao' },
        route: [
            { lat: 7.0736, lng: 125.6115, address: 'Puregold Victoria Plaza, Davao' },
            { lat: 7.0911, lng: 125.6128, address: 'Puregold NCCC Mall, Davao' },
            { lat: 7.1082, lng: 125.6139, address: 'Puregold Damosa, Davao' }
        ],
        currentRouteIndex: 0
    },
    5: {
        model: 'Hino 500 Series',
        plateNumber: 'JKL 012',
        lastMaintenance: '2025-02-20',
        nextMaintenance: '2025-03-20',
        status: 'Available',
        mileage: '42,000 km',
        location: { lat: 13.1421, lng: 123.7249, address: 'Puregold Legazpi, Albay' },
        route: [
            { lat: 13.1421, lng: 123.7249, address: 'Puregold Legazpi, Albay' },
            { lat: 13.1516, lng: 123.7447, address: 'Puregold Daraga, Albay' },
            { lat: 13.1377, lng: 123.7332, address: 'Puregold Old Albay, Legazpi' }
        ],
        currentRouteIndex: 0
    },
    6: {
        model: 'Fuso Super Great',
        plateNumber: 'MNO 345',
        lastMaintenance: '2025-02-14',
        nextMaintenance: '2025-03-14',
        status: 'Available',
        mileage: '38,000 km',
        location: { lat: 16.4130, lng: 120.5964, address: 'Puregold Baguio City Proper' },
        route: [
            { lat: 16.4130, lng: 120.5964, address: 'Puregold Baguio City Proper' },
            { lat: 16.4177, lng: 120.5996, address: 'Puregold Magsaysay, Baguio' },
            { lat: 16.4088, lng: 120.5927, address: 'Puregold Leonard Wood, Baguio' }
        ],
        currentRouteIndex: 0
    }
};

let map, routePolyline, selectedTruckId = null, markers = {};

function initMap() {
    const defaultCenter = { lat: 12.8797, lng: 121.7740 };
    map = L.map('map', { center: defaultCenter, zoom: 6, zoomControl: true, dragging: true, scrollWheelZoom: true });
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: ' OpenStreetMap contributors', maxZoom: 19
    }).addTo(map);

    window.truckIcon = L.icon({
        iconUrl: '<?= base_url('/public/images/delivery-truck.gif'); ?>',
        iconSize: [32, 32], iconAnchor: [16, 16], popupAnchor: [0, -16]
    });

    initializeAllTrucks();
}

function initializeAllTrucks() {
    Object.values(markers).forEach(marker => { if (marker) map.removeLayer(marker); });
    markers = {};
    Object.entries(truckData).forEach(([id, truck]) => {
        markers[id] = L.marker([truck.location.lat, truck.location.lng], { icon: window.truckIcon })
            .addTo(map)
            .bindPopup(`<b>${truck.model}</b><br>${truck.location.address}`)
            .on('click', () => showTruckDetails(id));
    });
}

function showTruckDetails(truckId) {
    const truck = truckData[truckId];
    if (!truck) return;
    document.getElementById('truckModel').textContent = truck.model;
    document.getElementById('plateNumber').textContent = truck.plateNumber;
    document.getElementById('lastMaintenance').textContent = truck.lastMaintenance;
    document.getElementById('nextMaintenance').textContent = truck.nextMaintenance;
    document.getElementById('truckStatus').textContent = truck.status;
    document.getElementById('mileage').textContent = truck.mileage;
    document.getElementById('truckModal').style.display = 'block';
    updateTruckLocation(truckId);
}

function updateTruckLocation(truckId) {
    const truck = truckData[truckId];
    if (!truck) return;
    selectedTruckId = truckId;
    document.getElementById('currentTruckInfo').innerHTML = `
        <img src="<?= base_url('/public/images/delivery-truck.gif'); ?>" alt="Truck" class="small-truck-icon">
        <div class="location-info">
            <span class="truck-name">${truck.model}</span>
            <span class="location-address">${truck.location.address}</span>
        </div>
    `;
    if (routePolyline) { map.removeLayer(routePolyline); }
    const routePoints = truck.route.map(point => [point.lat, point.lng]);
    routePolyline = L.polyline(routePoints, { color: '#2196F3', weight: 3, opacity: 0.7 }).addTo(map);
    map.fitBounds(routePolyline.getBounds(), { padding: [50, 50] });
    simulateTruckMovement(truckId);
}

function interpolatePosition(start, end, progress) {
    return { lat: start.lat + (end.lat - start.lat) * progress, lng: start.lng + (end.lng - start.lng) * progress };
}

function simulateTruckMovement(truckId) {
    if (selectedTruckId !== truckId) return;
    const truck = truckData[truckId];
    const currentPoint = truck.route[truck.currentRouteIndex];
    const nextIndex = (truck.currentRouteIndex + 1) % truck.route.length;
    const nextPoint = truck.route[nextIndex];
    truck.movementProgress = (truck.movementProgress || 0) + 0.1;
    if (truck.movementProgress >= 1) {
        truck.currentRouteIndex = nextIndex;
        truck.movementProgress = 0;
        truck.location = { lat: nextPoint.lat, lng: nextPoint.lng, address: nextPoint.address };
    } else {
        const interpolated = interpolatePosition(currentPoint, nextPoint, truck.movementProgress);
        const progress = Math.round(truck.movementProgress * 100);
        truck.location = { lat: interpolated.lat, lng: interpolated.lng, address: `Traveling: ${currentPoint.address} → ${nextPoint.address} (${progress}%)` };
    }
    if (markers[truckId]) {
        markers[truckId].setLatLng([truck.location.lat, truck.location.lng]);
        const popupContent = `
            <div class="map-popup">
                <strong>${truck.model}</strong><br>
                <small>${truck.plateNumber}</small><br>
                <div style="margin-top: 5px;">${truck.location.address}</div>
            </div>
        `;
        markers[truckId].getPopup().setContent(popupContent);
    }
    document.getElementById('currentTruckInfo').innerHTML = `
        <img src="<?= base_url('/public/images/delivery-truck.gif'); ?>" alt="Truck" class="small-truck-icon">
        <div class="location-info">
            <span class="truck-name">${truck.model} (${truck.plateNumb


