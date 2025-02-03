document.addEventListener("DOMContentLoaded", function () {
    fetchStaff(); // Load staff list on page load
});

document.getElementById("addForm").addEventListener("submit", function (event) {
    event.preventDefault(); // Prevent page refresh

    let formData = new FormData();
    formData.append("emp_id", document.getElementById("emp_id").value);
    formData.append("name", document.getElementById("name").value);
    formData.append("position", document.getElementById("position").value);
    formData.append("salary", document.getElementById("salary").value);
    formData.append("password", document.getElementById("password").value);
    formData.append("image", document.getElementById("image").files[0]); // File upload

    fetch("addadmin.php", {
        method: "POST",
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            alert(data.message);
            document.getElementById("addForm").reset(); // Clear the form
            fetchStaff(); // Refresh staff list
        } else {
            alert(data.error);
        }
    })
    .catch(error => console.error("Error:", error));
});

function fetchStaff() {
    fetch("getStaff.php")
        .then(response => response.json())
        .then(data => {
            let tableBody = document.getElementById("employeeTable");
            tableBody.innerHTML = ""; // Clear previous data
            
            data.forEach(staff => {
                let row = `<tr>
                    <td>${staff.id}</td>
                    <td>${staff.name}</td>
                    <td>${staff.position}</td>
                    <td>RM ${staff.salary}</td>
                    <td><img src="${staff.image}" alt="${staff.name}" width="50"></td>
                    <td>
                        <button onclick="editStaff(${staff.id})">Edit</button>
                        <button onclick="deleteStaff(${staff.id})">Delete</button>
                    </td>
                </tr>`;
                tableBody.innerHTML += row;
            });
        })
        .catch(error => console.error("Error fetching staff data:", error));
}
