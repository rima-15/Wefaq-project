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
 


      // Load tasks 
        const project_ID = getProjectId();
        if (project_ID) {
            loadTasks(project_ID);
            loadFiles(project_ID);
            fetchDashboardData(project_ID); 

    
        }
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
                 loadFiles(project_ID); 
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
async function handleTaskOperation(operationPromise, modalId, project_ID) {
    let confirmBtn;
    let originalBtnText;

    try {
        // 1. Get the correct confirm button based on modalId
        if (modalId === "deleteTaskModal") {
            confirmBtn = document.getElementById('confirmDeleteTask');
        } else {
            confirmBtn = document.querySelector(`#${modalId} .modal-actions .btn-primary`);
        }

        // 2. Check if button exists
        if (!confirmBtn) {
            throw new Error(`Confirm button not found for modal: ${modalId}`);
        }

        // 3. Store original button state
        originalBtnText = confirmBtn.innerHTML;
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';

        // 4. Execute the operation
        const result = await operationPromise;

        // 5. Refresh data (parallel)
        await Promise.all([
            loadTasks(project_ID),
            fetchDashboardData(project_ID)
        ]);

        // 6. Close modal and reset button
        closeGenericModal(modalId);
        resetButton(confirmBtn, originalBtnText);

        return result;
    } catch (error) {
        console.error('Operation failed:', error);
        
        // 7. Reset button even on error
        if (confirmBtn && originalBtnText) {
            resetButton(confirmBtn, originalBtnText);
        }
        
        throw error;
    }
}

// Helper function to reset button state
function resetButton(button, originalHtml) {
    button.disabled = false;
    button.innerHTML = originalHtml;
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
                    fetchDashboardData(project_ID); 

                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
}

// Assign Task
function assignTask(task_ID) {
    const project_ID = getProjectId();
    const formData = new FormData();
    formData.append('update_task_status', '1');
    formData.append('task_ID', task_ID);
    formData.append('new_status', 'not started');
    formData.append('assign_to_self', '1');

    const operation = fetch('handleProject.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) throw new Error(data.message || 'Failed to assign task');
        return data;
    });

    return handleTaskOperation(operation, 'assignTaskModal', project_ID);
}
// Start Task
function startTask(task_ID) {
    const project_ID = getProjectId();
    const formData = new FormData();
    formData.append('update_task_status', '1');
    formData.append('task_ID', task_ID);
    formData.append('new_status', 'in progress');

    const operation = fetch('handleProject.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) throw new Error(data.message || 'Failed to start task');
        return data;
    });

    return handleTaskOperation(operation, 'startTaskModal', project_ID);
}

// Complete Task
function completeTask(task_ID) {
    const project_ID = getProjectId();
    const formData = new FormData();
    formData.append('update_task_status', '1');
    formData.append('task_ID', task_ID);
    formData.append('new_status', 'completed');

    const operation = fetch('handleProject.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) throw new Error(data.message || 'Failed to complete task');
        return data;
    });

    return handleTaskOperation(operation, 'completeTaskModal', project_ID);
}

// Delete Task
function deleteTask(task_ID) {
    const project_ID = getProjectId();
    const formData = new FormData();
    formData.append('delete_task', '1');
    formData.append('task_ID', task_ID);

    const operation = fetch('handleProject.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Failed to delete task');
             closeGenericModal('deleteTaskModal');

        }
        return data;
    });

    return handleTaskOperation(operation, 'deleteTaskModal', project_ID);
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



function uploadFile() {
    const fileInput = document.getElementById("fileInput");
    const file = fileInput.files[0];
    const project_ID = new URLSearchParams(window.location.search).get('project_ID');

    if (file && project_ID) {
        const formData = new FormData();
        formData.append('upload_file', '1');
        formData.append('project_ID', project_ID);
        formData.append('file', file);

        fetch('handleProject.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                loadFiles(project_ID);
                fileInput.value = "";
            } else {
                throw new Error(data.message || 'File upload failed');
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            alert('Error uploading file: ' + error.message);
        });
    }
}

function loadFiles(project_ID) {
    fetch(`handleProject.php?get_files=1&project_ID=${project_ID}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                renderFiles(data.files);
            } else {
                throw new Error(data.message || 'Failed to load files');
            }
        })
        .catch(error => {
            console.error('Error loading files:', error);
        });
}

function renderFiles(files) {
    const tbody = document.querySelector('#fileTable tbody');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (!files || files.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4">No files found</td></tr>';
        return;
    }

    files.forEach(file => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><a href="download.php?file_id=${file.file_ID}">${file.file_name}</a></td>
            <td>${file.username || 'Unknown'}</td>
            <td>${new Date(file.uploaded_at).toLocaleString()}</td>
<td>
                <i class="fas fa-trash delete-btn" 
                   onclick="openGenericModal('deleteFileModal', ${file.file_ID})"
                   title="Delete file"></i>
            </td>        `;
        tbody.appendChild(row);
    });
}

