<?php
/**
 * User Model
 */

class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO users (role_id, first_name, last_name, email, password, phone, birthdate, address, city, state, zip_code, verification_token, otp_code, otp_expires_at, otp_attempts, otp_locked_until) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['role_id'] ?? ROLE_CUSTOMER,
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['phone'] ?? null,
            $data['birthdate'] ?? null,
            $data['address'] ?? null,
            $data['city'] ?? null,
            $data['state'] ?? null,
            $data['zip_code'] ?? null,
            $data['verification_token'] ?? null,
            $data['otp_code'] ?? null,
            $data['otp_expires_at'] ?? null,
            $data['otp_attempts'] ?? 0,
            $data['otp_locked_until'] ?? null
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, city = ?, state = ?, zip_code = ? WHERE id = ?");
        return $stmt->execute([
            $data['first_name'], $data['last_name'], $data['email'],
            $data['phone'], $data['address'], $data['city'],
            $data['state'], $data['zip_code'], $id
        ]);
    }

    public function updateProfileImage($id, $filename) {
        $stmt = $this->pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
        return $stmt->execute([$filename, $id]);
    }

    public function updatePassword($id, $passwordHash) {
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$passwordHash, $id]);
    }

    public function getAll($roleId = null) {
        $sql = "SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id";
        if ($roleId) {
            $sql .= " WHERE u.role_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$roleId]);
        } else {
            $stmt = $this->pdo->query($sql);
        }
        return $stmt->fetchAll();
    }

    public function toggleActive($id) {
        $stmt = $this->pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getByVerificationToken($token) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE verification_token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function markEmailVerified($id) {
        $stmt = $this->pdo->prepare("UPDATE users SET email_verified_at = NOW(), verification_token = NULL, otp_code = NULL, otp_expires_at = NULL, otp_attempts = 0, otp_locked_until = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function setVerificationData($id, $token, $otpCode, $otpExpiresAt) {
        $stmt = $this->pdo->prepare("UPDATE users SET verification_token = ?, otp_code = ?, otp_expires_at = ?, otp_attempts = 0, otp_locked_until = NULL WHERE id = ?");
        return $stmt->execute([$token, $otpCode, $otpExpiresAt, $id]);
    }

    public function setOtpAttempts($id, $attempts) {
        $stmt = $this->pdo->prepare("UPDATE users SET otp_attempts = ? WHERE id = ?");
        return $stmt->execute([$attempts, $id]);
    }

    public function setOtpLockout($id, $lockedUntil) {
        $stmt = $this->pdo->prepare("UPDATE users SET otp_locked_until = ? WHERE id = ?");
        return $stmt->execute([$lockedUntil, $id]);
    }

    public function getByEmailForVerification($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function hasOrders($userId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
        $stmt->execute([(int)$userId]);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    public function deleteUser($userId) {
        $userId = (int)$userId;
        $this->pdo->beginTransaction();
        try {
            // Delete dependent rows that have FK(user_id) without ON DELETE
            $stmt = $this->pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$userId]);

            $stmt = $this->pdo->prepare("DELETE FROM cancel_requests WHERE user_id = ?");
            $stmt->execute([$userId]);

            $stmt = $this->pdo->prepare("DELETE FROM return_requests WHERE user_id = ?");
            $stmt->execute([$userId]);

            // logs.user_id is ON DELETE SET NULL, orders.user_id blocks deletion (checked in controller)
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);

            $ok = $stmt->rowCount() > 0;
            $this->pdo->commit();
            return $ok;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}
