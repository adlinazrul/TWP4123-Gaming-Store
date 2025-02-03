document.getElementById("addForm").addEventListener("submit", function (event) {
    event.preventDefault();

    let formData = new FormData();
    formData.append("emp_id", document.getElementById("emp_id").value);
    formData.append("username", document.getElementById("username").value);
    formData.append("email", document.getElementById("email").value);
    formData.append("position", document.getElementById("position").value);
    formData.append("salary", document.getElementById("salary").value);
    formData.append("password", document.getElementById("password").value);
    formData.append("image", document.getElementById("image").files[0]);

    fetch("addStaff.php", {
        method: "POST",
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        console.log(data); // Debugging: Check server response
        if (data.success) {
            alert("Staff added successfully!");
            fetchEmployees(); // 🔥 Refresh staff list after adding
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => console.error("Fetch Error:", error));
});
