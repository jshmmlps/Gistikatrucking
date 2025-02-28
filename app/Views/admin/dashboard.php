<?= $this->extend('templates/admin_layout.php') ?>

<?= $this->section('content') ?>
<link href=<?=base_url('public/assets/css/style.css');?> rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    padding: 20px;
}

.dashboard-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.maintenance-viz {
    grid-column: 1 / 2;
    grid-row: 1 / 3;
}

.available-trucks {
    grid-column: 2 / 3;
    grid-row: 1 / 2;
}

.geolocation {
    grid-column: 2 / 3;
    grid-row: 2 / 3;
}

.chart-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.chart-row {
    display: flex;
    gap: 20px;
    height: 250px;
}

.pie-chart {
    flex: 1;
    position: relative;
}

.bar-chart {
    flex: 2;
}

.line-chart {
    height: 200px;
}

.truck-count {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    padding: 20px;
}

.truck-icon {
    width: 100px !important;
    height: auto !important;
}

.count-display {
    text-align: center;
}

.count-display .number {
    font-size: 48px;
    font-weight: bold;
    color: #003366;
    display: block;
}

.count-display .label {
    font-size: 16px;
    color: #666;
}

.truck-info {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.small-truck-icon {
    width: 50px !important;
    height: auto !important;
}

#map {
    height: 100%;
    width: 100%;
    border-radius: 8px;
}

#mapContainer {
    height: 400px;
    margin-top: 16px;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #ddd;
}

.truck-marker {
    background: none;
    border: none;
}

.truck-marker i {
    filter: drop-shadow(2px 2px 2px rgba(0,0,0,0.5));
}

.truck-info {
    display: flex;
    align-items: center;
    padding: 10px;
    background: #f5f5f5;
    border-radius: 6px;
    margin-bottom: 10px;
}

.maintenance-item {
    margin: 10px 0;
    padding: 10px;
    border-radius: 4px;
    background: #f8f9fa;
}

.maintenance-item.warning {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
}

.maintenance-item.good {
    background: #d4edda;
    border-left: 4px solid #28a745;
}

.item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
}

.status-badge {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: bold;
}

.good .status-badge {
    background: #28a745;
    color: white;
}

.warning .status-badge {
    background: #ffc107;
    color: black;
}

.item-dates {
    display: flex;
    justify-content: space-between;
    font-size: 0.9em;
    color: #666;
}

.map-popup {
    padding: 5px;
}

.popup-indicators {
    display: flex;
    gap: 10px;
    margin-top: 5px;
}

.popup-indicators .indicator {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.popup-indicators .good {
    background: #d4edda;
    color: #28a745;
}

.popup-indicators .warning {
    background: #fff3cd;
    color: #ffc107;
}

.indicator-icon {
    width: 32px !important;
    height: 32px !important;
}
</style>


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
                                    <td><input type="date" id="lastMaintenance" name="lastMaintenance" required>
                                </tr>
                                <tr>
                                    <th><label for="nextMaintenance">Next Maintenance:</label></th>
                                    <td><input type="date" id="nextMaintenance" name="nextMaintenance" required>
                                </tr>
                                <tr>
                                    <th><label for="mileage">Mileage (km):</label></th>
                                    <td><input type="number" id="mileage" name="mileage" required>
                                </tr>
                                <tr>
                                    <th><label for="status">Status:</label></th>
                                    <td><select id="status" name="status" required>
                                    <option value="available">Available</option>
                                    <option value="maintenance">In Maintenance</option>
                                    <option value="route">On Route</option>
                                </select></td>
                        
                                </tr>
                                <tr>
                                    <th><label for="oilStatus">Oil Status:</label></th>
                                    <td><select id="oilStatus" name="oilStatus" required>
                                        <option value="good">Good</option>
                                        <option value="fair">Fair</option>
                                        <option value="poor">Poor</option>
                                    </select></td>
                                </tr>
                                <tr>
                                    <th><label for="batteryStatus">Battery Status:</label></th>
                                    <td><select id="batteryStatus" name="batteryStatus" required>
                                        <option value="good">Good</option>
                                        <option value="fair">Fair</option>
                                        <option value="poor">Poor</option>
                                    </select>
                                </tr>
                                <tr>
                                    <th><label for="engineStatus">Engine Status:</label></th>
                                    <td><select id="engineStatus" name="engineStatus" required>
                                        <option value="good">Good</option>
                                        <option value="fair">Fair</option>
                                        <option value="poor">Poor</option>
                                    </select></td>
                                </tr>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Chart initialization
function initCharts() {
    // Maintenance Status Chart (Pie)
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
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Maintenance Status'
                }
            }
        }
    });

    // Monthly Maintenance Chart (Bar)
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
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Monthly Maintenance'
                }
            }
        }
    });

    // Cost Trend Chart (Line)
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
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Cost Trends'
                }
            }
        }
    });
}

