<?php require_once('header.php'); 
$dao = new DataAccess();
$user_id = $_SESSION['user_id'];

$ids_str = isset($_GET['ids']) ? preg_replace('/[^0-9,]/', '', $_GET['ids']) : '';
if(empty($ids_str)) {
    echo "<script>window.location='bookings.php';</script>";
    exit();
}

// Fetch bookings
$bookings = $dao->getDataJoin(
    array("bookings.*", "menu_packages.package_name"),
    "bookings",
    "customer_id=$user_id AND bookings.id IN ($ids_str) AND payment_status='pending'",
    array("menu_packages" => array("bookings.package_id=menu_packages.id", "leftjoin"))
);

if(empty($bookings)) {
    swalRedirect("No pending bookings found or payment already completed.", "bookings.php", "error", "Error");
    exit();
}

$grand_total = 0;
$total_advance = 0;
foreach($bookings as $bk) {
    $grand_total += $bk['total_amount'];
    $total_advance += $bk['advance_amount'];
}

// Handle payment submission
if(isset($_POST['btnPay'])) {
    $cardName = trim($_POST['cardName'] ?? '');
    $cardNumber = str_replace(' ', '', trim($_POST['cardNumber'] ?? ''));
    $expiry = trim($_POST['expiry'] ?? '');
    $cvv = trim($_POST['cvv'] ?? '');

    $errors = [];
    if(empty($cardName) || !preg_match('/^[a-zA-Z\s]+$/', $cardName)) $errors[] = "Invalid cardholder name.";
    if(!preg_match('/^\d{16}$/', $cardNumber)) $errors[] = "Card number must be 16 digits.";
    if(preg_match('/^(\d{2})\/?(\d{2})$/', $expiry, $matches)) {
        $month = intval($matches[1]);
        $year = intval($matches[2]);
        if($month < 1 || $month > 12) {
            $errors[] = "Expiry month must be between 01 and 12.";
        } else {
            // Assume 21st century (e.g. 26 -> 2026)
            $current_year = intval(date('y'));
            $current_month = intval(date('m'));
            if($year < $current_year || ($year == $current_year && $month < $current_month)) {
                $errors[] = "The card has expired.";
            }
        }
    } else {
        $errors[] = "Invalid expiry date format. Use MM/YY.";
    }
    if(!preg_match('/^\d{3,4}$/', $cvv)) $errors[] = "Invalid CVV.";

    if(!empty($errors)) {
        $error = implode("<br>", $errors);
    } else {
        $con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $sql = "UPDATE bookings SET payment_status='advance_paid', booking_status='approved' WHERE id IN ($ids_str) AND customer_id=$user_id";
        if(mysqli_query($con, $sql)) {
            // Run auto allocation for each booking
            foreach($bookings as $bk) {
                $booking_id = $bk['id'];
                $guests = $bk['guest_count'];
                 $req_chefs = ceil($guests / 350);
                 $req_servers = ceil($guests / 250);
                 $req_sm = $guests >= 1000 ? 2 : 1;
                 $event_date = $bk['event_date'];
                
                $leave_cond = "AND id NOT IN (SELECT staff_id FROM leave_requests WHERE leave_date='$event_date' AND status='approved')";
                $available_chefs = $dao->getData("id", "users", "role='chef' AND availability_status=1 AND active_status=1 $leave_cond LIMIT $req_chefs");
                $available_servers = $dao->getData("id", "users", "role='server' AND availability_status=1 AND active_status=1 $leave_cond LIMIT $req_servers");
                $available_sm = $dao->getData("id", "users", "role='site_manager' AND availability_status=1 AND active_status=1 $leave_cond LIMIT $req_sm");
                
                if(is_array($available_chefs)) foreach($available_chefs as $s) $dao->insert(array("booking_id"=>$booking_id, "user_id"=>$s['id'], "assigned_role"=>"chef", "assignment_status"=>"assigned"), "booking_staff");
                if(is_array($available_servers)) foreach($available_servers as $s) $dao->insert(array("booking_id"=>$booking_id, "user_id"=>$s['id'], "assigned_role"=>"server", "assignment_status"=>"assigned"), "booking_staff");
                if(is_array($available_sm)) foreach($available_sm as $s) $dao->insert(array("booking_id"=>$booking_id, "user_id"=>$s['id'], "assigned_role"=>"site_manager", "assignment_status"=>"assigned"), "booking_staff");
            }

            swalRedirect("Payment Successful! Your bookings are now confirmed and staff have been automatically allocated.", "invoice.php?ids=$ids_str", "success", "Success");
            exit();
        } else {
            $error = "Payment update failed: " . mysqli_error($con);
        }
    }
}
?>

