<!DOCTYPE html> 
<html lang="en"> 
 
<head> 
    <?php include('includes/header.php'); ?> 
</head> 
 
<body id="page-top"> 
 
    <!-- Page Wrapper --> 
    <div id="wrapper"> 
         
        <?php include('includes/sidebar.php'); ?> 
        <?php include('includes/topbar.php'); ?> 
 
        <!-- Begin Page Content --> 
        <div class="container-fluid"> 
 
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Booking List</h1>
                <a href="add_booking.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Add New Booking
                </a>
            </div>

            <!-- Booking List Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">All Bookings</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Customer Name</th>
                                    <th>Car Model</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Sample Data - Replace with PHP loop to fetch from database -->
                                <tr>
                                    <td>#BK001</td>
                                    <td>John Smith</td>
                                    <td>Toyota Camry</td>
                                    <td>2024-01-15</td>
                                    <td>2024-01-20</td>
                                    <td>$250.00</td>
                                    <td><span class="badge badge-success">Confirmed</span></td>
                                    <td>
    <a href="view_booking.php?id=1" class="btn btn-info btn-sm" title="View">
        <i class="fas fa-eye"></i> View
    </a>
    <a href="accept_booking.php?id=1" class="btn btn-success btn-sm" 
       onclick="return confirm('Are you sure you want to accept this booking?')" title="Accept">
        <i class="fas fa-check"></i> Accept
    </a>
    <a href="decline_booking.php?id=1" class="btn btn-danger btn-sm" 
       onclick="return confirm('Are you sure you want to decline this booking?')" title="Decline">
        <i class="fas fa-times"></i> Decline
    </a>
</td>

                                </tr>
                                <tr>
                                    <td>#BK002</td>
                                    <td>Sarah Johnson</td>
                                    <td>Honda Civic</td>
                                    <td>2024-01-18</td>
                                    <td>2024-01-22</td>
                                    <td>$200.00</td>
                                    <td><span class="badge badge-warning">Pending</span></td>
                                    <td>
    <a href="view_booking.php?id=2" class="btn btn-info btn-sm" title="View">
        <i class="fas fa-eye"></i> View
    </a>
    <a href="accept_booking.php?id=2" class="btn btn-success btn-sm" 
       onclick="return confirm('Are you sure you want to accept this booking?')" title="Accept">
        <i class="fas fa-check"></i> Accept
    </a>
    <a href="decline_booking.php?id=2" class="btn btn-danger btn-sm" 
       onclick="return confirm('Are you sure you want to decline this booking?')" title="Decline">
        <i class="fas fa-times"></i> Decline
    </a>
</td>

                                </tr>
                                <tr>
                                    <td>#BK003</td>
                                    <td>Mike Davis</td>
                                    <td>Ford Mustang</td>
                                    <td>2024-01-20</td>
                                    <td>2024-01-25</td>
                                    <td>$350.00</td>
                                    <td><span class="badge badge-secondary">Completed</span></td>
                                    <td>
    <a href="view_booking.php?id=3" class="btn btn-info btn-sm" title="View">
        <i class="fas fa-eye"></i> View
    </a>
    <a href="accept_booking.php?id=3" class="btn btn-success btn-sm" 
       onclick="return confirm('Are you sure you want to accept this booking?')" title="Accept">
        <i class="fas fa-check"></i> Accept
    </a>
    <a href="decline_booking.php?id=3" class="btn btn-danger btn-sm" 
       onclick="return confirm('Are you sure you want to decline this booking?')" title="Decline">
        <i class="fas fa-times"></i> Decline
    </a>
</td>

                                </tr>
                                <tr>
                                    <td>#BK004</td>
                                    <td>Emily Wilson</td>
                                    <td>BMW X5</td>
                                    <td>2024-01-22</td>
                                    <td>2024-01-28</td>
                                    <td>$450.00</td>
                                    <td><span class="badge badge-danger">Cancelled</span></td>
                                    <td>
    <a href="view_booking.php?id=4" class="btn btn-info btn-sm" title="View">
        <i class="fas fa-eye"></i> View
    </a>
    <a href="accept_booking.php?id=4" class="btn btn-success btn-sm" 
       onclick="return confirm('Are you sure you want to accept this booking?')" title="Accept">
        <i class="fas fa-check"></i> Accept
    </a>
    <a href="decline_booking.php?id=4" class="btn btn-danger btn-sm" 
       onclick="return confirm('Are you sure you want to decline this booking?')" title="Decline">
        <i class="fas fa-times"></i> Decline
    </a>
</td>

                                </tr>
                                <!-- Add more sample data or replace with PHP database loop -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

</div> <!-- End of Main Content -->

        </div> 
        <?php include('includes/footer.php'); ?> 
    </div> 
 
</body> 
</html>