<?php
/**
 * ============================================
 * SGC - Header commun (à inclure dans chaque page)
 * ============================================
 */
if (!isset($pageTitle)) $pageTitle = 'SGC';
if (!isset($pageIcon)) $pageIcon = 'fa-home';
?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | SGC</title>
    
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #1a5f2a;
            --primary-light: #2d8a3e;
            --primary-dark: #0d3d16;
            --sidebar-bg: #1a5f2a;
            --sidebar-hover: #2d8a3e;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            overflow-x: hidden;
        }
        
        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--sidebar-bg) 0%, var(--primary-dark) 100%);
            color: white;
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
        }
        
        .sidebar-brand {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand i {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .sidebar-brand h4 {
            font-weight: 700;
            margin: 0;
            font-size: 1.1rem;
        }
        
        .sidebar-brand small {
            opacity: 0.7;
            font-size: 0.75rem;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .sidebar-menu .nav-link {
            color: rgba(255,255,255,0.85);
            padding: 0.85rem 1.5rem;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            text-decoration: none;
        }
        
        .sidebar-menu .nav-link:hover,
        .sidebar-menu .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: #fff;
        }
        
        .sidebar-menu .nav-link i {
            width: 28px;
            font-size: 1.1rem;
        }
        
        .sidebar-menu .nav-link span {
            font-size: 0.9rem;
        }
        
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 1rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            font-size: 0.75rem;
            opacity: 0.6;
        }
        
        /* ===== NAVBAR ===== */
        .navbar-custom {
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            height: 65px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
        }
        
        .page-title {
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 1.25rem;
        }
        
        .page-title i {
            color: var(--primary-color);
            margin-right: 0.75rem;
        }
        
        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .user-dropdown:hover {
            background: #f8f9fa;
        }
        
        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .user-info {
            line-height: 1.2;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: #333;
        }
        
        .user-role {
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: capitalize;
        }
        
        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: 65px;
            padding: 2rem;
            min-height: calc(100vh - 65px);
        }
        
        /* ===== CARDS ===== */
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: none;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 55px;
            height: 55px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .stat-icon.green { background: #d4edda; color: #155724; }
        .stat-icon.blue { background: #cce5ff; color: #004085; }
        .stat-icon.orange { background: #fff3cd; color: #856404; }
        .stat-icon.red { background: #f8d7da; color: #721c24; }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        /* ===== TABLE ===== */
        .data-table-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .data-table-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .data-table-header h5 {
            margin: 0;
            font-weight: 700;
            color: #333;
        }
        
        .table-custom {
            margin-bottom: 0;
        }
        
        .table-custom thead th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
            border: none;
            padding: 1rem 1.5rem;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table-custom tbody td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .table-custom tbody tr:hover {
            background: #f8f9fa;
        }
        
        /* ===== BUTTONS ===== */
        .btn-action {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.2s ease;
            margin: 0 2px;
        }
        
        .btn-view { background: #e3f2fd; color: #1976d2; }
        .btn-edit { background: #fff3e0; color: #f57c00; }
        .btn-delete { background: #ffebee; color: #d32f2f; }
        
        .btn-action:hover {
            transform: scale(1.1);
        }
        
        .btn-add {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(26, 95, 42, 0.3);
            color: white;
        }
        
        /* ===== BADGES ===== */
        .badge-custom {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-actif { background: #d4edda; color: #155724; }
        .badge-inactif { background: #f8d9da; color: #721c24; }
        
        /* Responsive */
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .navbar-custom { left: 0; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>