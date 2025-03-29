// DOM Elements
document.addEventListener('DOMContentLoaded', function() {
    // Skip sidebar initialization if it's being handled by components.js
    // This check prevents conflicts in profile and community pages
    if (!document.querySelector('.sidebar-overlay[data-from="components"]')) {
        // Initialize sidebar elements
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mainContent = document.querySelector('.main-content');

        // Create and append overlay
        const sidebarOverlay = document.createElement('div');
        sidebarOverlay.className = 'sidebar-overlay';
        document.body.appendChild(sidebarOverlay);

        // Toggle sidebar function
        function toggleSidebar() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            // Remove the sidebar-active class from main content since we don't shift it anymore
            mainContent.classList.remove('sidebar-active');
        }

        // Add click event to toggle button
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleSidebar();
            });
        }

        // Close sidebar when clicking overlay
        sidebarOverlay.addEventListener('click', function() {
            toggleSidebar();
        });

        // Close sidebar when pressing Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        });
    }

    // Project Management
    const newProjectModal = document.getElementById('newProjectModal');
    const newProjectForm = document.getElementById('newProjectForm');
 

    // Get project ID from URL
    function getProjectId() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('project_ID');
    }
      // Load tasks 
        const project_ID = getProjectId();
        if (project_ID) 
            loadTasks(project_ID);
    

    // Handle add task form submission
    const addTaskForm = document.getElementById('addTaskForm');
    if (addTaskForm) {
        addTaskForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const project_ID = getProjectId();
            if (project_ID) 
                addTask(project_ID);
        });
    }

    // Set up task action buttons (using event delegation)