// Truck data structure with more accurate routes
const truckData = {
    1: {
        model: 'Isuzu F-Series FSR34',
        plateNumber: 'ABC 123',
        lastMaintenance: '2025-02-10',
        nextMaintenance: '2025-03-10',
        status: 'Available',
        mileage: '45,000 km',
        location: {
            lat: 14.6016,
            lng: 120.9765,
            address: 'Puregold Tondo 1st, Manila'
        },
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
        location: {
            lat: 14.6547,
            lng: 121.0306,
            address: 'Puregold Novaliches, Quezon City'
        },
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
        location: {
            lat: 10.2932,
            lng: 123.9019,
            address: 'Puregold Cebu, Cebu City'
        },
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
        location: {
            lat: 7.0736,
            lng: 125.6115,
            address: 'Puregold Victoria Plaza, Davao'
        },
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
        location: {
            lat: 13.1421,
            lng: 123.7249,
            address: 'Puregold Legazpi, Albay'
        },
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
        location: {
            lat: 16.4130,
            lng: 120.5964,
            address: 'Puregold Baguio City Proper'
        },
        route: [
            { lat: 16.4130, lng: 120.5964, address: 'Puregold Baguio City Proper' },
            { lat: 16.4177, lng: 120.5996, address: 'Puregold Magsaysay, Baguio' },
            { lat: 16.4088, lng: 120.5927, address: 'Puregold Leonard Wood, Baguio' }
        ],
        currentRouteIndex: 0
    }
};

let map;
let currentMarker;
let routePolyline;
let selectedTruckId = null;
let markers = {};

function initMap() {
    const defaultCenter = { lat: 12.8797, lng: 121.7740 }; // Center of Philippines
    
    map = L.map('map', {
        center: defaultCenter,
        zoom: 6,
        zoomControl: true,
        dragging: true,
        scrollWheelZoom: true
    });
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: ' OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    window.truckIcon = L.icon({
        iconUrl: '<?= base_url('/public/images/delivery-truck.gif'); ?>',
        iconSize: [32, 32],
        iconAnchor: [16, 16],
        popupAnchor: [0, -16]
    });

    // Initialize all truck markers
    initializeAllTrucks();
}

function initializeAllTrucks() {
    // Clear existing markers
    Object.values(markers).forEach(marker => {
        if (marker) map.removeLayer(marker);
    });
    markers = {};

    // Add markers for all trucks
    Object.entries(truckData).forEach(([id, truck]) => {
        markers[id] = L.marker([truck.location.lat, truck.location.lng], {
            icon: window.truckIcon
        })
        .addTo(map)
        .bindPopup(`<b>${truck.model}</b><br>${truck.location.address}`)
        .on('click', () => showTruckDetails(id));
    });
}

function showTruckDetails(truckId) {
    const truck = truckData[truckId];
    if (!truck) return;

    // Update modal with truck details
    document.getElementById('truckModel').textContent = truck.model;
    document.getElementById('plateNumber').textContent = truck.plateNumber;
    document.getElementById('lastMaintenance').textContent = truck.lastMaintenance;
    document.getElementById('nextMaintenance').textContent = truck.nextMaintenance;
    document.getElementById('truckStatus').textContent = truck.status;
    document.getElementById('mileage').textContent = truck.mileage;

    // Show the modal
    const modal = document.getElementById('truckModal');
    modal.style.display = 'block';

    // Update truck location on map
    updateTruckLocation(truckId);
}