function deleteFile(fileId) {
    
    fetch('handleProject.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `delete_file=1&file_ID=${fileId}`
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Success - refresh file list
                    closeGenericModal('deleteFileModal');

            const project_ID = new URLSearchParams(window.location.search).get('project_ID');
            loadFiles(project_ID);
            
        } 
    })
    .catch(error => {
        console.error('Error:', error);
        showToast(error.message, 'error');
    });
}


// Activate the confirm delete button
document.addEventListener('DOMContentLoaded', function() {
document.getElementById('confirmDeleteFile')?.addEventListener('click', function() {
    if (currentTaskId) { // Note: currentTaskId is used for both tasks and files in this context
        deleteFile(currentTaskId)
            .then(() => {
                closeGenericModal('deleteFileModal');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete file: ' + error);
            });
    }
});
});

  async function fetchDashboardData(project_ID) {
        try {
            // Fetch project data
            const projectResponse = await fetch(`handleProject.php?get_project=1&project_ID=${project_ID}`);
            if (!projectResponse.ok) throw new Error("Failed to fetch project data");
            const projectData = await projectResponse.json();
            if (!projectData || projectData.error) throw new Error(projectData.error || "Invalid project data");
            
            // Fetch tasks data
            const tasksResponse = await fetch(`handleProject.php?get_tasks=1&project_ID=${project_ID}`);
            if (!tasksResponse.ok) throw new Error("Failed to fetch tasks data");
            const tasksData = await tasksResponse.json();
            if (!tasksData.success) throw new Error(tasksData.message || "Invalid tasks data");
            
            // Fetch team members
            const membersResponse = await fetch(`handleProject.php?get_team_members=1&project_ID=${project_ID}`);
            if (!membersResponse.ok) throw new Error("Failed to fetch team members");
            const membersData = await membersResponse.json();
            if (!membersData.success) throw new Error(membersData.message || "Invalid members data");
            
            // Now create all charts with the fetched data
            createWorkDistributionChart(tasksData.tasks, membersData.members);
            createTaskCompletionChart(tasksData.tasks);
            createDeadlineChart(projectData);
            createTeamContributionChart(tasksData.tasks, membersData.members);
            
        } catch (error) {
            console.error("Error fetching dashboard data:", error);
            alert("Failed to load dashboard data. Please try again later.");
        }
    }

