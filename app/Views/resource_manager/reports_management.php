<?= $this->extend('templates/resource_manager_layout') ?>

<?= $this->section('content') ?>
<!-- Custom style for read-only fields -->
<style>
  .custom-readonly {
    background-color: #f2f2f2; /* Custom gray background */
    color: #555;             /* Optional: dark gray text */
    cursor: not-allowed;
  }
</style>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
<title>Reports Management</title>

<div class="container-fluid mt-4">
  <h1>Maintenance Reports</h1>

  <!-- Flash Messages -->
  <?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
  <?php endif; ?>
  <?php if(session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
  <?php endif; ?>

  <!-- Create New Maintenance Report Form -->
  <div class="card mb-4">
    <div class="card-header">Create Maintenance Report</div>
    <div class="card-body">
      <form action="<?= base_url('resource/reports/store') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- Truck ID Dropdown -->
        <div class="mb-3 row">
          <label for="truck_id" class="col-sm-3 col-form-label">Truck ID</label>
          <div class="col-sm-9">
            <select name="truck_id" id="truck_id" class="form-select" required>
              <option value="">Select Truck</option>
              <?php if(isset($trucks) && is_array($trucks)): ?>
                <?php foreach($trucks as $tKey => $tData): ?>
                  <option value="<?= esc($tKey) ?>">
                    <?= esc($tKey) ?> - <?= esc($tData['truck_model'] ?? 'Unknown') ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
        </div>

        <!-- Component Dropdown -->
        <div class="mb-3 row">
          <label for="component" class="col-sm-3 col-form-label">Component</label>
          <div class="col-sm-9">
            <select name="component" id="component" class="form-select" required>
              <option value="">Select Component</option>
              <?php if(isset($majorComponents) && is_array($majorComponents)): ?>
                <?php foreach($majorComponents as $cKey => $cLabel): ?>
                  <option value="<?= esc($cKey) ?>"><?= esc($cLabel) ?></option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
        </div>

        <!-- Inspection Date -->
        <div class="mb-3 row">
          <label for="inspection_date" class="col-sm-3 col-form-label">Inspection Date</label>
          <div class="col-sm-9">
            <input type="date" class="form-control" name="inspection_date" id="inspection_date" required>
          </div>
        </div>

        <!-- Action Needed -->
        <div class="mb-3 row">
          <label for="action_needed" class="col-sm-3 col-form-label">Action Needed</label>
          <div class="col-sm-9">
            <textarea class="form-control" name="action_needed" id="action_needed" rows="3" required></textarea>
          </div>
        </div>

        <!-- Service Type -->
        <div class="mb-3 row">
          <label for="service_type" class="col-sm-3 col-form-label">Service Type</label>
          <div class="col-sm-9">
            <select name="service_type" id="service_type" class="form-select" required>
              <option value="">Choose...</option>
              <option value="preventive">Preventive</option>
              <option value="corrective">Corrective</option>
              <option value="replacement">Replacement</option>
              <option value="defective">Defective</option>
            </select>
          </div>
        </div>

        <!-- Technician Name -->
        <div class="mb-3 row">
          <label for="technician_name" class="col-sm-3 col-form-label">Technician Name</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" name="technician_name" id="technician_name" required>
          </div>
        </div>

        <!-- Mileage After Inspection -->
        <div class="mb-3 row">
          <label for="mileage_after_inspection" class="col-sm-3 col-form-label">Mileage After Inspection (km)</label>
          <div class="col-sm-9">
            <input type="number" class="form-control" name="mileage_after_inspection" id="mileage_after_inspection" required>
          </div>
        </div>

        <!-- Estimate Next Service (Mileage) -->
        <div class="mb-3 row">
          <label for="estimate_next_service_mileage" class="col-sm-3 col-form-label">Estimate Next Service (Mileage)</label>
          <div class="col-sm-9">
            <input type="number" class="form-control custom-readonly" name="estimate_next_service_mileage" id="estimate_next_service_mileage" readonly>
          </div>
        </div>

        <!-- Expected Next Service (Time) -->
        <div class="mb-3 row">
          <label for="expected_next_service_time" class="col-sm-3 col-form-label">Expected Next Service (Time)</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" name="expected_next_service_time" id="expected_next_service_time" placeholder="e.g. 3 months" required>
          </div>
        </div>

        <!-- Automatic Read-only Fields -->

        <!-- Last Service Date -->
        <div class="row mb-3">
          <label class="col-sm-3 col-form-label">Last Service Date (Auto)</label>
          <div class="col-sm-9">
            <input type="text" class="form-control custom-readonly" id="last_service_date" name="last_service_date" readonly>
          </div>
        </div>

        <!-- Last Service Mileage -->
        <div class="row mb-3">
          <label class="col-sm-3 col-form-label">Last Service Mileage (Auto)</label>
          <div class="col-sm-9">
            <input type="text" class="form-control custom-readonly" id="last_service_mileage" name="last_service_mileage" readonly>
          </div>
        </div>

        <!-- Current Mileage -->
        <div class="row mb-3">
          <label class="col-sm-3 col-form-label">Current Mileage (Auto)</label>
          <div class="col-sm-9">
            <input type="text" class="form-control custom-readonly" id="current_mileage" name="current_mileage" readonly>
          </div>
        </div>

        <!-- Truck Age / MFG Date -->
        <div class="row mb-3">
          <label class="col-sm-3 col-form-label">Truck Age / MFG Date (Auto)</label>
          <div class="col-sm-9">
            <input type="text" class="form-control custom-readonly" id="manufacturing_date" name="manufacturing_date" readonly>
          </div>
        </div>

        <!-- Attach Image -->
        <div class="mb-3 row">
          <label for="report_image" class="col-sm-3 col-form-label">Attach Image</label>
          <div class="col-sm-9">
            <input type="file" class="form-control" id="report_image" name="report_image">
          </div>
        </div>

        <div class="row">
          <div class="col-sm-9 offset-sm-3">
            <button type="submit" class="btn btn-primary">Create Maintenance Report</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- List of Existing Maintenance Reports -->
  <?php if(isset($reports) && is_array($reports) && !empty($reports)): ?>
    <div class="card">
      <div class="card-header">Existing Maintenance Reports</div>
      <div class="card-body">
        <table class="table table-bordered table-striped">
          <thead class="table-light">
            <tr>
              <th>Report Number</th>
              <th>Report Type</th>
              <th>Date</th>
              <th>Truck ID</th>
              <th>Component</th>
              <th>Inspection Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($reports as $rKey => $rData): ?>
              <tr>
                <td><?= esc($rData['report_number'] ?? $rKey) ?></td>
                <td><?= esc($rData['report_type'] ?? '') ?></td>
                <td><?= esc($rData['date'] ?? '') ?></td>
                <td><?= esc($rData['truck_id'] ?? '') ?></td>
                <td><?= esc($rData['component'] ?? '') ?></td>
                <td><?= esc($rData['inspection_date'] ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php else: ?>
    <p>No Maintenance Reports found.</p>
  <?php endif; ?>
</div>

<script>
  // Pass trucks data from the controller to a JS variable.
  let trucksData = <?= json_encode($trucks) ?>;

  // When a truck is selected, auto-populate the read-only fields.
  const truckSelect = document.getElementById('truck_id');
  truckSelect.addEventListener('change', function () {
    const selectedTruckId = this.value;
    if (selectedTruckId && trucksData[selectedTruckId]) {
      const truck = trucksData[selectedTruckId];
      document.getElementById('current_mileage').value = truck.current_mileage || '';
      document.getElementById('last_service_date').value = truck.last_inspection_date || '';
      document.getElementById('last_service_mileage').value = truck.last_inspection_mileage || '';
      document.getElementById('manufacturing_date').value = truck.manufacturing_date || '';
    } else {
      document.getElementById('current_mileage').value = '';
      document.getElementById('last_service_date').value = '';
      document.getElementById('last_service_mileage').value = '';
      document.getElementById('manufacturing_date').value = '';
    }
  });

  const componentIntervals = {
    'engine_system':     { new: 5000, old: 4000, time: '6 months' },
    'transmission_drivetrain':      { new: 20000, old: 15000, time: '24 months' },
    'brake_system':      { new: 10000, old: 4000, time: 'N/A' },
    'suspension_chassis':        { new: 5000, old: 4000, time: 'N/A' },
    'fuel_cooling_system':      { new: 20000, old: 15000, time: 'N/A' },
    'steering_system':          { new: 20000, old: 10000, time: 'N/A' },
    'electrical_auxiliary_system':    { new: 10000, old: 7000, time: 'N/A' }
  };

  // Utility: calculate truck age in years
  function calculateTruckAge(mfgDateStr) {
    if (!mfgDateStr) return 0;
    const today = new Date();
    const mfgDate = new Date(mfgDateStr);
    return today.getFullYear() - mfgDate.getFullYear();
  }

  function updateEstimatedServiceFields() {
    const truckId = truckSelect.value;
    const component = document.getElementById('component').value;
    const mileageAfter = parseInt(document.getElementById('mileage_after_inspection').value || 0);
    
    if (!truckId || !component || !mileageAfter) return;

    const truck = trucksData[truckId];
    const truckAge = calculateTruckAge(truck.manufacturing_date);
    const currentMileage = parseInt(truck.current_mileage || 0);
    const isOld = truckAge > 5 || currentMileage > 100000;

    const intervalInfo = componentIntervals[component];
    if (!intervalInfo) return;

    const interval = isOld ? intervalInfo.old : intervalInfo.new;
    const timeDue = intervalInfo.time !== 'N/A' ? intervalInfo.time : 'N/A';

    const nextMileage = mileageAfter + interval;

    document.getElementById('estimate_next_service_mileage').value = nextMileage;
    document.getElementById('expected_next_service_time').value = timeDue;
  }

  // Trigger on any relevant input
  document.getElementById('component').addEventListener('change', updateEstimatedServiceFields);
  document.getElementById('mileage_after_inspection').addEventListener('input', updateEstimatedServiceFields);
  truckSelect.addEventListener('change', function () {
    const selectedTruckId = this.value;
    if (selectedTruckId && trucksData[selectedTruckId]) {
      const truck = trucksData[selectedTruckId];
      document.getElementById('current_mileage').value = truck.current_mileage || '';
      document.getElementById('last_service_date').value = truck.last_inspection_date || '';
      document.getElementById('last_service_mileage').value = truck.last_inspection_mileage || '';
      document.getElementById('manufacturing_date').value = truck.manufacturing_date || '';
    } else {
      document.getElementById('current_mileage').value = '';
      document.getElementById('last_service_date').value = '';
      document.getElementById('last_service_mileage').value = '';
      document.getElementById('manufacturing_date').value = '';
    }

    updateEstimatedServiceFields(); // Re-evaluate after truck change
  });

</script>

<?= $this->endSection() ?>