function updateTruckLocation(truckId) {
    const truck = truckData[truckId];
    if (!truck) return;

    selectedTruckId = truckId;

    // Update info panel
    const truckInfoElement = document.getElementById('currentTruckInfo');
    truckInfoElement.innerHTML = `
        <img src="<?= base_url('/public/images/delivery-truck.gif'); ?>" alt="Truck" class="small-truck-icon">
        <div class="location-info">
            <span class="truck-name">${truck.model}</span>
            <span class="location-address">${truck.location.address}</span>
        </div>
    `;

    // Remove existing polyline
    if (routePolyline) {
        map.removeLayer(routePolyline);
    }

    // Draw route line
    const routePoints = truck.route.map(point => [point.lat, point.lng]);
    routePolyline = L.polyline(routePoints, {
        color: '#2196F3',
        weight: 3,
        opacity: 0.7
    }).addTo(map);

    // Fit map to show entire route
    const bounds = routePolyline.getBounds();
    map.fitBounds(bounds, {
        padding: [50, 50]
    });

    // Start movement simulation
    simulateTruckMovement(truckId);
}

function interpolatePosition(start, end, progress) {
    return {
        lat: start.lat + (end.lat - start.lat) * progress,
        lng: start.lng + (end.lng - start.lng) * progress
    };
}

function simulateTruckMovement(truckId) {
    if (selectedTruckId !== truckId) return; // Stop if another truck is selected

    const truck = truckData[truckId];
    const currentPoint = truck.route[truck.currentRouteIndex];
    const nextIndex = (truck.currentRouteIndex + 1) % truck.route.length;
    const nextPoint = truck.route[nextIndex];
    
    // Track progress of movement between points (0 to 1)
    truck.movementProgress = (truck.movementProgress || 0) + 0.1; // Move 10% each step
    
    if (truck.movementProgress >= 1) {
        // Reached the next point
        truck.currentRouteIndex = nextIndex;
        truck.movementProgress = 0;
        truck.location = {
            lat: nextPoint.lat,
            lng: nextPoint.lng,
            address: nextPoint.address
        };
    } else {
        // Calculate intermediate position
        const interpolated = interpolatePosition(currentPoint, nextPoint, truck.movementProgress);
        const progress = Math.round(truck.movementProgress * 100);
        truck.location = {
            lat: interpolated.lat,
            lng: interpolated.lng,
            address: `Traveling: ${currentPoint.address} → ${nextPoint.address} (${progress}%)`
        };
    }

    // Update marker position and popup
    if (markers[truckId]) {
        markers[truckId].setLatLng([truck.location.lat, truck.location.lng]);
        
        // Create detailed popup content
        const popupContent = `
            <div class="map-popup">
                <strong>${truck.model}</strong><br>
                <small>${truck.plateNumber}</small><br>
                <div style="margin-top: 5px;">
                    ${truck.location.address}
                </div>
            </div>
        `;
        markers[truckId].getPopup().setContent(popupContent);
    }

    // Update info panel with detailed information
    const truckInfoElement = document.getElementById('currentTruckInfo');
    if (truckInfoElement) {
        truckInfoElement.innerHTML = `
            <img src="<?= base_url('/public/images/delivery-truck.gif'); ?>" alt="Truck" class="small-truck-icon">
            <div class="location-info">
                <span class="truck-name">${truck.model} (${truck.plateNumber})</span>
                <span class="location-address">${truck.location.address}</span>
            </div>
        `;
    }

    // Continue movement
    setTimeout(() => {
        if (selectedTruckId === truckId) {
            simulateTruckMovement(truckId);
        }
    }, 3000); // Update every 3 seconds for smooth movement
}

// Modal functionality
function showAddTruckModal() {
    document.getElementById('addTruckModal').style.display = 'block';
}

function closeAddTruckModal() {
    document.getElementById('addTruckModal').style.display = 'none';
}

function handleAddTruck(event) {
    event.preventDefault();
    // Add truck handling logic here
    closeAddTruckModal();
}

// Close modal functionality
document.querySelectorAll('.close-modal').forEach(button => {
    button.onclick = function() {
        this.closest('.modal').style.display = 'none';
    }
});

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// Initialize everything when the page loads
document.addEventListener('DOMContentLoaded', () => {
    initMap();
    initCharts(); // Initialize the charts
});
</script>

<?= $this->endSection() ?>