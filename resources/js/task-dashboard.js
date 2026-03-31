import './bootstrap';

/**
 * Task Management Dashboard
 * Vanilla JavaScript application for managing tasks via REST API
 */

const TaskDashboard = {
    apiBase: '/api/tasks',
    
    /**
     * Initialize the application
     */
    init() {
        this.setupTabNavigation();
        this.loadTasks();
        this.setupFormHandlers();
        this.setMinDate();
        console.log('Task Dashboard initialized');
    },

    /**
     * Set minimum date to today
     */
    setMinDate() {
        const today = new Date().toISOString().split('T')[0];
        const dueDate = document.getElementById('taskDueDate');
        const reportDate = document.getElementById('reportDate');
        
        if (dueDate) dueDate.min = today;
        if (reportDate) reportDate.valueAsDate = new Date();
    },

    /**
     * Setup tab navigation
     */
    setupTabNavigation() {
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', (e) => {
                this.switchTab(e.target.dataset.tab);
            });
        });
    },

    /**
     * Switch between tabs
     */
    switchTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active');
        });

        // Show selected tab
        const tabElement = document.getElementById(tabName + '-tab');
        if (tabElement) {
            tabElement.classList.add('active');
        }
        
        document.querySelector(`[data-tab="${tabName}"]`)?.classList.add('active');
    },

    /**
     * Setup form event handlers
     */
    setupFormHandlers() {
        const createForm = document.getElementById('createTaskForm');
        if (createForm) {
            createForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.createTask();
            });
        }

        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', () => {
                this.loadTasks();
            });
        }
    },

    /**
     * Create a new task
     */
    async createTask() {
        const title = document.getElementById('taskTitle').value.trim();
        const dueDate = document.getElementById('taskDueDate').value;
        const priority = document.getElementById('taskPriority').value;

        // Validation
        if (!title || !dueDate || !priority) {
            this.showAlert('Please fill in all required fields', 'error');
            return;
        }

        try {
            const btn = document.getElementById('createTaskBtn');
            btn.disabled = true;
            btn.textContent = 'Creating...';

            const response = await fetch(this.apiBase, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    title, 
                    due_date: dueDate, 
                    priority 
                })
            });

            const data = await response.json();

            if (!response.ok) {
                const errorMsg = data.message || 'Failed to create task';
                this.showAlert(errorMsg, 'error');
                return;
            }

            this.showAlert('✓ Task created successfully!', 'success');
            document.getElementById('createTaskForm').reset();
            await this.loadTasks();
        } catch (error) {
            this.showAlert('❌ Error creating task: ' + error.message, 'error');
            console.error('Create task error:', error);
        } finally {
            const btn = document.getElementById('createTaskBtn');
            btn.disabled = false;
            btn.textContent = 'Create Task';
        }
    },

    /**
     * Load and display tasks
     */
    async loadTasks() {
        const tasksList = document.getElementById('tasksList');
        if (!tasksList) return;

        tasksList.innerHTML = '<div class="loading"><div class="spinner"></div></div>';

        try {
            const statusFilter = document.getElementById('statusFilter').value;
            const url = statusFilter ? `${this.apiBase}?status=${statusFilter}` : this.apiBase;
            
            const response = await fetch(url);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to load tasks');
            }

            const tasks = Array.isArray(data.data) ? data.data : [];
            
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
                    <strong>❌ Error:</strong> ${error.message}
                </div>
            `;
            console.error('Load tasks error:', error);
        }
    },

    /**
     * Render a single task card
     */
    renderTask(task) {
        const canDelete = task.status === 'done';
        const canProgress = task.status !== 'done';
        const nextStatus = task.status === 'pending' ? 'in_progress' : 'done';
        const nextLabel = nextStatus === 'in_progress' ? '▶ Start' : '✓ Complete';

        const dueDate = new Date(task.due_date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });

        const createdDate = new Date(task.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });

        const statusDisplay = task.status === 'in_progress' ? 'In Progress' : 
                             task.status.charAt(0).toUpperCase() + task.status.slice(1);

        const priorityDisplay = task.priority.charAt(0).toUpperCase() + task.priority.slice(1);

        return `
            <div class="task-card" data-task-id="${task.id}">
                <div class="flex justify-between items-start">
                    <div style="flex: 1;">
                        <h3 class="text-lg font-semibold mb-2">${this.escapeHtml(task.title)}</h3>
                        <div class="flex gap-2 flex-wrap mb-3">
                            <span class="priority-badge priority-${task.priority}">
                                ${priorityDisplay}
                            </span>
                            <span class="status-badge status-${task.status}">
                                ${statusDisplay}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600">
                            📅 Due: <strong>${dueDate}</strong>
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Created: ${createdDate}
                        </p>
                    </div>
                </div>

                <div class="flex gap-2 flex-wrap">
                    ${canProgress ? `
                        <button 
                            class="btn"
                            onclick="TaskDashboard.updateTaskStatus(${task.id}, '${nextStatus}')"
                            title="Move task to ${nextStatus} status"
                        >
                            ${nextLabel}
                        </button>
                    ` : ''}
                    ${canDelete ? `
                        <button 
                            class="btn btn-danger"
                            onclick="TaskDashboard.deleteTask(${task.id})"
                            title="Delete completed task"
                        >
                            🗑 Delete
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    },

    /**
     * Update task status
     */
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

            this.showAlert('✓ Task status updated!', 'success');
            await this.loadTasks();
        } catch (error) {
            this.showAlert('❌ Error updating task: ' + error.message, 'error');
            console.error('Update status error:', error);
        }
    },

    /**
     * Delete a task
     */
    async deleteTask(taskId) {
        if (!confirm('Are you sure you want to delete this task? This action cannot be undone.')) {
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

            this.showAlert('✓ Task deleted successfully!', 'success');
            await this.loadTasks();
        } catch (error) {
            this.showAlert('❌ Error deleting task: ' + error.message, 'error');
            console.error('Delete error:', error);
        }
    },

    /**
     * Generate daily report
     */
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
                    <strong>❌ Error:</strong> ${error.message}
                </div>
            `;
            console.error('Report error:', error);
        }
    },

    /**
     * Render report display
     */
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
            
            const priorityColor = priority === 'high' ? '#f53003' : 
                                 priority === 'medium' ? '#f8b803' : '#706f6c';
            
            html += `
                <div class="report-card" style="border-left: 4px solid ${priorityColor}">
                    <h4 style="color: ${priorityColor}">
                        ${priority.toUpperCase()} PRIORITY
                    </h4>
                    <div style="margin-top: 1rem; text-align: left;">
                        <div style="display: flex; justify-content: space-between; margin: 0.5rem 0; font-size: 0.875rem;">
                            <span>⏳ Pending:</span>
                            <strong>${counts.pending}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin: 0.5rem 0; font-size: 0.875rem;">
                            <span>⚙️ In Progress:</span>
                            <strong>${counts.in_progress}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin: 0.5rem 0; font-size: 0.875rem;">
                            <span>✓ Done:</span>
                            <strong>${counts.done}</strong>
                        </div>
                        <hr style="margin: 0.5rem 0; border: none; border-top: 1px solid #e3e3e0;">
                        <div style="display: flex; justify-content: space-between; font-weight: bold; margin-top: 0.5rem;">
                            <span>TOTAL:</span>
                            <strong style="font-size: 1.25rem;">${total}</strong>
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        return html;
    },

    /**
     * Show alert notification
     */
    showAlert(message, type = 'info') {
        const alertsContainer = document.getElementById('alerts-container');
        if (!alertsContainer) return;

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

    /**
     * Escape HTML special characters
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

// Make TaskDashboard available globally
window.TaskDashboard = TaskDashboard;

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => TaskDashboard.init());
} else {
    TaskDashboard.init();
}
