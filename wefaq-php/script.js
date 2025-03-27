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
    const addProjectBtn = document.querySelector('.add-project');
    const cancelProjectBtn = document.getElementById('cancelProject');
    const closeModalBtn = document.querySelector('.close-modal');

    // Project data storage
    let projects = JSON.parse(localStorage.getItem('projects')) || [];

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

    // Handle form submission
    if (projectForm) {
        projectForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Get form values
            const projectName = document.getElementById('projectName').value;
            const projectDescription = document.getElementById('projectDescription').value;

            // Create new project object
            const newProject = {
                id: Date.now(),
                name: projectName,
                description: projectDescription,
                tasks: [],
                members: [],
                created: new Date().toISOString()
            };

            // Add to projects array
            projects.push(newProject);

            // Save to localStorage
            localStorage.setItem('projects', JSON.stringify(projects));

            // Add to sidebar list
            const projectItem = document.createElement('li');
            projectItem.innerHTML = `
                <a href="project-${newProject.id}.html">
                    <i class="fas fa-folder"></i>
                    <span>${newProject.name}</span>
                </a>
            `;

            // Insert before the "Add Project" button
            const projectsList = document.getElementById('projectsList');
            const addProjectItem = projectsList.querySelector('.add-project').parentElement;
            projectsList.insertBefore(projectItem, addProjectItem);

            // Close modal
            closeModal();

            // Create and navigate to project page
            window.location.href = `project-${newProject.id}.html`;
        });
    }

    // Load existing projects
    function loadProjects() {
        if (projectsList) {
            projects.forEach(project => {
                const projectItem = document.createElement('li');
                projectItem.innerHTML = `
                    <a href="project-${project.id}.html">
                        <i class="fas fa-folder"></i>
                        <span>${project.name}</span>
                    </a>
                `;

                // Insert before the "Add Project" button
                const addProjectItem = projectsList.querySelector('.add-project').parentElement;
                projectsList.insertBefore(projectItem, addProjectItem);
            });
        }
    }

    // Initialize projects
    loadProjects();

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
        const addTaskForm = document.getElementById('addTaskForm');
        if (addTaskForm) {
            addTaskForm.addEventListener('submit', (e) => {
                e.preventDefault();

                // Get form values
                const taskName = document.getElementById('taskName').value;
                const taskDeadline = document.getElementById('taskDeadline').value;

                // Log the values (replace this with your logic to save the task)
                console.log('Task Name:', taskName);
                console.log('Task Deadline:', taskDeadline);

                // Close the modal
                closeGenericModal('addTaskModal');

                // Clear the form fields
                addTaskForm.reset();
            });
        }
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
// Modal Handling
function openGenericModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
    }
}

function closeGenericModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}