// Delete Task
document.getElementById('confirmDeleteTask')?.addEventListener('click', function() {
    if (currentTaskId) {
        deleteTask(currentTaskId)
            .then(() => {
                closeGenericModal('deleteTaskModal');
                const projectId = getProjectId();
                loadTasks(projectId); // Refresh the task list
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
});

// Assign Task
document.getElementById('confirmAssignTask')?.addEventListener('click', function() {
    if (currentTaskId) {
        assignTask(currentTaskId)
            .then(() => {
                closeGenericModal('assignTaskModal');
                const projectId = getProjectId();
                loadTasks(projectId); // Refresh the task list
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
});

// Start Task
document.getElementById('confirmStartTask')?.addEventListener('click', function() {
    if (currentTaskId) {
        startTask(currentTaskId)
            .then(() => {
                closeGenericModal('startTaskModal');
                const projectId = getProjectId();
                loadTasks(projectId); // Refresh the task list
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
});

// Complete Task
document.getElementById('confirmCompleteTask')?.addEventListener('click', function() {
    if (currentTaskId) {
        completeTask(currentTaskId)
            .then(() => {
                closeGenericModal('completeTaskModal');
                const projectId = getProjectId();
                loadTasks(projectId); // Refresh the task list
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
});


  

    // Get modal elements
    const modal = document.getElementById('newProjectModal');
    const closeBtn = document.querySelector('.close-modal');
    const projectForm = document.getElementById('newProjectForm');

    // Function to open modal
    function openModal() {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Prevent scrolling
    }

    // Function to close modal
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = ''; // Restore scrolling
        projectForm.reset(); // Reset form
    }

    // Add click event listeners for all "Add Project" buttons
    document.addEventListener('click', function(e) {
        // Check if the clicked element or its parent is an add-project button
        const addProjectBtn = e.target.closest('.add-project');
        if (addProjectBtn) {
            e.preventDefault();
            openModal();
        }
    });
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Close modal when pressing Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            closeModal();
        }
    });


    // Projects Dropdown
    const projectsDropdown = document.getElementById('projectsDropdown');
    const dropdownIcon = projectsDropdown?.querySelector('.dropdown-icon');
    const projectsList = document.getElementById('projectsList');

    if (projectsDropdown) {
        projectsDropdown.addEventListener('click', (e) => {
            e.preventDefault();
            projectsList.classList.toggle('show');
            dropdownIcon.classList.toggle('open');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!projectsDropdown.contains(e.target) && !projectsList.contains(e.target)) {
                projectsList.classList.remove('show');
                dropdownIcon.classList.remove('open');
            }
        });
    }

    // Tab Navigation
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons and panes
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));

            // Add active class to clicked button and corresponding pane
            button.classList.add('active');
            const tabId = button.dataset.tab;
            document.getElementById(tabId).classList.add('active');
        });
    });

    // Form Validation
    const signupForm = document.getElementById('signupForm');
    const loginForm = document.getElementById('loginForm');

    if (signupForm) {
        signupForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return;
            }

            // Add signup logic here
            console.log('Signup form submitted');
            window.location.href = 'dashboard.html';
        });
    }

    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            // Add login logic here
            console.log('Login form submitted');
            window.location.href = 'dashboard.html';
        });
    }


    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
        }
    });

    // Project Completion Review
    const nextMemberBtn = document.getElementById('nextMember');
    if (nextMemberBtn) {
        nextMemberBtn.addEventListener('click', () => {
            // Add logic to show next member review
            console.log('Show next member review');
        });
    }

    // File Upload
    const uploadFileBtn = document.getElementById('uploadFile');
    if (uploadFileBtn) {
        uploadFileBtn.addEventListener('click', () => {
            // Add file upload logic here
            console.log('Upload file clicked');
        });
    }

    // Add Task
    document.addEventListener('DOMContentLoaded', function() {
        // Add Task Button
        const addTaskBtn = document.getElementById('addTaskBtn');
        if (addTaskBtn) {
            addTaskBtn.addEventListener('click', () => {
                openGenericModal('addTaskModal');
            });
        }

        // Close "Add Task" Modal
        const closeTaskModalBtn = document.querySelector('#addTaskModal .close-modal');
        if (closeTaskModalBtn) {
            console.log('Close button found'); // Debugging

            closeTaskModalBtn.addEventListener('click', () => {
                closeGenericModal("addTaskModal");
            });
        }

        // Close "Add Task" Modal when clicking outside
        window.addEventListener('click', (e) => {
            const addTaskModal = document.getElementById('addTaskModal');
            if (e.target === addTaskModal) {
                closeGenericModal('addTaskModal');
            }
        });

        // Handle "Add Task" Form Submission
    document.getElementById('addTaskForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const project_ID = new URLSearchParams(window.location.search).get('project_ID');
    const formData = new FormData(this); // Automatically captures form inputs
    formData.append('add_task', '1');
    formData.append('project_ID', project_ID);

    fetch('handleProject.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeGenericModal('addTaskModal');
            this.reset();
            loadTasks(project_ID);
        } else {
            alert('Error: ' + (data.message || 'Task creation failed'));
        }
    })
    .catch(error => console.error('Error:', error));
});
    });

    // Search Functionality
    const searchInput = document.querySelector('.search-bar input');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            // Add search logic here
            console.log('Searching for:', searchTerm);
        });
    }

    // Filter Buttons
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            // Add filter logic here
            console.log('Filter applied:', button.textContent);
        });
    });


});
// Task Functions (add these to your existing functions)
function loadTasks(project_ID) {
         console.log('load Tasks ');

    fetch('handleProject.php?get_tasks=1&project_ID=' + project_ID)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                             console.log('load Tasks data success before rederder call');

                    renderTasks(data.tasks);
         console.log('load Tasks data success after rederder call');

                } else {
                    console.error('Error loading tasks:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
}

function renderTasks(tasks) {
     console.log('Tasks data:', tasks);
    const tbody = document.querySelector('.tasks-table tbody');
    tbody.innerHTML = '';

    tasks.forEach(task => {
        const row = document.createElement('tr');
        row.dataset.taskId = task.task_ID;

        // Task name and description
            row.innerHTML = `
        <td>${escapeHtml(task.task_name)}</td>
        <td class='taskDescription'>${escapeHtml(task.task_description)}</td>
        <td>
            ${task.assigned_to ? `
            <div class="member avatar-container">
                ${generateAvatar(task.username, '--task')}
                <span>${task.username || ''}</span>
            </div>
            ` : '<div><span></span></div>'}
        </td>
      <td><span class="status-badge ${getStatusClass(task.status)}">${getStatusText(task.status)}</span></td>
            <td>${formatDate(task.task_deadline)}</td>
            <td class="actions">
                ${getActionButtons(task)}
            </td>
        `;
       

        tbody.appendChild(row);
    });
}
function generateAvatar(username, variant = '') {
    if (!username) return '';
    
    const themeColors = ['#9096DE', '#EED442', '#886B63', '#D3D3D3','#634d47','#b0aeae','#c4c8f2','#fae987'];
    const letter = username.charAt(0).toUpperCase();
    
    // Consistent color based on username hash
    const hash = Array.from(username).reduce((hash, char) => {
        return char.charCodeAt(0) + ((hash << 5) - hash);
    }, 0);
    const color = themeColors[Math.abs(hash) % themeColors.length];
    
    // Size variants
    const sizes = {
        '': 40,
        '--invite': 36,
        '--profile': 80,
        '--message': 32
    };
    
    const size = sizes[variant] || sizes[''];

    return `
        <div class="avatar ${variant}" style="background-color: ${color}; font-size: ${size * 0.4}px">
            ${letter}
        </div>
    `;
}

function getActionButtons(task) {
    let buttons = '';

    // Delete button is always present
    buttons += `
        <button class="btn-icon delete" title="Delete Task" 
                data-task-id="${task.task_ID}" 
                onclick="openGenericModal('deleteTaskModal', ${task.task_ID})">
            <i class="fas fa-trash"></i>
        </button>
    `;

    // Status-specific buttons
    switch (task.status) {
        case 'unassigned':
            buttons = `
                <button class="btn-icon status-icons" title="Choose Task" 
                        data-task-id="${task.task_ID}"
                        onclick="openGenericModal('assignTaskModal', ${task.task_ID})">
                    <i class="fas fa-user-check"></i>
                </button>
            ` + buttons;
            break;
        case 'not started':
            buttons = `
                <button class="btn-icon status-icons" title="Start Task" 
                        data-task-id="${task.task_ID}"
                        onclick="openGenericModal('startTaskModal', ${task.task_ID})">
                    <i class="fas fa-play"></i>
                </button>
            ` + buttons;
            break;
        case 'in progress':
            buttons = `
                <button class="btn-icon status-icons" title="Complete Task" 
                        data-task-id="${task.task_ID}"
                        onclick="openGenericModal('completeTaskModal', ${task.task_ID})">
                    <i class="fas fa-flag-checkered"></i>
                </button>
            ` + buttons;
            break;
    }

    return buttons;
}

function addTask(project_ID) {
    const task_name = document.getElementById('taskName').value;
    const task_description = document.getElementById('taskDescription').value;
    const task_deadline = document.getElementById('taskDeadline').value;

    const formData = new FormData();
    formData.append('add_task', '1');
    formData.append('project_ID', project_ID);
    formData.append('task_name', task_name);
    formData.append('task_description', task_description);
    formData.append('task_deadline', task_deadline);

    fetch('handleProject.php', {
        method: 'POST',
        body: formData
    })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeGenericModal('addTaskModal');
                    document.getElementById('addTaskForm').reset();
                    loadTasks(project_ID);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
}

function assignTask(task_ID) {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('update_task_status', '1');
        formData.append('task_ID', task_ID);
        formData.append('new_status', 'not started');
        formData.append('assign_to_self', '1');

        fetch('handleProject.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resolve(data);
            } else {
                reject(data.message || 'Failed to assign task');
            }
        })
        .catch(error => reject(error));
    });
}
function startTask(task_ID) {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('update_task_status', '1');
        formData.append('task_ID', task_ID);
        formData.append('new_status', 'in progress');

        fetch('handleProject.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resolve(data);
            } else {
                reject(data.message || 'Failed to start task');
            }
        })
        .catch(error => reject(error));
    });
}

