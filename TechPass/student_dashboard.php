<?php
session_start();

// If they don't have a badge, kick them back to the login page!
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login_page.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Dashboard - TechPass</title>
<style>
/* --- ORIGINAL STYLES RESTORED --- */
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, Helvetica, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
.dashboard-container { max-width: 1200px; margin: 0 auto; }

/* Header */
.header { background: white; padding: 20px 30px; border-radius: 15px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.header-left h1 { font-size: 24px; color: #2d3748; margin-bottom: 5px; }
.header-left p { color: #718096; font-size: 14px; }
.header-right { display: flex; gap: 15px; align-items: center; }
.user-info { text-align: right; }
.user-name { font-weight: 600; color: #2d3748; font-size: 14px; }
.user-number { font-size: 12px; color: #718096; }
.logout-btn { background: #e53e3e; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; }
.logout-btn:hover { background: #c53030; }

/* Grid & Cards */
.dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; margin-bottom: 25px; }
.card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e2e8f0; }
.card-title { font-size: 18px; font-weight: 700; color: #2d3748; }
.view-all { color: #667eea; text-decoration: none; font-size: 13px; font-weight: 600; }
.view-all:hover { text-decoration: underline; }

/* Tables & Lists */
.fee-table { width: 100%; border-collapse: collapse; }
.fee-table th { text-align: left; padding: 12px; background: #f7fafc; color: #4a5568; font-size: 13px; font-weight: 600; }
.fee-table td { padding: 12px; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
.status-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
.status-paid { background: #c6f6d5; color: #22543d; }
.status-pending { background: #fed7d7; color: #742a2a; }
.status-partial { background: #feebc8; color: #7c2d12; }
.pay-btn { background: #667eea; color: white; border: none; padding: 6px 16px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; }
.pay-btn:hover { background: #5a67d8; }

.event-item { padding: 15px; border-left: 4px solid #667eea; background: #f7fafc; margin-bottom: 12px; border-radius: 8px; }
.event-date { font-size: 12px; color: #718096; font-weight: 600; margin-bottom: 5px; }
.event-title { font-size: 15px; font-weight: 700; color: #2d3748; margin-bottom: 4px; }
.event-location { font-size: 13px; color: #4a5568; }
.event-mandatory { display: inline-block; background: #fc8181; color: white; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; margin-left: 8px; }

.announcement-item { padding: 15px; background: #fffaf0; border-left: 4px solid #ed8936; margin-bottom: 12px; border-radius: 8px; }
.announcement-title { font-size: 14px; font-weight: 700; color: #2d3748; margin-bottom: 6px; }
.announcement-content { font-size: 13px; color: #4a5568; line-height: 1.5; margin-bottom: 6px; }
.announcement-date { font-size: 11px; color: #718096; }

/* Summary & Actions */
.summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px; }
.summary-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.summary-label { font-size: 13px; color: #718096; margin-bottom: 8px; font-weight: 600; }
.summary-value { font-size: 28px; font-weight: 700; color: #2d3748; }
.summary-card.total { border-left: 4px solid #667eea; }
.summary-card.paid { border-left: 4px solid #48bb78; }
.summary-card.pending { border-left: 4px solid #f56565; }

.quick-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.action-btn { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 18px; border-radius: 12px; cursor: pointer; font-weight: 600; font-size: 14px; text-align: center; transition: transform 0.2s; }
.action-btn:hover { transform: translateY(-3px); }

.loading { text-align: center; padding: 40px; color: #718096; }
.empty-state { text-align: center; padding: 30px; color: #a0aec0; font-size: 14px; }

@media (max-width: 968px) {
    .dashboard-grid { grid-template-columns: 1fr; }
    .summary-grid { grid-template-columns: 1fr; }
    .header { flex-direction: column; gap: 15px; text-align: center; }
    .header-right { flex-direction: column; }
}
</style>
</head>
<body>

<div class="dashboard-container">
    <div class="header">
        <div class="header-left">
            <h1>Student Dashboard</h1>
            <p>Welcome back! Here's your account overview</p>
        </div>
        <div class="header-right">
            <div class="user-info">
                <div class="user-name" id="userName">Loading...</div>
                <div class="user-number" id="userNumber">-</div>
            </div>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-card total">
            <div class="summary-label">TOTAL FEES</div>
            <div class="summary-value" id="totalFees">₱0.00</div>
        </div>
        <div class="summary-card paid">
            <div class="summary-label">PAID</div>
            <div class="summary-value" id="totalPaid">₱0.00</div>
        </div>
        <div class="summary-card pending">
            <div class="summary-label">BALANCE</div>
            <div class="summary-value" id="totalBalance">₱0.00</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Payment Status</h2>
                    <a href="#" class="view-all">View History</a>
                </div>
                <div id="paymentTable" class="loading">Loading payments...</div>
            </div>

            <div class="card" style="margin-top: 25px;">
                <div class="card-header">
                    <h2 class="card-title">Quick Actions</h2>
                </div>
                <div class="quick-actions">
                    <button class="action-btn" onclick="window.location.href='Request.html'">
                        Submit Request
                    </button>
                    <button class="action-btn" onclick="openPaymentModal()">
                        Upload Payment
                    </button>
                    <button class="action-btn" onclick="window.location.href='announcement.html'">
                        View Announcements
                    </button>
                    <button class="action-btn" onclick="window.location.href='Contact.html'">
                        Contact Us
                    </button>
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Upcoming Events</h2>
                </div>
                <div id="eventsList" class="loading">Loading events...</div>
            </div>

            <div class="card" style="margin-top: 25px;">
                <div class="card-header">
                    <h2 class="card-title">Announcements</h2>
                    <a href="announcement.html" class="view-all">View All</a>
                </div>
                <div id="announcementsList" class="loading">Loading announcements...</div>
            </div>
        </div>
    </div>
</div>

<script>
// --- FIXED SCRIPT (Safe Paths & Logic) ---

// 1. Check Session (php folder)
fetch('php/check_session.php')
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            // If not logged in, go to login_page.html (Same folder)
            window.location.href = 'login_page.html';
            return;
        }
        
        // Display User Info
        document.getElementById('userName').textContent = data.user.full_name;
        document.getElementById('userNumber').textContent = data.user.student_number + ' • ' + data.user.program;
        
        // Load the Tables
        loadDashboardData();
    })
    .catch(error => {
        console.error('Session check error:', error);
        // On crash, go to login
        window.location.href = 'login_page.html';
    });

// 2. Load Data (php folder)
function loadDashboardData() {
    fetch('php/get_dashboard.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPayments(data.data.payments);
                displayEvents(data.data.events);
                displayAnnouncements(data.data.announcements);
                updateSummary(data.data.payments);
            }
        })
        .catch(error => {
            console.error('Error loading dashboard:', error);
        });
}

function displayPayments(payments) {
    const container = document.getElementById('paymentTable');
    if (!payments || payments.length === 0) {
        container.innerHTML = '<div class="empty-state">No payment records found</div>';
        return;
    }
    
    let html = `
        <table class="fee-table">
            <thead>
                <tr>
                    <th>Fee Type</th>
                    <th>Amount</th>
                    <th>Paid</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    payments.forEach(payment => {
        const amount = parseFloat(payment.amount);
        const paid = parseFloat(payment.paid || 0);
        const balance = amount - paid;
        
        let status = 'Pending';
        let statusClass = 'status-pending';
        
        if (paid >= amount) {
            status = 'Paid';
            statusClass = 'status-paid';
        } else if (paid > 0) {
            status = 'Partial';
            statusClass = 'status-partial';
        }
        
        html += `
            <tr>
                <td><strong>${payment.fee_name}</strong><br><small>${payment.semester} Semester</small></td>
                <td>₱${amount.toFixed(2)}</td>
                <td>₱${paid.toFixed(2)}</td>
                <td><span class="status-badge ${statusClass}">${status}</span></td>
                <td>
                    ${balance > 0 ? `<button class="pay-btn" onclick="openPaymentModal('${payment.fee_name}', ${balance})">Pay ₱${balance.toFixed(2)}</button>` : '-'}
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

function displayEvents(events) {
    const container = document.getElementById('eventsList');
    if (!events || events.length === 0) {
        container.innerHTML = '<div class="empty-state">No upcoming events</div>';
        return;
    }
    
    let html = '';
    events.forEach(event => {
        const date = new Date(event.start_date);
        const dateStr = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        
        html += `
            <div class="event-item">
                <div class="event-date">${dateStr}${event.start_time ? ' • ' + event.start_time : ''}</div>
                <div class="event-title">
                    ${event.event_name}
                    ${event.is_mandatory == 1 ? '<span class="event-mandatory">MANDATORY</span>' : ''}
                </div>
                <div class="event-location">${event.location || 'TBA'}</div>
            </div>
        `;
    });
    container.innerHTML = html;
}

function displayAnnouncements(announcements) {
    const container = document.getElementById('announcementsList');
    if (!announcements || announcements.length === 0) {
        container.innerHTML = '<div class="empty-state">No announcements</div>';
        return;
    }
    
    let html = '';
    announcements.slice(0, 3).forEach(announcement => {
        const date = new Date(announcement.post_date);
        const dateStr = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        
        html += `
            <div class="announcement-item">
                <div class="announcement-title">${announcement.title}</div>
                <div class="announcement-content">${announcement.content.substring(0, 100)}...</div>
                <div class="announcement-date">${dateStr}</div>
            </div>
        `;
    });
    container.innerHTML = html;
}

function updateSummary(payments) {
    let totalFees = 0;
    let totalPaid = 0;
    
    if (payments) {
        payments.forEach(payment => {
            totalFees += parseFloat(payment.amount);
            totalPaid += parseFloat(payment.paid || 0);
        });
    }
    
    const balance = totalFees - totalPaid;
    document.getElementById('totalFees').textContent = '₱' + totalFees.toFixed(2);
    document.getElementById('totalPaid').textContent = '₱' + totalPaid.toFixed(2);
    document.getElementById('totalBalance').textContent = '₱' + balance.toFixed(2);
}

function openPaymentModal(feeName, amount) {
    alert('Payment upload feature coming soon!\n\nFee: ' + (feeName || 'Select') + '\nAmount: ₱' + (amount ? amount.toFixed(2) : '0.00'));
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        // Safe path to logout
        window.location.href = 'php/logout.php';
    }
}
</script>

</body>
</html>