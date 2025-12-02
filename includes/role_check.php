<?php
if ($_SESSION['role'] !== 'admin') {
    die("Access denied. Admins only.");
}
