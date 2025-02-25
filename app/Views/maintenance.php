<?= $this->extend('templates/layout.php') ?>

<?= $this->section('content') ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Maintenance Analytics</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- External CSS -->
  <link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- Bootstrap Bundle JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Leaflet CSS and JS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body class="bg-light">
  <header class="dashboard-header">
    <h1>Maintenance Analytics</h1>
    <h2>DASHBOARD</h2>
  </header>

  <div class="container mt-4">
    <!-- Dashboard Cards -->
    <div class="row">
      <div class="col-md-3">
        <div class="dashboard-card">
          <h3>Available Truck</h3>
          <p>4</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="dashboard-card">
          <h3>Geolocation</h3>
          <div id="map"></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="dashboard-card">
          <h3>Truck Information</h3>
          <img src="<?= base_url('public/assets/images/delivery-truck.gif'); ?>" alt="Truck Logo" style="width: 100px; height: auto;">
        </div>
      </div>
    </div>

    <!-- Maintenance Table -->
    <div class="row mt-4">
      <div class="col-md-12">
        <div class="card">
          <table class="table table-bordered table-striped">
            <thead class="table-primary">
              <tr>
                <th>Truck ID</th>
                <th>Last Maintenance</th>
                <th>Next Maintenance Due</th>
                <th>Mileage</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="maintenanceTable">
              <?php if (!empty($maintenanceData) && is_array($maintenanceData)) : ?>
                <?php foreach ($maintenanceData as $truck) : ?>
                  <tr>
                    <td><?= esc($truck['truckId']); ?></td>
                    <td><?= esc($truck['lastMaintenance']); ?></td>
                    <td><?= esc($truck['nextMaintenance']); ?></td>
                    <td><?= esc($truck['mileage']); ?></td>
                    <td><?= esc($truck['status']); ?></td>
                    <td>
                      <button class="btn btn-info btn-sm" onclick="showIndicators(<?= $truck['truckId']; ?>)">Details</button>
                      <button class="btn btn-warning btn-sm" onclick="editTruck(<?= $truck['truckId']; ?>)">Edit</button>
                      <button class="btn btn-danger btn-sm" onclick="removeTruck(<?= $truck['truckId']; ?>)">Remove</button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else : ?>
                <tr>
                  <td colspan="6">No maintenance records found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
          <button class="btn btn-primary" onclick="addTruck()">Add Truck</button>
        </div>
      </div>
    </div>

    <!-- Charts -->
    <div class="row mt-4">
      <div class="col-md-6">
        <div class="card chart-container">
          <canvas id="maintenanceChart"></canvas>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card chart-container">
          <canvas id="distanceTraveledChart"></canvas>
        </div>
      </div>
    </div>
    <div class="row mt-4">
      <div class="col-md-12">
        <div class="card chart-container">
          <canvas id="maintenanceDaysChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Truck Modal -->
  <div class="modal fade" id="editTruckModal" tabindex="-1" aria-labelledby="editTruckModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editTruckModalLabel">Edit Truck</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editTruckForm">
            <div class="mb-3">
              <label for="editTruckId" class="form-label">Truck ID</label>
              <input type="number" class="form-control" id="editTruckId" required>
            </div>
            <div class="mb-3">
              <label for="editLastMaintenance" class="form-label">Last Maintenance</label>
              <input type="date" class="form-control" id="editLastMaintenance" required>
            </div>
            <div class="mb-3">
              <label for="editNextMaintenance" class="form-label">Next Maintenance Due</label>
              <input type="date" class="form-control" id="editNextMaintenance" required>
            </div>
            <div class="mb-3">
              <label for="editMileage" class="form-label">Mileage</label>
              <input type="number" class="form-control" id="editMileage" required>
            </div>
            <div class="mb-3">
              <label for="editStatus" class="form-label">Status</label>
              <select class="form-select" id="editStatus" required>
                <option value="Good">Good</option>
                <option value="Due Soon">Due Soon</option>
                <option value="Overdue">Overdue</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Key Indicators Container -->
  <div id="keyIndicatorsContainer">
    <button class="close-btn" onclick="hideKeyIndicators()">Close</button>
    <div class="grid-container" id="keyIndicatorsIcons"></div>
  </div>

  <script>
    // Get maintenance data passed from the controller (PHP)
    let maintenanceData = <?= json_encode($maintenanceData) ?>;

    function updateTable() {
      let tableBody = document.getElementById("maintenanceTable");
      tableBody.innerHTML = "";
      maintenanceData.forEach(truck => {
        tableBody.innerHTML += `
          <tr>
            <td>${truck.truckId}</td>
            <td>${truck.lastMaintenance}</td>
            <td>${truck.nextMaintenance}</td>
            <td>${truck.mileage}</td>
            <td>${truck.status}</td>
            <td>
              <button class="btn btn-info btn-sm" onclick="showIndicators(${truck.truckId})">Details</button>
              <button class="btn btn-warning btn-sm" onclick="editTruck(${truck.truckId})">Edit</button>
              <button class="btn btn-danger btn-sm" onclick="removeTruck(${truck.truckId})">Remove</button>
            </td>
          </tr>
        `;
      });
    }

    function showIndicators(truckId) {
      const truck = maintenanceData.find(t => t.truckId === truckId);
      document.getElementById("keyIndicatorsContainer").style.display = "block";

      document.getElementById("keyIndicatorsIcons").innerHTML = `
        <div class="indicator-card">
          <img src="<?= base_url('public/assets/images/engine.gif'); ?>" alt="Engine">
          <p>Engine: ${truck.engine}</p>
        </div>
        <div class="indicator-card">
          <img src="<?= base_url('public/assets/images/battery.gif'); ?>" alt="Battery">
          <p>Battery: ${truck.battery}</p>
        </div>
        <div class="indicator-card">
          <img src="<?= base_url('public/assets/images/oil.gif'); ?>" alt="Oil">
          <p>Oil Level: ${truck.oil}</p>
        </div>
        <div class="indicator-card">
          <img src="<?= base_url('public/assets/images/gas.gif'); ?>" alt="Gas">
          <p>Gas: ${truck.gas}</p>
        </div>
      `;
    }

    function hideKeyIndicators() {
      document.getElementById("keyIndicatorsContainer").style.display = "none";
    }

    function addTruck() {
      const newTruck = {
        truckId: 104,
        lastMaintenance: "2023-10-01",
        nextMaintenance: "2023-12-01",
        mileage: 5000,
        distanceTraveled: 2500,
        status: "Good",
        engine: "Good",
        battery: "Good",
        oil: "90%",
        gas: "60L",
        fuelConsumption: 100
      };
      maintenanceData.push(newTruck);
      updateTable();
    }

    function editTruck(truckId) {
      const truck = maintenanceData.find(t => t.truckId === truckId);
      document.getElementById("editTruckId").value = truck.truckId;
      document.getElementById("editLastMaintenance").value = truck.lastMaintenance;
      document.getElementById("editNextMaintenance").value = truck.nextMaintenance;
      document.getElementById("editMileage").value = truck.mileage;
      document.getElementById("editStatus").value = truck.status;

      const modal = new bootstrap.Modal(document.getElementById('editTruckModal'));
      modal.show();

      const form = document.getElementById("editTruckForm");
      form.onsubmit = (e) => {
        e.preventDefault();
        truck.truckId = parseInt(document.getElementById("editTruckId").value);
        truck.lastMaintenance = document.getElementById("editLastMaintenance").value;
        truck.nextMaintenance = document.getElementById("editNextMaintenance").value;
        truck.mileage = parseInt(document.getElementById("editMileage").value);
        truck.status = document.getElementById("editStatus").value;
        updateTable();
        modal.hide();
      };
    }

    function removeTruck(truckId) {
      const index = maintenanceData.findIndex(t => t.truckId === truckId);
      if (index > -1) {
        maintenanceData.splice(index, 1);
        updateTable();
      }
      hideKeyIndicators();
    }

    // Create charts using Chart.js
    const daysSinceLastMaintenance = maintenanceData.map(truck => {
      const lastMaintenance = new Date(truck.lastMaintenance);
      const today = new Date();
      const diffTime = today - lastMaintenance;
      return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    });

    new Chart(document.getElementById("maintenanceChart"), {
      type: "bar",
      data: {
        labels: maintenanceData.map(truck => `Truck ${truck.truckId}`),
        datasets: [{
          label: "Days Since Last Maintenance",
          data: daysSinceLastMaintenance,
          backgroundColor: "#4e73df",
        }],
      },
      options: { 
        scales: { y: { beginAtZero: true } }
      }
    });

    new Chart(document.getElementById("distanceTraveledChart"), {
      type: "pie",
      data: {
        labels: maintenanceData.map(truck => `Truck ${truck.truckId}`),
        datasets: [{
          label: "Distance Traveled",
          data: maintenanceData.map(truck => truck.distanceTraveled),
          backgroundColor: ["#FF5733", "#33FF57", "#3357FF"],
        }],
      },
    });

    new Chart(document.getElementById("maintenanceDaysChart"), {
      type: "line",
      data: {
        labels: maintenanceData.map(truck => `Truck ${truck.truckId}`),
        datasets: [
          {
            label: "Days Since Last Maintenance",
            data: daysSinceLastMaintenance,
            fill: false,
            borderColor: "#28a745",
            yAxisID: "y1",
          },
          {
            label: "Fuel Consumption (L)",
            data: maintenanceData.map(truck => truck.fuelConsumption),
            fill: false,
            borderColor: "#FF5733",
            yAxisID: "y2",
          },
        ],
      },
      options: {
        scales: {
          y1: {
            type: "linear",
            position: "left",
            title: { display: true, text: "Days Since Last Maintenance" },
          },
          y2: {
            type: "linear",
            position: "right",
            title: { display: true, text: "Fuel Consumption (L)" },
            grid: { drawOnChartArea: false },
          },
        },
      },
    });

    // Initialize Leaflet map
    const map = L.map('map').setView([12.8797, 121.7740], 6); 
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    L.marker([12.8797, 121.7740]).addTo(map)
      .bindPopup('Philippines')
      .openPopup();

    // Update table on load
    updateTable();
  </script>
</body>
</html>
