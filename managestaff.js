let employees = [];

document.getElementById('addForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const emp_id = document.getElementById('emp_id').value;
    const name = document.getElementById('name').value;
    const position = document.getElementById('position').value;
    const salary = document.getElementById('salary').value;

    employees.push({ emp_id, name, position, salary });
    displayEmployees();

    e.target.reset();
});

function displayEmployees() {
    const employeeTable = document.getElementById('employeeTable');
    employeeTable.innerHTML = '';

    employees.forEach((employee, index) => {
        const row = document.createElement('tr');

        row.innerHTML = `
            <td>${employee.emp_id}</td>
            <td>${employee.name}</td>
            <td>${employee.position}</td>
            <td>${employee.salary}</td>
            <td class="actions">
                <button onclick="editEmployee(${index})">Edit</button>
                <button onclick="deleteEmployee(${index})">Delete</button>
            </td>
        `;

        employeeTable.appendChild(row);
    });
}

function editEmployee(index) {
    const employee = employees[index];

    const newName = prompt('Enter new name:', employee.name);
    const newPosition = prompt('Enter new position:', employee.position);
    const newSalary = prompt('Enter new salary:', employee.salary);

    if (newName) employee.name = newName;
    if (newPosition) employee.position = newPosition;
    if (newSalary) employee.salary = newSalary;

    displayEmployees();
}

function deleteEmployee(index) {
    if (confirm('Are you sure you want to delete this employee?')) {
        employees.splice(index, 1);
        displayEmployees();
    }
}