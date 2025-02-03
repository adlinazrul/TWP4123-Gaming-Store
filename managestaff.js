document.getElementById("addForm").addEventListener("submit", function (event) {
    event.preventDefault();

    let formData = new FormData();
    formData.append("emp_id", document.getElementById("emp_id").value);
    formData.append("username", document.getElementById("name").value);
    formData.append("email", document.getElementById("email").value);  // ✅ Ensure email is included
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
        if (data.success) {
            alert("Staff added successfully!");
            location.reload();
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => console.error("Error:", error));
});
