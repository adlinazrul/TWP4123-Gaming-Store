document.getElementById("addForm").addEventListener("submit", function(event) {
    event.preventDefault();

    let formData = new FormData(this);

    fetch("addStaff.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log("Response from server:", data);
        alert(data);
        fetchStaff(); // Refresh table
    })
    .catch(error => console.error("Error:", error));
});
