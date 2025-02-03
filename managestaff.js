document.addEventListener("DOMContentLoaded", function () {
    fetchStaff();
});

function fetchStaff() {
    fetch("getStaff.php") // Replace with your actual API endpoint
        .then(response => response.json())
        .then(data => {
            let tableBody = document.getElementById("employeeTable");
            tableBody.innerHTML = ""; // Clear existing data
            
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