function createWorkDistributionChart(tasks, members) {
    if (!tasks || !members || !Array.isArray(tasks) || !Array.isArray(members)) {
        console.error("Invalid data for work distribution chart");
        return;
    }
    
    // Count tasks per member
    const taskCounts = {};
    members.forEach(member => {
        if (member && member.user_ID) {
            taskCounts[member.user_ID] = {
                count: 0,
                username: member.username || 'Unknown'
            };
        }
    });
    
    tasks.forEach(task => {
        if (task && task.assigned_to && taskCounts[task.assigned_to]) {
            taskCounts[task.assigned_to].count++;
        }
    });
    
    // Prepare data for chart - filter out 0% contributions
    const labels = [];
    const data = [];
    const backgroundColors = [];
    const baseColors = ['#9096DE', '#EED442', '#886B63', '#D3D3D3'];
    
    members.forEach((member, index) => {
        if (!member || !member.user_ID) return;
        
        const username = member.username || 'Member ' + (index + 1);
        const count = taskCounts[member.user_ID]?.count || 0;
        
        // Only add to chart data if count > 0
        if (count > 0) {
            labels.push(username);
            data.push(count);
            
            // Assign colors - first 4 get base colors, others get shades
            if (index < 4) {
                backgroundColors.push(baseColors[index]);
            } else {
                const baseColorIndex = index % 4;
                const shadeFactor = 0.8 + (Math.floor(index / 4) * 0.1);
                backgroundColors.push(shadeColor(baseColors[baseColorIndex], shadeFactor));
            }
        }
    });
    
    // Calculate percentages
    const totalTasks = tasks.length > 0 ? tasks.length : 1;
    const percentages = data.map(count => Math.round((count / totalTasks) * 100));
    
    // Get or create chart
    const chartElement = document.getElementById('workDistributionChart');
    if (chartElement.chart) {
        chartElement.chart.destroy();
    }
    
    // Create chart with custom label formatting
    const workCtx = chartElement.getContext('2d');
    chartElement.chart = new Chart(workCtx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: backgroundColors
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    // Custom legend labels to show all members
                    labels: {
                        generateLabels: function(chart) {
                            // Get default labels
                            const originalLabels = Chart.defaults.plugins.legend.labels.generateLabels(chart);
                            
                            // Create labels for all members, not just those with data
                            return members.map((member, index) => {
                                const username = member.username || 'Member ' + (index + 1);
                                const color = index < 4 ? baseColors[index] : 
                                    shadeColor(baseColors[index % 4], 0.8 + (Math.floor(index / 4) * 0.1));
                                
                                return {
                                    text: username,
                                    fillStyle: color,
                                    hidden: false,
                                    lineWidth: 0,
                                    strokeStyle: color,
                                    borderRadius: 0,
                                    datasetIndex: 0
                                };
                            });
                        }
                    }
                },
                datalabels: {
                    color: '#000',
                    anchor: 'center',
                    align: 'center',
                    formatter: (value, context) => {
                        // Only show percentage if value > 0
                        return value > 0 ? Math.round((value / totalTasks) * 100) + '%' : '';
                    },
                    font: {
                        weight: 'bold'
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

    function createTaskCompletionChart(tasks) {
        if (!tasks || !Array.isArray(tasks)) {
            console.error("Invalid tasks data for completion chart");
            return;
        }

        // Count tasks by status
        const statusCounts = {
            'completed': 0,
            'in progress': 0,
            'not started': 0,
            'unassigned': 0
        };
        
        tasks.forEach(task => {
            if (task && task.status && statusCounts.hasOwnProperty(task.status)) {
                statusCounts[task.status]++;
            }
        });
        
        // Get or create chart
        const chartElement = document.getElementById('taskCompletionChart');
        if (chartElement.chart) {
            chartElement.chart.destroy();
        }
        
        // Create chart
        const taskCtx = chartElement.getContext('2d');
        chartElement.chart = new Chart(taskCtx, {
            type: 'bar',
            data: {
                labels: ['Completed', 'In Progress', 'Not Started', 'Unassigned'],
                datasets: [{
                    data: [
                        statusCounts['completed'],
                        statusCounts['in progress'],
                        statusCounts['not started'],
                        statusCounts['unassigned']
                    ],
                    backgroundColor: ['#9096DE', '#EED442', '#D3D3D3','#886B63']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.raw + ' tasks';
                            }
                        }
                    }
                }
            }
        });
    }

    function createDeadlineChart(projectData) {
        if (!projectData || !projectData.project_deadline || !projectData.created_at) {
            console.error("Missing required data for deadline chart");
            return;
        }
        
        try {
            const deadline = new Date(projectData.project_deadline);
            const today = new Date();
            const createdDate = new Date(projectData.created_at);
            
            if (isNaN(deadline.getTime()) || isNaN(createdDate.getTime())) {
                throw new Error("Invalid date format");
            }
            
            const timeDiff = deadline.getTime() - today.getTime();
            const daysLeft = Math.ceil(timeDiff / (1000 * 3600 * 24));
            
            // Calculate days passed
            const totalDays = Math.ceil((deadline.getTime() - createdDate.getTime()) / (1000 * 3600 * 24));
            const daysPassed = totalDays - daysLeft;
            
            // Ensure we don't have negative values
            const displayDaysLeft = Math.max(0, daysLeft);
            const displayDaysPassed = Math.max(0, Math.min(daysPassed, totalDays));
            
            // Get or create chart
            const chartElement = document.getElementById('deadlineChart');
            if (chartElement.chart) {
                chartElement.chart.destroy();
            }
            
            // Create chart
            const deadlineCtx = chartElement.getContext('2d');
            chartElement.chart = new Chart(deadlineCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Days Passed', 'Days Left'],
                    datasets: [{
                        data: [displayDaysPassed, displayDaysLeft],
                        backgroundColor: ['#9096DE', '#EED442']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    rotation: 270,
                    circumference: 180,
                    plugins: {
                        legend: { 
                            display: true,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'rectRounded',
                                padding: 20,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        datalabels: {
                            color: '#000',
                            anchor: 'center',
                            align: 'center',
                            font: { 
                                size: 14, 
                                weight: 'light' 
                            },
                            formatter: (value) => value + ' days'
                        }
                    },
                    radius: '65%'
                },
                plugins: [ChartDataLabels]
            });
        } catch (error) {
            console.error("Error creating deadline chart:", error);
        }
    }
function createTeamContributionChart(tasks, members) {
    if (!tasks || !members || !Array.isArray(tasks) || !Array.isArray(members)) {
        console.error("Invalid data for team contribution chart");
        return;
    }

    try {
        // Prepare data structure
        const memberData = {};
        members.forEach(member => {
            if (member && member.user_ID) {
                memberData[member.user_ID] = {
                    username: member.username || 'Member',
                    completed: 0,
                    inProgress: 0,
                    notStarted: 0
                };
            }
        });
        
        // Count tasks by status for each member
        tasks.forEach(task => {
            if (task && task.assigned_to && memberData[task.assigned_to]) {
                switch(task.status) {
                    case 'completed':
                        memberData[task.assigned_to].completed++;
                        break;
                    case 'in progress':
                        memberData[task.assigned_to].inProgress++;
                        break;
                    case 'not started':
                        memberData[task.assigned_to].notStarted++;
                        break;
                }
            }
        });
        
        // Prepare chart data - separate arrays for each status
        const labels = members.map(member => 
            member && member.username ? member.username : 'Member'
        );
        const completedData = members.map(member => 
            member && memberData[member.user_ID] ? memberData[member.user_ID].completed : 0
        );
        const inProgressData = members.map(member => 
            member && memberData[member.user_ID] ? memberData[member.user_ID].inProgress : 0
        );
        const notStartedData = members.map(member => 
            member && memberData[member.user_ID] ? memberData[member.user_ID].notStarted : 0
        );
        
        // Get or create chart
        const chartElement = document.getElementById('teamContributionChart');
        if (chartElement.chart) {
            chartElement.chart.destroy();
        }
        
        // Create chart with grouped bars
        const contributionCtx = chartElement.getContext('2d');
        chartElement.chart = new Chart(contributionCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Completed Tasks',
                        data: completedData,
                        backgroundColor: '#9096DE',
                        borderWidth: 1
                    },
                    {
                        label: 'In Progress Tasks',
                        data: inProgressData,
                        backgroundColor: '#EED442',
                        borderWidth: 1
                    },
                    {
                        label: 'Not Started Tasks',
                        data: notStartedData,
                        backgroundColor: '#D3D3D3',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                indexAxis: 'y', // Horizontal bars
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Tasks'
                        },
                        grid: {
                            display: true
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        // This creates space between groups of bars
                        offset: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw}`;
                            }
                        }
                    }
                },
                // These control the spacing between bars
                barPercentage: 0.8,
                categoryPercentage: 0.8
            }
        });
    } catch (error) {
        console.error("Error creating team contribution chart:", error);
    }
}

    // Helper function to create color shades
    function shadeColor(color, percent) {
        if (!color || typeof color !== 'string' || !color.startsWith('#')) {
            return '#cccccc'; // Default gray if invalid color
        }
        
        try {
            // Convert hex to RGB
            let R = parseInt(color.substring(1,3), 16);
            let G = parseInt(color.substring(3,5), 16);
            let B = parseInt(color.substring(5,7), 16);

            // Adjust brightness
            R = parseInt(R * percent);
            G = parseInt(G * percent);
            B = parseInt(B * percent);

            // Ensure values stay within bounds
            R = Math.min(255, Math.max(0, R));
            G = Math.min(255, Math.max(0, G));
            B = Math.min(255, Math.max(0, B));

            // Convert back to hex
            const toHex = (val) => val.toString(16).padStart(2, '0');
            return `#${toHex(R)}${toHex(G)}${toHex(B)}`;
        } catch (e) {
            console.error("Error shading color:", e);
            return color; // Return original if error
        }
    }
document.addEventListener('click', function(e) {
    // Handle all delete buttons with a single listener
    if (e.target.closest('.delete-task-btn')) {
        e.preventDefault();
        const button = e.target.closest('.delete-task-btn');
        const task_ID = button.dataset.taskId;
        
        if (task_ID) {
            deleteTask(task_ID).catch(error => {
                console.error('Delete task failed:', error);
                // Show error to user
                alert('Failed to delete task: ' + error.message);
            });
        }
    }
});

    // Get project ID from URL
    function getProjectId() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('project_ID');
    }
    
    
   


let currentTaskId = null;

function openGenericModal(modalId, taskId = null) {
    currentTaskId = taskId;
    document.getElementById(modalId).style.display = 'block';
}

function closeGenericModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}
