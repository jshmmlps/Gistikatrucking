$(document).ready(function () {
    console.log('Profile Page Loaded');
    // Add any interactive JavaScript functionality here.
});

// Pie Chart For Dashbaord
const pieCtx = document.getElementById('pieChart').getContext('2d');
new Chart(pieCtx, {
    type: 'pie',
    data: {
        labels: ['Good Condition', 'Requires Maintenance', 'Critical Condition'],
        datasets: [{
            data: [55.3, 23.1, 21.6],
            backgroundColor: ['#4caf50', '#ffc107', '#f44336']
        }]
    }
});

// Bar Chart
const barCtx = document.getElementById('barChart').getContext('2d');
new Chart(barCtx, {
    type: 'bar',
    data: {
        labels: ['NBD 1234', 'ABM 9876', 'TQR 4567', 'GXL 3210', 'JPA 8542', 'LYN 5087', 'KWD 7485'],
        datasets: [{
            label: 'Days Until Maintenance',
            data: [3, 7, 2, 10, 4, 5, 8],
            backgroundColor: '#007bff'
        }]
    }
});

// For Driver List
function viewDetails(employeeId) {
    fetch(`/drivercontroller/getDetails/${employeeId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('name').textContent = `${data.first_name} ${data.last_name}`;
            document.getElementById('contact').textContent = data.contact_number;
            document.getElementById('position').textContent = data.position;
            document.getElementById('employee_id').textContent = data.employee_id;
            document.getElementById('address').textContent = data.home_address;
        })
        .catch(error => console.error('Error:', error));
}

//User Accounts
function viewUser(userId) {
    fetch(`/usercontroller/getUserDetails/${userId}`)
        .then(response => response.json())
        .then(user => {
            document.getElementById('user-name').textContent = user.first_name + " " + user.last_name;
            document.getElementById('user-email').textContent = user.email;
            document.getElementById('user-contact').textContent = user.contact_number;
            document.getElementById('user-address').textContent = user.address;
            document.getElementById('user-position').textContent = user.position;
            document.getElementById('user-username').textContent = user.username;
        })
        .catch(error => console.error('Error fetching user:', error));
}

function showTruckDetails(truck) {
    document.getElementById("detail-name").innerText = truck.name || "N/A";
    document.getElementById("detail-plate").innerText = truck.plate_number || "N/A";
    document.getElementById("detail-engine").innerText = truck.engine_number || "N/A";
    document.getElementById("detail-chassis").innerText = truck.chassis_number || "N/A";
    document.getElementById("detail-color").innerText = truck.color || "N/A";
    document.getElementById("detail-cert").innerText = truck.certificate_registration || "N/A";
    document.getElementById("detail-insurance").innerText = truck.insurance_details || "N/A";
    document.getElementById("detail-license-expiry").innerText = truck.license_plate_expiry || "N/A";
    document.getElementById("detail-registration-expiry").innerText = truck.registration_expiry || "N/A";
    document.getElementById("detail-type").innerText = truck.type || "N/A";
    document.getElementById("detail-fuel").innerText = truck.fuel_type || "N/A";
    document.getElementById("detail-length").innerText = truck.truck_length || "N/A";
    document.getElementById("detail-load").innerText = truck.load_capacity || "N/A";
    document.getElementById("detail-technician").innerText = truck.maintenance_technician || "N/A";
}

// FOR MODAL POPUP

document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".view-btn").forEach(button => {
        button.addEventListener("click", function() {
            let itemId = this.getAttribute("data-id");
            let module = this.getAttribute("data-module");
            let modalTitle = document.getElementById("globalModalLabel");
            let modalContent = document.getElementById("modalContent");

            // Set modal title based on module
            switch(module) {
                case "client":
                    modalTitle.innerText = "Client Details";
                    break;
                case "driver":
                    modalTitle.innerText = "Driver & Conductor Details";
                    break;
                case "booking":
                    modalTitle.innerText = "Booking Details";
                    break;
                default:
                    modalTitle.innerText = "Details";
            }

            // Fetch details dynamically
            fetch(`/fetch-details/${module}/${itemId}`)
                .then(response => response.json())
                .then(data => {
                    modalContent.innerHTML = `
                        <p><strong>Name:</strong> ${data.name || 'N/A'}</p>
                        <p><strong>Email:</strong> ${data.email || 'N/A'}</p>
                        <p><strong>Address:</strong> ${data.address || 'N/A'}</p>
                        ${module === 'driver' ? `<p><strong>Position:</strong> ${data.position || 'N/A'}</p>` : ''}
                        ${module === 'booking' ? `<p><strong>Status:</strong> ${data.status || 'N/A'}</p>` : ''}
                        <p><strong>Notes:</strong> ${data.notes || 'N/A'}</p>
                    `;
                })
                .catch(error => {
                    modalContent.innerHTML = `<p>Error fetching details.</p>`;
                    console.error("Error:", error);
                });
        });
    });
});
