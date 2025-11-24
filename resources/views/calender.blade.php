<!doctype html>
<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8">
    <title>Calendar & Tasks</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet"/>

    <style>
        body { 
            padding: 0;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        .main-container {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        .left-sidebar {
            width: 250px;
            background: #f8f9fa;
            border-right: 1px solid #e0e0e0;
            padding: 20px;
            overflow-y: auto;
        }
        .center-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .top-bar {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            background: white;
        }
        .search-container {
            position: relative;
            margin-bottom: 20px;
        }
        .search-container input {
            width: 100%;
            padding: 10px 40px 10px 36px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        .search-container .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 14px;
            pointer-events: none;
        }
        .search-container .clear-search {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            font-size: 18px;
            display: none;
        }
        .date-range-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .date-range-btn {
            padding: 8px 16px;
            border: none;
            background: #f0f0f0;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }
        .date-range-btn.active {
            background: #007bff;
            color: white;
        }
        .date-range-btn:hover {
            background: #e0e0e0;
        }
        .date-range-btn.active:hover {
            background: #0056b3;
        }
        .calendar-container {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: white;
        }
        #calendar {
            min-height: 500px;
        }
        .fc .fc-toolbar-title { 
            font-size: 1.5rem; 
            font-weight: 600;
        }
        .filter-section {
            margin-bottom: 30px;
        }
        .filter-section h6 {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }
        .filter-buttons {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .filter-btn {
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            text-align: left;
            transition: all 0.2s;
            background: #f0f0f0;
            color: #333;
        }
        .filter-btn:hover {
            background: #e0e0e0;
        }
        .filter-btn.active {
            background: #007bff;
            color: white;
        }
        .filter-btn.priority-low.active {
            background: #28a745;
        }
        .filter-btn.priority-medium.active {
            background: #ffc107;
            color: #333;
        }
        .filter-btn.priority-high.active {
            background: #dc3545;
        }
        .task-list-container {
            width: 400px;
            padding: 20px;
            overflow-y: auto;
            background: #f8f9fa;
            border-left: 1px solid #e0e0e0;
        }
        .task-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: box-shadow 0.2s;
        }
        .task-card:hover {
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }
        .task-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        .task-completed .task-title {
            text-decoration: line-through;
            opacity: 0.6;
        }
        .task-tags {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        .task-tag {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        .task-tag.priority-high {
            background: #fee;
            color: #c33;
        }
        .task-tag.priority-medium {
            background: #fff3cd;
            color: #856404;
        }
        .task-tag.priority-low {
            background: #d4edda;
            color: #155724;
        }
        .task-tag.category {
            background: #e3f2fd;
            color: #1976d2;
        }
        .task-description {
            color: #666;
            font-size: 14px;
            margin-bottom: 12px;
            line-height: 1.5;
        }
        .task-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .task-status {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
        }
        .task-status.pending {
            color: #666;
        }
        .task-status.completed {
            color: #28a745;
        }
        .task-actions {
            display: flex;
            gap: 8px;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 6px;
        }
        .custom-date-range {
            display: none;
            margin-top: 12px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .custom-date-range.show {
            display: block;
        }
        .task-list-container h5 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
    </style>
</head>
<body>
<div class="main-container">
    <div class="left-sidebar">
        <div class="filter-section">
            <h6>Priority</h6>
            <div class="filter-buttons" id="priorityFilters">
                <button type="button" class="filter-btn priority-low" data-priority="low">Low</button>
                <button type="button" class="filter-btn priority-medium" data-priority="medium">Medium</button>
                <button type="button" class="filter-btn priority-high" data-priority="high">High</button>
            </div>
        </div>

        <div class="filter-section">
            <h6>Category</h6>
            <div class="filter-buttons" id="categoryFilters">
                <button type="button" class="filter-btn active" data-category="">All</button>
                @foreach($categories as $c)
                    <button type="button" class="filter-btn" data-category="{{ $c }}">{{ $c }}</button>
                @endforeach
            </div>
        </div>

        <div class="filter-section">
            <h6>Status</h6>
            <div class="filter-buttons" id="statusFilters">
                <button type="button" class="filter-btn active" data-status="">All</button>
                <button type="button" class="filter-btn" data-status="pending">Pending</button>
                <button type="button" class="filter-btn" data-status="completed">Completed</button>
            </div>
        </div>
    </div>

    <!-- Center Area with Calendar and Tasks -->
    <div class="center-area">
        <!-- Top Bar with Search and Date Range -->
        <div class="top-bar">
            <div class="search-container">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchTasks" placeholder="Search tasks...">
                <span class="clear-search" id="clearSearch">×</span>
            </div>

            <div class="date-range-buttons">
                <button type="button" class="date-range-btn active" data-range="today">Today</button>
                <button type="button" class="date-range-btn" data-range="week">Week</button>
                <button type="button" class="date-range-btn" data-range="month">Month</button>
                <button type="button" class="date-range-btn" data-range="custom">Custom</button>
            </div>

            <div class="custom-date-range" id="customDateRange">
                <div style="display: flex; gap: 12px;">
                    <div style="flex: 1;">
                        <label for="customStartDate" class="form-label small">Start Date</label>
                        <input type="date" id="customStartDate" class="form-control form-control-sm">
                    </div>
                    <div style="flex: 1;">
                        <label for="customEndDate" class="form-label small">End Date</label>
                        <input type="date" id="customEndDate" class="form-control form-control-sm">
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar Container -->
        <div class="calendar-container">
            <div style="position: relative;">
                <button id="openAddModal" class="btn btn-primary" style="position: absolute; top: 0; right: 0; z-index: 10;" disabled>+ Add Task</button>
            </div>
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Right Sidebar for Task List (Hidden by default, shown when date is selected) -->
    <div class="task-list-container" id="taskListContainer" style="display: none;">
        <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e0e0e0;">
            <h5 style="margin: 0; font-weight: 600;">Tasks for <span id="selected-date-text">-</span></h5>
        </div>
        <div id="task-list"></div>
    </div>
</div>

@include('task-modal')

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<script>
    window.Laravel = { csrfToken: '{{ csrf_token() }}' }
    
    // Debug: Check what FullCalendar exposes
    window.addEventListener('load', function() {
        if (window.FullCalendar) {
            console.log('FullCalendar.Calendar:', window.FullCalendar.Calendar);
        }
    });
</script>
<script src="{{ asset('js/tasks.js') }}"></script>
</body>
</html>
