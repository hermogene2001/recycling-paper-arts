<?php
session_start();
include '../../includes/db_connection.php';
include '../../auth/function.php';

// Ensure that the agent is logged in
if ($_SESSION['role'] !== 'agent') {
    header('Location: ../../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recharge processing
    if (isset($_POST['recharge_id'])) {
        $recharge_id = intval($_POST['recharge_id']);
        $action = $_POST['action'];

        if ($action === 'approve') {
            $sql = "SELECT client_id, amount FROM recharges WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $recharge_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $row = $result->fetch_assoc()) {
                $user_id = $row['client_id'];
                $amount = $row['amount'];

                $conn->begin_transaction();
                try {
                    $sql = "UPDATE recharges SET status = 'confirmed' WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $recharge_id);
                    $stmt->execute();

                    $sql = "UPDATE users SET balance = balance + ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("di", $amount, $user_id);
                    $stmt->execute();

                    $sql = "INSERT INTO transactions (user_id, transaction_type, amount, transaction_date) VALUES (?, 'recharge', ?, NOW())";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("id", $user_id, $amount);
                    $stmt->execute();

                    applyReferralBonus($user_id, $amount, $conn);

                    $conn->commit();
                    $_SESSION['action_message'] = 'Recharge approved and balance updated!';
                } catch (Exception $e) {
                    $conn->rollback();
                    $_SESSION['action_message'] = 'Error processing recharge!';
                }
            }
        } elseif ($action === 'reject') {
            $sql = "UPDATE recharges SET status = 'rejected' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $recharge_id);
            $stmt->execute();
            $_SESSION['action_message'] = 'Recharge rejected!';
        }
        header('Location: agent_dashboard.php');
        exit();
    }

    // Withdrawal processing
    if (isset($_POST['withdrawal_id'])) {
        $withdrawal_id = intval($_POST['withdrawal_id']);
        $action = $_POST['action'];

        if ($action === 'approve') {
            $sql = "SELECT client_id, amount, source FROM withdrawals WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $withdrawal_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $row = $result->fetch_assoc()) {
                $user_id = $row['client_id'];
                $amount = $row['amount'];
                $source = $row['source'];

                $conn->begin_transaction();
                try {
                    if ($source === 'compound') {
                        $sql = "SELECT id, withdrawable_amount FROM compound_investments WHERE client_id = ? AND status = 'completed' LIMIT 1";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $compoundResult = $stmt->get_result();

                        if ($compoundResult && $compoundRow = $compoundResult->fetch_assoc()) {
                            if ($compoundRow['withdrawable_amount'] >= $amount) {
                                $newCompoundBalance = $compoundRow['withdrawable_amount'] - $amount;
                                $sql = "UPDATE compound_investments SET withdrawable_amount = ? WHERE id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("di", $newCompoundBalance, $compoundRow['id']);
                                $stmt->execute();
                            } else {
                                throw new Exception('Insufficient compound balance!');
                            }
                        }
                    } elseif ($source === 'main') {
                        $sql = "SELECT balance FROM users WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $stmt->bind_result($current_balance);
                        $stmt->fetch();
                        $stmt->close();

                        if ($current_balance >= $amount) {
                            $newMainBalance = $mainBalance['balance'] - $amount;
                            $sql = "UPDATE users SET balance = ? WHERE id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("di", $newMainBalance, $user_id);
                            $stmt->execute();
                        } else {
                            throw new Exception('Insufficient main balance!');
                        }
                    }

                    $sql = "UPDATE withdrawals SET status = 'approved' WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $withdrawal_id);
                    $stmt->execute();

                    $sql = "INSERT INTO transactions (user_id, transaction_type, amount, transaction_date) VALUES (?, 'withdrawal', ?, NOW())";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("id", $user_id, $amount);
                    $stmt->execute();

                    $conn->commit();
                    $_SESSION['action_message'] = 'Withdrawal approved!';
                } catch (Exception $e) {
                    $conn->rollback();
                    $_SESSION['action_message'] = $e->getMessage();
                }
            }
        } elseif ($action === 'reject') {
            $sql = "UPDATE withdrawals SET status = 'rejected' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $withdrawal_id);
            $stmt->execute();
            $_SESSION['action_message'] = 'Withdrawal rejected!';
        }
        header('Location: agent_dashboard.php');
        exit();
    }
}
?>
