document.getElementById('addForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the form from submitting normally

    // Get form values
    const custId = document.getElementById('cust_id').value;
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;
    const address = document.getElementById('address').value;

    // Create a new row in the customer table
    const table = document.getElementById('customerTable');
    const newRow = table.insertRow();

    newRow.innerHTML = `
        <td>${custId}</td>
        <td><a href="#" class="customer-name" onclick="showDetails('${custId}', '${name}', '${email}', '${phone}', '${address}')">${name}</a></td>
        <td>${email}</td>
        <td>${phone}</td>
        <td>${address}</td>
    `;

    // Clear the form
    document.getElementById('addForm').reset();
});

// Function to show customer details
function showDetails(custId, name, email, phone, address) {
    const detailsDiv = document.getElementById('details');
    detailsDiv.innerHTML = `
        <p><strong>Customer ID:</strong> ${custId}</p>
        <p><strong>Name:</strong> ${name}</p>
        <p><strong>Email:</strong> ${email}</p>
        <p><strong>Phone Number:</strong> ${phone}</p>
        <p><strong>Address:</strong> ${address}</p>
    `;
    document.getElementById('customer-details').style.display = 'block'; // Show details section
}

// Function to hide customer details
function hideDetails() {
    document.getElementById('customer-details').style.display = 'none'; // Hide details section
}