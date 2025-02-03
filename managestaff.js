document.getElementById("addForm").addEventListener("submit", function(event) {
    event.preventDefault(); // Stop page reload

    let formData = new FormData(this);

    fetch("addStaff.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text()) // Get PHP response
    .then(data => {
        console.log("Response from server:", data); // Debugging
        alert(data); // Show message
        fetchStaff(); // Refresh table
    })
    .catch(error => console.error("Error:", error));
});
