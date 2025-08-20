<?php
include('../config/db.php');

// Create uploads directory if it doesn't exist
$upload_dir = '../uploads/cars/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Function to handle image upload
function uploadCarImage($file) {
    global $upload_dir;
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception("Invalid file type. Only JPG, PNG, and GIF are allowed.");
    }
    
    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception("File too large. Maximum size is 5MB.");
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'car_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filename;
    } else {
        throw new Exception("Failed to upload image.");
    }
}

// Function to get safe image source
function getCarImageSrc($car_image) {
    if (!empty($car_image) && file_exists('../uploads/cars/' . $car_image)) {
        return '../uploads/cars/' . $car_image;
    }
    return '../assets/img/no-image.png';
}

// Handle adding a new car
if (isset($_POST['add_car'])) {
    try {
        $car_name = $_POST['car_name'];
        $brand = $_POST['brand'];
        $plate_number = $_POST['plate_number'];
        $description = $_POST['description'];
        $passenger_seater = $_POST['passenger_seater'];
        $transmission = $_POST['transmission'];
        $rate_6h = $_POST['rate_6h'];
        $rate_8h = $_POST['rate_8h'];
        $rate_12h = $_POST['rate_12h'];
        $rate_24h = $_POST['rate_24h'];
        $status = $_POST['status'];
        
        $image_filename = null;
        if (isset($_FILES['car_image']) && $_FILES['car_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $image_filename = uploadCarImage($_FILES['car_image']);
        }

        $stmt = $conn->prepare("INSERT INTO cars (car_name, brand, plate_number, description, passenger_seater, transmission, rate_6h, rate_8h, rate_12h, rate_24h, status, car_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssisddddis", $car_name, $brand, $plate_number, $description, $passenger_seater, $transmission, $rate_6h, $rate_8h, $rate_12h, $rate_24h, $status, $image_filename);
        $stmt->execute();
        echo "<script>
            Swal.fire({
                title: 'Success!',
                text: 'Car added successfully!',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = 'car_list.php';
                }
            });
        </script>";
    } catch (Exception $e) {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: '" . addslashes($e->getMessage()) . "',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
    }
}