function completeTask(task_ID) {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('update_task_status', '1');
        formData.append('task_ID', task_ID);
        formData.append('new_status', 'completed');

        fetch('handleProject.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resolve(data);
            } else {
                reject(data.message || 'Failed to complete task');
            }
        })
        .catch(error => reject(error));
    });
}

function updateTaskStatus(task_ID, new_status) {
    const formData = new FormData();
    formData.append('update_task_status', '1');
    formData.append('task_ID', task_ID);
    formData.append('new_status', new_status);

    fetch('handleProject.php', {
        method: 'POST',
        body: formData
    })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const project_ID = getProjectId();
                    loadTasks(project_ID);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
}

function deleteTask(task_ID) {
   /* if (!confirm('Are you sure you want to delete this task?'))
        return;*/

      return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('delete_task', '1');
        formData.append('task_ID', task_ID);

        fetch('handleProject.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resolve(data);
            } else {
                reject(data.message || 'Failed to delete task');
            }
        })
        .catch(error => reject(error));
    });
}

// Helper functions (add these to your existing helpers)
function getStatusClass(status) {
    switch (status) {
        case 'completed':
            return 'completed-task';
        case 'in progress':
            return 'in-progress';
        case 'not started':
            return 'not-started';
        case 'unassigned':
            return 'pending';
        default:
            return '';
    }
}

function getStatusText(status) {
    switch (status) {
        case 'completed':
            return 'Completed';
        case 'in progress':
            return 'In Progress';
        case 'not started':
            return 'Not Started';
        case 'unassigned':
            return 'Unassigned';
        default:
            return status;
    }
}

function formatDate(dateString) {
    if (!dateString)
        return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {year: 'numeric', month: 'short', day: 'numeric'});
}

function escapeHtml(unsafe) {
    if (!unsafe)
        return '';
    return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
}

let currentTaskId = null;

function openGenericModal(modalId, taskId = null) {
    currentTaskId = taskId;
    document.getElementById(modalId).style.display = 'block';
}
/*
// Modal Handling
function openGenericModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
    }
}
*/
function closeGenericModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}