<style>
/* CSS for 3D Card Animation */
.card-container {
    perspective: 1000px;
    width: 350px;
    height: 220px;
    margin: 0 auto 30px auto;
}
.credit-card {
    width: 100%;
    height: 100%;
    position: relative;
    transition: transform 0.8s;
    transform-style: preserve-3d;
}
.card-container.flip .credit-card {
    transform: rotateY(180deg);
}
.card-face {
    position: absolute;
    width: 100%;
    height: 100%;
    backface-visibility: hidden;
    border-radius: 15px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.5);
    background: linear-gradient(135deg, #1f1f1f, #0a0a0a);
    border: 1px solid rgba(212,175,55,0.3);
    color: #fff;
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.card-front::before, .card-back::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPScxMDAlJyBoZWlnaHQ9JzEwMCUnPjxmaWx0ZXIgaWQ9J24nPjxmZVR1cmJ1bGVuY2UgdHlwZT0nZnJhY3RhbE5vaXNlJyBiYXNlRnJlcXVlbmN5PScwLjA1JyB5Q2hhbm5lbFNlbGVjdG9yPSdHJyB2ZXJzaW9uPScxLjEnLz48L2ZpbHRlcj48cmVjdCB3aWR0aD0nMTAwJScgaGVpZ2h0PScxMDAlJyBmaWx0ZXI9J3VybCgjbiknIG9wYWNpdHk9JzAuMDUnLz48L3N2Zz4=');
    pointer-events: none;
    border-radius: 15px;
}
.card-back {
    transform: rotateY(180deg);
    padding: 20px 0;
}
.magnetic-strip {
    background: #000;
    height: 40px;
    width: 100%;
    margin-top: 10px;
}
.cvv-band {
    background: #fff;
    color: #000;
    height: 35px;
    margin: 20px 20px;
    text-align: right;
    padding-right: 15px;
    line-height: 35px;
    font-style: italic;
    border-radius: 4px;
}
.card-chip {
    width: 45px;
    height: 35px;
    background: linear-gradient(135deg, #e0c879, #b8860b);
    border-radius: 5px;
    margin-bottom: 15px;
}
.card-number-display {
    font-size: 1.5rem;
    letter-spacing: 2px;
    font-family: 'Courier New', monospace;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
    margin-bottom: 15px;
}
.card-info {
    display: flex;
    justify-content: space-between;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 1px;
}
.card-info span {
    display: block;
    font-size: 0.6rem;
    color: #aaa;
}
</style>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card mb-5 border-0" style="background: rgba(20,20,20,0.9); box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
            <div class="card-header text-center py-4" style="border-bottom: 1px solid rgba(212,175,55,0.2);">
                <i class="fa fa-credit-card fa-3x mb-3" style="color:var(--primary-gold)"></i>
                <h4 class="brand-font text-gold m-0" style="color:var(--primary-gold)">Secure Payment Checkout</h4>
            </div>
            
            <div class="card-body p-4">
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><i class="fa fa-exclamation-triangle me-2"></i><?= $error ?></div>
                <?php endif; ?>

                <div class="row">
                    <!-- Order Summary -->
                    <div class="col-md-5 mb-4">
                        <h5 class="text-white mb-3">Order Summary</h5>
                        <div class="p-3 rounded" style="background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.05);">
                            <?php foreach($bookings as $i => $bk): ?>
                                <div class="mb-3 border-bottom pb-2" style="border-color: rgba(255,255,255,0.1) !important;">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="badge bg-secondary mb-1">Day <?= $i+1 ?></span><br>
                                            <strong class="text-white"><?= htmlspecialchars($bk['event_type']) ?></strong>
                                            <div class="small text-muted"><?= date('d M Y', strtotime($bk['event_date'])) ?> | <?= $bk['guest_count'] ?> Guests</div>
                                        </div>
                                        <div class="text-end">
                                            <span class="d-block text-white">₹<?= number_format($bk['total_amount'],2) ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="d-flex justify-content-between mt-3">
                                <span class="text-muted">Grand Total:</span>
                                <span class="text-white fw-bold">₹<?= number_format($grand_total, 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mt-2 pt-2 border-top" style="border-color: rgba(212,175,55,0.3) !important;">
                                <span class="text-gold fw-bold" style="color:var(--primary-gold)">Advance to Pay Now (50%):</span>
                                <span class="text-success fw-bold fs-5">₹<?= number_format($total_advance, 2) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Form & Interactive Card -->
                    <div class="col-md-7">
                        
                        <!-- 3D Card Animation -->
                        <div class="card-container" id="cardContainer">
                            <div class="credit-card">
                                <div class="card-face card-front">
                                    <div>
                                        <div class="d-flex justify-content-between">
                                            <div class="card-chip"></div>
                                            <i class="fab fa-cc-visa fa-2x" id="cardIcon" style="opacity: 0.8;"></i>
                                        </div>
                                        <div class="card-number-display" id="cardNumberDisplay">#### #### #### ####</div>
                                    </div>
                                    <div class="card-info">
                                        <div style="max-width: 220px; overflow: hidden;">
                                            <span>Card Holder</span>
                                            <div id="cardNameDisplay" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">JOHN DOE</div>
                                        </div>
                                        <div style="min-width: 60px; text-align: right;">
                                            <span>Expires</span>
                                            <div id="cardExpiryDisplay">MM/YY</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-face card-back">
                                    <div class="magnetic-strip"></div>
                                    <div class="cvv-band" id="cardCvvDisplay">***</div>
                                </div>
                            </div>
                        </div>

                        <form method="POST" id="paymentForm">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Cardholder Name</label>
                                <input type="text" name="cardName" id="cardName" class="form-control" style="background:var(--card-bg); color:white;" required placeholder="JOHN DOE" maxlength="50">
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Card Number</label>
                                <input type="text" name="cardNumber" id="cardNumber" class="form-control" style="background:var(--card-bg); color:white;" required placeholder="XXXX XXXX XXXX XXXX" maxlength="19">
                            </div>
                            <div class="row">
                                <div class="col-6 mb-4">
                                    <label class="form-label text-muted small">Expiry Date</label>
                                    <input type="text" name="expiry" id="cardExpiry" class="form-control" style="background:var(--card-bg); color:white;" required placeholder="MM/YY" maxlength="5">
                                </div>
                                <div class="col-6 mb-4">
                                    <label class="form-label text-muted small">CVV</label>
                                    <input type="password" name="cvv" id="cardCvv" class="form-control" style="background:var(--card-bg); color:white;" required placeholder="XXX" maxlength="4">
                                </div>
                            <div class="mb-3 d-flex align-items-start align-items-sm-center">
                                <input type="checkbox" name="terms" id="termsCheck" required style="width: 18px; height: 18px; accent-color: var(--primary-gold); cursor: pointer; flex-shrink: 0;" class="me-2 mt-1 mt-sm-0">
                                <label class="text-muted small mb-0" for="termsCheck" style="cursor: pointer; user-select: none;">
                                    I agree to the <a href="javascript:void(0);" onclick="showTermsModal()" class="text-gold fw-bold" style="color: var(--primary-gold); text-decoration: underline;">Terms & Conditions</a>, including the cancellation and booking policy.
                                </label>
                            </div>
                            
                            <button type="submit" name="btnPay" class="btn btn-gold w-100 py-3 fw-bold" style="font-size: 1.1rem;">
                                <i class="fa fa-lock me-2"></i> Pay ₹<?= number_format($total_advance, 2) ?> & Confirm
                            </button>
                            <p class="text-center text-muted small mt-3"><i class="fa fa-shield-alt"></i> Payments are secure and encrypted.</p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cardContainer = document.getElementById('cardContainer');
    const nameInput = document.getElementById('cardName');
    const numberInput = document.getElementById('cardNumber');
    const expiryInput = document.getElementById('cardExpiry');
    const cvvInput = document.getElementById('cardCvv');
    
    const nameDisplay = document.getElementById('cardNameDisplay');
    const numberDisplay = document.getElementById('cardNumberDisplay');
    const expiryDisplay = document.getElementById('cardExpiryDisplay');
    const cvvDisplay = document.getElementById('cardCvvDisplay');
    const cardIcon = document.getElementById('cardIcon');

    // Real-time updates
    nameInput.addEventListener('input', (e) => {
        let val = e.target.value.replace(/[^a-zA-Z\s]/g, ''); // strip numbers/symbols
        e.target.value = val;
        nameDisplay.textContent = val.toUpperCase() || 'JOHN DOE';
    });

    numberInput.addEventListener('input', (e) => {
        let val = e.target.value.replace(/\D/g, ''); // strip non-digits
        
        // Update Card Icon based on first digit
        if(val.startsWith('4')) {
            cardIcon.className = 'fab fa-cc-visa fa-2x';
        } else if(val.startsWith('5')) {
            cardIcon.className = 'fab fa-cc-mastercard fa-2x';
        } else if(val.startsWith('3')) {
            cardIcon.className = 'fab fa-cc-amex fa-2x';
        } else {
            cardIcon.className = 'fa fa-credit-card fa-2x';
        }

        let formatted = val.match(/.{1,4}/g);
        let displayStr = formatted ? formatted.join(' ') : '';
        e.target.value = displayStr; // format input box
        
        // Pad with # for visual completeness on the card
        let visualStr = val.padEnd(16, '#');
        let visualFormatted = visualStr.match(/.{1,4}/g).join(' ');
        numberDisplay.textContent = val.length === 0 ? '#### #### #### ####' : visualFormatted;
    });

    expiryInput.addEventListener('input', (e) => {
        let val = e.target.value.replace(/\D/g, '');
        if (val.length > 2) {
            val = val.substring(0, 2) + '/' + val.substring(2, 4);
        }
        e.target.value = val;
        expiryDisplay.textContent = val || 'MM/YY';
    });

    cvvInput.addEventListener('input', (e) => {
        let val = e.target.value.replace(/\D/g, '');
        e.target.value = val;
        cvvDisplay.textContent = '*'.repeat(val.length) || '***';
    });

    // Flip animation
    cvvInput.addEventListener('focus', () => {
        cardContainer.classList.add('flip');
    });
    cvvInput.addEventListener('blur', () => {
        cardContainer.classList.remove('flip');
    });

    // Luhn Algorithm Validator
    function isValidLuhn(num) {
        let arr = (num + '')
            .split('')
            .reverse()
            .map(x => parseInt(x));
        let sum = arr.reduce((acc, val, i) => (i % 2 !== 0 ? acc + (val * 2 > 9 ? val * 2 - 9 : val * 2) : acc + val), 0);
        return sum % 10 === 0;
    }

    // Form submission validation
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        const errors = [];
        
        const termsCheck = document.getElementById('termsCheck');
        if(!termsCheck.checked) {
            errors.push("You must agree to the Terms & Conditions to proceed.");
        }

        const cName = nameInput.value.trim();
        if(!/^[a-zA-Z\s]+$/.test(cName)) errors.push("Cardholder name must contain only letters.");

        const cNum = numberInput.value.replace(/\s/g, '');
        if(cNum.length !== 16) {
            errors.push("Card number must be 16 digits.");
        }

        const exp = expiryInput.value.trim();
        if(!/^\d{2}\/\d{2}$/.test(exp)) {
            errors.push("Expiry date must be in MM/YY format.");
        }

        const cvv = cvvInput.value.trim();
        if(cvv.length < 3 || cvv.length > 4) errors.push("CVV must be 3 or 4 digits.");

        if(errors.length > 0) {
            e.preventDefault();
            Swal.fire({
                title: 'Validation Error',
                html: errors.join('<br>'),
                icon: 'error',
                background: '#1e1e1e',
                color: '#fff',
                confirmButtonColor: '#d4af37'
            });
        }
    });
});
</script>

<?php require_once('footer.php'); ?>
