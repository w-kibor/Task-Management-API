<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Task Management Dashboard</title>
    
    <!-- Styles -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/task-dashboard.js'])
    @else
        @vite(['resources/css/app.css'])
    @endif
    
    <style>
        .task-card {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            padding: 1.5rem;
            border: 1px solid #e3e3e0;
            border-radius: 0.5rem;
            background: white;
            transition: all 0.2s;
        }

        .task-card:hover {
            border-color: #1b1b18;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .priority-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .priority-high {
            background-color: #fff2f2;
            color: #f53003;
        }

        .priority-medium {
            background-color: #fff8f0;
            color: #f8b803;
        }

        .priority-low {
            background-color: #f3f3f0;
            color: #706f6c;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: #e3e3e0;
            color: #1b1b18;
        }

        .status-in_progress {
            background-color: #fff8f0;
            color: #f8b803;
        }

        .status-done {
            background-color: #f0f5f0;
            color: #2d7c2d;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: 1px solid #1b1b18;
            border-radius: 0.375rem;
            background: white;
            color: #1b1b18;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.15s;
        }

        .btn:hover {
            background-color: #1b1b18;
            color: white;
        }

        .btn-primary {
            background-color: #1b1b18;
            color: white;
            border-color: #1b1b18;
        }

        .btn-primary:hover {
            background-color: #000;
            border-color: #000;
        }

        .btn-danger {
            border-color: #f53003;
            color: #f53003;
        }

        .btn-danger:hover {
            background-color: #f53003;
            color: white;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-input,
        .form-select {
            padding: 0.75rem;
            border: 1px solid #e3e3e0;
            border-radius: 0.375rem;
            font-size: 1rem;
            font-family: inherit;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #1b1b18;
            box-shadow: 0 0 0 3px rgba(27, 27, 24, 0.1);
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .alert-success {
            background-color: #f0f5f0;
            color: #2d7c2d;
            border: 1px solid #dde9dd;
        }

        .alert-error {
            background-color: #fff2f2;
            color: #f53003;
            border: 1px solid #ffd9d9;
        }

        .alert-info {
            background-color: #f0f3f8;
            color: #1b4db8;
            border: 1px solid #dde3f0;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #706f6c;
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .spinner {
            display: inline-block;
            width: 1.5rem;
            height: 1.5rem;
            border: 2px solid #e3e3e0;
            border-top-color: #1b1b18;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .tabs {
            display: flex;
            gap: 1rem;
            border-bottom: 1px solid #e3e3e0;
            margin-bottom: 1.5rem;
        }

        .tab-button {
            padding: 0.75rem 1rem;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            color: #706f6c;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            transition: all 0.2s;
        }

        .tab-button.active {
            color: #1b1b18;
            border-bottom-color: #1b1b18;
        }

        .tab-button:hover {
            color: #1b1b18;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }

        .report-card {
            padding: 1.5rem;
            border: 1px solid #e3e3e0;
            border-radius: 0.5rem;
            text-align: center;
        }

        .report-card h4 {
            margin: 0 0 0.5rem 0;
            font-size: 0.875rem;
            color: #706f6c;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .report-card .value {
            font-size: 2rem;
            font-weight: bold;
            color: #1b1b18;
        }

        .filter-controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .filter-controls select {
            padding: 0.5rem 0.75rem;
            border: 1px solid #e3e3e0;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body class="bg-[#FDFDFC] text-[#1b1b18] p-4 md:p-8">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold mb-2">📋 Task Manager</h1>
            <p class="text-gray-600">Manage your tasks with priority levels and status tracking</p>
        </div>

        <!-- Alerts Container -->
        <div id="alerts-container"></div>

        <!-- Tabs Navigation -->
        <div class="tabs">
            <button class="tab-button active" data-tab="tasks">
                Tasks
            </button>
            <button class="tab-button" data-tab="report">
                Daily Report
            </button>
        </div>

        <!-- Tasks Tab -->
        <div id="tasks-tab" class="tab-content active">
            <!-- Create Task Form -->
            <div class="mb-6 p-6 bg-white border border-gray-200 rounded-md">
                <h2 class="text-xl font-semibold mb-4">Create New Task</h2>
                <form id="createTaskForm">
                    <div class="grid-2">
                        <div class="form-group">
                            <label for="taskTitle" class="font-medium mb-1">Task Title *</label>
                            <input 
                                type="text" 
                                id="taskTitle" 
                                class="form-input"
                                placeholder="e.g., Prepare quarterly report"
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label for="taskDueDate" class="font-medium mb-1">Due Date *</label>
                            <input 
                                type="date" 
                                id="taskDueDate" 
                                class="form-input"
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label for="taskPriority" class="font-medium mb-1">Priority *</label>
                            <select id="taskPriority" class="form-select" required>
                                <option value="">Select priority...</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="form-group flex justify-end">
                            <button type="submit" class="btn btn-primary" id="createTaskBtn">
                                Create Task
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Filter Tasks -->
            <div class="filter-controls">
                <select id="statusFilter" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="done">Done</option>
                </select>
                <button class="btn" onclick="app.loadTasks()">Refresh</button>
            </div>

            <!-- Tasks List -->
            <div id="tasksList" class="space-y-4">
                <div class="loading">
                    <div class="spinner"></div>
                </div>
            </div>
        </div>

        <!-- Report Tab -->
        <div id="report-tab" class="tab-content">
            <div class="mb-6 p-6 bg-white border border-gray-200 rounded-md">
                <h2 class="text-xl font-semibold mb-4">Daily Task Report</h2>
                <div class="form-group" style="max-width: 300px;">
                    <label for="reportDate" class="font-medium mb-1">Select Date *</label>
                    <input 
                        type="date" 
                        id="reportDate" 
                        class="form-input"
                    >
                    <button class="btn btn-primary mt-2" onclick="app.generateReport()">
                        Generate Report
                    </button>
                </div>
            </div>

            <!-- Report Display -->
            <div id="reportDisplay"></div>
        </div>
    </div>

    <script>
        // Task Management App
        const app = {
            apiBase: '/api/tasks',
            
            init() {
                this.setupTabNavigation();
                this.loadTasks();
                this.setupFormHandlers();
                this.setMinDate();
            },

            setMinDate() {
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('taskDueDate').min = today;
                document.getElementById('reportDate').valueAsDate = new Date();
            },

            setupTabNavigation() {
                document.querySelectorAll('.tab-button').forEach(button => {
                    button.addEventListener('click', () => {
                        const tabName = button.dataset.tab;
                        this.switchTab(tabName);
                    });
                });
            },

            switchTab(tabName) {
                // Hide all tabs
                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.remove('active');
                });
                document.querySelectorAll('.tab-button').forEach(button => {
                    button.classList.remove('active');
                });

                // Show selected tab
                document.getElementById(tabName + '-tab').classList.add('active');
                event.target.classList.add('active');
            },

            setupFormHandlers() {
                document.getElementById('createTaskForm').addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.createTask();
                });

                document.getElementById('statusFilter').addEventListener('change', () => {
                    this.loadTasks();
                });
            },

            async createTask() {
                const title = document.getElementById('taskTitle').value;
                const dueDate = document.getElementById('taskDueDate').value;
                const priority = document.getElementById('taskPriority').value;

                try {
                    const response = await fetch(this.apiBase, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ title, due_date: dueDate, priority })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        const errorMsg = data.message || 'Failed to create task';
                        this.showAlert(errorMsg, 'error');
                        return;
                    }

                    this.showAlert('Task created successfully!', 'success');
                    document.getElementById('createTaskForm').reset();
                    this.loadTasks();
                } catch (error) {
                    this.showAlert('Error creating task: ' + error.message, 'error');
                }
            },

            async loadTasks() {
                const tasksList = document.getElementById('tasksList');
                tasksList.innerHTML = '<div class="loading"><div class="spinner"></div></div>';

                try {
                    const statusFilter = document.getElementById('statusFilter').value;
                    const url = statusFilter ? `${this.apiBase}?status=${statusFilter}` : this.apiBase;
                    
                    const response = await fetch(url);
                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Failed to load tasks');
                    }

                    const tasks = data.data || [];
                    
                    if (tasks.length === 0) {
                        tasksList.innerHTML = `
                            <div class="empty-state">
                                <div class="empty-state-icon">📭</div>
                                <p>No tasks found. Create one to get started!</p>
                            </div>
                        `;
                        return;
                    }

                    tasksList.innerHTML = tasks.map(task => this.renderTask(task)).join('');
                } catch (error) {
                    tasksList.innerHTML = `
                        <div class="alert alert-error">
                            <strong>Error:</strong> ${error.message}
                        </div>
                    `;
                }
            },

            renderTask(task) {
                const canDelete = task.status === 'done';
                const canProgress = task.status !== 'done';
                const nextStatus = task.status === 'pending' ? 'in_progress' : 'done';

                return `
                    <div class="task-card">
                        <div class="flex justify-between items-start">
                            <div style="flex: 1;">
                                <h3 class="text-lg font-semibold mb-2">${this.escapeHtml(task.title)}</h3>
                                <div class="flex gap-2 flex-wrap mb-3">
                                    <span class="priority-badge priority-${task.priority}">
                                        ${task.priority.charAt(0).toUpperCase() + task.priority.slice(1)}
                                    </span>
                                    <span class="status-badge status-${task.status}">
                                        ${task.status === 'in_progress' ? 'In Progress' : task.status.charAt(0).toUpperCase() + task.status.slice(1)}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600">
                                    📅 Due: <strong>${new Date(task.due_date).toLocaleDateString()}</strong>
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Created: ${new Date(task.created_at).toLocaleDateString()}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-2 flex-wrap">
                            ${canProgress ? `
                                <button 
                                    class="btn"
                                    onclick="app.updateTaskStatus(${task.id}, '${nextStatus}')"
                                >
                                    ${nextStatus === 'in_progress' ? '▶ Start' : '✓ Complete'}
                                </button>
                            ` : ''}
                            ${canDelete ? `
                                <button 
                                    class="btn btn-danger"
                                    onclick="app.deleteTask(${task.id})"
                                >
                                    🗑 Delete
                                </button>
                            ` : ''}
                        </div>
                    </div>
                `;
            },

            async updateTaskStatus(taskId, newStatus) {
                try {
                    const response = await fetch(`${this.apiBase}/${taskId}/status`, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ status: newStatus })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.showAlert(data.message || 'Failed to update task status', 'error');
                        return;
                    }

                    this.showAlert('Task status updated!', 'success');
                    this.loadTasks();
                } catch (error) {
                    this.showAlert('Error updating task: ' + error.message, 'error');
                }
            },

            async deleteTask(taskId) {
                if (!confirm('Are you sure you want to delete this task?')) {
                    return;
                }

                try {
                    const response = await fetch(`${this.apiBase}/${taskId}`, {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json' }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.showAlert(data.message || 'Failed to delete task', 'error');
                        return;
                    }

                    this.showAlert('Task deleted successfully!', 'success');
                    this.loadTasks();
                } catch (error) {
                    this.showAlert('Error deleting task: ' + error.message, 'error');
                }
            },

            async generateReport() {
                const dateInput = document.getElementById('reportDate').value;
                const reportDisplay = document.getElementById('reportDisplay');

                if (!dateInput) {
                    this.showAlert('Please select a date', 'error');
                    return;
                }

                reportDisplay.innerHTML = '<div class="loading"><div class="spinner"></div></div>';

                try {
                    const response = await fetch(`${this.apiBase}/report?date=${dateInput}`);
                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Failed to generate report');
                    }

                    reportDisplay.innerHTML = this.renderReport(data);
                } catch (error) {
                    reportDisplay.innerHTML = `
                        <div class="alert alert-error">
                            <strong>Error:</strong> ${error.message}
                        </div>
                    `;
                }
            },

            renderReport(reportData) {
                const summary = reportData.data?.summary || {};
                const date = reportData.data?.date || 'N/A';

                if (Object.keys(summary).length === 0) {
                    return `
                        <div class="alert alert-info">
                            📊 No tasks found for ${date}
                        </div>
                    `;
                }

                let html = `
                    <h3 class="text-2xl font-bold mb-4">📊 Report for ${date}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                `;

                // Create priority sections
                const priorities = ['high', 'medium', 'low'];
                priorities.forEach(priority => {
                    const counts = summary[priority] || { pending: 0, in_progress: 0, done: 0 };
                    const total = counts.pending + counts.in_progress + counts.done;
                    
                    html += `
                        <div class="p-6 bg-white border border-gray-200 rounded-md">
                            <h4 class="text-lg font-semibold mb-3 capitalize">
                                ${priority} Priority
                            </h4>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <span class="text-gray-600">Pending:</span>
                                    <strong class="float-right">${counts.pending}</strong>
                                </div>
                                <div>
                                    <span class="text-gray-600">In Progress:</span>
                                    <strong class="float-right">${counts.in_progress}</strong>
                                </div>
                                <div>
                                    <span class="text-gray-600">Done:</span>
                                    <strong class="float-right">${counts.done}</strong>
                                </div>
                                <hr style="margin: 0.5rem 0; border-color: #e3e3e0;">
                                <div>
                                    <span class="font-semibold">Total:</span>
                                    <strong class="float-right">${total}</strong>
                                </div>
                            </div>
                        </div>
                    `;
                });

                html += '</div>';
                return html;
            },

            showAlert(message, type = 'info') {
                const alertsContainer = document.getElementById('alerts-container');
                const alertId = 'alert-' + Date.now();
                
                const alert = document.createElement('div');
                alert.id = alertId;
                alert.className = `alert alert-${type}`;
                alert.textContent = message;
                
                alertsContainer.appendChild(alert);
                
                // Auto-remove after 4 seconds
                setTimeout(() => {
                    alert.remove();
                }, 4000);
            },

            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        };

        // Initialize app when DOM is ready
        document.addEventListener('DOMContentLoaded', () => app.init());
    </script>
</body>
</html>
