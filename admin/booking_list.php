<!DOCTYPE html> 
<html lang="en"> 
 
<head> 
    <?php 
    include('includes/header.php'); 
    include('../config/db.php'); 
    include('../config/mail.php'); // ✅ include PHPMailer function
    ?> 
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.css">
    <!-- Bootstrap CSS for Modal -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
</head> 
 
<body id="page-top"> 
 
    <!-- Page Wrapper --> 
    <div id="wrapper"> 
         
        <?php include('includes/sidebar.php'); ?> 
        <?php include('includes/topbar.php'); ?> 
 
        <!-- Begin Page Content --> 
        <div class="container-fluid"> 

            <?php
            // Handle Accept/Decline/Complete/Extend actions
            if (isset($_GET['action']) && isset($_GET['id'])) {
                $booking_id = intval($_GET['id']);
                $action = $_GET['action'];
                
                // Get current user ID (assuming you have session management)
                $approved_by = $_SESSION['user_id'] ?? 1; // Replace with session user ID

                // Helper: fetch customer info
                function getBookingInfo($conn, $booking_id) {
                    $stmt = $conn->prepare("SELECT customer_email, customer_name, start_date, end_date, total_cost FROM bookings WHERE booking_id = ?");
                    $stmt->bind_param("i", $booking_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    return $result->fetch_assoc();
                }
                
                if ($action == 'accept') {
                    $sql = "UPDATE bookings SET 
                            status = 'approved', 
                            approved_by = ?, 
                            approved_at = NOW(), 
                            updated_at = NOW() 
                            WHERE booking_id = ?";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ii", $approved_by, $booking_id);
                    
                    if ($stmt->execute()) {
                        $info = getBookingInfo($conn, $booking_id);
                        if ($info) {
                            $to = $info['customer_email'];
                            $name = $info['customer_name'];
                            $start = date("M d, Y", strtotime($info['start_date']));
                            $end = date("M d, Y", strtotime($info['end_date']));

                            $subject = "Booking Approved - Car Rental System";
                            $body = "
                                <h2>Dear $name,</h2>
                                <p>Your booking has been <b>approved</b> 🎉</p>
                                <p><b>Booking Details:</b><br>
                                   Start Date: $start <br>
                                   End Date: $end
                                </p>
                                <p>Thank you for choosing Car Rental System.</p>
                            ";
                            $mailResult = sendMail($to, $subject, $body);
                        }
                        $success_message = 'Booking approved successfully! ' . ($mailResult === true ? 'Email sent ✅' : 'Email failed ❌: '.$mailResult);
                    }
                    $stmt->close();
                } 
                elseif ($action == 'decline') {
                    $sql = "UPDATE bookings SET 
                            status = 'rejected', 
                            approved_by = ?, 
                            approved_at = NOW(), 
                            updated_at = NOW() 
                            WHERE booking_id = ?";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ii", $approved_by, $booking_id);
                    
                    if ($stmt->execute()) {
                        $info = getBookingInfo($conn, $booking_id);
                        if ($info) {
                            $to = $info['customer_email'];
                            $name = $info['customer_name'];

                            $subject = "Booking Declined - Car Rental System";
                            $body = "
                                <h2>Dear $name,</h2>
                                <p>We regret to inform you that your booking has been <b>declined</b>.</p>
                                <p>Please contact support if you have questions.</p>
                            ";
                            $mailResult = sendMail($to, $subject, $body);
                        }
                        $warning_message = 'Booking declined! ' . ($mailResult === true ? 'Email sent ✅' : 'Email failed ❌: '.$mailResult);
                    }
                    $stmt->close();
                }
                elseif ($action == 'complete') {
                    $sql = "UPDATE bookings SET 
                            status = 'completed', 
                            updated_at = NOW() 
                            WHERE booking_id = ?";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $booking_id);
                    
                    if ($stmt->execute()) {
                        $info = getBookingInfo($conn, $booking_id);
                        if ($info) {
                            $to = $info['customer_email'];
                            $name = $info['customer_name'];

                            $subject = "Booking Completed - Car Rental System";
                            $body = "
                                <h2>Dear $name,</h2>
                                <p>Your booking has been marked as <b>completed</b>.</p>
                                <p>We hope you enjoyed our service. Thank you for choosing us!</p>
                            ";
                            $mailResult = sendMail($to, $subject, $body);
                        }
                        $success_message = 'Booking completed! ' . ($mailResult === true ? 'Email sent ✅' : 'Email failed ❌: '.$mailResult);
                    }
                    $stmt->close();
                }
                elseif ($action == 'activate') {
                    $sql = "UPDATE bookings SET 
                            status = 'active', 
                            updated_at = NOW() 
                            WHERE booking_id = ?";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $booking_id);
                    
                    if ($stmt->execute()) {
                        $info_message = 'Booking has been activated (vehicle picked up)!';
                    }
                    $stmt->close();
                }
                elseif ($action == 'extend' && isset($_POST['extend_hours'])) {
                    $extend_hours = intval($_POST['extend_hours']);
                    
                    if ($extend_hours > 0 && $extend_hours <= 48) { // Max 48 hours extension
                        // Get current booking info
                        $info = getBookingInfo($conn, $booking_id);
                        
                        if ($info) {
                            // Calculate extension cost (assuming hourly rate or daily rate)
                            $hourly_rate = 100; // You can get this from vehicle table or set a standard rate
                            $extension_cost = $extend_hours * $hourly_rate;
                            
                            // Update end date and total cost
                            $sql = "UPDATE bookings SET 
                                    end_date = DATE_ADD(end_date, INTERVAL ? HOUR),
                                    total_cost = total_cost + ?,
                                    updated_at = NOW()
                                    WHERE booking_id = ?";
                            
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("idi", $extend_hours, $extension_cost, $booking_id);
                            
                            if ($stmt->execute()) {
                                // Send email notification
                                $to = $info['customer_email'];
                                $name = $info['customer_name'];
                                $new_end_date = date("M d, Y H:i", strtotime($info['end_date'] . " +$extend_hours hours"));
                                
                                $subject = "Booking Extended - Car Rental System";
                                $body = "
                                    <h2>Dear $name,</h2>
                                    <p>Your booking has been <b>extended</b> by $extend_hours hours.</p>
                                    <p><b>Extension Details:</b><br>
                                       Extended Hours: $extend_hours hours<br>
                                       New End Date: $new_end_date<br>
                                       Extension Cost: ₱" . number_format($extension_cost, 2) . "
                                    </p>
                                    <p>Please settle the additional payment upon vehicle return.</p>
                                    <p>Thank you for choosing Car Rental System.</p>
                                ";
                                $mailResult = sendMail($to, $subject, $body);
                                
                                $success_message = "Booking extended by $extend_hours hours! Additional cost: ₱" . number_format($extension_cost, 2) . 
                                                 ($mailResult === true ? ' Email sent ✅' : ' Email failed ❌: '.$mailResult);
                            }
                            $stmt->close();
                        }
                    } else {
                        $error_message = 'Invalid extension hours. Please enter between 1-48 hours.';
                    }
                }
            }
            ?>
 
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
                                    <th>Email</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>                        
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch bookings - ORDER BY booking_id DESC for newest to oldest based on booking ID
                                $sql = "SELECT b.*, c.car_name, c.brand 
                                        FROM bookings b 
                                        LEFT JOIN cars c ON b.vehicle_id = c.car_id 
                                        ORDER BY b.booking_id DESC";
                                $result = $conn->query($sql);
                                
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        $status_class = '';
                                        $status_text = ucfirst($row['status']);
                                        
                                        switch($row['status']) {
                                            case 'pending':   $status_class = 'badge-warning'; break;
                                            case 'approved':  $status_class = 'badge-success'; break;
                                            case 'rejected':  $status_class = 'badge-danger'; break;
                                            case 'active':    $status_class = 'badge-info'; break;
                                            case 'completed': $status_class = 'badge-secondary'; break;
                                            case 'cancelled': $status_class = 'badge-dark'; break;
                                            default:          $status_class = 'badge-light';
                                        }
                                        
                                        echo '<tr>';
                                        echo '<td>#BK' . str_pad($row['booking_id'], 3, '0', STR_PAD_LEFT) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['customer_name']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['customer_email']) . '</td>';
                                        echo '<td>' . date('M d, Y', strtotime($row['start_date'])) . '</td>';
                                        echo '<td>' . date('M d, Y', strtotime($row['end_date'])) . '</td>';
                                        echo '<td>₱' . number_format($row['total_cost'], 2) . '</td>';
                                        echo '<td><span class="badge ' . $status_class . '">' . $status_text . '</span></td>';
                                  
                                        echo '<td>';
                                        
                                        // View button - always available
                                        echo '<a href="view_booking.php?id=' . $row['booking_id'] . '" class="btn btn-info btn-sm mr-1 mb-1"><i class="fas fa-eye"></i> View</a>';
                                        
                                        if ($row['status'] == 'pending') {
                                            echo '<button class="btn btn-success btn-sm mr-1 mb-1" onclick="confirmActionWithRefresh(\'accept\', ' . $row['booking_id'] . ', \'' . htmlspecialchars($row['customer_name'], ENT_QUOTES) . '\')"><i class="fas fa-check"></i> Accept</button>';
                                            echo '<button class="btn btn-danger btn-sm mr-1 mb-1" onclick="confirmActionWithRefresh(\'decline\', ' . $row['booking_id'] . ', \'' . htmlspecialchars($row['customer_name'], ENT_QUOTES) . '\')"><i class="fas fa-times"></i> Decline</button>';
                                        }
                                        elseif ($row['status'] == 'approved') {
                                            echo '<button class="btn btn-primary btn-sm mr-1 mb-1" onclick="confirmActionWithRefresh(\'activate\', ' . $row['booking_id'] . ', \'' . htmlspecialchars($row['customer_name'], ENT_QUOTES) . '\')"><i class="fas fa-play"></i> Start</button>';
                                        }
                                        elseif ($row['status'] == 'active') {
                                            // Vehicle picked up - Show Extend and Complete buttons
                                            echo '<button class="btn btn-warning btn-sm mr-1 mb-1" onclick="showExtendModalWithRefresh(' . $row['booking_id'] . ', \'' . htmlspecialchars($row['customer_name'], ENT_QUOTES) . '\')"><i class="fas fa-clock"></i> Extend</button>';
                                            echo '<button class="btn btn-success btn-sm mr-1 mb-1" onclick="confirmActionWithRefresh(\'complete\', ' . $row['booking_id'] . ', \'' . htmlspecialchars($row['customer_name'], ENT_QUOTES) . '\')"><i class="fas fa-flag-checkered"></i> Complete</button>';
                                        }
                                        
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="8" class="text-center">No bookings found</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div> <!-- End of Main Content -->
        
        <!-- Extend Booking Modal -->
        <div class="modal fade" id="extendModal" tabindex="-1" role="dialog" aria-labelledby="extendModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form id="extendForm" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="extendModalLabel">
                                <i class="fas fa-clock text-warning"></i> Extend Booking
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="customerName"><strong>Customer:</strong></label>
                                <input type="text" id="customerName" class="form-control" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label for="extendHours"><strong>Extend by how many hours?</strong></label>
                                <input type="number" 
                                       id="extendHours" 
                                       name="extend_hours" 
                                       class="form-control" 
                                       min="1" 
                                       max="48" 
                                       placeholder="Enter hours (1-48)" 
                                       required>
                                <small class="form-text text-muted">
                                    Maximum extension: 48 hours. Rate: ₱100/hour
                                </small>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Cost Calculation:</strong>
                                <div id="costCalculation">
                                    Enter hours above to see the extension cost
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-clock"></i> Extend Booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>
        <?php include('includes/footer.php'); ?> 
    </div> 

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.all.min.js"></script>
    
    <script>
        // Show success/warning/info/error messages after page actions with auto-refresh
        <?php if (isset($success_message)): ?>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?php echo addslashes($success_message); ?>',
            confirmButtonColor: '#28a745',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then(() => {
            // Auto-refresh after success message
            window.location.href = window.location.pathname;
        });
        <?php endif; ?>

        <?php if (isset($warning_message)): ?>
        Swal.fire({
            icon: 'warning',
            title: 'Booking Declined',
            text: '<?php echo addslashes($warning_message); ?>',
            confirmButtonColor: '#ffc107',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then(() => {
            // Auto-refresh after warning message
            window.location.href = window.location.pathname;
        });
        <?php endif; ?>

        <?php if (isset($info_message)): ?>
        Swal.fire({
            icon: 'info',
            title: 'Booking Activated',
            text: '<?php echo addslashes($info_message); ?>',
            confirmButtonColor: '#17a2b8',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then(() => {
            // Auto-refresh after info message
            window.location.href = window.location.pathname;
        });
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?php echo addslashes($error_message); ?>',
            confirmButtonColor: '#dc3545',
            timer: 4000,
            timerProgressBar: true,
            showConfirmButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then(() => {
            // Auto-refresh after error message
            window.location.href = window.location.pathname;
        });
        <?php endif; ?>

        // Confirmation function for booking actions
        function confirmAction(action, bookingId, customerName) {
            let title, text, confirmButtonText, icon, confirmButtonColor;
            
            switch(action) {
                case 'accept':
                    title = 'Accept Booking?';
                    text = `Are you sure you want to approve the booking for ${customerName}?`;
                    confirmButtonText = 'Yes, Accept it!';
                    icon = 'question';
                    confirmButtonColor = '#28a745';
                    break;
                    
                case 'decline':
                    title = 'Decline Booking?';
                    text = `Are you sure you want to decline the booking for ${customerName}? This action will notify the customer.`;
                    confirmButtonText = 'Yes, Decline it!';
                    icon = 'warning';
                    confirmButtonColor = '#dc3545';
                    break;
                    
                case 'activate':
                    title = 'Start Booking?';
                    text = `Mark ${customerName}'s booking as active? This indicates the vehicle has been picked up.`;
                    confirmButtonText = 'Yes, Start it!';
                    icon = 'info';
                    confirmButtonColor = '#007bff';
                    break;
                    
                case 'complete':
                    title = 'Complete Booking?';
                    text = `Mark ${customerName}'s booking as completed? This indicates the rental period has ended.`;
                    confirmButtonText = 'Yes, Complete it!';
                    icon = 'success';
                    confirmButtonColor = '#28a745';
                    break;
                    
                default:
                    return;
            }
            
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: confirmButtonColor,
                cancelButtonColor: '#6c757d',
                confirmButtonText: confirmButtonText,
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Please wait while we process your request.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Redirect to perform action
                    window.location.href = `?action=${action}&id=${bookingId}`;
                }
            });
        }

        // Show extend modal
        function showExtendModal(bookingId, customerName) {
            document.getElementById('customerName').value = customerName;
            document.getElementById('extendForm').action = `?action=extend&id=${bookingId}`;
            document.getElementById('extendHours').value = '';
            document.getElementById('costCalculation').innerHTML = 'Enter hours above to see the extension cost';
            $('#extendModal').modal('show');
        }

        // Calculate extension cost in real-time
        document.getElementById('extendHours').addEventListener('input', function() {
            const hours = parseInt(this.value) || 0;
            const hourlyRate = 100;
            const totalCost = hours * hourlyRate;
            
            const costDiv = document.getElementById('costCalculation');
            
            if (hours > 0 && hours <= 48) {
                costDiv.innerHTML = `
                    <strong>${hours} hours</strong> × ₱${hourlyRate}/hour = <strong>₱${totalCost.toLocaleString()}</strong>
                `;
                costDiv.className = 'text-success';
            } else if (hours > 48) {
                costDiv.innerHTML = '<span class="text-danger">Maximum 48 hours allowed</span>';
            } else {
                costDiv.innerHTML = 'Enter hours above to see the extension cost';
                costDiv.className = '';
            }
        });

        // Handle extend form submission
        document.getElementById('extendForm').addEventListener('submit', function(e) {
            const hours = parseInt(document.getElementById('extendHours').value) || 0;
            
            if (hours < 1 || hours > 48) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Hours',
                    text: 'Please enter between 1-48 hours for extension.',
                    confirmButtonColor: '#dc3545'
                });
                return false;
            }
            
            // Show confirmation before submitting
            e.preventDefault();
            const customerName = document.getElementById('customerName').value;
            const cost = hours * 100;
            
            Swal.fire({
                title: 'Confirm Extension',
                html: `
                    <strong>Customer:</strong> ${customerName}<br>
                    <strong>Extension:</strong> ${hours} hours<br>
                    <strong>Additional Cost:</strong> ₱${cost.toLocaleString()}<br><br>
                    <small class="text-muted">Customer will be notified via email</small>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Extend Booking!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Close modal and show loading
                    $('#extendModal').modal('hide');
                    Swal.fire({
                        title: 'Processing Extension...',
                        text: 'Please wait while we extend the booking.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit form
                    this.submit();
                }
            });
        });

        // Auto-refresh functionality for real-time updates
        let refreshInterval;
        let isActionInProgress = false;

        // Function to start auto-refresh (every 30 seconds when idle)
        function startAutoRefresh() {
            if (!isActionInProgress) {
                refreshInterval = setInterval(() => {
                    if (!isActionInProgress) {
                        // Silently refresh the page content without showing alerts
                        fetch(window.location.href)
                            .then(response => response.text())
                            .then(html => {
                                const parser = new DOMParser();
                                const newDoc = parser.parseFromString(html, 'text/html');
                                const newTable = newDoc.querySelector('#dataTable tbody');
                                const currentTable = document.querySelector('#dataTable tbody');
                                
                                if (newTable && currentTable) {
                                    // Only update if content has changed
                                    if (newTable.innerHTML !== currentTable.innerHTML) {
                                        currentTable.innerHTML = newTable.innerHTML;
                                        
                                        // Show subtle notification of update
                                        const toast = Swal.mixin({
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 2000,
                                            timerProgressBar: true
                                        });
                                        
                                        toast.fire({
                                            icon: 'info',
                                            title: 'Booking list updated'
                                        });
                                    }
                                }
                            })
                            .catch(error => {
                                console.log('Auto-refresh error:', error);
                            });
                    }
                }, 30000); // Refresh every 30 seconds
            }
        }

        // Function to stop auto-refresh
        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        }

        // Wrap confirmAction with auto-refresh handling
        function confirmActionWithRefresh(action, bookingId, customerName) {
            isActionInProgress = true;
            stopAutoRefresh();
            confirmAction(action, bookingId, customerName);
        }

        // Wrap showExtendModal with auto-refresh handling  
        function showExtendModalWithRefresh(bookingId, customerName) {
            isActionInProgress = true;
            stopAutoRefresh();
            showExtendModal(bookingId, customerName);
        }

        // Enhanced table styling with hover effects and event delegation
        $(document).ready(function() {
            // Start auto-refresh when page loads
            startAutoRefresh();
            
            // Use event delegation for dynamically generated buttons
            $(document).on('mouseenter', '.btn', function() {
                $(this).addClass('shadow-sm');
            }).on('mouseleave', '.btn', function() {
                $(this).removeClass('shadow-sm');
            });
            
            // Resume auto-refresh when modal is closed
            $('#extendModal').on('hidden.bs.modal', function () {
                isActionInProgress = false;
                setTimeout(startAutoRefresh, 1000); // Small delay before resuming
            });
            
            // Ensure buttons are properly initialized
            console.log('Booking management system initialized');
        });
    </script>
 
</body> 
</html>