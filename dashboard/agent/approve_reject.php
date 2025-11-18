<?php
session_start();
if ($_SESSION['role'] !== 'agent') {
    header("Location: ../../index.php");
    exit;
}

require_once('../../includes/db_connection.php');
date_default_timezone_set('Africa/Kigali');

// Check if there is a message to display
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Referral System Logic
function applyReferralBonus($clientId, $rechargeAmount, $conn) {
    $sql = "SELECT invitation_code FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $stmt->bind_result($level1ReferrerId);
    $stmt->fetch();
    $stmt->close();

    if ($level1ReferrerId) {
        // Level 1 Bonus (3%)
        $level1Bonus = $rechargeAmount * 0.03;
        $sql = "UPDATE users SET referral_bonus = referral_bonus + ?, balance = balance + ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ddi", $level1Bonus, $level1Bonus, $level1ReferrerId);
        if (!$stmt->execute()) {
            die('Error applying Level 1 bonus: ' . $stmt->error);
        }
        $stmt->close();

        // Level 2 Bonus (1%)
        $sql = "SELECT invitation_code FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $level1ReferrerId);
        $stmt->execute();
        $stmt->bind_result($level2ReferrerId);
        $stmt->fetch();
        $stmt->close();

        if ($level2ReferrerId) {
            $level2Bonus = $rechargeAmount * 0.01;
            $sql = "UPDATE users SET referral_bonus = referral_bonus + ?, balance = balance + ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ddi", $level2Bonus, $level2Bonus, $level2ReferrerId);
            if (!$stmt->execute()) {
                die('Error applying Level 2 bonus: ' . $stmt->error);
            }
            $stmt->close();
        }
    }
}

// Function to handle recharge/withdrawal approval/rejection
function handleTransaction($type, $id, $action, $conn) {
    if ($type === 'recharge') {
        $query = "SELECT client_id, amount FROM recharges WHERE id = ? AND status = 'pending'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($clientId, $amount);
        $stmt->fetch();
        $stmt->close();

        if ($clientId && $amount) {
            $conn->begin_transaction();
            try {
                if ($action === 'approve') {
                    // Approve recharge and update balance
                    $update_query = "UPDATE recharges SET status = 'confirmed', recharge_time = NOW() WHERE id = ?";
                    $stmt = $conn->prepare($update_query);
                    $stmt->bind_param("i", $id);
                    if (!$stmt->execute()) {
                        throw new Exception("Error updating recharge status");
                    }

                    $update_balance_query = "UPDATE users SET balance = balance + ? WHERE id = ?";
                    $stmt = $conn->prepare($update_balance_query);
                    $stmt->bind_param("di", $amount, $clientId);
                    if (!$stmt->execute()) {
                        throw new Exception("Error updating balance");
                    }

                    // Apply referral bonuses
                    applyReferralBonus($clientId, $amount, $conn);

                    // Record transaction
                    $transaction_query = "INSERT INTO transactions (client_id, transaction_type, amount, date) VALUES (?, 'deposit', ?, NOW())";
                    $stmt = $conn->prepare($transaction_query);
                    $stmt->bind_param("id", $clientId, $amount);
                    if (!$stmt->execute()) {
                        throw new Exception("Error recording transaction");
                    }

                    $_SESSION['message'] = "Recharge approved successfully!";
                } else {
                    // Reject recharge
                    $reject_query = "UPDATE recharges SET status = 'rejected' WHERE id = ?";
                    $stmt = $conn->prepare($reject_query);
                    $stmt->bind_param("i", $id);
                    if (!$stmt->execute()) {
                        throw new Exception("Error rejecting recharge");
                    }

                    $_SESSION['message'] = "Recharge rejected!";
                }

                $conn->commit();
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['message'] = "Error: " . $e->getMessage();
            }
        }
    } elseif ($type === 'withdraw') {
        $query = "SELECT client_id, amount FROM withdrawals WHERE id = ? AND status = 'pending'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($clientId, $amount);
        $stmt->fetch();
        $stmt->close();

        if ($clientId && $amount) {
            $conn->begin_transaction();
            try {
                if ($action === 'approve') {
                    $approve_query = "UPDATE withdrawals SET status = 'approved', date = NOW() WHERE id = ?";
                    $stmt = $conn->prepare($approve_query);
                    $stmt->bind_param("i", $id);
                    if (!$stmt->execute()) {
                        throw new Exception("Error approving withdrawal");
                    }

                    $_SESSION['message'] = "Withdrawal approved successfully!";
                } else {
                    // Reject withdrawal
                    $reject_query = "UPDATE withdrawals SET status = 'rejected', date = NOW() WHERE id = ?";
                    $stmt = $conn->prepare($reject_query);
                    $stmt->bind_param("i", $id);
                    if (!$stmt->execute()) {
                        throw new Exception("Error rejecting withdrawal");
                    }

                    $_SESSION['message'] = "Withdrawal rejected!";
                }

                $conn->commit();
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['message'] = "Error: " . $e->getMessage();
            }
        }
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['recharge_id'])) {
        handleTransaction('recharge', intval($_POST['recharge_id']), $_POST['action'], $conn);
    } elseif (isset($_POST['withdrawal_id'])) {
        handleTransaction('withdraw', intval($_POST['withdrawal_id']), $_POST['action'], $conn);
    }
    header("Location: agent_dashboard.php");
    exit;
}
?>