// Handle editing an existing car
if (isset($_POST['edit_car'])) {
    try {
        $car_id = $_POST['car_id'];
        $car_name = $_POST['car_name'];
        $brand = $_POST['brand'];
        $plate_number = $_POST['plate_number'];
        $description = $_POST['description'];
        $passenger_seater = $_POST['passenger_seater'];
        $transmission = $_POST['transmission'];
        $rate_6h = $_POST['rate_6h'];
        $rate_8h = $_POST['rate_8h'];
        $rate_12h = $_POST['rate_12h'];
        $rate_24h = $_POST['rate_24h'];
        $status = $_POST['status'];
        
        // Get current car data for image handling
        $current_stmt = $conn->prepare("SELECT car_image FROM cars WHERE car_id = ?");
        $current_stmt->bind_param("i", $car_id);
        $current_stmt->execute();
        $current_result = $current_stmt->get_result();
        $current_car = $current_result->fetch_assoc();
        
        $image_filename = $current_car['car_image']; // Keep current image by default
        
        // Handle new image upload
        if (isset($_FILES['car_image']) && $_FILES['car_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Delete old image if it exists
            if ($current_car['car_image'] && file_exists($upload_dir . $current_car['car_image'])) {
                unlink($upload_dir . $current_car['car_image']);
            }
            $image_filename = uploadCarImage($_FILES['car_image']);
        }

        $stmt = $conn->prepare("UPDATE cars SET car_name=?, brand=?, plate_number=?, description=?, passenger_seater=?, transmission=?, rate_6h=?, rate_8h=?, rate_12h=?, rate_24h=?, status=?, car_image=? WHERE car_id=?");
        $stmt->bind_param("ssssisddddisi", $car_name, $brand, $plate_number, $description, $passenger_seater, $transmission, $rate_6h, $rate_8h, $rate_12h, $rate_24h, $status, $image_filename, $car_id);
        $stmt->execute();
        echo "<script>
            Swal.fire({
                title: 'Success!',
                text: 'Car updated successfully!',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = 'car_list.php';
                }
            });
        </script>";
    } catch (Exception $e) {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: '" . addslashes($e->getMessage()) . "',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('includes/header.php'); ?>
    <style>
        .car-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            margin-top: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .current-image {
            max-width: 150px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
    </style>
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
                            <th>Image</th>
                            <th>Car Name</th>
                            <th>Brand</th>
                            <th>Plate Number</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM cars ORDER BY car_id DESC");
                        while ($row = $result->fetch_assoc()) {
                            $image_src = $row['car_image'] ? '../uploads/cars/' . $row['car_image'] : '../assets/img/no-image.png';
                            $status_text = '';
                            switch($row['status']) {
                                case 3:
                                    $status_text = '<span class="badge badge-danger">Booked</span>';
                                    break;
                                case 2:
                                    $status_text = '<span class="badge badge-warning">Pending</span>';
                                    break;
                                case 1:
                                    $status_text = '<span class="badge badge-success">Available</span>';
                                    break;
                                case 0:
                                    $status_text = '<span class="badge badge-secondary">Unavailable</span>';
                                    break;
                            }
                            
                            echo "<tr>
                                    <td><img src='" . htmlspecialchars($image_src) . "' class='car-image' alt='Car Image' onerror=\"this.src='../assets/img/no-image.png'\"></td>
                                    <td>" . htmlspecialchars($row['car_name']) . "</td>
                                    <td>" . htmlspecialchars($row['brand']) . "</td>
                                    <td>" . htmlspecialchars($row['plate_number']) . "</td>
                                    <td>{$status_text}</td>
                                    <td>
                                        <button class='btn btn-info btn-sm' data-toggle='modal' data-target='#viewCarModal{$row['car_id']}'>
                                            <i class='fas fa-eye'></i>
                                        </button>
                                        <button class='btn btn-warning btn-sm' data-toggle='modal' data-target='#editCarModal{$row['car_id']}'>
                                            <i class='fas fa-edit'></i> 
                                        </button>
                                    </td>
                            </tr>";

                            // View Modal for each car
                            echo "
                            <div class='modal fade' id='viewCarModal{$row['car_id']}' tabindex='-1'>
                                <div class='modal-dialog modal-lg'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title'>Car Details</h5>
                                            <button type='button' class='close' data-dismiss='modal'>&times;</button>
                                        </div>
                                        <div class='modal-body'>
                                            <div class='row'>
                                                <div class='col-md-4 text-center'>
                                                    <img src='" . htmlspecialchars($image_src) . "' class='img-fluid rounded border' alt='Car Image' onerror=\"this.src='../assets/img/no-image.png'\">
                                                </div>
                                                <div class='col-md-8'>
                                                    <h4>" . htmlspecialchars($row['car_name']) . " <small class='text-muted'>(" . htmlspecialchars($row['brand']) . ")</small></h4>
                                                    <p><strong>Plate Number:</strong> " . htmlspecialchars($row['plate_number']) . "</p>
                                                    <p><strong>Description:</strong> " . nl2br(htmlspecialchars($row['description'])) . "</p>
                                                    <p><strong>Passenger Seater:</strong> " . htmlspecialchars($row['passenger_seater']) . "</p>
                                                    <p><strong>Transmission:</strong> " . htmlspecialchars($row['transmission']) . "</p>
                                                    <p><strong>Status:</strong> {$status_text}</p>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class='row'>
                                                <div class='col-md-6'>
                                                    <p><strong>6 Hours:</strong> ₱" . number_format($row['rate_6h'],2) . "</p>
                                                    <p><strong>8 Hours:</strong> ₱" . number_format($row['rate_8h'],2) . "</p>
                                                </div>
                                                <div class='col-md-6'>
                                                    <p><strong>12 Hours:</strong> ₱" . number_format($row['rate_12h'],2) . "</p>
                                                    <p><strong>24 Hours:</strong> ₱" . number_format($row['rate_24h'],2) . "</p>
                                                </div>
                                            </div>
                                            <p class='text-muted'><small>Created at: " . date("F d, Y h:i A", strtotime($row['created_at'])) . "</small></p>
                                        </div>
                                        <div class='modal-footer'>
                                            <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>";

                            // Edit Modal for each car
                            echo "
                            <div class='modal fade' id='editCarModal{$row['car_id']}' tabindex='-1'>
                                <div class='modal-dialog modal-lg'>
                                    <form method='POST' enctype='multipart/form-data'>
                                        <div class='modal-content'>
                                            <div class='modal-header'>
                                                <h5 class='modal-title'>Edit Car</h5>
                                                <button type='button' class='close' data-dismiss='modal'>&times;</button>
                                            </div>
                                            <div class='modal-body'>
                                                <input type='hidden' name='car_id' value='{$row['car_id']}'>
                                                
                                                <div class='row'>
                                                    <div class='col-md-8'>
                                                        <div class='form-group'>
                                                            <label>Car Name</label>
                                                            <input type='text' name='car_name' value='" . htmlspecialchars($row['car_name']) . "' class='form-control' required>
                                                        </div>
                                                        <div class='form-group'>
                                                            <label>Brand</label>
                                                            <input type='text' name='brand' value='" . htmlspecialchars($row['brand']) . "' class='form-control' required>
                                                        </div>
                                                        <div class='form-group'>
                                                            <label>Plate Number</label>
                                                            <input type='text' name='plate_number' value='" . htmlspecialchars($row['plate_number']) . "' class='form-control' required>
                                                        </div>
                                                    </div>
                                                    <div class='col-md-4'>
                                                        <div class='form-group'>
                                                            <label>Current Image</label><br>
                                                            <img src='" . htmlspecialchars($image_src) . "' class='current-image' alt='Current Car Image' onerror=\"this.src='../assets/img/no-image.png'\">
                                                        </div>
                                                        <div class='form-group'>
                                                            <label>Upload New Image (Optional)</label>
                                                            <input type='file' name='car_image' class='form-control-file' accept='image/*' onchange='previewEditImage(this, {$row['car_id']})'>
                                                            <small class='text-muted'>Max size: 5MB. Formats: JPG, PNG, GIF</small>
                                                            <img id='editImagePreview{$row['car_id']}' class='image-preview' style='display:none;'>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class='row'>
                                                    <div class='col-md-12'>
                                                        <div class='form-group'>
                                                            <label>Description</label>
                                                            <textarea name='description' class='form-control' rows='3'>" . htmlspecialchars($row['description']) . "</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class='row'>
                                                    <div class='col-md-6'>
                                                        <div class='form-group'>
                                                            <label>Passenger Seater</label>
                                                            <select name='passenger_seater' class='form-control' required>
                                                                <option value=''>Select Passenger Count</option>
                                                                <option value='2' " . ($row['passenger_seater'] == 2 ? 'selected' : '') . ">2 Seater</option>
                                                                <option value='4' " . ($row['passenger_seater'] == 4 ? 'selected' : '') . ">4 Seater</option>
                                                                <option value='5' " . ($row['passenger_seater'] == 5 ? 'selected' : '') . ">5 Seater</option>
                                                                <option value='7' " . ($row['passenger_seater'] == 7 ? 'selected' : '') . ">7 Seater</option>
                                                                <option value='8' " . ($row['passenger_seater'] == 8 ? 'selected' : '') . ">8 Seater</option>
                                                                <option value='9' " . ($row['passenger_seater'] == 9 ? 'selected' : '') . ">9 Seater</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class='col-md-6'>
                                                        <div class='form-group'>
                                                            <label>Transmission</label>
                                                            <select name='transmission' class='form-control' required>
                                                                <option value=''>Select Transmission</option>
                                                                <option value='Manual' " . ($row['transmission'] == 'Manual' ? 'selected' : '') . ">Manual</option>
                                                                <option value='Automatic' " . ($row['transmission'] == 'Automatic' ? 'selected' : '') . ">Automatic</option>
                                                                <option value='CVT' " . ($row['transmission'] == 'CVT' ? 'selected' : '') . ">CVT</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class='row'>
                                                    <div class='col-md-6'>
                                                        <div class='form-group'>
                                                            <label>Rate for 6 Hours (₱)</label>
                                                            <input type='number' step='0.01' name='rate_6h' value='{$row['rate_6h']}' class='form-control' required>
                                                        </div>
                                                        <div class='form-group'>
                                                            <label>Rate for 8 Hours (₱)</label>
                                                            <input type='number' step='0.01' name='rate_8h' value='{$row['rate_8h']}' class='form-control' required>
                                                        </div>
                                                    </div>
                                                    <div class='col-md-6'>
                                                        <div class='form-group'>
                                                            <label>Rate for 12 Hours (₱)</label>
                                                            <input type='number' step='0.01' name='rate_12h' value='{$row['rate_12h']}' class='form-control' required>
                                                        </div>
                                                        <div class='form-group'>
                                                            <label>Rate for 24 Hours (₱)</label>
                                                            <input type='number' step='0.01' name='rate_24h' value='{$row['rate_24h']}' class='form-control' required>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class='form-group'>
                                                    <label>Status</label>
                                                    <select name='status' class='form-control'>
                                                        <option value='3' " . ($row['status'] == 3 ? 'selected' : '') . ">Booked</option>
                                                        <option value='2' " . ($row['status'] == 2 ? 'selected' : '') . ">Pending</option>
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
        <div class="modal-dialog modal-lg">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Car</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Car Name</label>
                                    <input type="text" name="car_name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Brand</label>
                                    <input type="text" name="brand" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Plate Number</label>
                                    <input type="text" name="plate_number" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Car Image (Optional)</label>
                                    <input type="file" name="car_image" class="form-control-file" accept="image/*" onchange="previewImage(this)">
                                    <small class="text-muted">Max size: 5MB. Formats: JPG, PNG, GIF</small>
                                    <img id="imagePreview" class="image-preview" style="display:none;">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description" class="form-control" rows="3" placeholder="Enter car description..."></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Passenger Seater</label>
                                    <select name="passenger_seater" class="form-control" required>
                                        <option value="">Select Passenger Count</option>
                                        <option value="2">2 Seater</option>
                                        <option value="4">4 Seater</option>
                                        <option value="5">5 Seater</option>
                                        <option value="7">7 Seater</option>
                                        <option value="8">8 Seater</option>
                                        <option value="9">9 Seater</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Transmission</label>
                                    <select name="transmission" class="form-control" required>
                                        <option value="">Select Transmission</option>
                                        <option value="Manual">Manual</option>
                                        <option value="Automatic">Automatic</option>
                                        <option value="CVT">CVT</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Rate for 6 Hours (₱)</label>
                                    <input type="number" step="0.01" name="rate_6h" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Rate for 8 Hours (₱)</label>
                                    <input type="number" step="0.01" name="rate_8h" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Rate for 12 Hours (₱)</label>
                                    <input type="number" step="0.01" name="rate_12h" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Rate for 24 Hours (₱)</label>
                                    <input type="number" step="0.01" name="rate_24h" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="1">Available</option>
                                <option value="0">Unavailable</option>
                                <option value="2">Pending</option>
                                <option value="3">Booked</option>
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

<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const file = input.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
}

function previewEditImage(input, carId) {
    const preview = document.getElementById('editImagePreview' + carId);
    const file = input.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
}
</script>

<?php include('includes/footer.php'); ?>
</body>
</html>