<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<style>
    body {
        margin: 0;
        min-height: 100vh;
        font-family: Arial, sans-serif;
        background: linear-gradient(135deg, #00b4db 0%, #36d1dc 50%, #5b86e5 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1f2937;
    }

    .choice-card {
        width: 100%;
        max-width: 420px;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 16px;
        box-shadow: 0 14px 34px rgba(0, 0, 0, 0.2);
        padding: 28px 24px;
    }

    .choice-title {
        margin: 0 0 6px;
        font-size: 32px;
        line-height: 1.2;
    }

    .choice-subtitle {
        margin: 0 0 22px;
        color: #6b7280;
        font-size: 14px;
    }

    .choice-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: 10px;
    }

    .choice-link {
        display: block;
        padding: 12px 14px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        color: #0f172a;
        background: #f1f5f9;
        transition: all 0.2s ease;
    }

    .choice-link:hover {
        background: #2563eb;
        color: #ffffff;
        transform: translateY(-1px);
    }
</style>

<div class="choice-card">
    <h1 class="choice-title">Your Choice</h1>
    <p class="choice-subtitle">Pilih menu yang ingin dibuka</p>

    <ul class="choice-list">
        <li><a class="choice-link" href="<?php echo site_url('/'); ?>">Home</a></li>
        <li><a class="choice-link" href="<?php echo site_url('admin'); ?>">Admin</a></li>
        <li><a class="choice-link" href="<?php echo site_url('/client'); ?>">Display</a></li>
        <li><a class="choice-link" href="<?php echo site_url('auth/logout'); ?>">Logout</a></li>
    </ul>
</div>

