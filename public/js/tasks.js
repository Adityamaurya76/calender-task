
(function() {
    'use strict';
    
    let retryCount = 0;
    const maxRetries = 50; 
    let $selectedDateText, $openAddModal, $taskListContainer, $taskList, $searchTasks, $clearSearch;
    let $customDateRange, $priorityFilters, $categoryFilters, $statusFilters;
    let calendar, selectedDate = null;
    let activePriority = '';
    let activeCategory = '';
    let activeStatus = '';
    let currentRequest = null;
    
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    function initCalendar() {
        const FullCalendar = window.FullCalendar || (typeof FullCalendar !== 'undefined' ? FullCalendar : null);
        
        if (!FullCalendar || typeof jQuery === 'undefined' || typeof bootstrap === 'undefined') {
            retryCount++;
            if (retryCount < maxRetries) {
                setTimeout(initCalendar, 100);
                return;
            } else {
                console.error('Libraries failed to load');
                return;
            }
        }

        $(function(){
            $selectedDateText = $('#selected-date-text');
            $openAddModal = $('#openAddModal');
            $taskListContainer = $('#taskListContainer');
            $taskList = $('#task-list');
            $searchTasks = $('#searchTasks');
            $clearSearch = $('#clearSearch');
            $customDateRange = $('#customDateRange');
            $priorityFilters = $('#priorityFilters');
            $categoryFilters = $('#categoryFilters');
            $statusFilters = $('#statusFilters');
            
            // Setup CSRF token once
            const csrfToken = (window.Laravel && window.Laravel.csrfToken) ? window.Laravel.csrfToken : $('meta[name="csrf-token"]').attr('content');
            if (csrfToken) {
                $.ajaxSetup({
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
            }

            const calendarEl = document.getElementById('calendar');
            if (!calendarEl) {
                console.error('Calendar element not found');

                return;
            }

            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 'auto',
                contentHeight: 'auto',
                selectable: true,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth'
                },
                events: {
                    url: '/tasks',
                    method: 'GET',
                    failure: function(error) {
                        console.error('Failed to load calendar events:', error);
                    }
                },
                dateClick: function(info) {
                    selectDate(info.dateStr);
                },
                eventClick: function(info) {
                    selectDate(info.event.startStr);
                }
            });

            try {
                calendar.render();
                const today = new Date();
                const todayStr = formatDate(today);
                selectDate(todayStr);
            } catch (error) {
                console.error('Error rendering calendar:', error);
            }

            function selectDate(dateStr) {
                selectedDate = dateStr;
                $selectedDateText.text(dateStr);
                $openAddModal.prop('disabled', false);
                $taskListContainer.show();
                loadTasksForDate(dateStr);
            }
            
            function formatDate(date) {

                return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
            }

            // Filter button handlers with event delegation
            $priorityFilters.on('click', '.filter-btn', function(){
                const $btn = $(this);
                const priority = $btn.data('priority');
                if (activePriority === priority) {
                    activePriority = '';
                    $btn.removeClass('active');
                } else {
                    activePriority = priority;
                    $priorityFilters.find('.filter-btn').removeClass('active');
                    $btn.addClass('active');
                }
                if(selectedDate) loadTasksForDate(selectedDate);
            });

            $categoryFilters.on('click', '.filter-btn', function(){
                const $btn = $(this);
                const category = $btn.data('category');
                if (activeCategory === category) {
                    activeCategory = '';
                    $btn.removeClass('active');
                } else {
                    activeCategory = category;
                    $categoryFilters.find('.filter-btn').removeClass('active');
                    $btn.addClass('active');
                }
                if(selectedDate) loadTasksForDate(selectedDate);
            });

            $statusFilters.on('click', '.filter-btn', function(){
                const $btn = $(this);
                const status = $btn.data('status');
                if (activeStatus === status) {
                    activeStatus = '';
                    $btn.removeClass('active');
                } else {
                    activeStatus = status;
                    $statusFilters.find('.filter-btn').removeClass('active');
                    $btn.addClass('active');
                }
                if(selectedDate) loadTasksForDate(selectedDate);
            });

            $(document).on('click', '.date-range-btn', function(){
                const $btn = $(this);
                const range = $btn.data('range');
                activeDateRange = range;
                $('.date-range-btn').removeClass('active');
                $btn.addClass('active');
                
                if (range === 'custom') {
                    $customDateRange.addClass('show');
                } else {
                    $customDateRange.removeClass('show');
                    if(selectedDate) loadTasksForDate(selectedDate);
                }
            });
        
            const debouncedSearch = debounce(function() {
                if(selectedDate) loadTasksForDate(selectedDate);
            }, 300);

            $searchTasks.on('input', function(){
                const val = $(this).val();
                $clearSearch.toggle(val.length > 0);
                debouncedSearch();
            });

            $clearSearch.on('click', function(){
                $searchTasks.val('');
                $(this).hide();
                if(selectedDate) loadTasksForDate(selectedDate);
            });

            const $taskModal = $('#taskModal');
            const $taskModalLabel = $('#taskModalLabel');
            const $deleteTaskBtn = $('#deleteTaskBtn');
            const $taskId = $('#taskId');
            const $title = $('#title');
            const $description = $('#description');
            const $dueDate = $('#due_date');
            const $priority = $('#priority');
            const $category = $('#category');
            const $formErrors = $('#formErrors');
            
            $openAddModal.on('click', function(){
                clearModal();
                $taskModalLabel.text('Add Task');
                $deleteTaskBtn.hide();
                $dueDate.val(selectedDate);
                new bootstrap.Modal($taskModal[0]).show();
            });

            // Save task (create or update)
            $('#taskForm').on('submit', function(e){
                e.preventDefault();
                $formErrors.text('');
                const id = $taskId.val();
                const data = {
                    title: $title.val(),
                    description: $description.val(),
                    due_date: $dueDate.val(),
                    priority: $priority.val(),
                    category: $category.val()
                };

                if(!data.due_date){
                    $formErrors.text('Please select due date');
                    return;
                }

                const url = id ? '/tasks/' + id : '/tasks';
                const method = id ? 'PUT' : 'POST';
                
                $.ajax({
                    url: url,
                    method: method,
                    data: data,
                    success: function(res){
                        if(res.status === 'ok'){
                            calendar.refetchEvents();
                            const taskDate = normalizeDate(res.task.due_date);
                            if (taskDate) {
                                selectDate(taskDate);
                            }
                            bootstrap.Modal.getInstance($taskModal[0]).hide();
                        }
                    },
                    error: handleAjaxError
                });
            });

            // delete
            $deleteTaskBtn.on('click', function(){
                const id = $taskId.val();
                if(!id || !confirm('Delete this task?')) return;
                $.ajax({
                    url: '/tasks/' + id,
                    method: 'DELETE',
                    success: function(){
                        calendar.refetchEvents();
                        if(selectedDate) loadTasksForDate(selectedDate);
                        bootstrap.Modal.getInstance($taskModal[0]).hide();
                    }
                });
            });

            function normalizeDate(dateStr) {
                if (!dateStr) return null;
                if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
                    return dateStr;
                }
            
                return dateStr.split(' ')[0].split('T')[0];
            }

            function createTaskCard(t) {
                const completedClass = t.status === 'completed' ? 'task-completed' : '';
                const priorityClass = t.priority ? `priority-${t.priority}` : '';
                const statusIcon = t.status === 'completed' ? '✓' : '⏰';
                const statusText = t.status === 'completed' ? 'Completed' : 'Pending';
                const doneBtnClass = t.status === 'completed' ? 'btn-success' : 'btn-outline-success';
                const doneBtnText = t.status === 'completed' ? 'Done' : 'Mark Done';
                const categoryTag = t.category ? `<span class="task-tag category">${escapeHtml(t.category)}</span>` : '';
                const descriptionDiv = t.description ? `<div class="task-description">${escapeHtml(t.description)}</div>` : '';
                
                return `<div class="task-card ${completedClass}">
                    <div class="task-header">
                        <h6 class="task-title">${escapeHtml(t.title)}</h6>
                    </div>
                    <div class="task-tags">
                        <span class="task-tag ${priorityClass}">${t.priority || 'medium'}</span>
                        ${categoryTag}
                    </div>
                    ${descriptionDiv}
                    <div class="task-footer">
                        <div class="task-status ${t.status}">
                            <span>${statusIcon}</span>
                            <span>${statusText}</span>
                        </div>
                        <div class="task-actions">
                            <button class="btn btn-sm btn-outline-secondary editTaskBtn" data-id="${t.id}">Edit</button>
                            <button class="btn btn-sm btn-outline-danger deleteBtn" data-id="${t.id}">Delete</button>
                            <button class="btn btn-sm ${doneBtnClass} toggleStatusBtn" data-id="${t.id}">${doneBtnText}</button>
                        </div>
                    </div>
                </div>`;
            }

            // Load tasks for sidebar by selected date with filters
            function loadTasksForDate(date){
                const normalizedDate = normalizeDate(date);
                if (!normalizedDate) {
                    console.error('Invalid date:', date);
                    return;
                }

                // Cancel previous request if still pending
                if (currentRequest && currentRequest.readyState !== 4) {
                    currentRequest.abort();
                }

                // Get current filter values
                const priority = activePriority;
                const category = activeCategory;
                const status = activeStatus;
                const search = $searchTasks.val().trim();
                
                $taskList.html('<div class="text-muted">Loading...</div>');

                currentRequest = $.get('/tasks', { date: normalizedDate })
                    .done(function(res){
                        if(res.status !== 'ok'){
                            $taskList.html('<div class="text-danger">Error loading tasks</div>');
                            return;
                        }
                        
                        let tasks = res.tasks || [];
                        
                        // Debug: Log filter state and task count
                        console.log('Filter state:', { priority, category, status, search });
                        console.log('Tasks before filtering:', tasks.length);
                        
                        // Apply all filters in a single pass for better performance
                        if (priority || category || status || search) {
                            const searchLower = search ? search.toLowerCase() : '';
                            const beforeCount = tasks.length;
                            tasks = tasks.filter(t => {
                                if (priority && t.priority !== priority) return false;
                                if (category && t.category !== category) return false;
                                if (status && t.status !== status) return false;
                                if (searchLower) {
                                    const titleMatch = t.title.toLowerCase().includes(searchLower);
                                    const descMatch = t.description && t.description.toLowerCase().includes(searchLower);
                                    if (!titleMatch && !descMatch) return false;
                                }
                                return true;
                            });
                            console.log('Tasks after filtering:', tasks.length, '(filtered from', beforeCount + ')');
                        }

                        if(tasks.length === 0){
                            const hasActiveFilters = priority || category || status || search;
                            const message = hasActiveFilters 
                                ? 'No tasks for this date (try clearing filters)' 
                                : 'No tasks for this date';
                            $taskList.html('<div class="text-muted">' + message + '</div>');
                            return;
                        }

                        // Use array map for better performance than string concatenation
                        const html = tasks.map(createTaskCard).join('');
                        $taskList.html(html);
                    })
                    .fail(function(xhr, status, error){
                        if (status !== 'abort') {
                            console.error('Failed to load tasks:', error);
                            $taskList.html('<div class="text-danger">Failed to load tasks: ' + error + '</div>');
                        }
                    });
            }

            // Event delegation for task list actions (more efficient)
            $taskList.on('click', '.editTaskBtn', function(){
                const id = $(this).data('id');
                $.get('/tasks', { date: selectedDate })
                    .done(function(res){
                        const t = res.tasks.find(x => x.id == id);
                        if(!t) return alert('Task not found');

                        $taskId.val(t.id);
                        $title.val(t.title);
                        $description.val(t.description);
                        $dueDate.val(t.due_date);
                        $priority.val(t.priority);
                        $category.val(t.category);
                        $taskModalLabel.text('Edit Task');
                        $deleteTaskBtn.show();
                        new bootstrap.Modal($taskModal[0]).show();
                    });
            });

            $taskList.on('click', '.deleteBtn', function(){
                const id = $(this).data('id');
                if(!confirm('Delete this task?')) return;
                $.ajax({
                    url: '/tasks/' + id,
                    method: 'DELETE',
                    success: function(){
                        calendar.refetchEvents();
                        if(selectedDate) loadTasksForDate(selectedDate);
                    }
                });
            });

            $taskList.on('click', '.toggleStatusBtn', function(){
                const id = $(this).data('id');
                $.ajax({
                    url: '/tasks/' + id + '/toggle',
                    method: 'PATCH',
                    success: function(){
                        calendar.refetchEvents();
                        if(selectedDate) loadTasksForDate(selectedDate);
                    }
                });
            });

            function clearModal(){
                $taskId.val('');
                $title.val('');
                $description.val('');
                $dueDate.val('');
                $priority.val('medium');
                $category.val('');
                $formErrors.text('');
            }

            function handleAjaxError(xhr){
                if(xhr.status === 422){
                    const errors = xhr.responseJSON.errors;
                    const msg = Object.values(errors).map(arr => arr.join(' ')).join(' ');
                    $formErrors.text(msg);
                } else {
                    $formErrors.text('Request failed. Try again.');
                }
            }

            // Optimized escapeHtml using object lookup
            const htmlEscapes = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
                '/': '&#x2F;',
                '`': '&#x60;',
                '=': '&#x3D;'
            };
            
            function escapeHtml(text){
                if(!text) return '';
                return text.replace(/[&<>"'`=\/]/g, s => htmlEscapes[s]);
            }
        });
    }

    // Start initialization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCalendar);
    } else {
        initCalendar();
    }
})();
