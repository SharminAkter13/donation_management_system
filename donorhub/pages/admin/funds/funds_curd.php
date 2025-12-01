<?php
include("../connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"] ?? "";

    if ($action == "save") {
        $name = $_POST["name"];
        $status = $_POST["status"];
        $amount = $_POST["amount"];

        $stmt = $connection->prepare("INSERT INTO funds (name, status, collected_amount) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $status, $amount);
        if ($stmt->execute()) {
            echo "<span class='text-success'>Fund saved successfully</span>";
        } else {
            echo "<span class='text-danger'>Error: " . $stmt->error . "</span>";
        }
        $stmt->close();
    }

    elseif ($action == "update") {
        $id = $_POST["id"];
        $name = $_POST["name"];
        $status = $_POST["status"];
        $amount = $_POST["amount"];

        $stmt = $connection->prepare("UPDATE funds SET name=?, status=?, collected_amount=? WHERE id=?");
        $stmt->bind_param("ssii", $name, $status, $amount, $id);
        if ($stmt->execute()) {
            echo "<span class='text-success'>Fund updated successfully</span>";
        } else {
            echo "<span class='text-danger'>Error: " . $stmt->error . "</span>";
        }
        $stmt->close();
    }

    elseif ($action == "delete") {
        $id = $_POST["id"];

        $stmt = $connection->prepare("DELETE FROM funds WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "<span class='text-success'>Fund deleted successfully</span>";
        } else {
            echo "<span class='text-danger'>Error: " . $stmt->error . "</span>";
        }
        $stmt->close();
    }
}
?>
