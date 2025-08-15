<?php
include('../config/db.php');
session_start();
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
            <h1 class="h3 mb-0 text-gray-800">User Management</h1>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addUserModal">
                <i class="fas fa-plus"></i> Add New User
            </button>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Registered Users</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Date Created</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM users ORDER BY id DESC");
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['username']}</td>
                                <td>" . ucfirst($row['role']) . "</td>
                                <td>{$row['created_at']}</td>
                                <td>
                                    <button class='btn btn-warning btn-sm' data-toggle='modal' data-target='#editUserModal{$row['id']}'>
                                        <i class='fas fa-edit'></i> Edit
                                    </button>
                                    <a href='delete_user.php?id={$row['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this user?\")'>
                                        <i class='fas fa-trash'></i> Delete
                                    </a>
                                </td>
                            </tr>";

                            // Edit User Modal
                            echo "<div class='modal fade' id='editUserModal{$row['id']}' tabindex='-1' role='dialog'>
                                    <div class='modal-dialog' role='document'>
                                        <div class='modal-content'>
                                            <form action='update_user.php' method='POST'>
                                                <div class='modal-header'>
                                                    <h5 class='modal-title'>Edit User</h5>
                                                    <button type='button' class='close' data-dismiss='modal'>&times;</button>
                                                </div>
                                                <div class='modal-body'>
                                                    <input type='hidden' name='id' value='{$row['id']}'>
                                                    <div class='form-group'>
                                                        <label>Username</label>
                                                        <input type='text' name='username' class='form-control' value='{$row['username']}' required>
                                                    </div>
                                                    <div class='form-group'>
                                                        <label>Role</label>
                                                        <select name='role' class='form-control'>
                                                            <option value='admin' " . ($row['role'] == 'admin' ? 'selected' : '') . ">Admin</option>
                                                            <option value='staff' " . ($row['role'] == 'staff' ? 'selected' : '') . ">Staff</option>
                                                        </select>
                                                    </div>
                                                    <div class='form-group'>
                                                        <label>New Password (leave blank to keep current)</label>
                                                        <input type='password' name='password' class='form-control'>
                                                    </div>
                                                </div>
                                                <div class='modal-footer'>
                                                    <button type='submit' class='btn btn-primary'>Save Changes</button>
                                                    <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
                                                </div>
                                            </form>
                                        </div>
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
</div>

    <?php include('includes/footer.php'); ?>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="add_user.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" class="form-control">
                            <option value="admin">Admin</option>
                            <option value="staff" selected>Staff</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Add User</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>


</body>
</html>
