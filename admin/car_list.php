<?php
include('../config/db.php');

// Handle adding a new car
if (isset($_POST['add_car'])) {
    $car_name = $_POST['car_name'];
    $brand = $_POST['brand'];
    $plate_number = $_POST['plate_number'];
    $rate_6h = $_POST['rate_6h'];
    $rate_8h = $_POST['rate_8h'];
    $rate_12h = $_POST['rate_12h'];
    $rate_24h = $_POST['rate_24h'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO cars (car_name, brand, plate_number, rate_6h, rate_8h, rate_12h, rate_24h, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssddddi", $car_name, $brand, $plate_number, $rate_6h, $rate_8h, $rate_12h, $rate_24h, $status);
    $stmt->execute();
    echo "<script>alert('Car added successfully!'); window.location='car_list.php';</script>";
}

// Handle editing an existing car
if (isset($_POST['edit_car'])) {
    $car_id = $_POST['car_id'];
    $car_name = $_POST['car_name'];
    $brand = $_POST['brand'];
    $plate_number = $_POST['plate_number'];
    $rate_6h = $_POST['rate_6h'];
    $rate_8h = $_POST['rate_8h'];
    $rate_12h = $_POST['rate_12h'];
    $rate_24h = $_POST['rate_24h'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE cars SET car_name=?, brand=?, plate_number=?, rate_6h=?, rate_8h=?, rate_12h=?, rate_24h=?, status=? WHERE car_id=?");
    $stmt->bind_param("sssddddii", $car_name, $brand, $plate_number, $rate_6h, $rate_8h, $rate_12h, $rate_24h, $status, $car_id);
    $stmt->execute();
    echo "<script>alert('Car updated successfully!'); window.location='car_list.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('includes/header.php'); ?>
</head>

<body id="page-top">

<div id="wrapper">
    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/topbar.php'); ?>

    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Car List</h1>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addCarModal">
                <i class="fas fa-plus"></i> Add New Car
            </button>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Available Cars</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th>Car ID</th>
                            <th>Car Name</th>
                            <th>Brand</th>
                            <th>Plate Number</th>
                            <th>Rate 6h</th>
                            <th>Rate 8h</th>
                            <th>Rate 12h</th>
                            <th>Rate 24h</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM cars ORDER BY car_id DESC");
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>{$row['car_id']}</td>
                                <td>{$row['car_name']}</td>
                                <td>{$row['brand']}</td>
                                <td>{$row['plate_number']}</td>
                                <td>₱" . number_format($row['rate_6h'], 2) . "</td>
                                <td>₱" . number_format($row['rate_8h'], 2) . "</td>
                                <td>₱" . number_format($row['rate_12h'], 2) . "</td>
                                <td>₱" . number_format($row['rate_24h'], 2) . "</td>
                                <td>" . ($row['status'] == 1 ? 'Available' : 'Unavailable') . "</td>
                                <td>
                                    <button class='btn btn-warning btn-sm' data-toggle='modal' data-target='#editCarModal{$row['car_id']}'>
                                        <i class='fas fa-edit'></i> Edit
                                    </button>
                                    <a href='delete_car.php?id={$row['car_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure?\")'>
                                        <i class='fas fa-trash'></i> Delete
                                    </a>
                                </td>
                            </tr>";

                            // Edit Modal for each car
                            echo "
                            <div class='modal fade' id='editCarModal{$row['car_id']}' tabindex='-1'>
                                <div class='modal-dialog'>
                                    <form method='POST'>
                                        <div class='modal-content'>
                                            <div class='modal-header'>
                                                <h5 class='modal-title'>Edit Car</h5>
                                                <button type='button' class='close' data-dismiss='modal'>&times;</button>
                                            </div>
                                            <div class='modal-body'>
                                                <input type='hidden' name='car_id' value='{$row['car_id']}'>
                                                <div class='form-group'>
                                                    <label>Car Name</label>
                                                    <input type='text' name='car_name' value='{$row['car_name']}' class='form-control' required>
                                                </div>
                                                <div class='form-group'>
                                                    <label>Brand</label>
                                                    <input type='text' name='brand' value='{$row['brand']}' class='form-control' required>
                                                </div>
                                                <div class='form-group'>
                                                    <label>Plate Number</label>
                                                    <input type='text' name='plate_number' value='{$row['plate_number']}' class='form-control' required>
                                                </div>
                                                <div class='form-group'>
                                                    <label>Rate for 6 Hours (₱)</label>
                                                    <input type='number' step='0.01' name='rate_6h' value='{$row['rate_6h']}' class='form-control' required>
                                                </div>
                                                <div class='form-group'>
                                                    <label>Rate for 8 Hours (₱)</label>
                                                    <input type='number' step='0.01' name='rate_8h' value='{$row['rate_8h']}' class='form-control' required>
                                                </div>
                                                <div class='form-group'>
                                                    <label>Rate for 12 Hours (₱)</label>
                                                    <input type='number' step='0.01' name='rate_12h' value='{$row['rate_12h']}' class='form-control' required>
                                                </div>
                                                <div class='form-group'>
                                                    <label>Rate for 24 Hours (₱)</label>
                                                    <input type='number' step='0.01' name='rate_24h' value='{$row['rate_24h']}' class='form-control' required>
                                                </div>
                                                <div class='form-group'>
                                                    <label>Status</label>
                                                    <select name='status' class='form-control'>
                                                        <option value='1' " . ($row['status'] == 1 ? 'selected' : '') . ">Available</option>
                                                        <option value='0' " . ($row['status'] == 0 ? 'selected' : '') . ">Unavailable</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class='modal-footer'>
                                                <button type='submit' name='edit_car' class='btn btn-primary'>Update Car</button>
                                                <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Car Modal -->
    <div class="modal fade" id="addCarModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Car</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Car Name</label><input type="text" name="car_name" class="form-control" required></div>
                        <div class="form-group"><label>Brand</label><input type="text" name="brand" class="form-control" required></div>
                        <div class="form-group"><label>Plate Number</label><input type="text" name="plate_number" class="form-control" required></div>
                        <div class="form-group"><label>Rate for 6 Hours (₱)</label><input type="number" step="0.01" name="rate_6h" class="form-control" required></div>
                        <div class="form-group"><label>Rate for 8 Hours (₱)</label><input type="number" step="0.01" name="rate_8h" class="form-control" required></div>
                        <div class="form-group"><label>Rate for 12 Hours (₱)</label><input type="number" step="0.01" name="rate_12h" class="form-control" required></div>
                        <div class="form-group"><label>Rate for 24 Hours (₱)</label><input type="number" step="0.01" name="rate_24h" class="form-control" required></div>
                        <div class="form-group"><label>Status</label>
                            <select name="status" class="form-control">
                                <option value="1">Available</option>
                                <option value="0">Unavailable</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add_car" class="btn btn-primary">Save Car</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
                    </div>
    <?php include('includes/footer.php'); ?>
</div>
</body>
</html>
